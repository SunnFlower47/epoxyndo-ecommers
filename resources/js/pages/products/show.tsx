import { Link, Head, usePage } from '@inertiajs/react';
import StorefrontLayout from '@/layouts/storefront-layout';
import { Button } from '@/components/ui/button';
import { ShoppingCart, Heart, Share2, ChevronRight, Star, Truck, ShieldCheck, Tag } from 'lucide-react';
import { useCartStore } from '@/stores/use-cart-store';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';

export default function ProductShow({ product, relatedProducts }: any) {
    const { locale } = usePage<any>().props;
    const lang = locale || 'id';
    const { addItem, setIsOpen } = useCartStore();

    const [quantity, setQuantity] = useState(1);
    const [selectedVariant, setSelectedVariant] = useState<any>(
        product.active_variants && product.active_variants.length > 0 
            ? product.active_variants[0] 
            : null
    );
    const [selectedImage, setSelectedImage] = useState(
        product.images && product.images.length > 0 
            ? product.images[0].image_url 
            : '/assets/logo/logo-epoxyndo.png'
    );

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

    const productName = String(getTranslated(product.name));
    const productDesc = String(getTranslated(product.description));
    
    // Fallback images handling
    const images = product.images && product.images.length > 0 
        ? product.images 
        : [{ image_url: '/assets/logo/logo-epoxyndo.png', id: 'default' }];

    const handleAddToCart = () => {
        const productForCart = {
            ...product,
            primary_image_url: images[0].image_url,
            name: productName,
            variant: selectedVariant,
            variant_id: selectedVariant?.id,
        };
        // Ensure quantity is passed as the SECOND argument so Zustand store picks it up
        addItem(productForCart, quantity);
        setIsOpen(true);
    };

    return (
        <StorefrontLayout>
            <Head title={`${productName} | Epoxyndo Art Lestari`}>
                <meta name="description" content={productDesc.substring(0, 150)} />
            </Head>

            {/* Breadcrumbs */}
            <div className="bg-muted/30 border-b">
                <div className="container mx-auto px-4 md:px-6 py-3 flex items-center text-sm text-muted-foreground">
                    <Link href="/" className="hover:text-primary transition-colors">{lang === 'id' ? 'Beranda' : 'Home'}</Link>
                    <ChevronRight className="w-4 h-4 mx-2" />
                    <Link href="/products" className="hover:text-primary transition-colors">{lang === 'id' ? 'Produk' : 'Products'}</Link>
                    {product.category && (
                        <>
                            <ChevronRight className="w-4 h-4 mx-2" />
                            <Link href={`/products?category=${encodeURIComponent(getTranslated(product.category.name))}`} className="hover:text-primary transition-colors">
                                {getTranslated(product.category.name)}
                            </Link>
                        </>
                    )}
                    <ChevronRight className="w-4 h-4 mx-2" />
                    <span className="text-foreground font-medium truncate max-w-[200px] md:max-w-xs">{productName}</span>
                </div>
            </div>

            <div className="container mx-auto px-4 md:px-6 py-8 md:py-12">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">
                    
                    {/* Product Images Gallery */}
                    <div className="space-y-4">
                        <div className="aspect-square rounded-xl bg-white border shadow-sm overflow-hidden flex items-center justify-center p-4">
                            <img 
                                src={selectedImage} 
                                alt={productName} 
                                className="w-full h-full object-contain"
                                onError={(e) => { e.currentTarget.src = '/assets/logo/logo-epoxyndo.png'; }}
                            />
                        </div>
                        {images.length > 1 && (
                            <div className="grid grid-cols-4 sm:grid-cols-5 gap-3">
                                {images.map((img: any, idx: number) => (
                                    <button 
                                        key={img.id || idx}
                                        onClick={() => setSelectedImage(img.image_url)}
                                        className={`aspect-square rounded-lg border bg-white p-2 flex items-center justify-center transition-all ${selectedImage === img.image_url ? 'ring-2 ring-primary border-transparent' : 'hover:border-primary/50'}`}
                                    >
                                        <img 
                                            src={img.image_url} 
                                            alt={`${productName} - ${idx + 1}`} 
                                            className="w-full h-full object-contain"
                                            onError={(e) => { e.currentTarget.src = '/assets/logo/logo-epoxyndo.png'; }}
                                        />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Product Info */}
                    <div className="flex flex-col">
                        {product.category && (
                            <Link href={`/products?category=${encodeURIComponent(getTranslated(product.category.name))}`} className="text-primary text-sm font-semibold tracking-wider uppercase mb-2 hover:underline">
                                {getTranslated(product.category.name)}
                            </Link>
                        )}
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight text-foreground mb-4">{productName}</h1>
                        
                        <div className="flex items-center flex-wrap gap-y-2 text-sm text-muted-foreground mb-6">
                            <div className="flex items-center text-amber-400 mr-4">
                                {[1, 2, 3, 4, 5].map((star) => (
                                    <Star 
                                        key={star} 
                                        className={`w-5 h-5 ${star <= Math.round(product.average_rating || 0) ? 'fill-current' : 'text-muted fill-muted/20'}`} 
                                    />
                                ))}
                                <span className="text-muted-foreground text-sm ml-2 font-medium">
                                    {product.average_rating > 0 ? product.average_rating.toFixed(1) : '0'} 
                                    <span className="font-normal text-xs ml-1">({product.reviews_count || 0} {lang === 'id' ? 'Ulasan' : 'Reviews'})</span>
                                </span>
                            </div>
                            
                            <div className="w-px h-5 bg-border hidden sm:block mr-4"></div>
                            
                            <div className="flex items-center mr-4">
                                <span className="font-medium text-foreground">{product.sold_count || 0}</span>
                                <span className="ml-1">{lang === 'id' ? 'Terjual' : 'Sold'}</span>
                            </div>
                            
                            <div className="w-px h-5 bg-border hidden sm:block mr-4"></div>
                            
                            <span className="font-medium">
                                {selectedVariant 
                                    ? (selectedVariant.stock > 0 ? (lang === 'id' ? `Tersedia (${selectedVariant.stock})` : `In Stock (${selectedVariant.stock})`) : (lang === 'id' ? 'Habis' : 'Out of Stock'))
                                    : (product.stock > 0 ? (lang === 'id' ? 'Tersedia' : 'In Stock') : (lang === 'id' ? 'Habis' : 'Out of Stock'))
                                }
                            </span>
                        </div>

                        <div className="mb-6">
                            <div className="flex items-end gap-3 mb-2">
                                <span className="text-3xl font-bold text-primary">
                                    Rp {number_format(selectedVariant ? (selectedVariant.final_price || selectedVariant.price) : (product.final_price || product.price))}
                                </span>
                                {product.has_discount && (
                                    <>
                                        <span className="text-lg text-muted-foreground line-through">
                                            Rp {number_format(selectedVariant ? selectedVariant.price : product.price)}
                                        </span>
                                        <Badge variant="destructive" className="ml-2 bg-red-500 hover:bg-red-600">
                                            {product.discount_type === 'percentage' ? `${product.discount_value}% OFF` : `Rp ${number_format(product.discount_value)} OFF`}
                                        </Badge>
                                    </>
                                )}
                            </div>
                        </div>

                        {product.active_variants && product.active_variants.length > 0 && (
                            <div className="mb-6">
                                <h3 className="text-sm font-medium mb-3">{lang === 'id' ? 'Pilih Varian (Berat/Ukuran)' : 'Select Variant (Weight/Size)'}</h3>
                                <div className="flex flex-wrap gap-2">
                                    <button
                                        onClick={() => {
                                            setSelectedVariant(null);
                                            setQuantity(1);
                                        }}
                                        className={`px-4 py-2 border rounded-md text-sm font-medium transition-all ${
                                            selectedVariant === null 
                                                ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary' 
                                                : 'border-border text-muted-foreground hover:border-primary/50'
                                        }`}
                                    >
                                        {product.variant_label || (lang === 'id' ? `Kemasan Standar` : `Standard Package`)}
                                    </button>
                                    {product.active_variants.map((variant: any) => (
                                        <button
                                            key={variant.id}
                                            onClick={() => {
                                                setSelectedVariant(variant);
                                                setQuantity(1);
                                            }}
                                            className={`px-4 py-2 border rounded-md text-sm font-medium transition-all ${
                                                selectedVariant?.id === variant.id 
                                                    ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary' 
                                                    : 'border-border text-muted-foreground hover:border-primary/50'
                                            }`}
                                        >
                                            {variant.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        <div className="prose prose-sm md:prose-base dark:prose-invert max-w-none text-muted-foreground mb-8 line-clamp-4">
                            <p>{productDesc}</p>
                        </div>

                        {/* Actions */}
                        <div className="space-y-4 mb-8">
                            <div className="flex items-center gap-4">
                                <div className="flex items-center border rounded-md">
                                    <button 
                                        className="w-10 h-10 flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors disabled:opacity-50"
                                        onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                        disabled={quantity <= 1}
                                    >
                                        -
                                    </button>
                                    <div className="w-12 h-10 flex items-center justify-center font-medium border-x">
                                        {quantity}
                                    </div>
                                    <button 
                                        className="w-10 h-10 flex items-center justify-center text-muted-foreground hover:bg-muted transition-colors disabled:opacity-50"
                                        onClick={() => setQuantity(Math.min(selectedVariant ? selectedVariant.stock : product.stock, quantity + 1))}
                                        disabled={quantity >= (selectedVariant ? selectedVariant.stock : product.stock)}
                                    >
                                        +
                                    </button>
                                </div>
                                <span className="text-sm text-muted-foreground">
                                    {lang === 'id' 
                                        ? `Tersisa ${selectedVariant ? selectedVariant.stock : product.stock} barang` 
                                        : `${selectedVariant ? selectedVariant.stock : product.stock} items available`}
                                </span>
                            </div>

                            <div className="flex gap-2 sm:gap-3 pt-4">
                                <Button 
                                    className="flex-1 h-12 text-sm sm:text-base font-semibold px-2 sm:px-4" 
                                    onClick={handleAddToCart}
                                    disabled={(selectedVariant ? selectedVariant.stock : product.stock) <= 0}
                                >
                                    <ShoppingCart className="w-4 h-4 sm:w-5 sm:h-5 mr-2 shrink-0" />
                                    <span className="truncate">{lang === 'id' ? 'Tambah ke Keranjang' : 'Add to Cart'}</span>
                                </Button>
                                <Button variant="outline" size="icon" className="h-12 w-12 shrink-0 border-2">
                                    <Heart className="w-5 h-5" />
                                </Button>
                                <Button variant="outline" size="icon" className="h-12 w-12 shrink-0 border-2">
                                    <Share2 className="w-5 h-5" />
                                </Button>
                            </div>
                        </div>

                        {/* Features Info */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t pt-6">
                            <div className="flex items-start gap-3">
                                <div className="bg-primary/10 p-2 rounded-full text-primary">
                                    <ShieldCheck className="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm">Produk Original</h4>
                                    <p className="text-xs text-muted-foreground mt-0.5">Jaminan keaslian 100%</p>
                                </div>
                            </div>
                            <div className="flex items-start gap-3">
                                <div className="bg-primary/10 p-2 rounded-full text-primary">
                                    <Truck className="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm">Pengiriman Aman</h4>
                                    <p className="text-xs text-muted-foreground mt-0.5">Dikemas dengan standar tinggi</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                {/* Product Description Tab */}
                <div className="mt-16 border-t pt-10">
                    <h2 className="text-2xl font-bold mb-6">{lang === 'id' ? 'Deskripsi Produk' : 'Product Description'}</h2>
                    <div className="prose dark:prose-invert max-w-4xl bg-white dark:bg-muted/10 p-6 rounded-xl border">
                        <p className="whitespace-pre-wrap leading-relaxed">{productDesc}</p>
                    </div>
                </div>

                {/* Product Reviews */}
                <div className="mt-16 border-t pt-10">
                    <h2 className="text-2xl font-bold mb-6">{lang === 'id' ? 'Ulasan Pembeli' : 'Customer Reviews'}</h2>
                    
                    {product.reviews && product.reviews.length > 0 ? (
                        <div className="space-y-6 max-w-4xl">
                            {product.reviews.map((review: any) => (
                                <div key={review.id} className="bg-white dark:bg-muted/10 p-6 rounded-xl border flex flex-col sm:flex-row gap-4">
                                    <div className="shrink-0 w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center font-bold text-lg">
                                        {review.user?.name?.charAt(0).toUpperCase() || 'U'}
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-center justify-between mb-2">
                                            <h4 className="font-semibold text-foreground">{review.user?.name || 'User'}</h4>
                                            <span className="text-xs text-muted-foreground">
                                                {new Date(review.created_at).toLocaleDateString(lang === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'short', year: 'numeric' })}
                                            </span>
                                        </div>
                                        <div className="flex items-center text-amber-400 mb-3">
                                            {[1, 2, 3, 4, 5].map((star) => (
                                                <Star 
                                                    key={star} 
                                                    className={`w-4 h-4 ${star <= review.rating ? 'fill-current' : 'text-muted fill-muted/20'}`} 
                                                />
                                            ))}
                                        </div>
                                        <p className="text-sm text-muted-foreground leading-relaxed">
                                            {review.comment}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="bg-muted/30 p-8 rounded-xl border border-dashed flex flex-col items-center justify-center text-center max-w-4xl">
                            <Star className="w-12 h-12 text-muted-foreground/30 mb-3" />
                            <h3 className="text-lg font-medium text-foreground mb-1">{lang === 'id' ? 'Belum Ada Ulasan' : 'No Reviews Yet'}</h3>
                            <p className="text-sm text-muted-foreground">{lang === 'id' ? 'Jadilah yang pertama mengulas produk ini.' : 'Be the first to review this product.'}</p>
                        </div>
                    )}
                </div>

                {/* Related Products */}
                {relatedProducts && relatedProducts.length > 0 && (
                    <div className="mt-20">
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-2xl font-bold">{lang === 'id' ? 'Produk Terkait' : 'Related Products'}</h2>
                            <Link href={`/products?category=${encodeURIComponent(getTranslated(product.category?.name))}`} className="text-primary hover:underline text-sm font-medium">
                                {lang === 'id' ? 'Lihat Semua' : 'View All'}
                            </Link>
                        </div>
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                            {relatedProducts.map((rp: any) => (
                                <Link href={`/product/${rp.slug}`} key={rp.id} className="group flex flex-col bg-card rounded-xl border overflow-hidden hover:shadow-lg transition-all duration-300">
                                    <div className="relative aspect-square bg-white flex items-center justify-center p-4">
                                        <img 
                                            src={rp.primary_image_url || '/assets/logo/logo-epoxyndo.png'} 
                                            alt={getTranslated(rp.name)} 
                                            className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                            onError={(e) => { e.currentTarget.src = '/assets/logo/logo-epoxyndo.png'; }}
                                        />
                                        {rp.has_discount && (
                                            <Badge variant="destructive" className="absolute top-3 left-3 bg-red-500 font-semibold px-2">
                                                {rp.discount_type === 'percentage' ? `-${rp.discount_value}%` : `Sale`}
                                            </Badge>
                                        )}
                                    </div>
                                    <div className="p-4 flex flex-col flex-1">
                                        <h3 className="font-semibold text-sm md:text-base line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                                            {getTranslated(rp.name)}
                                        </h3>
                                        <div className="mt-auto">
                                            <div className="flex items-baseline gap-2 flex-wrap">
                                                <span className="font-bold text-primary">
                                                    Rp {number_format(rp.final_price || rp.price)}
                                                </span>
                                                {rp.has_discount && (
                                                    <span className="text-xs text-muted-foreground line-through">
                                                        Rp {number_format(rp.price)}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </StorefrontLayout>
    );
}

function number_format(number: number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
