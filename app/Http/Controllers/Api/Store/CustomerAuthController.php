<?php

namespace App\Http\Controllers\Api\Store;

use App\Enums\AddressInputMode;
use App\Http\Controllers\Api\Concerns\ResolvesStore;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\CustomerAuthTokenService;
use App\Services\ViaCepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    use ResolvesStore;

    public function __construct(
        private readonly CustomerAuthTokenService $tokenService,
        private readonly ViaCepService $viaCepService,
    ) {
    }

    public function register(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->where('user_id', $store->id)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'address_mode' => ['required', 'in:cep,manual'],
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

        $customer = Customer::query()->create([
            'user_id' => $store->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'] ?? null,
            'password' => $data['password'],
            'is_active' => true,
        ]);

        $address = CustomerAddress::create([
            'customer_id' => $customer->id,
            'user_id' => $store->id,
            'label' => 'Principal',
            'is_default' => true,
            'zip' => $data['zip'],
            'street' => $data['street'],
            'number' => $data['number'],
            'complement' => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'],
            'city' => $data['city'],
            'state' => strtoupper($data['state']),
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'metadata' => [
                'input_mode' => $data['address_mode'],
            ],
        ]);

        $address->setAddressMode(AddressInputMode::from($data['address_mode']));
        $address->save();

        $token = $this->tokenService->issueToken($customer, 'customer-login', now()->addDays(30));

        return response()->json([
            'token' => $token['token'],
            'expires_at' => $token['expires_at'],
            'customer' => $customer,
        ], 201);
    }

    public function login(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::query()
            ->where('user_id', $store->id)
            ->where('email', $data['email'])
            ->first();

        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            return response()->json(['message' => 'Credenciais invalidas.'], 422);
        }

        $customer->forceFill(['last_login_at' => now()])->save();

        $token = $this->tokenService->issueToken($customer, 'customer-login', now()->addDays(30));

        return response()->json([
            'token' => $token['token'],
            'expires_at' => $token['expires_at'],
            'customer' => $customer,
        ]);
    }

    public function logout(Request $request, string $storeSlug): JsonResponse
    {
        $token = $request->attributes->get('customer_token');

        if ($token) {
            $this->tokenService->revokeCurrentToken($token);
        }

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request, string $storeSlug): JsonResponse
    {
        $customer = $request->attributes->get('customer');

        return response()->json([
            'customer' => $customer->load('addresses'),
        ]);
    }

    public function lookupZip(Request $request, string $storeSlug): JsonResponse
    {
        $data = $request->validate([
            'zip' => ['required', 'string', 'max:10'],
        ]);

        return response()->json($this->viaCepService->lookup($data['zip']));
    }
}
