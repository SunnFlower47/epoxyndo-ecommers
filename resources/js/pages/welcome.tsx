import { Head, Link, usePage } from '@inertiajs/react';
import React, { useState } from 'react';
import StorefrontLayout from '@/layouts/storefront-layout';
import { ProductCard } from '@/components/product-card';
import { Button } from '@/components/ui/button';
import { BannerCarousel } from '@/components/banner-carousel';
import { CategorySidebar } from '@/components/category-sidebar';

interface Banner {
    id: number;
    title: Record<string, string> | string;
    image_url: string;
    link_url?: string;
}

interface Product {
    id: number;
    name: Record<string, string> | string;
    slug: Record<string, string> | string;
    price: number | string;
    final_price: number | string;
    has_discount: boolean;
    discount_value?: number | string;
    discount_type?: string;
    primary_image?: {
        r2_url?: string;
    };
    category?: {
        name: Record<string, string> | string;
    };
    is_preorder?: boolean;
    stock: number;
}

export default function Welcome() {
    const { banners, products, shared_categories, general_settings, locale } = usePage<{ banners: Banner[], products: Product[], shared_categories: any[], general_settings: any, locale: string }>().props;
    const [activeTab, setActiveTab] = useState('Semua Produk');
    
    const currentLocale = locale || 'id';

    const filterTabs = [
        "Semua Produk",
        "Produk Terjual",
        "Preorder"
    ];

    const filteredProducts = products.filter(p => {
        if (activeTab === "Produk Terjual") {
            return (p as any).sold_count > 0;
        } else if (activeTab === "Preorder") {
            return p.is_preorder;
        }
        return true;
    });

    return (
        <StorefrontLayout>
            <Head title={`Beranda | ${general_settings?.company_name || 'PT Epoxyndo Art Lestari'}`} />
            
            {/* Main Content Area */}
            <div className="container mx-auto px-4 py-6 md:px-6">
                
                <BannerCarousel banners={banners} locale={currentLocale} />

                <div className="flex flex-col md:flex-row gap-8">
                    
                    {/* Left Sidebar - Etalase Toko */}
                    <aside className="hidden md:block w-64 shrink-0">
                        <CategorySidebar categories={shared_categories} locale={currentLocale} />
                    </aside>

                    {/* Right Content - Products */}
                    <div className="flex-1">
                        <div className="flex overflow-x-auto gap-2 mb-6 border-b pb-4 scrollbar-none snap-x">
                            {filterTabs.map((tab) => (
                                <button
                                    key={tab}
                                    onClick={() => setActiveTab(tab)}
                                    className={`whitespace-nowrap snap-start px-4 py-2 text-sm font-medium rounded-full transition-colors ${
                                        activeTab === tab 
                                            ? 'bg-primary text-primary-foreground shadow-sm' 
                                            : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                    }`}
                                >
                                    {tab}
                                </button>
                            ))}
                        </div>

                        {/* Product Grid */}
                        <div className="mb-8 flex items-center justify-between">
                            <h2 className="text-2xl font-bold text-slate-800">
                                {activeTab === 'Semua Produk' ? 'Rekomendasi Produk' : activeTab}
                            </h2>
                            <Link href="/products" className="text-sm font-medium text-primary hover:underline">
                                Lihat Semua
                            </Link>
                        </div>

                        {filteredProducts && filteredProducts.length > 0 ? (
                            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                                {filteredProducts.map((product) => (
                                    <ProductCard key={product.id} product={product} locale={currentLocale} />
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-20 text-center bg-muted/30 rounded-xl border border-dashed">
                                <div className="w-16 h-16 mb-4 rounded-full bg-muted flex items-center justify-center">
                                    <svg className="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-foreground">Belum ada produk</h3>
                                <p className="text-muted-foreground mt-1 max-w-sm">Produk untuk kategori ini belum tersedia saat ini. Silakan periksa kembali nanti.</p>
                            </div>
                        )}
                        
                        {products && products.length > 0 && (
                            <div className="mt-10 text-center">
                                <Link href="/products">
                                    <Button variant="outline" size="lg" className="border-primary text-primary hover:bg-primary/5 min-w-[200px]">
                                        Muat Lebih Banyak
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </StorefrontLayout>
    );
}
