import React, { useState } from "react";
import { Head, useForm, usePage } from "@inertiajs/react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";

export default function Profile() {
    const { auth, flash } = usePage<any>().props;
    const user = auth.user;

    const { data, setData, post, processing, errors } = useForm({
        name: user.name || "",
        email: user.email || "",
        phone: user.phone || "",
        address: user.address || "",
        province: user.province || "",
        city: user.city || "",
        district: user.district || "",
        postal_code: user.postal_code || "",
    });

    const [successMsg, setSuccessMsg] = useState(flash?.success || "");

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post("/profile", {
            preserveScroll: true,
            onSuccess: (page) => {
                setSuccessMsg(page.props.flash?.success as string || "Profil berhasil diperbarui.");
                setTimeout(() => setSuccessMsg(""), 5000);
            },
        });
    };

    return (
        <div className="py-8">
            <Head title="Profil Saya" />
            
            <div className="container mx-auto px-4 max-w-3xl">
                <h1 className="text-3xl font-bold mb-8">Profil Saya</h1>

                {successMsg && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {successMsg}
                    </div>
                )}

                <div className="bg-card border rounded-xl p-6 shadow-sm">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">Nama Lengkap</Label>
                                <Input 
                                    id="name" 
                                    value={data.name} 
                                    onChange={(e) => setData("name", e.target.value)} 
                                    required 
                                />
                                {errors.name && <p className="text-red-500 text-xs">{errors.name}</p>}
                            </div>
                            
                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input 
                                    id="email" 
                                    type="email" 
                                    value={data.email} 
                                    onChange={(e) => setData("email", e.target.value)} 
                                    required 
                                />
                                {errors.email && <p className="text-red-500 text-xs">{errors.email}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="phone">Nomor HP/WhatsApp</Label>
                                <Input 
                                    id="phone" 
                                    value={data.phone} 
                                    onChange={(e) => setData("phone", e.target.value)} 
                                    required 
                                />
                                {errors.phone && <p className="text-red-500 text-xs">{errors.phone}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="postal_code">Kode Pos</Label>
                                <Input 
                                    id="postal_code" 
                                    value={data.postal_code} 
                                    onChange={(e) => setData("postal_code", e.target.value)} 
                                    required 
                                />
                                {errors.postal_code && <p className="text-red-500 text-xs">{errors.postal_code}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="province">Provinsi</Label>
                                <Input 
                                    id="province" 
                                    value={data.province} 
                                    onChange={(e) => setData("province", e.target.value)} 
                                    required 
                                />
                                {errors.province && <p className="text-red-500 text-xs">{errors.province}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="city">Kota/Kabupaten</Label>
                                <Input 
                                    id="city" 
                                    value={data.city} 
                                    onChange={(e) => setData("city", e.target.value)} 
                                    required 
                                />
                                {errors.city && <p className="text-red-500 text-xs">{errors.city}</p>}
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <Label htmlFor="district">Kecamatan / Area</Label>
                                <Input 
                                    id="district" 
                                    value={data.district} 
                                    onChange={(e) => setData("district", e.target.value)} 
                                    required 
                                />
                                {errors.district && <p className="text-red-500 text-xs">{errors.district}</p>}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="address">Alamat Lengkap</Label>
                            <Textarea 
                                id="address" 
                                rows={3}
                                value={data.address} 
                                onChange={(e) => setData("address", e.target.value)} 
                                required 
                            />
                            {errors.address && <p className="text-red-500 text-xs">{errors.address}</p>}
                        </div>

                        <Button type="submit" disabled={processing} className="w-full md:w-auto">
                            {processing ? "Menyimpan..." : "Simpan Profil"}
                        </Button>
                    </form>
                </div>
            </div>
        </div>
    );
}
