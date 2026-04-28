<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', $this->dashboardData());
    }

    public function storeInventory(Request $request): RedirectResponse
    {
        $data = $this->validateInventoryProduct($request);

        $product = new Product();
        $product->fill($data);
        $product->is_active = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $product->image_url = $request->file('image')->store('inventory-products', 'public');
        }

        $product->save();

        return redirect()->route('admin.home')->with('status', 'Product added successfully.');
    }

    public function updateInventory(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateInventoryProduct($request);

        $product->fill($data);
        $product->is_active = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $product->image_url = $request->file('image')->store('inventory-products', 'public');
        }

        $product->save();

        return redirect()->route('admin.home')->with('status', 'Product updated successfully.');
    }

    public function destroyInventory(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.home')->with('status', 'Product removed successfully.');
    }

    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys($this->allowedOrderStatusTransitions()))],
        ]);

        $currentStatus = $order->status;
        $nextStatus = $validated['status'];
        $allowedTransitions = $this->allowedOrderStatusTransitions()[$currentStatus] ?? [];

        if (! in_array($nextStatus, $allowedTransitions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'That order update is not allowed from the current workflow state.',
            ], 422);
        }

        $order->update([
            'status' => $nextStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order workflow updated successfully.',
            'data' => [
                'order' => $this->orderCard($order->fresh(['user.detail', 'items.product'])),
            ],
        ]);
    }

    public function updateOrderPaymentStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'string', Rule::in(['pending', 'paid', 'failed', 'expired', 'unpaid'])],
        ]);

        $payload = [
            'payment_status' => $validated['payment_status'],
        ];

        if ($validated['payment_status'] === 'paid' && ! $order->payment_paid_at) {
            $payload['payment_paid_at'] = now();
        }

        $order->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully.',
            'data' => [
                'order' => $this->orderCard($order->fresh(['user.detail', 'items.product'])),
            ],
        ]);
    }

    private function dashboardData(): array
    {
        $products = Product::query()->latest('id')->get();
        $orders = Order::with(['user.detail', 'items.product'])->latest('id')->get();
        $customers = User::with('detail')->where('role', 'customer')->latest('user_id')->get();
        $admins = User::with('detail')->where('role', 'admin')->latest('user_id')->get();

        $metrics = [
            ['label' => 'Total products ordered', 'value' => number_format((int) OrderItem::sum('quantity')), 'detail' => 'Count from order items'],
            ['label' => 'Completed orders', 'value' => number_format($orders->where('status', 'delivered')->count()), 'detail' => 'All time completed'],
            ['label' => 'Orders completed this week', 'value' => number_format($this->completedOrdersSince($orders, Carbon::now()->startOfWeek())), 'detail' => 'Current 7-day window'],
            ['label' => 'Orders completed this month', 'value' => number_format($this->completedOrdersSince($orders, Carbon::now()->startOfMonth())), 'detail' => 'Current month total'],
        ];

        $productCards = $products->map(fn (Product $product) => $this->productCard($product))->values()->all();
        $orderCards = $orders->map(fn (Order $order) => $this->orderCard($order))->values()->all();
        $customerCards = $customers->map(fn (User $user) => $this->personCard($user, 'customer'))->values()->all();
        $adminCards = $admins->map(fn (User $user) => $this->personCard($user, 'admin'))->values()->all();
        $notifications = $this->notificationsFromOrders($orders, $customers);
        $weeklyCompletions = $this->weeklyCompletionSeries($orders);
        $productTypeBreakdown = $this->productTypeBreakdown($products);

        return [
            'metrics' => $metrics,
            'products' => $productCards,
            'orders' => $orderCards,
            'customers' => $customerCards,
            'admins' => $adminCards,
            'notifications' => $notifications,
            'weeklyCompletions' => $weeklyCompletions,
            'productTypeBreakdown' => $productTypeBreakdown,
            'reportPayload' => [
                'metrics' => $metrics,
                'products' => $productCards,
                'orders' => $orderCards,
                'customers' => $customerCards,
                'admins' => $adminCards,
                'notifications' => $notifications,
                'weeklyCompletions' => $weeklyCompletions,
                'productTypeBreakdown' => $productTypeBreakdown,
            ],
            'sidebarCounts' => [
                'dashboard' => '*',
                'inventory' => $products->count(),
                'orders' => $orders->count(),
                'customers' => $customers->count(),
                'notifications' => $notifications->count(),
            ],
        ];
    }

    private function validateInventoryProduct(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'product_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function productCard(Product $product): array
    {
        return [
            'id' => $product->id,
            'product_id' => $product->product_id ?? $product->id,
            'name' => $product->product_name,
            'product_name' => $product->product_name,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $product->price,
            'formatted_price' => 'PHP ' . number_format((float) $product->price, 2),
            'image_url' => $this->resolveImageUrl($product->image_url),
            'is_active' => (bool) $product->is_active,
            'status' => $product->is_active ? 'active' : 'inactive',
        ];
    }

    private function orderCard(Order $order): array
    {
        $customerName = $order->user?->detail?->name ?? 'Unknown customer';
        $containsCustom = $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom');
        $nextWorkflowStep = $this->nextWorkflowStep($order, $containsCustom);
        $itemLines = $order->items->map(function (OrderItem $item): array {
            $detailParts = array_filter([
                $item->size ? 'Size ' . $item->size : null,
                $item->flavor ? 'Flavor ' . $item->flavor : null,
                $item->item_type === 'custom' ? 'Custom brief included' : null,
            ]);

            return [
                'detail' => implode(' | ', $detailParts),
                'summary' => $item->quantity . ' x ' . ($item->product?->product_name ?? $item->product_name ?? 'Product'),
            ];
        })->values();

        $customItems = $order->items
            ->filter(fn (OrderItem $item) => $item->item_type === 'custom')
            ->map(function (OrderItem $item): array {
                return [
                    'dedication_message' => $item->dedication_message,
                    'design_description' => $item->design_description,
                    'flavor' => $item->flavor,
                    'image_url' => $this->resolveImageUrl($item->image_url) ?: asset('images/bakerdan/Cake_Celebration.png'),
                    'name' => $item->product_name ?: 'Custom Celebration Order',
                    'quantity' => $item->quantity,
                    'size' => $item->size,
                ];
            })
            ->values();

        return [
            'amount' => 'PHP ' . number_format((float) $order->total_amount, 2),
            'contains_custom' => $containsCustom,
            'custom_items' => $customItems->all(),
            'customer' => $customerName,
            'id' => $order->id,
            'item_lines' => $itemLines->all(),
            'items' => $itemLines->pluck('summary')->join(', '),
            'next_status' => $nextWorkflowStep['status'] ?? null,
            'next_status_label' => $nextWorkflowStep['label'] ?? null,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'payment_reference' => $order->payment_reference,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $this->paymentStatusLabel($order->payment_status),
            'placed_at' => $order->created_at?->format('M d, Y h:i A'),
            'status' => $order->status,
            'status_label' => $this->orderStatusLabel($order->status, $containsCustom),
            'workflow_note' => $containsCustom
                ? ($order->payment_status === 'paid'
                    ? 'Review the custom brief before moving this order into production.'
                    : 'Wait for payment confirmation, then review the custom brief before baking.')
                : 'Move the order through the normal bakery production flow.',
        ];
    }

    private function personCard(User $user, string $role): array
    {
        $detail = $user->detail;

        return [
            'id' => $user->user_id,
            'role' => $role,
            'name' => $detail?->name ?? 'Unnamed user',
            'username' => $detail?->username ?? '',
            'age' => $detail?->age ?? null,
            'email' => $detail?->email ?? '',
            'contact' => $detail?->contact ?? '',
            'address' => $detail?->address ?? '',
            'status' => $user->is_active ? 'active' : 'inactive',
        ];
    }

    private function notificationsFromOrders($orders, $customers)
    {
        $recentOrders = $orders->take(3)->map(function (Order $order): array {
            return [
                'id' => $order->id,
                'customer_name' => $order->user?->detail?->name ?? 'Customer',
                'message' => 'Order #' . $order->id . ' is now ' . str_replace('_', ' ', $order->status) . '.',
                'date' => $order->updated_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            ];
        });

        $newCustomers = $customers->take(2)->map(function (User $user): array {
            return [
                'id' => $user->user_id,
                'customer_name' => $user->detail?->name ?? 'Customer',
                'message' => 'New customer account is active.',
                'date' => $user->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            ];
        });

        return $recentOrders->concat($newCustomers)->values();
    }

    private function weeklyCompletionSeries($orders): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $days = collect(range(0, 6))->map(function (int $offset) use ($start): array {
            $day = $start->copy()->addDays($offset);

            return [
                'label' => $day->format('D'),
                'date' => $day->toDateString(),
            ];
        });

        return $days->map(function (array $entry) use ($orders): array {
            $entry['value'] = $orders->filter(function (Order $order) use ($entry): bool {
                return $order->status === 'delivered' && $order->updated_at?->toDateString() === $entry['date'];
            })->count();

            unset($entry['date']);

            return $entry;
        })->all();
    }

    private function productTypeBreakdown($products): array
    {
        return $products
            ->groupBy(fn (Product $product) => $product->category)
            ->map(fn ($group, string $category): array => [
                'label' => $category,
                'value' => $group->count(),
            ])
            ->values()
            ->all();
    }

    private function completedOrdersSince($orders, Carbon $threshold): int
    {
        return $orders->filter(function (Order $order) use ($threshold): bool {
            return $order->status === 'delivered' && $order->updated_at && $order->updated_at->greaterThanOrEqualTo($threshold);
        })->count();
    }

    private function allowedOrderStatusTransitions(): array
    {
        return [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];
    }

    private function nextWorkflowStep(Order $order, bool $containsCustom): ?array
    {
        return match ($order->status) {
            'pending' => [
                'status' => 'processing',
                'label' => $containsCustom ? 'Review and Start' : 'Start Processing',
            ],
            'processing' => [
                'status' => 'shipped',
                'label' => $containsCustom ? 'Mark Ready' : 'Mark Shipped',
            ],
            'shipped' => [
                'status' => 'delivered',
                'label' => 'Mark Delivered',
            ],
            default => null,
        };
    }

    private function orderStatusLabel(string $status, bool $containsCustom): string
    {
        return match ($status) {
            'pending' => $containsCustom ? 'Pending Review' : 'Pending',
            'processing' => $containsCustom ? 'In Production' : 'Processing',
            'shipped' => $containsCustom ? 'Ready for Release' : 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'unpaid' => 'Unpaid',
            default => 'Pending',
        };
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match (strtolower((string) $paymentMethod)) {
            'gcash' => 'GCash',
            'maya', 'paymaya' => 'Maya',
            default => 'Payment pending',
        };
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }
}
