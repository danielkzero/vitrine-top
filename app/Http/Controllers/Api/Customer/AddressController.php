<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request, string $storeSlug): JsonResponse
    {
        $customer = $request->attributes->get('customer');

        return response()->json([
            'data' => $customer->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request, string $storeSlug): JsonResponse
    {
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'is_default' => ['sometimes', 'boolean'],
            'zip' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if (($data['is_default'] ?? false) === true) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create(array_merge($data, [
            'user_id' => $customer->user_id,
            'state' => strtoupper($data['state']),
        ]));

        return response()->json(['data' => $address], 201);
    }

    public function update(Request $request, string $storeSlug, int $addressId): JsonResponse
    {
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'is_default' => ['sometimes', 'boolean'],
            'zip' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $address = CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        if (($data['is_default'] ?? false) === true) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address->update(array_merge($data, [
            'state' => strtoupper($data['state']),
        ]));

        return response()->json(['data' => $address]);
    }

    public function destroy(Request $request, string $storeSlug, int $addressId): JsonResponse
    {
        $customer = $request->attributes->get('customer');

        $address = CustomerAddress::query()
            ->where('id', $addressId)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $address->delete();

        return response()->json(['message' => 'Endereco removido com sucesso.']);
    }
}
