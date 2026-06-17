import React, { useState } from "react";
import { Head, useForm, usePage } from "@inertiajs/react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";

export default function Profile() {
    const { auth, flash } = usePage<any>().props;
    const user = auth.user;

    const { data, setData, post, processing, errors } = useForm({
        name: user.name || "",
        email: user.email || "",
        phone: user.phone || "",
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
            
            <div className="container mx-auto px-4 max-w-2xl">
                <h1 className="text-3xl font-bold mb-8">Profil Saya</h1>

                {successMsg && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {successMsg}
                    </div>
                )}

                <div className="bg-card border rounded-xl p-6 shadow-sm">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-4">
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
                        </div>

                        <div className="pt-4 flex justify-end">
                            <Button type="submit" disabled={processing}>
                                {processing ? "Menyimpan..." : "Simpan Perubahan"}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
