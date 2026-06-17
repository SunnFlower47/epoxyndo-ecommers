import { useState, useEffect } from 'react';
import { Bell, Check, ShoppingBag, Megaphone } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/react';

export function NotificationDropdown() {
    const { auth } = usePage().props as any;
    const [isOpen, setIsOpen] = useState(false);
    const [notifications, setNotifications] = useState<any[]>([]);
    const [unreadCount, setUnreadCount] = useState(auth.unread_notifications_count || 0);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        setUnreadCount(auth.unread_notifications_count || 0);
    }, [auth.unread_notifications_count]);

    const fetchNotifications = async () => {
        setIsLoading(true);
        try {
            const res = await fetch('/api/notifications');
            const data = await res.json();
            setNotifications(data.notifications || []);
            setUnreadCount(data.unread_count || 0);
        } catch (error) {
            console.error('Failed to fetch notifications', error);
        } finally {
            setIsLoading(false);
        }
    };

    const markAsRead = async (id: string, e: React.MouseEvent) => {
        e.stopPropagation();
        try {
            const res = await fetch(`/api/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    'Content-Type': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success) {
                setNotifications(notifications.map(n => n.id === id ? { ...n, read_at: new Date().toISOString() } : n));
                setUnreadCount(data.unread_count || 0);
            }
        } catch (error) {
            console.error('Failed to mark notification as read', error);
        }
    };

    const toggleDropdown = () => {
        if (!isOpen) {
            fetchNotifications();
        }
        setIsOpen(!isOpen);
    };

    const handleClickOutside = (e: MouseEvent) => {
        if (isOpen) {
            const target = e.target as HTMLElement;
            if (!target.closest('.notification-dropdown-container')) {
                setIsOpen(false);
            }
        }
    };

    useEffect(() => {
        document.addEventListener('click', handleClickOutside);
        return () => document.removeEventListener('click', handleClickOutside);
    }, [isOpen]);

    const getIcon = (type: string) => {
        if (type === 'promo') return <Megaphone className="w-4 h-4 text-primary" />;
        if (type === 'order_success') return <ShoppingBag className="w-4 h-4 text-green-500" />;
        return <Bell className="w-4 h-4 text-muted-foreground" />;
    };

    const handleNotificationClick = (notification: any) => {
        if (!notification.read_at) {
            fetch(`/api/notifications/${notification.id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': (document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                setNotifications(notifications.map(n => n.id === notification.id ? { ...n, read_at: new Date().toISOString() } : n));
                setUnreadCount(Math.max(0, unreadCount - 1));
            });
        }
        
        if (notification.data?.type === 'order_success') {
            window.location.href = '/dashboard/orders';
        } else {
            setIsOpen(false);
        }
    };

    return (
        <div className="relative notification-dropdown-container">
            <button 
                onClick={toggleDropdown}
                className="relative p-2 rounded-full hover:bg-muted transition-colors focus:outline-none"
            >
                <Bell className="w-5 h-5 text-foreground" />
                {unreadCount > 0 && (
                    <span className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-80 sm:w-96 bg-background rounded-xl shadow-xl border border-border z-50 overflow-hidden animate-in fade-in slide-in-from-top-2">
                    <div className="flex items-center justify-between px-4 py-3 border-b bg-muted/30">
                        <h3 className="font-semibold text-sm">Notifikasi</h3>
                        {unreadCount > 0 && (
                            <span className="text-xs text-muted-foreground">{unreadCount} Belum Dibaca</span>
                        )}
                    </div>
                    
                    <div className="max-h-[60vh] overflow-y-auto">
                        {isLoading ? (
                            <div className="p-8 text-center text-muted-foreground text-sm flex justify-center">
                                <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                            </div>
                        ) : notifications.length > 0 ? (
                            <div className="divide-y divide-border">
                                {notifications.map((notification) => (
                                    <div 
                                        key={notification.id} 
                                        onClick={() => handleNotificationClick(notification)}
                                        className={`p-4 hover:bg-muted/50 cursor-pointer transition-colors relative flex gap-3 ${!notification.read_at ? 'bg-primary/5' : ''}`}
                                    >
                                        <div className={`mt-1 flex-shrink-0 p-2 rounded-full h-8 w-8 flex items-center justify-center ${!notification.read_at ? 'bg-primary/10' : 'bg-muted'}`}>
                                            {getIcon(notification.data?.type)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex justify-between items-start mb-1">
                                                <h4 className={`text-sm truncate font-medium ${!notification.read_at ? 'text-foreground' : 'text-muted-foreground'}`}>
                                                    {notification.data?.title || 'Notifikasi'}
                                                </h4>
                                                {!notification.read_at && (
                                                    <button 
                                                        onClick={(e) => markAsRead(notification.id, e)}
                                                        className="text-muted-foreground hover:text-primary transition-colors flex-shrink-0 ml-2"
                                                        title="Tandai sudah dibaca"
                                                    >
                                                        <Check className="w-3.5 h-3.5" />
                                                    </button>
                                                )}
                                            </div>
                                            <p className={`text-xs leading-relaxed line-clamp-2 ${!notification.read_at ? 'text-foreground/90' : 'text-muted-foreground'}`}>
                                                {notification.data?.message}
                                            </p>
                                            <p className="text-[10px] text-muted-foreground mt-2">
                                                {new Date(notification.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute:'2-digit' })}
                                            </p>
                                        </div>
                                        {!notification.read_at && (
                                            <div className="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary rounded-r-md"></div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-8 text-center text-muted-foreground flex flex-col items-center">
                                <Bell className="w-10 h-10 mb-3 opacity-20" />
                                <p className="text-sm">Belum ada notifikasi.</p>
                            </div>
                        )}
                    </div>
                    
                    <div className="p-2 border-t bg-muted/20 text-center">
                        <Button variant="ghost" className="w-full text-xs h-8 text-muted-foreground hover:text-foreground" onClick={() => setIsOpen(false)}>
                            Tutup
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}
