<?php

namespace App\Http\Controllers;

use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $data = $this->dashboardData();
        return view('admin.dashboard', $data);
    }
    /**
     * Store a walk-in order from the admin UI.
     */
    public function storeWalkinOrder(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('walkinOrder', [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'linked_customer_user_id' => ['nullable', 'integer', 'exists:users,user_id'],
            'payment_status' => ['required', 'string', Rule::in(['unpaid', 'paid'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:inventory_products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $products = Product::query()
            ->whereIn('id', collect($data['items'])->pluck('product_id')->all(), 'and', false)
            ->get()
            ->keyBy('id');

        $linkedCustomer = null;
        if (! empty($data['linked_customer_user_id'])) {
            $linkedCustomer = User::query()
                ->with('detail')
                ->where('role', 'customer')
                ->where('user_id', (int) $data['linked_customer_user_id'])
                ->first();

            if (! $linkedCustomer) {
                return back()
                    ->withErrors(['linked_customer_user_id' => 'Please choose a valid registered customer account.'], 'walkinOrder')
                    ->withInput()
                    ->with('admin_section', 'orders');
            }
        }

        $customerName = trim((string) ($data['customer_name'] ?? '')) ?: 'Walk-in Customer';
        $notes = trim((string) ($data['notes'] ?? ''));

        $order = DB::transaction(function () use ($data, $products, $request, $customerName, $notes, $linkedCustomer): Order {
            $totalAmount = collect($data['items'])->sum(function (array $item) use ($products): float {
                $product = $products->get((int) $item['product_id']);

                return ((float) $product->price) * (int) $item['quantity'];
            });

            $order = Order::query()->create([
                'user_id' => $linkedCustomer?->user_id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => $data['payment_status'],
                'payment_method' => 'walk-in',
                'payment_provider' => 'onsite',
                'payment_paid_at' => $data['payment_status'] === 'paid' ? now() : null,
                'shipping_address' => 'Walk-in customer: ' . $customerName,
                'payment_metadata' => array_filter([
                    'walk_in' => true,
                    'walk_in_customer_name' => $customerName,
                    'walk_in_linked_customer_id' => $linkedCustomer?->user_id,
                    'walk_in_notes' => $notes !== '' ? $notes : null,
                    'created_by_admin_user_id' => $request->user()->user_id,
                ], fn ($value) => $value !== null && $value !== ''),
            ]);

            foreach ($data['items'] as $item) {
                $product = $products->get((int) $item['product_id']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'item_type' => 'catalog',
                    'quantity' => (int) $item['quantity'],
                    'price' => $product->price,
                    'product_name' => $product->product_name,
                    'description' => $product->description,
                    'image_url' => $product->image_url,
                ]);
            }

            return $order;
        });

        if ($linkedCustomer) {
            $this->notifyWalkInCustomer($linkedCustomer, $order);
        }

        return redirect()
            ->route('admin.home')
            ->with('status', "Walk-in order #{$order->id} created successfully.")
            ->with('admin_section', 'orders');
    }

    /**
     * Bulk upload inventory products from CSV.
     */
    public function bulkUploadInventory(Request $request): RedirectResponse
    {
        $request->validateWithBag('bulkUpload', [
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return back()
                ->withErrors(['csv' => 'The uploaded CSV file is empty.'], 'bulkUpload')
                ->withInput()
                ->with('admin_section', 'inventory');
        }

        $normalizedHeader = array_map(fn ($value) => strtolower(trim((string) $value)), $header);
        $requiredColumns = ['name', 'price', 'category'];

        foreach ($requiredColumns as $column) {
            if (! in_array($column, $normalizedHeader, true) && ! in_array('product_name', $normalizedHeader, true)) {
                fclose($handle);

                return back()
                    ->withErrors(['csv' => 'Missing required CSV columns. Required: name (or product_name), price, category.'], 'bulkUpload')
                    ->withInput()
                    ->with('admin_section', 'inventory');
            }
        }

        $created = 0;
        $createdProducts = collect();
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($row) !== count($normalizedHeader)) {
                fclose($handle);

                return back()
                    ->withErrors(['csv' => "CSV row {$lineNumber} does not match the header column count."], 'bulkUpload')
                    ->withInput()
                    ->with('admin_section', 'inventory');
            }

            $rowData = array_combine($normalizedHeader, $row);
            $mappedData = [
                'product_name' => trim((string) ($rowData['product_name'] ?? $rowData['name'] ?? '')),
                'description' => trim((string) ($rowData['description'] ?? '')),
                'price' => $rowData['price'] ?? null,
                'category' => trim((string) ($rowData['category'] ?? '')),
                'image_url' => trim((string) ($rowData['image_url'] ?? '')),
                'is_active' => $rowData['is_active'] ?? 1,
            ];

            $validator = Validator::make($mappedData, [
                'product_name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
                'price' => ['required', 'numeric', 'min:0'],
                'category' => ['required', 'string', 'max:100'],
                'image_url' => ['nullable', 'string', 'max:2048'],
                'is_active' => ['nullable'],
            ]);

            if ($validator->fails()) {
                fclose($handle);

                return back()
                    ->withErrors(['csv' => "CSV row {$lineNumber} is invalid: " . $validator->errors()->first()], 'bulkUpload')
                    ->withInput()
                    ->with('admin_section', 'inventory');
            }

            $product = Product::query()->create([
                'product_name' => $mappedData['product_name'],
                'description' => $mappedData['description'] !== '' ? $mappedData['description'] : null,
                'price' => $mappedData['price'],
                'category' => $mappedData['category'],
                'image_url' => $mappedData['image_url'] !== '' ? $mappedData['image_url'] : null,
                'is_active' => filter_var($mappedData['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (string) $mappedData['is_active'] !== '0',
            ]);

            $createdProducts->push($product);
            $created++;
        }
        fclose($handle);

        if ($created === 0) {
            return back()
                ->withErrors(['csv' => 'No product rows were found in the uploaded CSV.'], 'bulkUpload')
                ->withInput()
                ->with('admin_section', 'inventory');
        }

        $this->notifyCustomersAboutNewProducts($createdProducts);

        return redirect()
            ->route('admin.home')
            ->with('status', "Bulk upload complete: {$created} product(s) added.")
            ->with('admin_section', 'inventory');
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
        $this->notifyCustomersAboutNewProducts(collect([$product]));

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
        Product::destroy($product->id);

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

        $this->notifyCustomerAboutOrderStatus($order->fresh(['user.detail', 'items']));

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
        $order = $order->fresh(['user.detail', 'items']);

        $this->notifyCustomerAboutPaymentStatus($order);

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
        $messages = $this->adminMessages($orders, $customers);
        $weeklyCompletions = $this->weeklyCompletionSeries($orders);
        $productTypeBreakdown = $this->productTypeBreakdown($products);

        return [
            'metrics' => $metrics,
            'products' => $productCards,
            'orders' => $orderCards,
            'customers' => $customerCards,
            'admins' => $adminCards,
            'notifications' => $notifications,
            'messages' => $messages,
            'weeklyCompletions' => $weeklyCompletions,
            'productTypeBreakdown' => $productTypeBreakdown,
            'reportPayload' => [
                'user' => [
                    'user_id' => auth()->id(),
                    'name' => auth()->user()->detail?->name,
                ],
                'metrics' => $metrics,
                'products' => $productCards,
                'orders' => $orderCards,
                'customers' => $customerCards,
                'admins' => $adminCards,
                'notifications' => $notifications,
                'messages' => $messages,
                'weeklyCompletions' => $weeklyCompletions,
                'productTypeBreakdown' => $productTypeBreakdown,
            ],
            'sidebarCounts' => [
                'dashboard' => '*',
                'inventory' => $products->count(),
                'orders' => $orders->count(),
                'customers' => $customers->count(),
                'notifications' => $notifications->count(),
                'messages' => collect($messages)->where('unread', true)->count(),
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
        $customerName = data_get($order->payment_metadata, 'walk_in_customer_name')
            ?: ($order->user?->detail?->name ?? 'Unknown customer');
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
                : ((bool) data_get($order->payment_metadata, 'walk_in')
                    ? 'Prepare this counter order for immediate bakery handling.'
                    : 'Move the order through the normal bakery production flow.'),
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

    /**
     * @param EloquentCollection<int, Order> $orders
     * @param EloquentCollection<int, User> $customers
     */
    private function notificationsFromOrders(EloquentCollection $orders, EloquentCollection $customers): SupportCollection
    {
        $orderNotifications = $orders->map(function (Order $order): array {
            $customerName = data_get($order->payment_metadata, 'walk_in_customer_name')
                ?: ($order->user?->detail?->name ?? 'Customer');
            $isPaid = $order->payment_status === 'paid';
            $containsCustom = $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom');

            return [
                'id' => 'order-' . $order->id,
                'customer_name' => $customerName,
                'title' => $isPaid ? 'Payment received' : 'New customer order',
                'message' => $isPaid
                    ? 'Order #' . $order->id . ' from ' . $customerName . ' is paid and ready for admin handling.'
                    : 'Order #' . $order->id . ' from ' . $customerName . ' was sent to admin and is waiting for review.',
                'category' => $isPaid ? 'payments' : 'orders',
                'category_label' => $isPaid ? 'Payment' : 'Order',
                'order_id' => $order->id,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'contains_custom' => $containsCustom,
                'date' => $order->updated_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'timestamp' => $order->updated_at?->timestamp ?? now()->timestamp,
            ];
        });

        $newCustomers = $customers->take(2)->map(function (User $user): array {
            return [
                'id' => 'customer-' . $user->user_id,
                'customer_name' => $user->detail?->name ?? 'Customer',
                'title' => 'New customer account',
                'message' => 'New customer account is active.',
                'category' => 'customers',
                'category_label' => 'Customer',
                'order_id' => null,
                'status' => null,
                'payment_status' => null,
                'contains_custom' => false,
                'date' => $user->created_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'timestamp' => $user->created_at?->timestamp ?? now()->timestamp,
            ];
        });

        return $orderNotifications
            ->concat($newCustomers)
            ->sortByDesc('timestamp')
            ->take(30)
            ->values();
    }

    /**
     * @param EloquentCollection<int, Order> $orders
     * @param EloquentCollection<int, User> $customers
     */
    private function adminMessages(EloquentCollection $orders, EloquentCollection $customers): array
    {
        $conversations = \App\Models\Conversation::with(['customer.detail', 'lastMessage'])
            ->latest('last_message_at')
            ->get();

        return $conversations->map(function ($conversation) {
            $customerName = $conversation->customer->detail?->name ?? 'Customer';
            return [
                'id' => $conversation->id,
                'name' => $customerName,
                'avatar' => strtoupper(substr($customerName, 0, 2)),
                'label' => 'Direct Message',
                'subtitle' => 'Customer since ' . $conversation->customer->created_at->format('M Y'),
                'time' => $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'No messages',
                'unread' => $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->exists(),
                'preview' => $conversation->lastMessage?->content ?? 'No messages yet',
                'messages' => $conversation->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender' => $message->sender_id === auth()->id() ? 'me' : 'them',
                        'text' => $message->content,
                        'time' => $message->created_at->format('h:i A'),
                    ];
                })->all(),
            ];
        })->all();
    }

    /**
     * @param EloquentCollection<int, Order> $orders
     */
    private function weeklyCompletionSeries(EloquentCollection $orders): array
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

    /**
     * @param EloquentCollection<int, Product> $products
     */
    private function productTypeBreakdown(EloquentCollection $products): array
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

    /**
     * @param EloquentCollection<int, Order> $orders
     */
    private function completedOrdersSince(EloquentCollection $orders, Carbon $threshold): int
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
            'walk-in', 'walkin', 'cash' => 'Walk-in / Counter',
            default => 'Payment pending',
        };
    }

    private function notifyWalkInCustomer(User $customer, Order $order): void
    {
        $customerName = data_get($order->payment_metadata, 'walk_in_customer_name')
            ?: ($customer->detail?->name ?? 'Customer');

        $itemCount = $order->items()->sum('quantity');

        CustomerNotification::query()->create([
            'user_id' => $customer->user_id,
            'type' => 'order',
            'title' => 'Your walk-in order was added',
            'message' => "Hi {$customerName}, your walk-in order #{$order->id} with {$itemCount} item(s) has been added to BakerDan's order queue.",
            'payload' => [
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'source' => 'walk_in',
            ],
            'image_url' => null,
        ]);
    }

    private function notifyCustomerAboutOrderStatus(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        CustomerNotification::query()->create([
            'user_id' => $order->user_id,
            'type' => 'order',
            'title' => 'Your order status was updated',
            'message' => 'Order #' . $order->id . ' is now ' . $this->orderStatusLabel(
                $order->status,
                $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom')
            ) . '.',
            'payload' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'source' => 'admin_order_status_update',
            ],
        ]);
    }

    private function notifyCustomerAboutPaymentStatus(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        CustomerNotification::query()->create([
            'user_id' => $order->user_id,
            'type' => 'order',
            'title' => 'Your payment status was updated',
            'message' => 'Order #' . $order->id . ' payment is now ' . $this->paymentStatusLabel($order->payment_status) . '.',
            'payload' => [
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'source' => 'admin_payment_status_update',
            ],
        ]);
    }

    /**
     * @param SupportCollection<int, Product> $products
     */
    private function notifyCustomersAboutNewProducts(SupportCollection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $customers = User::query()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->get(['user_id']);

        if ($customers->isEmpty()) {
            return;
        }

        $firstProduct = $products->first();
        $productCount = $products->count();
        $title = $productCount === 1
            ? 'New bakery product added'
            : 'Fresh bakery products just arrived';
        $message = $productCount === 1
            ? "{$firstProduct->product_name} is now available in the BakerDan catalog. Check it out while it's fresh."
            : "{$productCount} new products were just added to the BakerDan catalog. Take a look at the latest bakery picks.";

        $timestamp = now();
        $rows = $customers->map(function (User $customer) use ($title, $message, $firstProduct, $productCount, $products, $timestamp): array {
            return [
                'user_id' => $customer->user_id,
                'type' => 'product',
                'title' => $title,
                'message' => $message,
                'image_url' => $firstProduct?->image_url,
                'payload' => json_encode([
                    'product_count' => $productCount,
                    'product_ids' => $products->pluck('id')->all(),
                    'source' => $productCount === 1 ? 'single_create' : 'bulk_create',
                ]),
                'is_read' => false,
                'read_at' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->all();

        CustomerNotification::query()->insert($rows);
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
