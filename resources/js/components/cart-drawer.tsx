import React, { useEffect, useState } from 'react';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetFooter,
} from "@/components/ui/sheet";
import { Button } from '@/components/ui/button';
import { useCartStore } from '@/stores/use-cart-store';
import { ShoppingCart, Trash2, Plus, Minus, X } from 'lucide-react';
import { Link } from '@inertiajs/react';

export function CartDrawer() {
    const { items, isOpen, setIsOpen, removeItem, updateQuantity, getTotalPrice, fetchFromDatabase } = useCartStore();
    const [isHydrated, setIsHydrated] = useState(false);

    useEffect(() => {
        setIsHydrated(true);
        // We could call fetchFromDatabase here if we are checking auth state, but 
        // usually we do that in a higher level layout or component
    }, []);

    const formatCurrency = (amount: number | string) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(amount));
    };

    if (!isHydrated) return null;

    return (
        <Sheet open={isOpen} onOpenChange={setIsOpen}>
            <SheetContent className="w-full sm:max-w-md flex flex-col">
                <SheetHeader className="pb-4 border-b">
                    <SheetTitle className="flex items-center gap-2">
                        <ShoppingCart className="w-5 h-5" />
                        Keranjang Belanja ({items.reduce((acc, item) => acc + item.quantity, 0)})
                    </SheetTitle>
                </SheetHeader>

                <div className="flex-1 overflow-y-auto py-4 -mx-6 px-6">
                    {items.length === 0 ? (
                        <div className="h-full flex flex-col items-center justify-center text-center space-y-4">
                            <div className="w-20 h-20 bg-muted rounded-full flex items-center justify-center">
                                <ShoppingCart className="w-10 h-10 text-muted-foreground opacity-50" />
                            </div>
                            <div>
                                <h3 className="font-medium text-lg">Keranjang Kosong</h3>
                                <p className="text-muted-foreground text-sm max-w-[250px] mt-1">
                                    Anda belum menambahkan produk apa pun ke keranjang.
                                </p>
                            </div>
                            <Button variant="outline" className="mt-4" onClick={() => setIsOpen(false)}>
                                Lanjut Belanja
                            </Button>
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {items.map((item) => {
                                const name = typeof item.product.name === 'string' 
                                    ? (item.product.name.startsWith('{') ? JSON.parse(item.product.name).id || JSON.parse(item.product.name).en : item.product.name)
                                    : (item.product.name?.id || item.product.name?.en || 'Produk');
                                    
                                const price = item.product.final_price || item.product.price;
                                const imageUrl = item.product.primary_image?.r2_url || '/placeholder-product.webp';

                                return (
                                    <div key={item.id} className="flex gap-4 border-b pb-4 last:border-0 last:pb-0">
                                        <div className="w-20 h-20 rounded-md overflow-hidden bg-muted shrink-0">
                                            <img src={imageUrl} alt={name} className="w-full h-full object-cover" />
                                        </div>
                                        <div className="flex-1 flex flex-col justify-between">
                                            <div className="flex justify-between items-start gap-2">
                                                <h4 className="font-medium text-sm line-clamp-2">{name}</h4>
                                                <button 
                                                    onClick={() => removeItem(item.product_id)}
                                                    className="text-muted-foreground hover:text-destructive shrink-0"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                            <div className="flex items-end justify-between mt-2">
                                                <span className="font-bold text-sm text-primary">
                                                    {formatCurrency(price)}
                                                </span>
                                                <div className="flex items-center border rounded-md">
                                                    <button 
                                                        className="p-1 text-muted-foreground hover:text-foreground disabled:opacity-50"
                                                        onClick={() => updateQuantity(item.product_id, item.quantity - 1)}
                                                        disabled={item.quantity <= 1}
                                                    >
                                                        <Minus className="w-3 h-3" />
                                                    </button>
                                                    <span className="px-2 text-xs font-medium w-8 text-center">
                                                        {item.quantity}
                                                    </span>
                                                    <button 
                                                        className="p-1 text-muted-foreground hover:text-foreground disabled:opacity-50"
                                                        onClick={() => updateQuantity(item.product_id, item.quantity + 1)}
                                                        disabled={item.quantity >= item.product.stock && !item.product.is_preorder}
                                                    >
                                                        <Plus className="w-3 h-3" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {items.length > 0 && (
                    <SheetFooter className="border-t pt-4 flex-col gap-4 sm:flex-col">
                        <div className="flex justify-between items-center w-full">
                            <span className="text-muted-foreground">Total Estimasi</span>
                            <span className="font-bold text-lg">{formatCurrency(getTotalPrice())}</span>
                        </div>
                        <Button className="w-full" asChild onClick={() => setIsOpen(false)}>
                            <Link href="/checkout">
                                Checkout
                            </Link>
                        </Button>
                    </SheetFooter>
                )}
            </SheetContent>
        </Sheet>
    );
}
