import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, ChevronDown } from 'lucide-react';

interface Category {
    id: number;
    name: Record<string, string> | string;
    image?: string;
    children?: Category[];
}

interface CategorySidebarProps {
    categories: Category[];
    locale: string;
    currentCategory?: string;
}

export function CategorySidebar({ categories, locale, currentCategory }: CategorySidebarProps) {
    const currentLocale = locale || 'id';

    // State to keep track of expanded categories
    const [expandedCats, setExpandedCats] = useState<Record<number, boolean>>({});

    // Helper for translating JSON titles
    const getTranslated = (field: any) => {
        if (!field) return '';
        if (typeof field === 'string') {
            try {
                const parsed = JSON.parse(field);
                return parsed[currentLocale] || parsed['id'] || parsed['en'] || field;
            } catch (e) {
                return field;
            }
        }
        return field[currentLocale] || field['id'] || field['en'] || '';
    };

    const toggleExpand = (id: number, e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setExpandedCats(prev => ({ ...prev, [id]: !prev[id] }));
    };

    const renderCategory = (cat: Category, level: number = 0) => {
        const catName = getTranslated(cat.name);
        const isActive = currentCategory === catName;
        const hasChildren = cat.children && cat.children.length > 0;
        const isExpanded = expandedCats[cat.id] || false;

        return (
            <li key={cat.id} className="flex flex-col">
                <div className={`flex items-center justify-between px-2 py-2 text-sm rounded-md transition-colors ${isActive ? 'bg-primary/10 text-primary font-medium' : 'text-muted-foreground hover:text-primary hover:bg-primary/5'}`}>
                    <Link
                        href={`/products?category=${encodeURIComponent(catName)}`}
                        className={`flex items-center gap-3 flex-1 ${level > 0 ? 'pl-6' : ''}`}
                    >
                        {level === 0 && (
                            cat.image ? (
                                <img src={cat.image} alt={catName} className="w-6 h-6 object-cover rounded" />
                            ) : (
                                <div className="w-6 h-6 bg-muted rounded"></div>
                            )
                        )}
                        <span>{catName}</span>
                    </Link>

                    {hasChildren && (
                        <button
                            onClick={(e) => toggleExpand(cat.id, e)}
                            className="p-1 rounded hover:bg-black/5"
                        >
                            {isExpanded ? (
                                <ChevronDown className="h-4 w-4 opacity-50" />
                            ) : (
                                <ChevronRight className="h-4 w-4 opacity-50" />
                            )}
                        </button>
                    )}
                </div>

                {hasChildren && isExpanded && (
                    <ul className="mt-1 space-y-1">
                        {cat.children!.map(child => renderCategory(child, level + 1))}
                    </ul>
                )}
            </li>
        );
    };

    return (
        <div className="sticky top-24 bg-card rounded-xl border p-5 shadow-sm max-h-[calc(100vh-100px)] flex flex-col">
            <h3 className="font-bold text-lg mb-4 text-foreground border-b pb-2 shrink-0">
                {currentLocale === 'en' ? 'Product Categories' : 'Kategori Produk'}
            </h3>
            <div className="overflow-y-auto pr-2 -mr-2 space-y-1 flex-1">
                <ul className="space-y-1">
                    <li>
                        <Link
                            href="/products"
                            className={`flex items-center justify-between px-2 py-2 text-sm rounded-md transition-colors ${!currentCategory ? 'bg-primary/10 text-primary font-medium' : 'text-muted-foreground hover:text-primary hover:bg-primary/5'}`}
                        >
                            <span className="font-medium">{currentLocale === 'en' ? 'All Products' : 'Semua Produk'}</span>
                            <ChevronRight className="h-4 w-4 opacity-50" />
                        </Link>
                    </li>
                    {categories && categories.map(cat => renderCategory(cat, 0))}
                </ul>
            </div>
            {currentCategory && (
                <div className="mt-4 pt-4 border-t shrink-0">
                    <Link href="/products" className="flex justify-center w-full">
                        <button className="w-full px-4 py-2 text-sm border rounded-lg text-destructive hover:bg-destructive/10 transition-colors">
                            {currentLocale === 'en' ? 'Clear Filter' : 'Hapus Filter'}
                        </button>
                    </Link>
                </div>
            )}
        </div>
    );
}
