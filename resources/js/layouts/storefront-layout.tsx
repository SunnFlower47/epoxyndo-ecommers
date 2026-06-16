import { Link, usePage } from '@inertiajs/react';
import React, { PropsWithChildren, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Search, ShoppingCart, User, Menu, Sun, Moon, Globe } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { useCartStore } from '@/stores/use-cart-store';
import { CartDrawer } from '@/components/cart-drawer';

export default function StorefrontLayout({ children }: PropsWithChildren) {
    const { auth, general_settings, locale, shared_categories, searchQuery, currentCategory } = usePage<any>().props;
    const companyName = String(general_settings?.company_name || 'Epoxyndo Art Lestari');
    
    const { setIsOpen, getTotalItems, fetchFromDatabase } = useCartStore();
    const totalItems = getTotalItems();

    const [isDark, setIsDark] = useState(false);
    const [lang, setLang] = useState(locale || 'id');
    const [searchVal, setSearchVal] = useState(searchQuery || '');
    const [selectedCat, setSelectedCat] = useState(currentCategory || '');

    useEffect(() => {
        if (auth.user) {
            // fetchFromDatabase();
        }
    }, [auth.user]);

    useEffect(() => {
        // Initialize dark mode
        const isDarkMode = document.documentElement.classList.contains('dark');
        setIsDark(isDarkMode);
    }, []);

    const toggleDarkMode = () => {
        document.documentElement.classList.toggle('dark');
        setIsDark(!isDark);
    };

    const toggleLanguage = () => {
        const newLang = lang === 'id' ? 'en' : 'id';
        setLang(newLang);
        document.cookie = `locale=${newLang}; path=/; max-age=31536000; SameSite=Lax`;
        window.location.reload();
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        const url = new URL(window.location.origin + '/products');
        if (searchVal) url.searchParams.set('q', searchVal);
        if (selectedCat) url.searchParams.set('category', selectedCat);
        window.location.href = url.toString();
    };

    const getTranslated = (field: any) => {
        if (!field) return '';
        if (typeof field === 'string') {
            try {
                const parsed = JSON.parse(field);
                return parsed[lang] || parsed['id'] || parsed['en'] || field;
            } catch (e) {
                return field;
            }
        }
        return field[lang] || field['id'] || field['en'] || '';
    };

    return (
        <div className="min-h-screen bg-background text-foreground flex flex-col font-sans">
            <CartDrawer />
            {/* Header */}
            <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div className="container mx-auto flex h-16 items-center px-4 md:px-6">
                    <div className="mr-4 flex md:hidden">
                        <Button variant="ghost" size="icon" className="mr-2">
                            <Menu className="h-6 w-6" />
                            <span className="sr-only">Toggle Menu</span>
                        </Button>
                    </div>

                    <div className="flex md:w-auto">
                        <Link href="/" className="mr-6 flex items-center space-x-3">
                            {/* Logo */}
                            {general_settings?.company_logo && (
                                <img src={general_settings.company_logo} alt={companyName} className="h-10 md:h-12 w-auto object-contain" />
                            )}
                            <span className="font-bold hidden sm:inline-block text-foreground text-lg md:text-xl tracking-tight">
                                {companyName}
                            </span>
                        </Link>
                    </div>

                    <div className="flex flex-1 items-center justify-between space-x-4 md:justify-end">
                        <div className="w-full flex-1 md:w-auto md:flex-none">
                            <form onSubmit={handleSearch} className="relative group flex w-full md:w-[400px] lg:w-[600px] rounded-md border border-input bg-background overflow-hidden focus-within:ring-1 focus-within:ring-primary">
                                {/* Category Dropdown */}
                                <select 
                                    className="hidden sm:block h-10 px-3 bg-muted/50 border-0 border-r border-input text-sm text-muted-foreground outline-none focus:ring-0 max-w-[150px]"
                                    value={selectedCat}
                                    onChange={(e) => {
                                        setSelectedCat(e.target.value);
                                        const url = new URL(window.location.origin + '/products');
                                        if (searchVal) url.searchParams.set('q', searchVal);
                                        if (e.target.value) url.searchParams.set('category', e.target.value);
                                        window.location.href = url.toString();
                                    }}
                                >
                                    <option value="">{lang === 'id' ? 'Semua Kategori' : 'All Categories'}</option>
                                    {shared_categories && shared_categories.map((cat: any) => {
                                        if (cat.children && cat.children.length > 0) {
                                            return (
                                                <optgroup key={cat.id} label={getTranslated(cat.name)}>
                                                    <option value={getTranslated(cat.name)}>{lang === 'id' ? 'Semua di ' : 'All in '}{getTranslated(cat.name)}</option>
                                                    {cat.children.map((child: any) => (
                                                        <option key={child.id} value={getTranslated(child.name)}>
                                                            {getTranslated(child.name)}
                                                        </option>
                                                    ))}
                                                </optgroup>
                                            );
                                        }
                                        return (
                                            <option key={cat.id} value={getTranslated(cat.name)}>
                                                {getTranslated(cat.name)}
                                            </option>
                                        );
                                    })}
                                </select>
                                
                                <Search className="absolute left-3 sm:left-[160px] top-2.5 h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors pointer-events-none" />
                                <Input
                                    type="search"
                                    name="q"
                                    value={searchVal}
                                    onChange={(e) => setSearchVal(e.target.value)}
                                    placeholder={lang === 'id' ? "Cari produk di toko ini..." : "Search products..."}
                                    className="h-10 w-full border-0 bg-transparent pl-10 sm:pl-10 focus-visible:ring-0 focus-visible:ring-offset-0"
                                />
                                <button type="submit" className="hidden"></button>
                            </form>
                        </div>

                        <nav className="flex items-center space-x-1">
                            {/* Language Toggle */}
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="relative hover:bg-muted/50 transition-colors"
                                onClick={toggleLanguage}
                                title={lang === 'id' ? "Switch to English" : "Ganti ke Indonesia"}
                            >
                                <Globe className="h-5 w-5" />
                                <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-muted text-[9px] font-medium border border-background">
                                    {lang.toUpperCase()}
                                </span>
                            </Button>

                            {/* Dark Mode Toggle */}
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="hover:bg-muted/50 transition-colors"
                                onClick={toggleDarkMode}
                                title="Toggle Dark Mode"
                            >
                                {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
                            </Button>

                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="relative hover:bg-muted/50 transition-colors"
                                onClick={() => setIsOpen(true)}
                            >
                                <ShoppingCart className="h-5 w-5" />
                                {totalItems > 0 && (
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-secondary text-[10px] text-white font-medium">
                                        {totalItems > 99 ? '99+' : totalItems}
                                    </span>
                                )}
                                <span className="sr-only">Keranjang</span>
                            </Button>
                            
                            <div className="h-6 w-px bg-border mx-2 hidden sm:block"></div>

                            {auth.user ? (
                                <Link href="/dashboard">
                                    <Button variant="ghost" size="sm" className="hidden sm:flex gap-2 text-muted-foreground hover:text-foreground">
                                        <User className="h-4 w-4" />
                                        <span>Dashboard</span>
                                    </Button>
                                </Link>
                            ) : (
                                <div className="hidden sm:flex items-center gap-2">
                                    <Link href="/login">
                                        <Button variant="outline" size="sm" className="border-primary text-primary hover:bg-primary/5">
                                            Masuk
                                        </Button>
                                    </Link>
                                    <Link href="/register">
                                        <Button variant="default" size="sm" className="bg-primary hover:bg-primary/90 text-white">
                                            Daftar
                                        </Button>
                                    </Link>
                                </div>
                            )}
                        </nav>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-1 flex flex-col">
                {children}
            </main>

            {/* Footer */}
            <footer className="border-t py-12 bg-muted/30">
                <div className="container mx-auto px-4 md:px-6">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div className="md:col-span-2">
                            <Link href="/" className="inline-flex items-center space-x-3 mb-4 group">
                                {general_settings?.company_logo && (
                                    <img src={general_settings.company_logo} alt={companyName} className="h-12 md:h-16 w-auto object-contain grayscale group-hover:grayscale-0 transition-all opacity-80 group-hover:opacity-100" />
                                )}
                                <span className="text-xl font-bold text-primary tracking-tight">{companyName}</span>
                            </Link>
                            <p className="text-sm text-muted-foreground leading-loose max-w-sm">
                                {general_settings?.company_address ? String(general_settings.company_address) : 'Jl. Contoh Alamat No. 123, Jakarta, Indonesia.'}
                            </p>
                            {general_settings?.support_phone && (
                                <p className="text-sm text-muted-foreground mt-2">
                                    <strong>Phone:</strong> {general_settings.support_phone}
                                </p>
                            )}
                            {general_settings?.support_email && (
                                <p className="text-sm text-muted-foreground mt-1">
                                    <strong>Email:</strong> {general_settings.support_email}
                                </p>
                            )}
                        </div>
                        <div>
                            <h3 className="font-semibold mb-4 text-foreground">Bantuan</h3>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><Link href="#" className="hover:text-primary transition-colors">Syarat & Ketentuan</Link></li>
                                <li><Link href="#" className="hover:text-primary transition-colors">Kebijakan Privasi</Link></li>
                                <li><Link href="#" className="hover:text-primary transition-colors">Hubungi Kami</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h3 className="font-semibold mb-4 text-foreground">Ikuti Kami</h3>
                            <div className="flex space-x-4 text-muted-foreground">
                                {general_settings?.social_media?.instagram && (
                                    <a href={general_settings.social_media.instagram} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors">Instagram</a>
                                )}
                                {general_settings?.social_media?.facebook && (
                                    <a href={general_settings.social_media.facebook} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors">Facebook</a>
                                )}
                                {general_settings?.social_media?.youtube && (
                                    <a href={general_settings.social_media.youtube} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors">YouTube</a>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="mt-12 pt-8 border-t text-center text-sm text-muted-foreground">
                        <p>&copy; {new Date().getFullYear()} {companyName}. Hak Cipta Dilindungi.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
