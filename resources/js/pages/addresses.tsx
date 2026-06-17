import React, { useState } from "react";
import { Head, router } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { MapPin, Plus, Trash2, Edit2, Star } from "lucide-react";

export default function Addresses({ addresses }: { addresses: any[] }) {
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [form, setForm] = useState({
        title: "",
        recipient_name: "",
        phone_number: "",
        full_address: "",
        province: "",
        city: "",
        district: "",
        postal_code: "",
        is_primary: false,
    });

    const resetForm = () => {
        setForm({
            title: "",
            recipient_name: "",
            phone_number: "",
            full_address: "",
            province: "",
            city: "",
            district: "",
            postal_code: "",
            is_primary: false,
        });
        setEditingId(null);
        setIsFormOpen(false);
    };

    const handleEdit = (address: any) => {
        setForm({
            title: address.title || "",
            recipient_name: address.recipient_name,
            phone_number: address.phone_number,
            full_address: address.full_address,
            province: address.province,
            city: address.city,
            district: address.district || "",
            postal_code: address.postal_code,
            is_primary: address.is_primary,
        });
        setEditingId(address.id);
        setIsFormOpen(true);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (editingId) {
            router.put(`/addresses/${editingId}`, form as any, {
                onSuccess: () => resetForm(),
            });
        } else {
            router.post("/addresses", form as any, {
                onSuccess: () => resetForm(),
            });
        }
    };

    const handleDelete = (id: number) => {
        if (confirm("Apakah Anda yakin ingin menghapus alamat ini?")) {
            router.delete(`/addresses/${id}`);
        }
    };

    const handleSetPrimary = (id: number) => {
        router.post(`/addresses/${id}/primary`);
    };

    return (
        <div className="py-8">
            <Head title="Buku Alamat" />
            
            <div className="container mx-auto px-4 max-w-4xl">
                <div className="flex justify-between items-center mb-8">
                    <h1 className="text-3xl font-bold">Buku Alamat</h1>
                    {!isFormOpen && (
                        <Button onClick={() => setIsFormOpen(true)}>
                            <Plus className="w-4 h-4 mr-2" />
                            Tambah Alamat
                        </Button>
                    )}
                </div>

                {isFormOpen && (
                    <Card className="mb-8 border-primary/20 shadow-md">
                        <CardHeader>
                            <CardTitle>{editingId ? "Ubah Alamat" : "Tambah Alamat Baru"}</CardTitle>
                            <CardDescription>Masukkan detail alamat pengiriman Anda</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="title">Label Alamat (opsional)</Label>
                                        <Input 
                                            id="title" 
                                            value={form.title} 
                                            onChange={(e) => setForm({...form, title: e.target.value})} 
                                            placeholder="Contoh: Rumah, Kantor" 
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="recipient_name">Nama Penerima</Label>
                                        <Input 
                                            id="recipient_name" 
                                            required 
                                            value={form.recipient_name} 
                                            onChange={(e) => setForm({...form, recipient_name: e.target.value})} 
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="phone_number">Nomor HP</Label>
                                        <Input 
                                            id="phone_number" 
                                            required 
                                            value={form.phone_number} 
                                            onChange={(e) => setForm({...form, phone_number: e.target.value})} 
                                        />
                                    </div>
                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="full_address">Alamat Lengkap</Label>
                                        <Textarea 
                                            id="full_address" 
                                            required 
                                            value={form.full_address} 
                                            onChange={(e) => setForm({...form, full_address: e.target.value})} 
                                            placeholder="Nama jalan, gedung, no. rumah"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="province">Provinsi</Label>
                                        <Input 
                                            id="province" 
                                            required 
                                            value={form.province} 
                                            onChange={(e) => setForm({...form, province: e.target.value})} 
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="city">Kota/Kabupaten</Label>
                                        <Input 
                                            id="city" 
                                            required 
                                            value={form.city} 
                                            onChange={(e) => setForm({...form, city: e.target.value})} 
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="district">Kecamatan (opsional)</Label>
                                        <Input 
                                            id="district" 
                                            value={form.district} 
                                            onChange={(e) => setForm({...form, district: e.target.value})} 
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="postal_code">Kode Pos</Label>
                                        <Input 
                                            id="postal_code" 
                                            required 
                                            value={form.postal_code} 
                                            onChange={(e) => setForm({...form, postal_code: e.target.value})} 
                                        />
                                    </div>
                                    {!editingId && (
                                        <div className="flex items-center space-x-2 md:col-span-2 mt-2">
                                            <input 
                                                type="checkbox" 
                                                id="is_primary" 
                                                className="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/50 focus:ring-opacity-50"
                                                checked={form.is_primary}
                                                onChange={(e) => setForm({...form, is_primary: e.target.checked})}
                                            />
                                            <Label htmlFor="is_primary" className="cursor-pointer">Jadikan Alamat Utama</Label>
                                        </div>
                                    )}
                                </div>
                                <div className="flex justify-end gap-2 pt-4">
                                    <Button type="button" variant="outline" onClick={resetForm}>Batal</Button>
                                    <Button type="submit">Simpan Alamat</Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {addresses.length === 0 && !isFormOpen ? (
                        <div className="col-span-full text-center py-12 border rounded-lg border-dashed text-muted-foreground">
                            <MapPin className="w-12 h-12 mx-auto mb-4 opacity-20" />
                            <p className="mb-4">Anda belum memiliki alamat tersimpan.</p>
                            <Button onClick={() => setIsFormOpen(true)} variant="outline">Tambah Alamat Pertama</Button>
                        </div>
                    ) : (
                        addresses.map((address) => (
                            <Card key={address.id} className={address.is_primary ? "border-primary shadow-sm" : ""}>
                                <CardHeader className="pb-3">
                                    <div className="flex justify-between items-start">
                                        <CardTitle className="text-base flex items-center gap-2">
                                            {address.title || "Alamat"}
                                            {address.is_primary && (
                                                <Badge className="bg-primary/10 text-primary hover:bg-primary/20 hover:text-primary pointer-events-none">
                                                    Utama
                                                </Badge>
                                            )}
                                        </CardTitle>
                                        <div className="flex gap-1">
                                            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => handleEdit(address)}>
                                                <Edit2 className="w-4 h-4 text-muted-foreground" />
                                            </Button>
                                            <Button variant="ghost" size="icon" className="h-8 w-8 hover:text-destructive" onClick={() => handleDelete(address.id)}>
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="text-sm space-y-2">
                                    <p className="font-semibold">{address.recipient_name} <span className="font-normal text-muted-foreground ml-2">{address.phone_number}</span></p>
                                    <p className="text-muted-foreground leading-relaxed">{address.full_address}</p>
                                    <p className="text-muted-foreground">
                                        {address.district ? `${address.district}, ` : ''}{address.city}, {address.province} {address.postal_code}
                                    </p>
                                    
                                    {!address.is_primary && (
                                        <Button 
                                            variant="link" 
                                            className="px-0 h-auto text-xs mt-2" 
                                            onClick={() => handleSetPrimary(address.id)}
                                        >
                                            Jadikan Alamat Utama
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}
