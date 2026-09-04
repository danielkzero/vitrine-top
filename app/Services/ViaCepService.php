<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    public function lookup(string $zip): array
    {
        $normalizedZip = preg_replace('/\D+/', '', $zip);

        if (strlen($normalizedZip) !== 8) {
            return [
                'found' => false,
                'message' => 'CEP inválido.',
            ];
        }

        $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$normalizedZip}/json/");

        if (! $response->successful() || $response->json('erro')) {
            return [
                'found' => false,
                'message' => 'CEP não encontrado.',
            ];
        }

        return [
            'found' => true,
            'street' => $response->json('logradouro'),
            'neighborhood' => $response->json('bairro'),
            'city' => $response->json('localidade'),
            'state' => $response->json('uf'),
            'zip' => $response->json('cep'),
        ];
    }
}
