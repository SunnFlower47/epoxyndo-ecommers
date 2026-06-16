import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

interface Banner {
    id: number;
    title: Record<string, string> | string;
    image_url: string;
    link_url?: string;
}

interface BannerCarouselProps {
    banners: Banner[];
    locale: string;
}

export function BannerCarousel({ banners, locale }: BannerCarouselProps) {
    const [currentBanner, setCurrentBanner] = useState(0);

    const currentLocale = locale || 'id';

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

    // Auto slide banners
    useEffect(() => {
        if (!banners || banners.length <= 1) return;
        const interval = setInterval(() => {
            setCurrentBanner((prev) => (prev + 1) % banners.length);
        }, 5000);
        return () => clearInterval(interval);
    }, [banners]);

    return (
        <section className="relative w-full h-[180px] md:h-[300px] lg:h-[350px] rounded-2xl overflow-hidden bg-muted mb-8 shadow-sm">
            {banners && banners.length > 0 ? (
                banners.map((banner, index) => (
                    <Link 
                        href={banner.link_url || '/products'}
                        key={banner.id}
                        className={`absolute inset-0 transition-opacity duration-1000 block ${index === currentBanner ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'}`}
                    >
                        <img 
                            src={banner.image_url} 
                            alt={getTranslated(banner.title)}
                            className="absolute inset-0 w-full h-full object-cover"
                        />
                        {/* Gradient Overlay for text readability */}
                        <div className="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent pointer-events-none"></div>
                        <div className="relative z-20 h-full flex flex-col justify-center px-8 md:px-12 lg:px-16 w-full md:w-2/3 pointer-events-auto">
                            <h2 className="text-2xl md:text-4xl font-bold text-white drop-shadow-md line-clamp-2">
                                {getTranslated(banner.title)}
                            </h2>
                        </div>
                    </Link>
                ))
            ) : (
                <div className="absolute inset-0 bg-primary/10 flex items-center justify-center">
                    <div className="text-center">
                        <h1 className="text-2xl md:text-4xl font-bold text-slate-800 mb-2">Promo Spesial</h1>
                        <p className="text-sm md:text-base text-slate-600">Temukan bahan kimia konstruksi terbaik di sini.</p>
                    </div>
                </div>
            )}

            {/* Banner Indicators */}
            {banners && banners.length > 1 && (
                <div className="absolute bottom-4 left-8 z-30 flex space-x-2">
                    {banners.map((_, idx) => (
                        <button
                            key={idx}
                            onClick={() => setCurrentBanner(idx)}
                            className={`h-2 rounded-full transition-all ${idx === currentBanner ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'}`}
                            aria-label={`Go to slide ${idx + 1}`}
                        />
                    ))}
                </div>
            )}
        </section>
    );
}
