import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';

interface Product {
    id: number;
    name: any;
    price: number | string;
    final_price?: number | string;
    slug: any;
    primary_image?: {
        r2_url?: string;
    };
    stock: number;
}

export interface CartItem {
    id: string; // Unique local ID for the cart item, e.g., `${productId}` or `${productId}-${variantId}`
    product_id: number;
    variant_id?: string;
    product: Product & { variant?: any, variant_id?: string };
    quantity: number;
}

interface CartState {
    items: CartItem[];
    isOpen: boolean;
    setIsOpen: (isOpen: boolean) => void;
    
    // Actions
    addItem: (product: Product & { variant?: any, variant_id?: string }, quantity?: number) => void;
    removeItem: (itemId: string) => void;
    updateQuantity: (itemId: string, quantity: number) => void;
    clearCart: () => void;
    
    // Sync with DB
    syncWithDatabase: () => Promise<void>;
    fetchFromDatabase: () => Promise<void>;
    
    // Computed (we use getters in components, but can expose helpers)
    getTotalItems: () => number;
    getTotalPrice: () => number;
}

export const useCartStore = create<CartState>()(
    persist(
        (set, get) => ({
            items: [],
            isOpen: false,
            
            setIsOpen: (isOpen) => set({ isOpen }),
            
            addItem: (product, quantity = 1) => {
                set((state) => {
                    const variantId = product.variant_id;
                    const itemId = variantId ? `${product.id}-${variantId}` : `${product.id}`;
                    
                    const existingItemIndex = state.items.findIndex(item => item.id === itemId);
                    
                    const maxStock = product.variant ? product.variant.stock : product.stock;

                    if (existingItemIndex >= 0) {
                        // Update quantity if item exists
                        const newItems = [...state.items];
                        const newQuantity = newItems[existingItemIndex].quantity + quantity;
                        
                        // Limit to stock
                        if (newQuantity > maxStock && !product.is_preorder) {
                            newItems[existingItemIndex].quantity = maxStock;
                        } else {
                            newItems[existingItemIndex].quantity = newQuantity;
                        }
                        
                        return { items: newItems, isOpen: true };
                    }
                    
                    // Add new item
                    const finalQty = quantity > maxStock && !product.is_preorder ? maxStock : quantity;
                    
                    const newItem: CartItem = {
                        id: itemId,
                        product_id: product.id,
                        variant_id: variantId,
                        product,
                        quantity: finalQty
                    };
                    
                    return { items: [...state.items, newItem], isOpen: true };
                });
            },
            
            removeItem: (itemId) => {
                set((state) => ({
                    items: state.items.filter(item => item.id !== itemId)
                }));
            },
            
            updateQuantity: (itemId, quantity) => {
                set((state) => ({
                    items: state.items.map(item => {
                        if (item.id === itemId) {
                            // Enforce min 1 and max stock
                            const maxStock = item.product.variant ? item.product.variant.stock : item.product.stock;
                            let newQty = Math.max(1, quantity);
                            if (newQty > maxStock && !item.product.is_preorder) {
                                newQty = maxStock;
                            }
                            return { ...item, quantity: newQty };
                        }
                        return item;
                    })
                }));
            },
            
            clearCart: () => set({ items: [] }),
            
            syncWithDatabase: async () => {
                // If the user logs in, we sync local cart to database
                const items = get().items;
                if (items.length === 0) return;
                
                try {
                    const payload = items.map(item => ({
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        quantity: item.quantity
                    }));
                    
                    await fetch('/api/cart/sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ items: payload })
                    });
                    // After sync, fetch the fresh cart from DB to ensure consistency
                    await get().fetchFromDatabase();
                } catch (error) {
                    console.error("Failed to sync cart:", error);
                }
            },
            
            fetchFromDatabase: async () => {
                // When authenticated, fetch cart from DB
                try {
                    const response = await fetch('/api/cart', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data && data.items) {
                            // Transform DB cart items to local format if needed
                            const dbItems: CartItem[] = data.items.map((dbItem: any) => ({
                                id: dbItem.variant_id ? `${dbItem.product_id}-${dbItem.variant_id}` : `${dbItem.product_id}`,
                                product_id: dbItem.product_id,
                                variant_id: dbItem.variant_id,
                                product: { ...dbItem.product, variant: dbItem.variant },
                                quantity: dbItem.quantity
                            }));
                            set({ items: dbItems });
                        }
                    }
                } catch (error) {
                    console.error("Failed to fetch cart:", error);
                }
            },
            
            getTotalItems: () => {
                return get().items.reduce((total, item) => total + item.quantity, 0);
            },
            
            getTotalPrice: () => {
                return get().items.reduce((total, item) => {
                    const price = item.product.variant ? item.product.variant.price : (item.product.final_price || item.product.price || (item.product as any).price || 0);
                    return total + (Number(price) * item.quantity);
                }, 0);
            }
        }),
        {
            name: 'epoxyndo-cart-storage',
            storage: createJSONStorage(() => localStorage),
            partialize: (state) => ({ items: state.items }), // Only persist items
        }
    )
);
