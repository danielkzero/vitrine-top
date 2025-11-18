<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    // crud store, update, delete for product images
    function store(Request $request)
    {
        // code to store product image
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'image_base64' => ['nullable', 'string'],
            'is_cover' => ['sometimes', 'boolean'],
        ]);
        $productImage = \App\Models\ProductImage::create($data);
        return response()->json([
            'message' => 'Imagem do produto criada com sucesso.',
            'product_image' => $productImage,
        ], 201);
    }

    function destroy($id)
    {
        // code to delete product image
        $productImage = \App\Models\ProductImage::findOrFail($id);
        $productImage->delete();
        return response()->json([
            'message' => 'Imagem do produto deletada com sucesso.',
        ], 200);
    }

    function update(Request $request, $id)
    {
        // code to update product image
        $productImage = \App\Models\ProductImage::findOrFail($id);
        $data = $request->validate([
            'image_path' => ['sometimes', 'string', 'max:255'],
            'image_base64' => ['nullable', 'string'],
            'is_cover' => ['sometimes', 'boolean'],
        ]);
        $productImage->update($data);
        return response()->json([
            'message' => 'Imagem do produto atualizada com sucesso.',
            'product_image' => $productImage,
        ], 200);
    }
}
