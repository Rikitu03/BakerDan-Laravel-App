<?php

namespace App\Http\Controllers;

use App\Mail\OrderShippedNotificationMail;
use App\Models\CartItem;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
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
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_contact' => ['required', 'string', 'max:50'],
            'customer_address' => ['required', 'string', 'max:1000'],
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
        $customerEmail = trim((string) $data['customer_email']);
        $customerContact = trim((string) $data['customer_contact']);
        $customerAddress = trim((string) $data['customer_address']);
        $notes = trim((string) ($data['notes'] ?? ''));

        $order = DB::transaction(function () use ($data, $products, $request, $customerName, $customerEmail, $customerContact, $customerAddress, $notes, $linkedCustomer): Order {
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
                'shipping_address' => $customerAddress,
                'payment_metadata' => array_filter([
                    'walk_in' => true,
                    'walk_in_customer_name' => $customerName,
                    'walk_in_customer_email' => $customerEmail,
                    'walk_in_customer_contact' => $customerContact,
                    'walk_in_customer_address' => $customerAddress,
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

        CustomerNotification::notifyAdmins(
            'order',
            'New walk-in order',
            'Order #' . $order->id . ' from ' . $customerName . ' was created by admin.',
            [
                'order_id' => $order->id,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_contact' => $customerContact,
                'customer_address' => $customerAddress,
                'payment_status' => $order->payment_status,
                'source' => 'walk_in'
            ]
        );

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
                'image_source' => 'bulk',
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

    public function storeInventory(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validateInventoryProduct($request);

        $product = new Product();
        $product->fill($data);
        $product->is_active = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $product->image_url = $request->file('image')->store('inventory-products', 'public');
            $product->image_source = 'uploaded';
        }

        $product->save();
        $this->notifyCustomersAboutNewProducts(collect([$product]));

        return $this->adminMutationResponse(
            $request,
            'Product added successfully.',
            ['product' => $this->productCard($product->fresh())],
            'inventory',
        );
    }

    public function updateInventory(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $data = $this->validateInventoryProduct($request);

        $product->fill($data);
        $product->is_active = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $product->image_url = $request->file('image')->store('inventory-products', 'public');
            $product->image_source = 'uploaded';
        }

        $product->save();

        return $this->adminMutationResponse(
            $request,
            'Product updated successfully.',
            ['product' => $this->productCard($product->fresh())],
            'inventory',
        );
    }

    public function destroyInventory(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $hasOrderHistory = OrderItem::query()
            ->where('product_id', $product->id)
            ->exists();
        $hasCartReferences = CartItem::query()
            ->where('product_id', $product->id)
            ->exists();

        if ($hasOrderHistory || $hasCartReferences) {
            $product->update(['is_active' => false]);

            return $this->adminMutationResponse(
                $request,
                'Product is linked to existing orders or carts, so it was archived instead of deleted.',
                ['product' => $this->productCard($product->fresh())],
                'inventory',
            );
        }

        $productId = $product->id;
        $product->delete();

        return $this->adminMutationResponse(
            $request,
            'Product removed successfully.',
            ['product_id' => $productId],
            'inventory',
        );
    }

    public function storePromo(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validatePromo($request);
        $promo = new Promo();
        $promo->fill($data);
        $promo->is_active = $request->boolean('is_active', true);
        
        if ($request->has('applicable_products')) {
            $promo->applicable_products = is_array($request->input('applicable_products')) 
                ? array_map('intval', $request->input('applicable_products'))
                : [];
        } else {
            $promo->applicable_products = null;
        }

        $promo->save();

        return $this->adminMutationResponse(
            $request,
            'Promo code created successfully.',
            ['promo' => $this->promoCard($promo->fresh())],
            'promos',
        );
    }

    public function updatePromo(Request $request, Promo $promo): RedirectResponse|JsonResponse
    {
        $data = $this->validatePromo($request, $promo);
        $promo->fill($data);
        $promo->is_active = $request->boolean('is_active', true);

        if ($request->has('applicable_products')) {
            $promo->applicable_products = is_array($request->input('applicable_products')) 
                ? array_map('intval', $request->input('applicable_products'))
                : [];
        } else {
            $promo->applicable_products = null;
        }

        $promo->save();

        return $this->adminMutationResponse(
            $request,
            'Promo code updated successfully.',
            ['promo' => $this->promoCard($promo->fresh())],
            'promos',
        );
    }

    public function destroyPromo(Request $request, Promo $promo): RedirectResponse|JsonResponse
    {
        $promoId = $promo->id;
        $promo->delete();

        return $this->adminMutationResponse(
            $request,
            'Promo code removed successfully.',
            ['promo_id' => $promoId],
            'promos',
        );
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

        $updatedOrder = $order->fresh(['user.detail', 'items.product', 'promo']);
        $this->notifyCustomerAboutOrderStatus($updatedOrder);
        $this->emailCustomerWhenOrderShips($updatedOrder, $currentStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order workflow updated successfully.',
            'data' => [
                'order' => $this->orderCard($updatedOrder),
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

    public function toggleUserStatus(User $user): JsonResponse
    {
        if ($user->user_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot suspend your own account.',
            ], 422);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        Session::flash('admin_section', 'customers');

        return response()->json([
            'success' => true,
            'message' => 'User account ' . ($user->is_active ? 'activated' : 'suspended') . ' successfully.',
            'data' => [
                'user_id' => $user->user_id,
                'is_active' => (bool) $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive',
            ],
        ]);
    }

    private function dashboardData(): array
    {
        $allProducts = Product::query()->latest('id')->get();
        $products = Product::query()
            ->where('is_active', true)
            ->latest('id')
            ->get();
        $promos = Promo::query()->latest('id')->get();
        $orders = Order::with(['user.detail', 'items.product', 'promo'])->latest('id')->get();
        $customers = User::with('detail')->where('role', 'customer')->latest('user_id')->get();
        $admins = User::with('detail')->where('role', 'admin')->latest('user_id')->get();

        $metrics = [
            ['label' => 'Total products ordered', 'value' => number_format((int) OrderItem::sum('quantity')), 'detail' => 'Count from order items'],
            ['label' => 'Completed orders', 'value' => number_format($orders->where('status', 'delivered')->count()), 'detail' => 'All time completed'],
            ['label' => 'Orders completed this week', 'value' => number_format($this->completedOrdersSince($orders, Carbon::now()->startOfWeek())), 'detail' => 'Current 7-day window'],
            ['label' => 'Orders completed this month', 'value' => number_format($this->completedOrdersSince($orders, Carbon::now()->startOfMonth())), 'detail' => 'Current month total'],
        ];

        $productCards = $products->map(fn (Product $product) => $this->productCard($product))->values()->all();
        $promoCards = $promos->map(fn (Promo $promo) => $this->promoCard($promo))->values()->all();
        $orderCards = $orders->map(fn (Order $order) => $this->orderCard($order))->values()->all();
        $customerCards = $customers->map(fn (User $user) => $this->personCard($user, 'customer'))->values()->all();
        $adminCards = $admins->map(fn (User $user) => $this->personCard($user, 'admin'))->values()->all();
        $notifications = $this->adminNotifications();
        $messages = $this->adminMessages($orders, $customers);
        $weeklyCompletions = $this->weeklyCompletionSeries($orders);
        $monthlyCompletions = $this->monthlyCompletionSeries($orders);
        $yearlyCompletions = $this->yearlyCompletionSeries($orders);
        // reporting aggregates
        $productSales = $this->productSalesAggregates($orders, $allProducts);
        $usersWeekly = $this->weeklyUserSeries($customers);
        $usersMonthly = $this->monthlyUserSeries($customers);
        $usersYearly = $this->yearlyUserSeries($customers);
        $ordersWeekly = $this->weeklyOrderSeries($orders);
        $ordersMonthly = $this->monthlyOrderSeries($orders);
        $ordersYearly = $this->yearlyOrderSeries($orders);
        $revenueWeekly = $this->weeklyRevenueSeries($orders);
        $revenueMonthly = $this->monthlyRevenueSeries($orders);
        $revenueYearly = $this->yearlyRevenueSeries($orders);
        $productTypeBreakdown = $this->productTypeBreakdown($allProducts);

        // comprehensive summary
        $totalRevenue = (float) $orders->where('payment_status', 'paid')->sum('total_amount');
        $topProducts = collect($productSales)->sortByDesc('quantity_sold')->take(10)->values()->all();
        $summary = [
            'generated_at' => now()->toIso8601String(),
            'total_products' => $allProducts->count(),
            'total_orders' => $orders->count(),
            'total_customers' => $customers->count(),
            'total_admins' => $admins->count(),
            'total_notifications' => $notifications->count(),
            'total_revenue' => round($totalRevenue, 2),
            'total_items_sold' => OrderItem::sum('quantity'),
            'top_products' => $topProducts,
            'orders_weekly' => $ordersWeekly,
            'orders_monthly' => $ordersMonthly,
            'orders_yearly' => $ordersYearly,
            'revenue_weekly' => $revenueWeekly,
            'revenue_monthly' => $revenueMonthly,
            'revenue_yearly' => $revenueYearly,
            'users_weekly' => $usersWeekly,
            'users_monthly' => $usersMonthly,
            'users_yearly' => $usersYearly,
        ];

        return [
            'metrics' => $metrics,
            'products' => $productCards,
            'promos' => $promoCards,
            'orders' => $orderCards,
            'customers' => $customerCards,
            'admins' => $adminCards,
            'notifications' => $notifications,
            'messages' => $messages,
            'weeklyCompletions' => $weeklyCompletions,
            'monthlyCompletions' => $monthlyCompletions,
            'yearlyCompletions' => $yearlyCompletions,
            'productSales' => $productSales,
            'usersWeekly' => $usersWeekly,
            'usersMonthly' => $usersMonthly,
            'usersYearly' => $usersYearly,
            'ordersWeekly' => $ordersWeekly,
            'ordersMonthly' => $ordersMonthly,
            'ordersYearly' => $ordersYearly,
            'revenueWeekly' => $revenueWeekly,
            'revenueMonthly' => $revenueMonthly,
            'revenueYearly' => $revenueYearly,
            'productTypeBreakdown' => $productTypeBreakdown,
            'reportPayload' => [
                'user' => [
                    'user_id' => auth()->id(),
                    'name' => auth()->user()->detail?->name,
                ],
                'metrics' => $metrics,
                'products' => $productCards,
                'promos' => $promoCards,
                'orders' => $orderCards,
                'customers' => $customerCards,
                'admins' => $adminCards,
                'notifications' => $notifications,
                'messages' => $messages,
                'weeklyCompletions' => $weeklyCompletions,
                'monthlyCompletions' => $monthlyCompletions,
                'yearlyCompletions' => $yearlyCompletions,
                'productSales' => $productSales,
                'usersWeekly' => $usersWeekly,
                'usersMonthly' => $usersMonthly,
                'usersYearly' => $usersYearly,
                'ordersWeekly' => $ordersWeekly,
                'ordersMonthly' => $ordersMonthly,
                'ordersYearly' => $ordersYearly,
                'revenueWeekly' => $revenueWeekly,
                'revenueMonthly' => $revenueMonthly,
                'revenueYearly' => $revenueYearly,
                'productTypeBreakdown' => $productTypeBreakdown,
                'summary' => $summary,
            ],
            'sidebarCounts' => [
                'dashboard' => '*',
                'inventory' => $products->count(),
                'promos' => $promos->count(),
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

    private function adminMutationResponse(Request $request, string $message, array $data = [], string $section = 'dashboard'): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]);
        }

        return redirect()
            ->route('admin.home')
            ->with('status', $message)
            ->with('admin_section', $section);
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

    private function validatePromo(Request $request, ?Promo $promo = null): array
    {
        $promoId = $promo ? $promo->id : 'NULL';
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promos,code,' . $promoId],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['required', 'string', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'applicable_products' => ['nullable', 'array'],
            'applicable_products.*' => ['integer', 'exists:inventory_products,id'],
        ]);
    }

    private function promoCard(Promo $promo): array
    {
        return [
            'id' => $promo->id,
            'code' => $promo->code,
            'description' => $promo->description,
            'discount_type' => $promo->discount_type,
            'discount_value' => $promo->discount_value,
            'formatted_discount' => $promo->discount_type === 'percentage'
                ? number_format((float) $promo->discount_value, 0) . '%'
                : 'PHP ' . number_format((float) $promo->discount_value, 2),
            'min_purchase' => $promo->min_purchase,
            'formatted_min_purchase' => $promo->min_purchase
                ? 'PHP ' . number_format((float) $promo->min_purchase, 2)
                : 'None',
            'max_discount' => $promo->max_discount,
            'formatted_max_discount' => $promo->max_discount
                ? 'PHP ' . number_format((float) $promo->max_discount, 2)
                : 'None',
            'usage_limit' => $promo->usage_limit,
            'limit_per_user' => $promo->limit_per_user,
            'usage_count' => $promo->usage_count,
            'applicable_products' => $promo->applicable_products ?? [],
            'starts_at' => $promo->starts_at?->format('Y-m-d H:i') ?? $promo->starts_at?->format('Y-m-d\TH:i') ?? null,
            'expires_at' => $promo->expires_at?->format('Y-m-d H:i') ?? $promo->expires_at?->format('Y-m-d\TH:i') ?? null,
            'formatted_starts_at' => $promo->starts_at?->format('M d, Y h:i A') ?? 'N/A',
            'formatted_expires_at' => $promo->expires_at?->format('M d, Y h:i A') ?? 'N/A',
            'is_active' => (bool) $promo->is_active,
            'status' => $promo->is_active ? 'active' : 'inactive',
        ];
    }

    private function orderCard(Order $order): array
    {
        $customerDetail = $order->user?->detail;
        $walkInCustomerName = data_get($order->payment_metadata, 'walk_in_customer_name');
        $walkInCustomerEmail = data_get($order->payment_metadata, 'walk_in_customer_email');
        $walkInCustomerContact = data_get($order->payment_metadata, 'walk_in_customer_contact');
        $walkInCustomerAddress = data_get($order->payment_metadata, 'walk_in_customer_address');
        $customerName = $walkInCustomerName
            ?: ($customerDetail?->name ?? 'Unknown customer');
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
            'customer_details' => [
                'address' => $customerDetail?->address ?? $walkInCustomerAddress ?? 'N/A',
                'age' => $customerDetail?->age ?? 'N/A',
                'contact' => $customerDetail?->contact ?? $walkInCustomerContact ?? 'N/A',
                'email' => $customerDetail?->email ?? $walkInCustomerEmail ?? 'N/A',
                'linked_account' => $order->user_id ? ($customerDetail?->name ?? 'Linked customer') : 'Guest / walk-in',
                'name' => $customerName,
                'source' => $order->user_id ? 'Registered customer' : 'Guest / walk-in',
                'username' => $customerDetail?->username ?? 'N/A',
            ],
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
            'discount_amount' => (float) $order->discount_amount,
            'discount_amount_label' => $order->discount_amount > 0 ? ('- PHP ' . number_format((float) $order->discount_amount, 2)) : null,
            'promo_code' => $order->promo?->code,
            'status' => $order->status,
            'status_label' => $this->orderStatusLabel($order->status, $containsCustom),
            'can_cancel' => in_array($order->status, ['pending', 'processing'], true),
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

    private function adminNotifications(): SupportCollection
    {
        return CustomerNotification::where('user_id', auth()->id())
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($notification) {
                $payload = $notification->payload ?? [];
                $category = 'orders';
                $categoryLabel = 'Order';

                if ($notification->type === 'customer') {
                    $category = 'customers';
                    $categoryLabel = 'Customer';
                } elseif (($payload['payment_status'] ?? '') === 'paid') {
                    $category = 'payments';
                    $categoryLabel = 'Payment';
                }

                return [
                    'id' => $notification->id,
                    'customer_name' => $payload['customer_name'] ?? 'System',
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'category' => $category,
                    'category_label' => $categoryLabel,
                    'order_id' => $payload['order_id'] ?? null,
                    'status' => $payload['status'] ?? null,
                    'payment_status' => $payload['payment_status'] ?? null,
                    'contains_custom' => $payload['contains_custom'] ?? false,
                    'date' => $notification->created_at->format('Y-m-d H:i'),
                    'timestamp' => $notification->created_at->timestamp,
                ];
            });
    }

    /**
     * @param EloquentCollection<int, Order> $orders
     * @param EloquentCollection<int, User> $customers
     */
    private function adminMessages(EloquentCollection $orders, EloquentCollection $customers): array
    {
        $adminId = auth()->id();

        $conversations = \App\Models\Conversation::with([
                'customer.detail',
                'lastMessage',
                'messages' => fn ($query) => $query
                    ->with('sender.detail')
                    ->orderBy('created_at')
                    ->orderBy('id'),
            ])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query
                    ->where('sender_id', '!=', $adminId)
                    ->where('is_read', false),
            ])
            ->latest('last_message_at')
            ->limit(80)
            ->get();

        return $conversations->map(function ($conversation) use ($adminId) {
            $customerName = $conversation->customer->detail?->name ?? 'Customer';
            return [
                'id' => $conversation->id,
                'name' => $customerName,
                'avatar' => strtoupper(substr($customerName, 0, 2)),
                'label' => 'Direct Message',
                'subtitle' => 'Customer since ' . ($conversation->customer->created_at ? $conversation->customer->created_at->format('M Y') : 'N/A'),
                'time' => $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'No messages',
                'unread' => (int) $conversation->unread_count > 0,
                'preview' => $conversation->lastMessage?->content ?? 'No messages yet',
                'messages' => $conversation->messages->map(function ($message) use ($adminId) {
                    return [
                        'id' => $message->id,
                        'sender' => $message->sender_id === $adminId ? 'me' : 'them',
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
     * @param EloquentCollection<int, Order> $orders
     */
    private function monthlyCompletionSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $months = collect(range(0, 11))->map(function (int $offset) use ($start): array {
            $month = $start->copy()->addMonths($offset);

            return [
                'label' => $month->format('M y'),
                'date' => $month->toDateString(),
            ];
        });

        return $months->map(function (array $entry) use ($orders): array {
            $entry['value'] = $orders->filter(function (Order $order) use ($entry): bool {
                return $order->status === 'delivered'
                    && $order->updated_at?->format('Y-m') === Carbon::parse($entry['date'])->format('Y-m');
            })->count();

            unset($entry['date']);

            return $entry;
        })->all();
    }

    /**
     * @param EloquentCollection<int, Order> $orders
     */
    private function yearlyCompletionSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subYears(4)->startOfYear();
        $years = collect(range(0, 4))->map(function (int $offset) use ($start): array {
            $year = $start->copy()->addYears($offset);

            return [
                'label' => $year->format('Y'),
                'date' => $year->toDateString(),
            ];
        });

        return $years->map(function (array $entry) use ($orders): array {
            $entry['value'] = $orders->filter(function (Order $order) use ($entry): bool {
                return $order->status === 'delivered'
                    && $order->updated_at?->format('Y') === Carbon::parse($entry['date'])->format('Y');
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
     * @param EloquentCollection<int, Product> $products
     */
    private function productSalesAggregates(EloquentCollection $orders, EloquentCollection $products): array
    {
        $map = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $pid = $item->product_id ?? null;
                $name = $item->product?->product_name ?? $item->product_name ?? 'Unknown';
                $qty = (int) ($item->quantity ?? 0);
                $linePrice = isset($item->price) ? (float) $item->price : 0.0;

                if (!isset($map[$pid])) {
                    $map[$pid] = [
                        'product_id' => $pid,
                        'name' => $name,
                        'quantity_sold' => 0,
                        'buyers' => [],
                        'revenue' => 0.0,
                    ];
                }

                $map[$pid]['quantity_sold'] += $qty;
                $map[$pid]['revenue'] += $linePrice * $qty;

                $buyerId = $order->user?->user_id ?? ('guest-' . $order->id);
                $map[$pid]['buyers'][$buyerId] = true;
            }
        }

        return collect($map)->map(function ($entry) {
            return [
                'product_id' => $entry['product_id'],
                'name' => $entry['name'],
                'quantity_sold' => $entry['quantity_sold'],
                'buyers_count' => count($entry['buyers']),
                'revenue' => round($entry['revenue'], 2),
            ];
        })->values()->all();
    }

    private function weeklyUserSeries(EloquentCollection $users): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $days = collect(range(0, 6))->map(fn (int $offset) => $start->copy()->addDays($offset));

        return $days->map(fn ($day) => [
            'label' => $day->format('D'),
            'value' => $users->filter(fn($u) => $u->created_at?->toDateString() === $day->toDateString())->count(),
        ])->all();
    }

    private function monthlyUserSeries(EloquentCollection $users): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $months = collect(range(0, 11))->map(fn (int $offset) => $start->copy()->addMonths($offset));

        return $months->map(fn ($m) => [
            'label' => $m->format('M y'),
            'value' => $users->filter(fn($u) => $u->created_at?->format('Y-m') === $m->format('Y-m'))->count(),
        ])->all();
    }

    private function yearlyUserSeries(EloquentCollection $users): array
    {
        $start = Carbon::now()->subYears(4)->startOfYear();
        $years = collect(range(0, 4))->map(fn (int $offset) => $start->copy()->addYears($offset));

        return $years->map(fn ($y) => [
            'label' => $y->format('Y'),
            'value' => $users->filter(fn($u) => $u->created_at?->format('Y') === $y->format('Y'))->count(),
        ])->all();
    }

    private function weeklyOrderSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $days = collect(range(0, 6))->map(fn (int $offset) => $start->copy()->addDays($offset));

        return $days->map(fn ($d) => [
            'label' => $d->format('D'),
            'value' => $orders->filter(fn($o) => $o->created_at?->toDateString() === $d->toDateString())->count(),
        ])->all();
    }

    private function monthlyOrderSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $months = collect(range(0, 11))->map(fn (int $offset) => $start->copy()->addMonths($offset));

        return $months->map(fn ($m) => [
            'label' => $m->format('M y'),
            'value' => $orders->filter(fn($o) => $o->created_at?->format('Y-m') === $m->format('Y-m'))->count(),
        ])->all();
    }

    private function yearlyOrderSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subYears(4)->startOfYear();
        $years = collect(range(0, 4))->map(fn (int $offset) => $start->copy()->addYears($offset));

        return $years->map(fn ($y) => [
            'label' => $y->format('Y'),
            'value' => $orders->filter(fn($o) => $o->created_at?->format('Y') === $y->format('Y'))->count(),
        ])->all();
    }

    private function weeklyRevenueSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $days = collect(range(0, 6))->map(fn (int $offset) => $start->copy()->addDays($offset));

        return $days->map(fn ($d) => [
            'label' => $d->format('D'),
            'value' => round($orders->filter(fn($o) => $o->payment_status === 'paid' && $o->created_at?->toDateString() === $d->toDateString())->sum(fn($o) => (float) $o->total_amount), 2),
        ])->all();
    }

    private function monthlyRevenueSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $months = collect(range(0, 11))->map(fn (int $offset) => $start->copy()->addMonths($offset));

        return $months->map(fn ($m) => [
            'label' => $m->format('M y'),
            'value' => round($orders->filter(fn($o) => $o->payment_status === 'paid' && $o->created_at?->format('Y-m') === $m->format('Y-m'))->sum(fn($o) => (float) $o->total_amount), 2),
        ])->all();
    }

    private function yearlyRevenueSeries(EloquentCollection $orders): array
    {
        $start = Carbon::now()->subYears(4)->startOfYear();
        $years = collect(range(0, 4))->map(fn (int $offset) => $start->copy()->addYears($offset));

        return $years->map(fn ($y) => [
            'label' => $y->format('Y'),
            'value' => round($orders->filter(fn($o) => $o->payment_status === 'paid' && $o->created_at?->format('Y') === $y->format('Y'))->sum(fn($o) => (float) $o->total_amount), 2),
        ])->all();
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

    private function emailCustomerWhenOrderShips(Order $order, string $previousStatus): void
    {
        if ($previousStatus === 'shipped' || $order->status !== 'shipped') {
            return;
        }

        $email = $order->user?->detail?->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new OrderShippedNotificationMail($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
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

        try {
            CustomerNotification::query()->insert($rows);
        } catch (\Throwable $exception) {
            report($exception);
        }
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
