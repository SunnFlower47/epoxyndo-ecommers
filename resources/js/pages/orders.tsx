import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Package, Truck, CheckCircle, Clock, Star, MessageSquare } from "lucide-react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { toast } from "sonner";

export default function Orders() {
    const { orders, midtrans_is_production, midtrans_client_key } = usePage<any>().props;
    const [activeTab, setActiveTab] = useState('all');
    
    // Review state
    const [isReviewModalOpen, setIsReviewModalOpen] = useState(false);
    const [reviewProduct, setReviewProduct] = useState<{ id: number; name: string } | null>(null);
    const [rating, setRating] = useState(5);
    const [hoveredRating, setHoveredRating] = useState(0);
    const [comment, setComment] = useState("");
    const [isSubmittingReview, setIsSubmittingReview] = useState(false);

    const openReviewModal = (productId: number, productName: string) => {
        setReviewProduct({ id: productId, name: productName });
        setRating(5);
        setComment("");
        setIsReviewModalOpen(true);
    };

    const submitReview = (e: React.FormEvent) => {
        e.preventDefault();
        if (!reviewProduct) return;
        
        setIsSubmittingReview(true);
        fetch('/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
            },
            body: JSON.stringify({
                product_id: reviewProduct.id,
                rating,
                comment
            })
        })
        .then(async res => {
            if (res.ok) {
                toast.success("Ulasan berhasil dikirim!");
                setIsReviewModalOpen(false);
            } else {
                const data = await res.json().catch(() => ({}));
                toast.error(data.message || "Gagal mengirim ulasan.");
            }
        })
        .catch(() => {
            toast.error("Terjadi kesalahan jaringan.");
        })
        .finally(() => {
            setIsSubmittingReview(false);
        });
    };

    useEffect(() => {
        // Load Midtrans snap.js if it hasn't been loaded
        const snapScript = midtrans_is_production 
            ? "https://app.midtrans.com/snap/snap.js"
            : "https://app.sandbox.midtrans.com/snap/snap.js";

        if (!document.querySelector(`script[src="${snapScript}"]`)) {
            const script = document.createElement("script");
            script.src = snapScript;
            script.setAttribute("data-client-key", midtrans_client_key);
            script.async = true;
            document.head.appendChild(script);
        }
    }, [midtrans_is_production]);

    const handlePayment = (snapToken: string) => {
        if ((window as any).snap) {
            (window as any).snap.pay(snapToken, {
                onSuccess: function (result: any) {
                    window.location.reload();
                },
                onPending: function (result: any) {
                    window.location.reload();
                },
                onError: function (result: any) {
                    alert("Pembayaran gagal!");
                },
                onClose: function () {
                    // Do nothing on close
                }
            });
        } else {
            alert("Sistem pembayaran sedang memuat, silakan coba beberapa saat lagi.");
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
    };

    const getStatusBadge = (status: string, paymentStatus: string) => {
        if (paymentStatus === 'unpaid') {
            return <Badge variant="destructive" className="flex gap-1"><Clock className="w-3 h-3" /> Belum Dibayar</Badge>;
        }
        
        switch (status) {
            case 'pending':
            case 'processing':
                return <Badge variant="secondary" className="flex gap-1 bg-blue-100 text-blue-800"><Package className="w-3 h-3" /> Diproses</Badge>;
            case 'shipped':
                return <Badge variant="default" className="flex gap-1 bg-amber-500 hover:bg-amber-600"><Truck className="w-3 h-3" /> Dikirim</Badge>;
            case 'completed':
                return <Badge variant="default" className="flex gap-1 bg-green-600 hover:bg-green-700"><CheckCircle className="w-3 h-3" /> Selesai</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">Dibatalkan</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    // Filter orders for tabs
    const unpaidOrders = orders.filter((o: any) => o.payment_status === 'unpaid');
    const shippingOrders = orders.filter((o: any) => o.status === 'shipped');
    const completedOrders = orders.filter((o: any) => o.status === 'completed');

    const OrderList = ({ items }: { items: any[] }) => {
        if (items.length === 0) {
            return (
                <div className="text-center py-12 text-muted-foreground border rounded-lg border-dashed">
                    <Package className="w-12 h-12 mx-auto mb-4 opacity-20" />
                    <p>Belum ada pesanan di kategori ini.</p>
                </div>
            );
        }

        return (
            <div className="space-y-6">
                {items.map((order) => (
                    <Card key={order.id} className="overflow-hidden">
                        <CardHeader className="bg-muted/30 pb-4 border-b">
                            <div className="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                <div>
                                    <p className="text-sm text-muted-foreground mb-1">{order.created_at}</p>
                                    <div className="flex items-center gap-3">
                                        <CardTitle className="text-lg">{order.order_number}</CardTitle>
                                        {getStatusBadge(order.status, order.payment_status)}
                                    </div>
                                </div>
                                <div className="text-left sm:text-right">
                                    <p className="text-sm text-muted-foreground mb-1">Total Belanja</p>
                                    <p className="font-bold text-lg text-primary">{formatCurrency(order.grand_total)}</p>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="divide-y">
                                {order.items.map((item: any) => (
                                    <div key={item.id} className="flex gap-4 p-4 items-start">
                                        <div className="w-20 h-20 bg-muted rounded-md overflow-hidden flex-shrink-0 border flex items-center justify-center">
                                            {item.image_url ? (
                                                <img src={item.image_url} alt={item.product_name} className="w-full h-full object-cover" />
                                            ) : (
                                                <Package className="w-8 h-8 text-muted-foreground/30" />
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <h4 className="font-semibold">{item.product_name}</h4>
                                            <p className="text-sm text-muted-foreground mt-1">
                                                {item.quantity} x {formatCurrency(item.price)}
                                            </p>
                                            <div className="mt-2 sm:hidden">
                                                <div className="font-medium text-primary mb-2">{formatCurrency(item.total)}</div>
                                                {order.status === 'completed' && (
                                                    <Button variant="outline" size="sm" onClick={() => openReviewModal(item.product_id, item.product_name)}>
                                                        Beri Ulasan
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                        <div className="font-medium text-right hidden sm:block">
                                            <div className="text-primary">{formatCurrency(item.total)}</div>
                                            {order.status === 'completed' && (
                                                <Button variant="outline" size="sm" className="mt-2" onClick={() => openReviewModal(item.product_id, item.product_name)}>
                                                    Beri Ulasan
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Action Buttons */}
                            {(order.payment_status === 'unpaid' && order.snap_token) || order.shipment ? (
                                <div className="p-4 bg-muted/10 border-t flex flex-col sm:flex-row justify-end items-center gap-3">
                                    {order.shipment && order.shipment.tracking_number && (
                                        <div className="flex-1 text-sm text-muted-foreground w-full">
                                            <Truck className="w-4 h-4 inline mr-2 text-amber-500" />
                                            Resi: <span className="font-medium text-foreground">{order.shipment.tracking_number}</span> ({order.shipment.courier_name || order.shipment.courier || 'Kurir'})
                                        </div>
                                    )}
                                    
                                    {order.payment_status === 'unpaid' && order.snap_token && (
                                        <Button 
                                            onClick={() => handlePayment(order.snap_token)}
                                            className="w-full sm:w-auto"
                                        >
                                            Lanjutkan Pembayaran
                                        </Button>
                                    )}
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>
                ))}
            </div>
        );
    };

    return (
        <div className="py-8">
            <Head title="Pesanan Saya" />
            
            <div className="container mx-auto px-4 max-w-4xl">
                <h1 className="text-3xl font-bold mb-8">Pesanan Saya</h1>

                <div className="w-full">
                    <div className="flex border-b mb-8 space-x-6">
                        <button 
                            className={`pb-2 border-b-2 font-medium text-sm transition-colors ${activeTab === 'all' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                            onClick={() => setActiveTab('all')}
                        >
                            Semua
                        </button>
                        <button 
                            className={`pb-2 border-b-2 font-medium text-sm transition-colors ${activeTab === 'unpaid' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                            onClick={() => setActiveTab('unpaid')}
                        >
                            Belum Dibayar
                        </button>
                        <button 
                            className={`pb-2 border-b-2 font-medium text-sm transition-colors ${activeTab === 'shipping' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                            onClick={() => setActiveTab('shipping')}
                        >
                            Dikirim
                        </button>
                        <button 
                            className={`pb-2 border-b-2 font-medium text-sm transition-colors ${activeTab === 'completed' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'}`}
                            onClick={() => setActiveTab('completed')}
                        >
                            Selesai
                        </button>
                    </div>
                    
                    {activeTab === 'all' && <OrderList items={orders} />}
                    {activeTab === 'unpaid' && <OrderList items={unpaidOrders} />}
                    {activeTab === 'shipping' && <OrderList items={shippingOrders} />}
                    {activeTab === 'completed' && <OrderList items={completedOrders} />}
                </div>
            </div>

            {/* Review Modal */}
            <Dialog open={isReviewModalOpen} onOpenChange={setIsReviewModalOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Beri Ulasan Produk</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitReview} className="space-y-4 py-2">
                        <div>
                            <Label className="text-sm text-muted-foreground block mb-2">Produk</Label>
                            <p className="font-medium">{reviewProduct?.name}</p>
                        </div>
                        
                        <div>
                            <Label className="block mb-2">Rating</Label>
                            <div className="flex gap-1">
                                {[1, 2, 3, 4, 5].map((star) => (
                                    <button
                                        key={star}
                                        type="button"
                                        onClick={() => setRating(star)}
                                        onMouseEnter={() => setHoveredRating(star)}
                                        onMouseLeave={() => setHoveredRating(0)}
                                        className="focus:outline-none"
                                    >
                                        <Star 
                                            className={`w-8 h-8 transition-colors ${
                                                star <= (hoveredRating || rating) 
                                                    ? "fill-yellow-400 text-yellow-400" 
                                                    : "text-muted-foreground/30"
                                            }`} 
                                        />
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div>
                            <Label htmlFor="comment" className="block mb-2">Ulasan (Opsional)</Label>
                            <Textarea 
                                id="comment"
                                placeholder="Bagaimana kualitas produk ini?"
                                value={comment}
                                onChange={(e) => setComment(e.target.value)}
                                className="resize-none"
                                rows={4}
                            />
                        </div>

                        <DialogFooter className="pt-4">
                            <Button type="button" variant="outline" onClick={() => setIsReviewModalOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" disabled={isSubmittingReview}>
                                {isSubmittingReview ? "Mengirim..." : "Kirim Ulasan"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}
