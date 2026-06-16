<?php

namespace App\Services\Shop;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get paginated products with filters and sorting.
     */
    public function getFilteredProducts(array $filters = [], string $sortBy = 'latest', int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()->where('status', 'active');

        // Apply search filter (if using fuzzy search or basic query)
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply packaging filter (set, pail, sack, etc.)
        if (!empty($filters['packaging_type'])) {
            $query->where('packaging_type', $filters['packaging_type']);
        }

        // Apply price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Get product detail by slug.
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Increment view count (to be called via Job to prevent slow page load).
     */
    public function incrementViewCount(Product $product): void
    {
        $product->increment('view_count');
    }
}
