import { Link, usePage } from '@inertiajs/react';
import React, { PropsWithChildren, useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Search, ShoppingCart, User, Menu, Sun, Moon, Globe, Bell, LogOut, LayoutDashboard } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { useCartStore } from '@/stores/use-cart-store';
import { CartDrawer } from '@/components/cart-drawer';
import { NotificationDropdown } from '@/components/notification-dropdown';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export default function StorefrontLayout({ children }: PropsWithChildren) {
    const { auth, general_settings, locale, shared_categories, searchQuery, currentCategory } = usePage<any>().props;
    const companyName = String(general_settings?.company_name || 'Epoxyndo Art Lestari');
    
    const { setIsOpen, getTotalItems, fetchFromDatabase } = useCartStore();
    const totalItems = getTotalItems();

    const [isDark, setIsDark] = useState(false);
    const [lang, setLang] = useState(locale || 'id');
    const [searchVal, setSearchVal] = useState(searchQuery || '');
    const [selectedCat, setSelectedCat] = useState(currentCategory || '');
    const [showAuthModal, setShowAuthModal] = useState(false);
    const [authModalType, setAuthModalType] = useState<'login' | 'register'>('login');
    const [showMobileMenu, setShowMobileMenu] = useState(false);
    const [showFloatingSearch, setShowFloatingSearch] = useState(false);
    const [showNotifications, setShowNotifications] = useState(false);
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

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
                        <Button variant="ghost" size="icon" className="mr-2" onClick={() => setShowMobileMenu(true)}>
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

                    <div className="hidden md:flex flex-1 items-center justify-end px-4 lg:px-8">
                        <form onSubmit={handleSearch} className="relative group flex w-full max-w-[500px] rounded-md border border-input bg-background overflow-hidden focus-within:ring-1 focus-within:ring-primary">
                            {/* Category Dropdown */}
                            <select 
                                className="h-10 px-3 bg-muted/50 border-0 border-r border-input text-sm text-muted-foreground outline-none focus:ring-0 w-[150px]"
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
                            
                            <Search className="absolute left-[162px] top-2.5 h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors pointer-events-none" />
                            <Input
                                type="search"
                                name="q"
                                value={searchVal}
                                onChange={(e) => setSearchVal(e.target.value)}
                                placeholder={lang === 'id' ? "Cari produk di toko ini..." : "Search products..."}
                                className="h-10 w-full border-0 bg-transparent pl-10 focus-visible:ring-0 focus-visible:ring-offset-0"
                            />
                            <button type="submit" className="hidden"></button>
                        </form>
                    </div>

                    <div className="flex items-center justify-end space-x-2 md:space-x-4">
                        <nav className="flex items-center space-x-1">
                            {/* Search Toggle */}
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="hover:bg-muted/50 transition-colors md:hidden"
                                onClick={() => setShowFloatingSearch(!showFloatingSearch)}
                                title={lang === 'id' ? "Pencarian" : "Search"}
                            >
                                <Search className="h-5 w-5" />
                            </Button>

                            {/* Language Toggle */}
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="relative hover:bg-muted/50 transition-colors hidden sm:inline-flex"
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
                                className="hover:bg-muted/50 transition-colors hidden sm:inline-flex"
                                onClick={toggleDarkMode}
                                title="Toggle Dark Mode"
                            >
                                {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
                            </Button>

                            {/* Notifications Toggle */}
                            {auth.user && <NotificationDropdown />}

                            {/* Cart Toggle */}
                            <Button 
                                variant="ghost" 
                                size="icon" 
                                className="relative hover:bg-muted/50 transition-colors"
                                onClick={() => setIsOpen(true)}
                            >
                                <ShoppingCart className="h-5 w-5" />
                                {mounted && totalItems > 0 && (
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-secondary text-[10px] text-white font-medium">
                                        {totalItems > 99 ? '99+' : totalItems}
                                    </span>
                                )}
                                <span className="sr-only">Keranjang</span>
                            </Button>
                            
                            <div className="h-6 w-px bg-border mx-2 hidden sm:block"></div>

                            {auth.user ? (
                                <div className="hidden sm:flex items-center gap-2">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" size="icon" className="rounded-full bg-muted/50 hover:bg-muted border shadow-sm">
                                                <User className="h-5 w-5" />
                                                <span className="sr-only">Profile menu</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" className="w-56 mt-2">
                                            <div className="flex items-center justify-start gap-3 p-3">
                                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                    <User className="h-5 w-5" />
                                                </div>
                                                <div className="flex flex-col space-y-0.5 leading-none">
                                                    <p className="font-semibold text-sm">{auth.user.name}</p>
                                                    <p className="text-xs text-muted-foreground truncate w-32">{auth.user.email}</p>
                                                </div>
                                            </div>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem asChild className="p-3 cursor-pointer">
                                                <Link href="/dashboard" className="flex items-center w-full">
                                                    <LayoutDashboard className="mr-2 h-4 w-4" />
                                                    <span>Dashboard</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem asChild className="p-3 cursor-pointer">
                                                <Link href="/profile" className="flex items-center w-full">
                                                    <User className="mr-2 h-4 w-4" />
                                                    <span>{lang === 'id' ? 'Profil Saya' : 'My Profile'}</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem asChild className="p-3 cursor-pointer text-red-500 focus:text-red-500 focus:bg-red-50">
                                                <Link href="/logout" method="post" as="button" className="flex items-center w-full">
                                                    <LogOut className="mr-2 h-4 w-4" />
                                                    <span>{lang === 'id' ? 'Keluar' : 'Logout'}</span>
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            ) : (
                                <div className="hidden sm:flex items-center gap-2">
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        className="border-primary text-primary hover:bg-primary/5"
                                        onClick={() => { setAuthModalType('login'); setShowAuthModal(true); }}
                                    >
                                        {lang === 'id' ? 'Masuk' : 'Login'}
                                    </Button>
                                    <Button 
                                        variant="default" 
                                        size="sm" 
                                        className="bg-primary hover:bg-primary/90 text-white"
                                        onClick={() => { setAuthModalType('register'); setShowAuthModal(true); }}
                                    >
                                        {lang === 'id' ? 'Daftar' : 'Register'}
                                    </Button>
                                </div>
                            )}
                        </nav>
                    </div>
                </div>

                {/* Floating Search Bar */}
                {showFloatingSearch && (
                    <div className="md:hidden absolute top-16 left-0 w-full bg-background border-b shadow-sm p-4 animate-in slide-in-from-top-2 duration-200">
                        <div className="container mx-auto max-w-3xl">
                            <form onSubmit={handleSearch} className="relative group flex w-full rounded-md border border-input bg-background overflow-hidden focus-within:ring-1 focus-within:ring-primary shadow-sm">
                                {/* Category Dropdown */}
                                <select 
                                    className="hidden sm:block h-12 px-4 bg-muted/50 border-0 border-r border-input text-sm text-muted-foreground outline-none focus:ring-0 max-w-[180px]"
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
                                
                                <Search className="absolute left-4 sm:left-[196px] top-3.5 h-5 w-5 text-muted-foreground group-focus-within:text-primary transition-colors pointer-events-none" />
                                <Input
                                    type="search"
                                    name="q"
                                    value={searchVal}
                                    onChange={(e) => setSearchVal(e.target.value)}
                                    placeholder={lang === 'id' ? "Ketik nama produk untuk mencari..." : "Type product name to search..."}
                                    className="h-12 w-full border-0 bg-transparent pl-12 sm:pl-12 focus-visible:ring-0 focus-visible:ring-offset-0 text-base"
                                    autoFocus
                                />
                                <Button type="submit" className="rounded-none h-12 px-6 bg-primary hover:bg-primary/90 text-white font-medium">
                                    {lang === 'id' ? 'Cari' : 'Search'}
                                </Button>
                            </form>
                        </div>
                    </div>
                )}
            </header>

            {/* Mobile Menu */}
            <Sheet open={showMobileMenu} onOpenChange={setShowMobileMenu}>
                <SheetContent side="left" className="w-[85vw] sm:max-w-md flex flex-col p-0">
                    <SheetHeader className="p-4 border-b text-left">
                        <SheetTitle className="flex items-center gap-3">
                            {general_settings?.company_logo && (
                                <img src={general_settings.company_logo} alt={companyName} className="h-8 w-auto object-contain" />
                            )}
                            <span className="font-bold text-lg">{companyName}</span>
                        </SheetTitle>
                    </SheetHeader>
                    
                    <div className="flex-1 overflow-y-auto p-4 space-y-6">
                        {/* Search & Category */}
                        <form onSubmit={handleSearch} className="space-y-3">
                            <select 
                                className="w-full h-10 px-3 bg-muted/50 border border-input rounded-md text-sm outline-none focus:ring-1 focus:ring-primary"
                                value={selectedCat}
                                onChange={(e) => {
                                    setSelectedCat(e.target.value);
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
                            <div className="relative">
                                <Search className="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground pointer-events-none" />
                                <Input
                                    type="search"
                                    name="q"
                                    value={searchVal}
                                    onChange={(e) => setSearchVal(e.target.value)}
                                    placeholder={lang === 'id' ? "Cari produk..." : "Search products..."}
                                    className="pl-10 w-full"
                                />
                            </div>
                            <Button type="submit" className="w-full">{lang === 'id' ? 'Cari' : 'Search'}</Button>
                        </form>

                        <div className="border-t pt-4">
                            {auth.user ? (
                                <Link href="/dashboard" className="w-full" onClick={() => setShowMobileMenu(false)}>
                                    <Button variant="outline" className="w-full justify-start gap-3">
                                        <User className="h-5 w-5" />
                                        Dashboard
                                    </Button>
                                </Link>
                            ) : (
                                <div className="space-y-3">
                                    <Button 
                                        variant="outline" 
                                        className="w-full justify-center"
                                        onClick={() => { setShowMobileMenu(false); setAuthModalType('login'); setShowAuthModal(true); }}
                                    >
                                        {lang === 'id' ? 'Masuk' : 'Login'}
                                    </Button>
                                    <Button 
                                        className="w-full justify-center"
                                        onClick={() => { setShowMobileMenu(false); setAuthModalType('register'); setShowAuthModal(true); }}
                                    >
                                        {lang === 'id' ? 'Daftar' : 'Register'}
                                    </Button>
                                </div>
                            )}
                        </div>

                        <div className="border-t pt-4 space-y-3">
                            <Button 
                                variant="ghost" 
                                className="w-full justify-start gap-3"
                                onClick={toggleLanguage}
                            >
                                <Globe className="h-5 w-5" />
                                {lang === 'id' ? 'Switch to English' : 'Ganti ke Indonesia'}
                            </Button>
                            
                            <Button 
                                variant="ghost" 
                                className="w-full justify-start gap-3"
                                onClick={toggleDarkMode}
                            >
                                {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
                                {isDark ? (lang === 'id' ? 'Mode Terang' : 'Light Mode') : (lang === 'id' ? 'Mode Gelap' : 'Dark Mode')}
                            </Button>
                        </div>
                    </div>
                </SheetContent>
            </Sheet>

            {/* Main Content */}
            <main className="flex-1 flex flex-col">
                {children}
            </main>

            {/* Client Logos Slider */}
            {general_settings?.client_logos && general_settings.client_logos.length > 0 && (
                <div className="border-t py-8 bg-background overflow-hidden relative">
                    <div className="container mx-auto px-4 md:px-6 mb-6 text-center">
                        <h3 className="text-xl font-bold tracking-tight text-foreground">{lang === 'id' ? 'Dipercaya Oleh' : 'Trusted By'}</h3>
                    </div>
                    <div className="flex w-full justify-center group overflow-hidden">
                        {/* Container with max-width and masked edges for the running effect */}
                        <div className="w-full max-w-4xl overflow-hidden relative mask-image-linear-gradient-x">
                            <div className="flex animate-marquee space-x-8 px-6 items-center min-w-max">
                                {general_settings.client_logos.map((partner: any, idx: number) => (
                                    <img key={idx} src={partner.logo} alt={partner.name} title={partner.name} className="h-10 md:h-14 w-auto max-w-[120px] object-contain grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-pointer" />
                                ))}
                                {/* Duplicate for seamless looping if not enough items */}
                                {general_settings.client_logos.map((partner: any, idx: number) => (
                                    <img key={`dup-${idx}`} src={partner.logo} alt={partner.name} title={partner.name} className="h-10 md:h-14 w-auto max-w-[120px] object-contain grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all cursor-pointer" />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Footer */}
            <footer className="border-t py-12 bg-muted/30">
                <div className="container mx-auto px-4 md:px-6">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div className="md:col-span-2">
                            <Link href="/" className="inline-flex items-center space-x-3 mb-4 group">
                                {general_settings?.company_logo && (
                                    <img src={general_settings.company_logo} alt={companyName} className="h-12 md:h-16 w-auto object-contain transition-all opacity-80 group-hover:opacity-100" />
                                )}
                                <span className="text-xl font-bold text-primary tracking-tight">{companyName}</span>
                            </Link>
                            <div className="space-y-4 mb-4">
                                {general_settings?.office_address && (
                                    <div>
                                        <h4 className="text-sm font-semibold text-foreground">Office & Training</h4>
                                        <p className="text-sm text-muted-foreground leading-snug">{general_settings.office_address}</p>
                                    </div>
                                )}
                                {general_settings?.marketing_address && (
                                    <div>
                                        <h4 className="text-sm font-semibold text-foreground">Marketing Office</h4>
                                        <p className="text-sm text-muted-foreground leading-snug">{general_settings.marketing_address}</p>
                                    </div>
                                )}
                                {general_settings?.factory_address && (
                                    <div>
                                        <h4 className="text-sm font-semibold text-foreground">Factory</h4>
                                        <p className="text-sm text-muted-foreground leading-snug">{general_settings.factory_address}</p>
                                    </div>
                                )}
                            </div>
                            <div className="space-y-2 mt-4">
                                {general_settings?.support_phone && (
                                    <p className="text-sm text-muted-foreground">
                                        <strong>Phone/WA:</strong> {general_settings.support_phone}
                                    </p>
                                )}
                                {general_settings?.support_email && (
                                    <p className="text-sm text-muted-foreground">
                                        <strong>Support Email:</strong> {general_settings.support_email}
                                    </p>
                                )}
                                {general_settings?.marketing_email && (
                                    <p className="text-sm text-muted-foreground">
                                        <strong>Marketing Email:</strong> {general_settings.marketing_email}
                                    </p>
                                )}
                            </div>
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
                            <h3 className="font-semibold mb-4 text-foreground">{lang === 'id' ? 'Ikuti Kami' : 'Follow Us'}</h3>
                            <div className="flex space-x-4 text-muted-foreground items-center">
                                {general_settings?.social_media?.instagram && (
                                    <a href={general_settings.social_media.instagram} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors hover:-translate-y-1 block">
                                        <img src="/assets/icon/instagaram/instagram-new-2016-seeklogo.png" alt="Instagram" className="w-6 h-6 object-contain" />
                                    </a>
                                )}
                                {general_settings?.social_media?.facebook && (
                                    <a href={general_settings.social_media.facebook} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors hover:-translate-y-1 block">
                                        <img src="/assets/icon/facebook/facebook-new-2019-seeklogo.png" alt="Facebook" className="w-6 h-6 object-contain" />
                                    </a>
                                )}
                                {general_settings?.social_media?.youtube && (
                                    <a href={general_settings.social_media.youtube} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors hover:-translate-y-1 block">
                                        <img src="/assets/icon/youtube/youtube-2017-icon-seeklogo.png" alt="YouTube" className="w-6 h-6 object-contain" />
                                    </a>
                                )}
                                {general_settings?.social_media?.tiktok && (
                                    <a href={general_settings.social_media.tiktok} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors hover:-translate-y-1 block">
                                        <img src="/assets/icon/tiktok/tiktok-seeklogo.png" alt="TikTok" className="w-6 h-6 object-contain" />
                                    </a>
                                )}
                                {general_settings?.support_phone && (
                                    <a href={`https://wa.me/${general_settings.support_phone.replace(/\D/g, '')}`} target="_blank" rel="noreferrer" className="hover:text-primary transition-colors hover:-translate-y-1 block">
                                        <img src="/assets/icon/whatsapp/whatsapp-icon-seeklogo.png" alt="WhatsApp" className="w-6 h-6 object-contain" />
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>
                    <div className="mt-12 pt-8 border-t text-center text-sm text-muted-foreground">
                        <p>&copy; {new Date().getFullYear()} {companyName}. Hak Cipta Dilindungi.</p>
                    </div>
                </div>
            </footer>

            {/* Auth Modal */}
            {showAuthModal && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div className="bg-background w-full max-w-md rounded-2xl p-6 shadow-xl relative animate-in fade-in zoom-in-95 duration-200">
                        <button 
                            onClick={() => setShowAuthModal(false)}
                            className="absolute top-4 right-4 text-muted-foreground hover:text-foreground bg-muted hover:bg-muted/80 rounded-full p-1"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        
                        <div className="text-center mb-6 mt-2">
                            <h2 className="text-2xl font-bold">
                                {authModalType === 'login' 
                                    ? (lang === 'id' ? 'Selamat Datang Kembali!' : 'Welcome Back!')
                                    : (lang === 'id' ? 'Buat Akun Baru' : 'Create New Account')
                                }
                            </h2>
                            <p className="text-sm text-muted-foreground mt-2">
                                {lang === 'id' 
                                    ? 'Pilih metode untuk melanjutkan' 
                                    : 'Choose a method to continue'}
                            </p>
                        </div>

                        <div className="space-y-4">
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full py-6 flex items-center justify-center gap-3 border-2 hover:bg-muted/50"
                                onClick={() => window.location.href = '/auth/google'}
                            >
                                <img src="/assets/icon/google/google.png" alt="Google" className="w-6 h-6 object-contain" />
                                <span className="font-semibold text-base">
                                    {lang === 'id' ? 'Lanjutkan dengan Google' : 'Continue with Google'}
                                </span>
                            </Button>

                            <div className="relative my-6">
                                <div className="absolute inset-0 flex items-center">
                                    <span className="w-full border-t border-muted-foreground/20" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-background px-4 text-muted-foreground font-medium">
                                        {lang === 'id' ? 'Atau gunakan email' : 'Or use email'}
                                    </span>
                                </div>
                            </div>

                            <Link 
                                href={authModalType === 'login' ? '/login' : '/register'}
                                className="w-full flex items-center justify-center"
                                onClick={() => setShowAuthModal(false)}
                            >
                                <Button
                                    type="button"
                                    className="w-full py-6 font-semibold text-base"
                                >
                                    {authModalType === 'login' 
                                        ? (lang === 'id' ? 'Masuk dengan Email' : 'Login with Email')
                                        : (lang === 'id' ? 'Daftar dengan Email' : 'Register with Email')
                                    }
                                </Button>
                            </Link>
                        </div>

                        <div className="mt-6 text-center text-sm text-muted-foreground">
                            {authModalType === 'login' ? (
                                <>
                                    {lang === 'id' ? 'Belum punya akun? ' : 'Don\'t have an account? '}
                                    <button onClick={() => setAuthModalType('register')} className="text-primary font-semibold hover:underline">
                                        {lang === 'id' ? 'Daftar sekarang' : 'Register now'}
                                    </button>
                                </>
                            ) : (
                                <>
                                    {lang === 'id' ? 'Sudah punya akun? ' : 'Already have an account? '}
                                    <button onClick={() => setAuthModalType('login')} className="text-primary font-semibold hover:underline">
                                        {lang === 'id' ? 'Masuk sekarang' : 'Login now'}
                                    </button>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
