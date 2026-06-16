import { Head, Link, router, usePage } from '@inertiajs/react';
import React from 'react';
import StorefrontLayout from '@/layouts/storefront-layout';
import { ProductCard } from '@/components/product-card';
import { Button } from '@/components/ui/button';
import { SlidersHorizontal, Search } from 'lucide-react';
import { CategorySidebar } from '@/components/category-sidebar';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProducts {
    data: any[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
}

export default function ProductsIndex({ 
    products, 
    currentCategory,
    searchQuery
}: { 
    products: PaginatedProducts, 
    currentCategory?: string,
    searchQuery?: string
}) {
    const { locale, shared_categories } = usePage<any>().props;
    const lang = locale || 'id';

    const clearFilter = () => {
        router.get('/products');
    };

    return (
        <StorefrontLayout>
            <Head title={
                searchQuery ? `Pencarian: ${searchQuery}` : 
                currentCategory ? `${currentCategory} | ${lang === 'en' ? 'Products' : 'Produk'}` : 
                (lang === 'en' ? 'Product Catalog' : 'Katalog Produk')
            } />
            
            <div className="bg-slate-50 border-b">
                <div className="container mx-auto px-4 py-8">
                    <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h1 className="text-3xl font-bold text-slate-800">
                                {searchQuery 
                                    ? (lang === 'en' ? 'Search Results' : 'Hasil Pencarian') 
                                    : (lang === 'en' ? 'Product Catalog' : 'Katalog Produk')}
                            </h1>
                            <p className="text-muted-foreground mt-2">
                                {searchQuery && currentCategory
                                    ? (lang === 'en' ? `Showing results for "${searchQuery}" in category: ${currentCategory}` : `Menampilkan hasil untuk "${searchQuery}" pada kategori: ${currentCategory}`)
                                    : searchQuery
                                        ? (lang === 'en' ? `Showing results for "${searchQuery}"` : `Menampilkan hasil untuk "${searchQuery}"`)
                                        : currentCategory
                                            ? (lang === 'en' ? `Showing products for category: ${currentCategory}` : `Menampilkan produk untuk kategori: ${currentCategory}`) 
                                            : (lang === 'en' ? 'Explore all our quality products' : 'Jelajahi semua produk berkualitas kami')}
                            </p>
                        </div>
                        <div className="md:hidden">
                            <Sheet>
                                <SheetTrigger asChild>
                                    <Button variant="outline" className="gap-2">
                                        <SlidersHorizontal className="h-4 w-4" />
                                        {lang === 'en' ? 'Filter & Category' : 'Filter & Kategori'}
                                    </Button>
                                </SheetTrigger>
                                <SheetContent side="left">
                                    <SheetHeader>
                                        <SheetTitle>{lang === 'en' ? 'Filter Products' : 'Filter Produk'}</SheetTitle>
                                        <SheetDescription>
                                            {lang === 'en' ? 'Customize your product search.' : 'Sesuaikan pencarian produk Anda.'}
                                        </SheetDescription>
                                    </SheetHeader>
                                    <div className="mt-6">
                                        <CategorySidebar categories={shared_categories} locale={lang} currentCategory={currentCategory} />
                                    </div>
                                </SheetContent>
                            </Sheet>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 py-8">
                <div className="flex flex-col md:flex-row gap-8">
                    {/* Desktop Sidebar */}
                    <aside className="hidden md:block w-64 shrink-0">
                        <CategorySidebar categories={shared_categories} locale={lang} currentCategory={currentCategory} />
                    </aside>

                    {/* Product Grid */}
                    <div className="flex-1">
                        <div className="mb-6 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                {lang === 'en' ? 'Showing' : 'Menampilkan'} <span className="font-medium text-foreground">{products.total}</span> {lang === 'en' ? 'products' : 'produk'}
                            </p>
                        </div>

                        {products.data.length > 0 ? (
                            <>
                                <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                                    {products.data.map((product) => (
                                        <ProductCard key={product.id} product={product} locale={lang} />
                                    ))}
                                </div>
                                
                                {/* Pagination */}
                                {products.last_page > 1 && (
                                    <div className="mt-12 flex justify-center">
                                        <div className="flex items-center gap-1">
                                            {products.links.map((link, i) => {
                                                if (link.url === null) {
                                                    return (
                                                        <span key={i} className="px-3 py-2 text-sm text-muted-foreground border rounded-md opacity-50 bg-slate-50" dangerouslySetInnerHTML={{ __html: link.label }}></span>
                                                    );
                                                }
                                                return (
                                                    <Link
                                                        key={i}
                                                        href={link.url}
                                                        className={`px-3 py-2 text-sm border rounded-md transition-colors ${link.active ? 'bg-primary text-primary-foreground border-primary font-medium' : 'bg-card hover:bg-muted text-foreground'}`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-20 text-center bg-muted/30 rounded-xl border border-dashed h-64">
                                <Search className="w-12 h-12 text-muted-foreground mb-4 opacity-20" />
                                <h3 className="text-xl font-medium text-foreground">{lang === 'en' ? 'No products found' : 'Produk tidak ditemukan'}</h3>
                                <p className="text-muted-foreground mt-2 max-w-md">{lang === 'en' ? 'We could not find products matching your filter. Please try another search or clear the filters.' : 'Kami tidak dapat menemukan produk yang sesuai dengan filter Anda. Silakan coba pencarian lain atau hapus filter.'}</p>
                                {(currentCategory || searchQuery) && (
                                    <Button variant="outline" className="mt-6" onClick={clearFilter}>
                                        {lang === 'en' ? 'Clear All Filters' : 'Hapus Semua Filter'}
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </StorefrontLayout>
    );
}
