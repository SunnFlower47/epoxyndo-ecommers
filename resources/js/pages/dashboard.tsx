import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { ShoppingBag, Package, Clock, ArrowRight } from 'lucide-react';

export default function Dashboard({ recentOrders, totalOrders, activeOrders }: any) {
    const { auth } = usePage().props as any;

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    };

    const getStatusBadge = (status: string) => {
        const statusMap: Record<string, { label: string; className: string }> = {
            pending: { label: 'Menunggu Pembayaran', className: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' },
            processing: { label: 'Diproses', className: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' },
            shipped: { label: 'Dikirim', className: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' },
            completed: { label: 'Selesai', className: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
            cancelled: { label: 'Dibatalkan', className: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' },
        };

        const config = statusMap[status] || { label: status, className: 'bg-gray-100 text-gray-800' };

        return (
            <span className={`px-2.5 py-0.5 rounded-full text-xs font-medium ${config.className}`}>
                {config.label}
            </span>
        );
    };

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div className="mb-2">
                    <h1 className="text-2xl font-bold tracking-tight">Selamat Datang, {auth.user.name}</h1>
                    <p className="text-muted-foreground mt-1">Kelola pesanan dan profil Anda dari panel ini.</p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <div className="flex items-center p-6 bg-card rounded-xl border border-border shadow-sm">
                        <div className="p-3 rounded-full bg-primary/10 text-primary mr-4">
                            <ShoppingBag className="w-6 h-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">Total Pesanan</p>
                            <h3 className="text-2xl font-bold">{totalOrders || 0}</h3>
                        </div>
                    </div>
                    <div className="flex items-center p-6 bg-card rounded-xl border border-border shadow-sm">
                        <div className="p-3 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mr-4">
                            <Package className="w-6 h-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">Pesanan Aktif</p>
                            <h3 className="text-2xl font-bold">{activeOrders || 0}</h3>
                        </div>
                    </div>
                    <div className="flex items-center p-6 bg-card rounded-xl border border-border shadow-sm">
                        <div className="p-3 rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 mr-4">
                            <Clock className="w-6 h-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">Bergabung Sejak</p>
                            <h3 className="text-lg font-bold">
                                {new Date(auth.user.created_at).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}
                            </h3>
                        </div>
                    </div>
                </div>

                <div className="mt-4 flex-1">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-semibold tracking-tight">Pesanan Terbaru</h2>
                        <Link href="/orders" className="text-sm text-primary hover:underline flex items-center">
                            Lihat Semua <ArrowRight className="w-4 h-4 ml-1" />
                        </Link>
                    </div>

                    <div className="bg-card rounded-xl border border-border shadow-sm overflow-hidden">
                        {recentOrders && recentOrders.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/50 text-muted-foreground text-xs uppercase">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Order ID</th>
                                            <th className="px-6 py-3 font-medium">Tanggal</th>
                                            <th className="px-6 py-3 font-medium">Total</th>
                                            <th className="px-6 py-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        {recentOrders.map((order: any) => (
                                            <tr key={order.id} className="hover:bg-muted/50 transition-colors">
                                                <td className="px-6 py-4 font-medium">#{order.order_number}</td>
                                                <td className="px-6 py-4 text-muted-foreground">
                                                    {new Date(order.created_at).toLocaleDateString('id-ID', {
                                                        day: 'numeric', month: 'short', year: 'numeric'
                                                    })}
                                                </td>
                                                <td className="px-6 py-4 font-medium">{formatCurrency(order.grand_total)}</td>
                                                <td className="px-6 py-4">
                                                    {getStatusBadge(order.status)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="p-8 text-center flex flex-col items-center justify-center text-muted-foreground">
                                <Package className="w-12 h-12 mb-3 opacity-20" />
                                <p>Belum ada pesanan.</p>
                                <Link href="/" className="mt-4 text-primary hover:underline text-sm">
                                    Mulai Belanja
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
