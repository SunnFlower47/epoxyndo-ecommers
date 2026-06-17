import React, { useState, useEffect } from "react";
import { Head, usePage, router } from "@inertiajs/react";
import StorefrontLayout from "@/layouts/storefront-layout";
import { useCartStore } from "@/stores/use-cart-store";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";
import { MapPin, CheckCircle2 } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Badge } from "@/components/ui/badge";

export default function Checkout() {
    const { auth, general_settings, midtrans_client_key, midtrans_is_production, flash, addresses = [] } = usePage<any>().props;
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

    // Address logic
    const primaryAddress = addresses.find((a: any) => a.is_primary) || addresses[0];
    const [selectedAddress, setSelectedAddress] = useState<any>(primaryAddress || null);
    const [isAddressModalOpen, setIsAddressModalOpen] = useState(false);

    const [form, setForm] = useState({
        customer_name: auth.user?.name || "",
        customer_email: auth.user?.email || "",
        customer_phone: auth.user?.phone || "",
        shipping_address: "",
        city: "",
        postal_code: "",
        courier: "",
        courier_service: "",
        shipping_cost: 0,
        coupon_code: "",
    });

    useEffect(() => {
        if (selectedAddress) {
            setForm(prev => ({
                ...prev,
                customer_name: selectedAddress.recipient_name,
                customer_phone: selectedAddress.phone_number,
                shipping_address: selectedAddress.full_address,
                city: selectedAddress.city,
                postal_code: selectedAddress.postal_code,
                courier: "",
                courier_service: "",
                shipping_cost: 0,
            }));
        }
    }, [selectedAddress]);

    const [errors, setErrors] = useState<any>({});
    const [shippingRates, setShippingRates] = useState<any[]>([]);
    const [loadingRates, setLoadingRates] = useState(false);
    
    // Coupon state
    const [couponInput, setCouponInput] = useState("");
    const [appliedCoupon, setAppliedCoupon] = useState<string | null>(null);
    const [discountAmount, setDiscountAmount] = useState(0);
    const [couponMessage, setCouponMessage] = useState<{text: string, type: 'success' | 'error'} | null>(null);
    const [applyingCoupon, setApplyingCoupon] = useState(false);

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
                            items: items.map(i => ({ product_id: i.product_id, variant_id: i.variant_id, quantity: i.quantity }))
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
                variant_id: item.variant_id,
                quantity: item.quantity
            }))
        }, {
            onError: (err) => {
                setErrors(err);
                setProcessing(false);
            }
        });
    };

    const handleApplyCoupon = async () => {
        if (!couponInput) return;
        setApplyingCoupon(true);
        setCouponMessage(null);

        try {
            const response = await fetch('/api/coupons/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    coupon_code: couponInput,
                    subtotal: getTotalPrice()
                })
            });

            const data = await response.json();

            if (data.success) {
                setDiscountAmount(data.discount_amount);
                setAppliedCoupon(couponInput);
                setForm('coupon_code', couponInput);
                setCouponMessage({ text: data.message, type: 'success' });
            } else {
                setDiscountAmount(0);
                setAppliedCoupon(null);
                setForm('coupon_code', "");
                setCouponMessage({ text: data.message, type: 'error' });
            }
        } catch (error) {
            setCouponMessage({ text: 'Gagal memverifikasi kupon.', type: 'error' });
        } finally {
            setApplyingCoupon(false);
        }
    };

    const removeCoupon = () => {
        setAppliedCoupon(null);
        setDiscountAmount(0);
        setCouponInput("");
        setForm('coupon_code', "");
        setCouponMessage(null);
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
    const taxAmount = ((subtotal - discountAmount) * taxPercentage) / 100;
    const grandTotal = subtotal - discountAmount + form.shipping_cost + taxAmount;

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
                    <div className="lg:w-2/3 space-y-6">
                        
                        {/* Address Selection Section for Logged in Users */}
                        {auth.user && (
                            <div className="bg-card border rounded-xl p-6 shadow-sm">
                                <div className="flex justify-between items-center mb-4">
                                    <h2 className="text-xl font-semibold">Alamat Pengiriman</h2>
                                    <Dialog open={isAddressModalOpen} onOpenChange={setIsAddressModalOpen}>
                                        <DialogTrigger asChild>
                                            <Button variant="outline" size="sm">Pilih Alamat Lain</Button>
                                        </DialogTrigger>
                                        <DialogContent className="max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle>Pilih Alamat Pengiriman</DialogTitle>
                                            </DialogHeader>
                                            <div className="space-y-4 max-h-[60vh] overflow-y-auto pr-2 mt-4">
                                                {addresses.length === 0 ? (
                                                    <div className="text-center py-8 text-muted-foreground">
                                                        Belum ada alamat tersimpan. 
                                                        <Button variant="link" onClick={() => router.visit('/addresses')}>Tambah Alamat</Button>
                                                    </div>
                                                ) : (
                                                    addresses.map((address: any) => (
                                                        <Card 
                                                            key={address.id} 
                                                            className={`cursor-pointer transition-all hover:border-primary ${selectedAddress?.id === address.id ? 'border-primary ring-1 ring-primary' : ''}`}
                                                            onClick={() => {
                                                                setSelectedAddress(address);
                                                                setIsAddressModalOpen(false);
                                                            }}
                                                        >
                                                            <CardContent className="p-4 flex items-start gap-3">
                                                                <MapPin className={`w-5 h-5 mt-0.5 ${selectedAddress?.id === address.id ? 'text-primary' : 'text-muted-foreground'}`} />
                                                                <div className="flex-1">
                                                                    <div className="flex items-center gap-2 mb-1">
                                                                        <span className="font-semibold">{address.recipient_name}</span>
                                                                        <span className="text-sm text-muted-foreground">({address.title || 'Alamat'})</span>
                                                                        {address.is_primary && <Badge variant="secondary" className="text-xs">Utama</Badge>}
                                                                    </div>
                                                                    <p className="text-sm">{address.phone_number}</p>
                                                                    <p className="text-sm text-muted-foreground mt-1">{address.full_address}</p>
                                                                    <p className="text-sm text-muted-foreground">
                                                                        {address.district ? `${address.district}, ` : ''}{address.city}, {address.province} {address.postal_code}
                                                                    </p>
                                                                </div>
                                                                {selectedAddress?.id === address.id && (
                                                                    <CheckCircle2 className="w-5 h-5 text-primary" />
                                                                )}
                                                            </CardContent>
                                                        </Card>
                                                    ))
                                                )}
                                                <Button variant="outline" className="w-full" onClick={() => {
                                                    setSelectedAddress(null);
                                                    setIsAddressModalOpen(false);
                                                }}>
                                                    Gunakan Alamat Manual Baru
                                                </Button>
                                            </div>
                                        </DialogContent>
                                    </Dialog>
                                </div>

                                {selectedAddress ? (
                                    <div className="p-4 bg-muted/30 rounded-lg border">
                                        <div className="flex items-center gap-2 mb-2">
                                            <span className="font-semibold">{selectedAddress.recipient_name}</span>
                                            <span className="text-sm text-muted-foreground">({selectedAddress.phone_number})</span>
                                        </div>
                                        <p className="text-sm text-muted-foreground">{selectedAddress.full_address}</p>
                                        <p className="text-sm text-muted-foreground">
                                            {selectedAddress.district ? `${selectedAddress.district}, ` : ''}{selectedAddress.city}, {selectedAddress.province} {selectedAddress.postal_code}
                                        </p>
                                    </div>
                                ) : (
                                    <p className="text-sm text-muted-foreground">Mengisi form alamat manual di bawah.</p>
                                )}
                            </div>
                        )}

                        <div className="bg-card border rounded-xl p-6 shadow-sm">
                            <h2 className="text-xl font-semibold mb-6">{selectedAddress ? "Detail Pengiriman (Otomatis)" : "Isi Alamat Pengiriman"}</h2>
                            
                            <form id="checkout-form" onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="customer_name">Nama Penerima</Label>
                                        <Input id="customer_name" name="customer_name" value={form.customer_name} onChange={handleChange} required disabled={!!selectedAddress} />
                                        {errors.customer_name && <p className="text-red-500 text-xs">{errors.customer_name}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="customer_email">Email Pemesan</Label>
                                        <Input id="customer_email" type="email" name="customer_email" value={form.customer_email} onChange={handleChange} required />
                                        {errors.customer_email && <p className="text-red-500 text-xs">{errors.customer_email}</p>}
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <Label htmlFor="customer_phone">Nomor Telepon / WhatsApp</Label>
                                    <Input id="customer_phone" name="customer_phone" value={form.customer_phone} onChange={handleChange} required disabled={!!selectedAddress} />
                                    {errors.customer_phone && <p className="text-red-500 text-xs">{errors.customer_phone}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="shipping_address">Alamat Lengkap</Label>
                                    <Textarea id="shipping_address" name="shipping_address" value={form.shipping_address} onChange={handleChange} required rows={3} disabled={!!selectedAddress} />
                                    {errors.shipping_address && <p className="text-red-500 text-xs">{errors.shipping_address}</p>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="city">Kota / Kabupaten</Label>
                                        <Input id="city" name="city" value={form.city} onChange={handleChange} required disabled={!!selectedAddress} />
                                        {errors.city && <p className="text-red-500 text-xs">{errors.city}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="postal_code">Kode Pos</Label>
                                        <Input id="postal_code" name="postal_code" value={form.postal_code} onChange={handleChange} required placeholder="Ketik kode pos untuk cek ongkir..." disabled={!!selectedAddress} />
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
                                {items.map((item) => {
                                    const baseName = typeof item.product.name === "string" ? item.product.name : (item.product.name?.id || "Produk");
                                    const name = item.product.variant ? `${baseName} - ${item.product.variant.label}` : baseName;
                                    const price = item.product.variant ? (item.product.variant.final_price || item.product.variant.price) : (item.product.final_price || item.product.price);
                                    
                                    return (
                                        <div key={item.id} className="flex justify-between items-start gap-4 text-sm">
                                            <div>
                                                <p className="font-medium">{name}</p>
                                                <p className="text-muted-foreground">{item.quantity} x {formatCurrency(price)}</p>
                                            </div>
                                            <p className="font-medium text-right shrink-0">
                                                {formatCurrency(price * item.quantity)}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>

                            <div className="border-t pt-4 mb-4">
                                <label className="text-sm font-medium mb-2 block">Punya Kupon Diskon?</label>
                                {appliedCoupon ? (
                                    <div className="flex items-center justify-between bg-green-50 text-green-700 p-3 rounded-md border border-green-200">
                                        <div className="flex items-center gap-2">
                                            <span className="font-semibold text-sm">{appliedCoupon}</span>
                                            <span className="text-xs bg-green-100 px-2 py-0.5 rounded-full">Aktif</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={removeCoupon}
                                            className="text-xs text-red-500 hover:text-red-700 font-medium"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                ) : (
                                    <div className="flex gap-2">
                                        <Input 
                                            placeholder="Masukkan kode kupon" 
                                            value={couponInput}
                                            onChange={(e) => setCouponInput(e.target.value.toUpperCase())}
                                            className="uppercase"
                                        />
                                        <Button 
                                            type="button" 
                                            variant="outline" 
                                            onClick={handleApplyCoupon}
                                            disabled={!couponInput || applyingCoupon}
                                        >
                                            {applyingCoupon ? "..." : "Gunakan"}
                                        </Button>
                                    </div>
                                )}
                                {couponMessage && (
                                    <p className={`text-xs mt-2 ${couponMessage.type === 'success' ? 'text-green-600' : 'text-red-500'}`}>
                                        {couponMessage.text}
                                    </p>
                                )}
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
                                {discountAmount > 0 && (
                                    <div className="flex justify-between text-sm text-green-600">
                                        <span className="font-medium">Diskon Kupon</span>
                                        <span className="font-medium">-{formatCurrency(discountAmount)}</span>
                                    </div>
                                )}
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
