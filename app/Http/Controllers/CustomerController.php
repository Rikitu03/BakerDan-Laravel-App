<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
// Don't forget to import Inertia at the top of your controller:
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function home(): Response
    {
        return $this->storefront('catalog');
    }

    public function cart(): Response
    {
        return $this->storefront('detail');
    }

    private function storefront(string $initialView): Response
    {   
        $customer = $this->customerProfile();
        $categories = $this->storefrontCategories();
        $sortOptions = [
            ['label' => 'Popularity', 'active' => true],
            ['label' => 'Price', 'active' => false],
            ['label' => 'Low to High', 'active' => false],
        ];
        $products = $this->storefrontProducts();
        $product = $this->featuredStorefrontProduct();

        // Change from view() to Inertia::render()
        // Make sure 'Customer/home' exactly matches your file path: resources/js/Pages/Customer/home.vue
        return Inertia::render('Customer/home', [
            'customer' => $customer,
            'categories' => $categories,
            'sortOptions' => $sortOptions,
            'products' => $products,
            'product' => $product,
            'initialView' => $initialView
        ]);
    }
}
