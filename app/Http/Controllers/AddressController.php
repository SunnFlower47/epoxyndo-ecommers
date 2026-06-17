<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('addresses', [
            'addresses' => $request->user()->addresses()->orderBy('is_primary', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'is_primary' => 'boolean',
        ]);

        if ($request->is_primary) {
            $request->user()->addresses()->update(['is_primary' => false]);
        } else if ($request->user()->addresses()->count() === 0) {
            $validated['is_primary'] = true;
        }

        $request->user()->addresses()->create($validated);

        return back()->with('flash', ['success' => 'Alamat berhasil ditambahkan.']);
    }

    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'is_primary' => 'boolean',
        ]);

        if ($request->is_primary) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
        }

        $address->update($validated);

        return back()->with('flash', ['success' => 'Alamat berhasil diperbarui.']);
    }

    public function destroy(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->delete();

        // If primary was deleted, set another one as primary if exists
        if ($address->is_primary) {
            $newPrimary = $request->user()->addresses()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return back()->with('flash', ['success' => 'Alamat berhasil dihapus.']);
    }

    public function setPrimary(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->addresses()->update(['is_primary' => false]);
        $address->update(['is_primary' => true]);

        return back()->with('flash', ['success' => 'Alamat utama berhasil diubah.']);
    }
}
