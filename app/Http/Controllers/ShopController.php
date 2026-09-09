<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Show the digital product store grid.
     */
    public function index(): View
    {
        $products = Product::query()
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->paginate(12);

        $metaTitle = 'Shop | Banglay Chinese';
        $metaDescription = 'Digital products — e-books, practice tests and study materials by Banglay Chinese.';

        return view('shop.index', compact('products', 'metaTitle', 'metaDescription'));
    }
}
