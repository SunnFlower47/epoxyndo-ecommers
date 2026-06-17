import React, { useState, useEffect } from "react";
import { Head, usePage, router } from "@inertiajs/react";
import StorefrontLayout from "@/layouts/storefront-layout";
import { useCartStore } from "@/stores/use-cart-store";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";

export default function Checkout() {
    const { auth, general_settings, midtrans_client_key, midtrans_is_production, flash } = usePage<any>().props;
    const { items, getTotalPrice, clearCart } = useCartStore();
    const [processing, setProcessing] = useState(false);
    
    // Inject Midtrans Snap
    useEffect(() => {
        const snapScript = midtrans_is_production 
            ? "https://app.midtrans.com/snap/snap.js"
            : "https://app.sandbox.midtrans.com/snap/snap.js";

        const script = document.createElement("script");
        script.src = snapScript;
        script.setAttribute("data-client-key", midtrans_client_key);
        script.async = true;
        document.body.appendChild(script);

        return () => {
            document.body.removeChild(script);
        };
    }, [midtrans_client_key, midtrans_is_production]);

    // Check for Flash Messages or Tokens
    useEffect(() => {
        if (flash?.error) {
            alert(flash.error);
            setProcessing(false);
        }
        
        if (flash?.snapToken) {
            setProcessing(true); // Keep button as processing while popup is open
            // Wait for snap to load
            const checkSnap = setInterval(() => {
                if ((window as any).snap) {
                    clearInterval(checkSnap);
                    (window as any).snap.pay(flash.snapToken, {
                        onSuccess: function(result: any) {
                            window.location.href = '/dashboard';
                        },
                        onPending: function(result: any) {
                            window.location.href = '/dashboard';
                        },
                        onError: function(result: any) {
                            alert("Pembayaran gagal!");
                            setProcessing(false);
                            window.location.href = '/dashboard';
                        },
                        onClose: function() {
                            setProcessing(false);
                            window.location.href = '/dashboard';
                        }
                    });
                }
            }, 500);

            // Timeout after 10 seconds if snap isn't loaded
            setTimeout(() => {
                clearInterval(checkSnap);
                setProcessing(false);
            }, 10000);
        }
    }, [flash]);

    const [form, setForm] = useState({
        customer_name: auth.user?.name || "",
        customer_email: auth.user?.email || "",
        customer_phone: auth.user?.phone || "",
        shipping_address: auth.user?.address || "",
        city: auth.user?.city || "",
        postal_code: auth.user?.postal_code || "",
        courier: "",
        courier_service: "",
        shipping_cost: 0,
    });

    const [errors, setErrors] = useState<any>({});
    const [shippingRates, setShippingRates] = useState<any[]>([]);
    const [loadingRates, setLoadingRates] = useState(false);

    useEffect(() => {
        if (items.length === 0 && !flash?.snapToken) {
            window.location.href = "/products";
        }
    }, [items, flash?.snapToken]);

    // Fetch Rates when address info changes
    useEffect(() => {
        const fetchRates = async () => {
            if (form.postal_code && form.postal_code.length >= 4) {
                setLoadingRates(true);
                try {
                    const response = await fetch('/api/shipping-rates', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            destination_postal_code: form.postal_code,
                            items: items.map(i => ({ product_id: i.product_id, quantity: i.quantity }))
                        })
                    });
                    const data = await response.json();
                    if (data.pricing) {
                        setShippingRates(data.pricing);
                    }
                } catch (err) {
                    console.error("Failed to fetch rates:", err);
                } finally {
                    setLoadingRates(false);
                }
            }
        };

        const debounce = setTimeout(() => {
            fetchRates();
        }, 1000);

        return () => clearTimeout(debounce);
    }, [form.postal_code, items]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleServiceChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const val = e.target.value;
        if (val) {
            const [courier, service, price] = val.split("|");
            setForm({ 
                ...form, 
                courier: courier, 
                courier_service: service, 
                shipping_cost: parseInt(price) 
            });
        } else {
            setForm({ ...form, courier: "", courier_service: "", shipping_cost: 0 });
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!form.courier) {
            alert("Silakan pilih layanan pengiriman.");
            return;
        }

        setProcessing(true);
        setErrors({});

        router.post("/checkout", {
            ...form,
            items: items.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            }))
        }, {
            onError: (err) => {
                setErrors(err);
                setProcessing(false);
            }
        });
    };

    const formatCurrency = (amount: number | string) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(amount));
    };

    const taxPercentage = general_settings?.tax_percentage || 11;
    const subtotal = getTotalPrice();
    const taxAmount = (subtotal * taxPercentage) / 100;
    const grandTotal = subtotal + form.shipping_cost + taxAmount;

    return (
        <StorefrontLayout>
            <Head title="Checkout" />
            
            <div className="container mx-auto px-4 py-8 max-w-6xl">
                <h1 className="text-3xl font-bold mb-8">Checkout</h1>

                {!auth.user && (
                    <div className="bg-primary/5 border border-primary/20 rounded-lg p-4 mb-8">
                        <p className="text-sm">
                            <strong>Punya akun?</strong> Login sekarang agar form otomatis terisi dan dapatkan poin reward! 
                            <a href="/login" className="text-primary font-medium ml-2 hover:underline">Login di sini</a>
                        </p>
                    </div>
                )}

                <div className="flex flex-col lg:flex-row gap-8">
                    <div className="lg:w-2/3">
                        <div className="bg-card border rounded-xl p-6 shadow-sm">
                            <h2 className="text-xl font-semibold mb-6">Informasi Pengiriman</h2>
                            
                            <form id="checkout-form" onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="customer_name">Nama Lengkap</Label>
                                        <Input id="customer_name" name="customer_name" value={form.customer_name} onChange={handleChange} required />
                                        {errors.customer_name && <p className="text-red-500 text-xs">{errors.customer_name}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="customer_email">Email</Label>
                                        <Input id="customer_email" type="email" name="customer_email" value={form.customer_email} onChange={handleChange} required />
                                        {errors.customer_email && <p className="text-red-500 text-xs">{errors.customer_email}</p>}
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <Label htmlFor="customer_phone">Nomor Telepon / WhatsApp</Label>
                                    <Input id="customer_phone" name="customer_phone" value={form.customer_phone} onChange={handleChange} required />
                                    {errors.customer_phone && <p className="text-red-500 text-xs">{errors.customer_phone}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="shipping_address">Alamat Lengkap</Label>
                                    <Textarea id="shipping_address" name="shipping_address" value={form.shipping_address} onChange={handleChange} required rows={3} />
                                    {errors.shipping_address && <p className="text-red-500 text-xs">{errors.shipping_address}</p>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="city">Kota / Kabupaten</Label>
                                        <Input id="city" name="city" value={form.city} onChange={handleChange} required />
                                        {errors.city && <p className="text-red-500 text-xs">{errors.city}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="postal_code">Kode Pos</Label>
                                        <Input id="postal_code" name="postal_code" value={form.postal_code} onChange={handleChange} required placeholder="Ketik kode pos untuk cek ongkir..." />
                                        {errors.postal_code && <p className="text-red-500 text-xs">{errors.postal_code}</p>}
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <div className="space-y-2">
                                        <Label htmlFor="courier_select">Layanan Pengiriman {loadingRates && <span className="text-primary text-xs ml-2 animate-pulse">Memuat tarif...</span>}</Label>
                                        <select 
                                            id="courier_select" 
                                            onChange={handleServiceChange} 
                                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                            required
                                        >
                                            <option value="">-- Pilih Layanan --</option>
                                            {shippingRates.map((rate: any, idx) => (
                                                <option key={idx} value={`${rate.company}|${rate.type}|${rate.price}`}>
                                                    {rate.company.toUpperCase()} - {rate.type} ({formatCurrency(rate.price)})
                                                </option>
                                            ))}
                                            {shippingRates.length === 0 && !loadingRates && (
                                                <option disabled>Masukkan kode pos untuk melihat layanan.</option>
                                            )}
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div className="lg:w-1/3">
                        <div className="bg-card border rounded-xl p-6 shadow-sm sticky top-24">
                            <h2 className="text-xl font-semibold mb-6">Ringkasan Pesanan</h2>
                            
                            <div className="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2">
                                {items.map((item) => (
                                    <div key={item.id} className="flex justify-between items-start gap-4 text-sm">
                                        <div>
                                            <p className="font-medium">{typeof item.product.name === "string" ? item.product.name : item.product.name?.id}</p>
                                            <p className="text-muted-foreground">{item.quantity} x {formatCurrency(item.product.final_price || item.product.price)}</p>
                                        </div>
                                        <p className="font-medium text-right shrink-0">
                                            {formatCurrency((item.product.final_price || item.product.price) * item.quantity)}
                                        </p>
                                    </div>
                                ))}
                            </div>

                            <div className="border-t pt-4 space-y-3">
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Subtotal Produk</span>
                                    <span className="font-medium">{formatCurrency(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Ongkos Kirim</span>
                                    <span className="font-medium">{form.shipping_cost > 0 ? formatCurrency(form.shipping_cost) : "Pilih layanan"}</span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">Pajak ({taxPercentage}%)</span>
                                    <span className="font-medium">{formatCurrency(taxAmount)}</span>
                                </div>
                                <div className="flex justify-between text-lg font-bold border-t pt-3 mt-3">
                                    <span>Total Pembayaran</span>
                                    <span className="text-primary">{formatCurrency(grandTotal)}</span>
                                </div>
                            </div>

                            <Button 
                                type="submit" 
                                form="checkout-form"
                                className="w-full mt-6 h-12 text-lg" 
                                disabled={processing || form.shipping_cost === 0}
                            >
                                {processing ? "Memproses..." : "Bayar Sekarang"}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </StorefrontLayout>
    );
}
