import { Link } from '@inertiajs/react';
import React from 'react';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ShoppingCart } from 'lucide-react';
import { useCartStore } from '@/stores/use-cart-store';

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

export function ProductCard({ product, locale = 'id' }: { product: Product, locale?: string }) {
    const { addItem } = useCartStore();
    
    // Helper to extract translated string
    const getTranslated = (field: Record<string, string> | string | undefined, defaultLocale = 'id') => {
        if (!field) return '';
        if (typeof field === 'string') {
            try {
                const parsed = JSON.parse(field);
                return parsed[locale] || parsed[defaultLocale] || field;
            } catch (e) {
                return field;
            }
        }
        return field[locale] || field[defaultLocale] || '';
    };

    const name = getTranslated(product.name);
    const slug = getTranslated(product.slug);
    const categoryName = product.category ? getTranslated(product.category.name) : '';
    
    // Formatting currency
    const formatCurrency = (amount: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(amount));
    };

    const originalPrice = Number(product.price);
    const finalPrice = Number(product.final_price);
    const imageUrl = product.primary_image?.r2_url || '/placeholder-product.webp';

    const handleAddToCart = () => {
        addItem(product as any);
    };

    return (
        <Card className="overflow-hidden flex flex-col h-full group hover:shadow-md transition-shadow">
            <Link href={`/p/${slug}`} className="relative aspect-square overflow-hidden bg-muted block">
                {product.is_preorder && (
                    <Badge variant="secondary" className="absolute top-2 left-2 z-10 bg-secondary text-white border-none">
                        Preorder
                    </Badge>
                )}
                {product.has_discount && !product.is_preorder && (
                    <Badge variant="destructive" className="absolute top-2 left-2 z-10">
                        {product.discount_type === 'percentage' ? `${Number(product.discount_value)}% OFF` : 'Diskon'}
                    </Badge>
                )}
                <img 
                    src={imageUrl} 
                    alt={name} 
                    className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300"
                    onError={(e) => {
                        (e.target as HTMLImageElement).src = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20200%20200%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_18c4e4b7b25%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3Avar(--font-sans)%2C%20Helvetica%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_18c4e4b7b25%22%3E%3Crect%20width%3D%22200%22%20height%3D%22200%22%20fill%3D%22%23F1F5F9%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2274.4296875%22%20y%3D%22104.5%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E';
                    }}
                />
            </Link>
            
            <CardContent className="p-4 flex-grow flex flex-col">
                <div className="text-xs text-muted-foreground mb-1 line-clamp-1">{categoryName}</div>
                <Link href={`/p/${slug}`} className="hover:text-primary transition-colors">
                    <h3 className="font-semibold text-sm line-clamp-2 mb-2 min-h-[40px]">{name}</h3>
                </Link>
                
                <div className="mt-auto">
                    {product.has_discount ? (
                        <div className="flex flex-col">
                            <span className="text-lg font-bold text-primary">{formatCurrency(finalPrice)}</span>
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-muted-foreground line-through decoration-destructive decoration-2">
                                    {formatCurrency(originalPrice)}
                                </span>
                            </div>
                        </div>
                    ) : (
                        <span className="text-lg font-bold text-foreground">{formatCurrency(originalPrice)}</span>
                    )}
                </div>
            </CardContent>
            
            <CardFooter className="p-4 pt-0">
                <Button 
                    className="w-full gap-2" 
                    disabled={product.stock <= 0 && !product.is_preorder}
                    onClick={handleAddToCart}
                >
                    <ShoppingCart className="w-4 h-4" />
                    {product.stock <= 0 && !product.is_preorder ? 'Stok Habis' : 'Keranjang'}
                </Button>
            </CardFooter>
        </Card>
    );
}
