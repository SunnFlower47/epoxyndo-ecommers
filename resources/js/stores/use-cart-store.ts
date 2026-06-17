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
    product: Product;
    quantity: number;
}

interface CartState {
    items: CartItem[];
    isOpen: boolean;
    setIsOpen: (isOpen: boolean) => void;
    
    // Actions
    addItem: (product: Product, quantity?: number) => void;
    removeItem: (productId: number) => void;
    updateQuantity: (productId: number, quantity: number) => void;
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
                    const existingItemIndex = state.items.findIndex(item => item.product_id === product.id);
                    
                    if (existingItemIndex >= 0) {
                        // Update quantity if item exists
                        const newItems = [...state.items];
                        const newQuantity = newItems[existingItemIndex].quantity + quantity;
                        
                        // Limit to stock
                        if (newQuantity > product.stock && !product.is_preorder) {
                            newItems[existingItemIndex].quantity = product.stock;
                        } else {
                            newItems[existingItemIndex].quantity = newQuantity;
                        }
                        
                        return { items: newItems, isOpen: true };
                    }
                    
                    // Add new item
                    const finalQty = quantity > product.stock && !product.is_preorder ? product.stock : quantity;
                    
                    const newItem: CartItem = {
                        id: `${product.id}`,
                        product_id: product.id,
                        product,
                        quantity: finalQty
                    };
                    
                    return { items: [...state.items, newItem], isOpen: true };
                });
            },
            
            removeItem: (productId) => {
                set((state) => ({
                    items: state.items.filter(item => item.product_id !== productId)
                }));
            },
            
            updateQuantity: (productId, quantity) => {
                set((state) => ({
                    items: state.items.map(item => {
                        if (item.product_id === productId) {
                            // Enforce min 1 and max stock
                            let newQty = Math.max(1, quantity);
                            if (newQty > item.product.stock && !item.product.is_preorder) {
                                newQty = item.product.stock;
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
                                id: `${dbItem.product_id}`,
                                product_id: dbItem.product_id,
                                product: dbItem.product,
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
                    const price = item.product.final_price || item.product.price || (item.product as any).price || 0;
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
