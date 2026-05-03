<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Serve the Vue SPA for all customer routes
     */
    public function spa(): View
    {
        $customer = $this->customerProfile();
        
        return view('customer.app', compact('customer'));
    }

    /**
     * Get customer profile data
     */
    private function customerProfile(array $overrides = []): array
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'customer') {
            return array_merge([
                'id' => 0,
                'name' => 'Jason Jay Recto',
                'first_name' => 'Jason',
                'email' => 'preview@bakerdan.test',
                'phone' => '+63 900 000 0000',
                'address' => 'Metro Manila',
                'total_orders' => 12,
                'loyalty_points' => 850,
                'member_since' => 'Preview mode',
                'avatar' => 'JR',
            ], $overrides);
        }

        $user->loadMissing('detail');
        $detail = $user->detail;
        $name = $detail?->name ?: 'Bakerdan Customer';
        $totalOrders = Order::query()
            ->where('user_id', $user->user_id)
            ->count();
        
        // Generate initials for avatar
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn(string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->join('');

        return array_merge([
            'id' => $user->user_id,
            'name' => $name,
            'first_name' => Str::of($name)->before(' ')->value() ?: $name,
            'email' => $detail?->email ?: 'customer@bakerdan.com',
            'phone' => $detail?->contact ?: '+63 900 000 0000',
            'address' => $detail?->address ?: 'Metro Manila',
            'total_orders' => $totalOrders,
            'loyalty_points' => $totalOrders * 10,
            'member_since' => $user->created_at?->format('F Y') ?: 'New member',
            'avatar' => $initials ?: 'BC',
        ], $overrides);
    }
}
