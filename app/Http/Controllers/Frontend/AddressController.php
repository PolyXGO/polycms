<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();
        return Inertia::render('Profile/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:2',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        if (empty($validated['is_default'])) {
            $validated['is_default'] = false;
        }

        // If this is the first address, make it default automatically
        if ($user->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        if ($validated['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return Redirect::route('account.addresses.index')->with('success', 'Address added successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:2',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        if (empty($validated['is_default'])) {
            $validated['is_default'] = false;
        }

        if ($validated['is_default'] && !$address->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return Redirect::route('account.addresses.index')->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);
        
        $wasDefault = $address->is_default;
        $address->delete();

        // If we deleted the default address, make the first remaining one default
        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return Redirect::route('account.addresses.index')->with('success', 'Address deleted successfully.');
    }

    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return Redirect::route('account.addresses.index')->with('success', 'Default address updated.');
    }
}
