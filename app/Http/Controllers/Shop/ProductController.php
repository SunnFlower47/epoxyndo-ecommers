<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\Shop\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * Display catalog of products.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'packaging_type', 'min_price', 'max_price']);
        $sortBy = $request->get('sort', 'latest');

        $products = $this->productService->getFilteredProducts($filters, $sortBy);

        return Inertia::render('Shop/Catalog', [
            'products' => $products,
            'filters' => $filters,
            'sort' => $sortBy,
        ]);
    }

    /**
     * Display product detail.
     */
    public function show(string $slug): Response
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            abort(404);
        }

        // Increment view count in background
        // dispatch(new TrackProductView($product));

        return Inertia::render('Shop/Product', [
            'product' => $product,
        ]);
    }
}
