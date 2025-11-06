<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $categories = Category::where('user_id', $this->user->id)
            ->orderBy('order')
            ->get();

        return $this->respond($request, 'Dashboard/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255',
                Rule::unique('categories')->where(fn($q) => $q->where('user_id', $this->user->id))
            ],
            'order' => 'nullable|integer',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['user_id'] = $this->user->id;
        $category = Category::create($data);

        return $this->json(['message' => 'Categoria criada.', 'category' => $category], 201);
    }

    public function edit(Category $category, Request $request)
    {
        $this->authorizeOwnership($category);

        return $this->respond($request, 'Dashboard/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeOwnership($category);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255',
                Rule::unique('categories')->ignore($category->id)->where(fn($q) => $q->where('user_id', $this->user->id))
            ],
            'order' => 'nullable|integer',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);

        return $this->json(['message' => 'Categoria atualizada.', 'category' => $category]);
    }

    public function destroy(Category $category)
    {
        $this->authorizeOwnership($category);
        $category->delete();

        return $this->json(['message' => 'Categoria removida.']);
    }

    protected function authorizeOwnership(Category $category)
    {
        if ($category->user_id !== $this->user->id) {
            abort(403, 'Recurso não pertence ao usuário autenticado.');
        }
    }
}
