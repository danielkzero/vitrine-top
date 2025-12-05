<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends BaseController
{
    /**
     * GET /dashboard/reviews
     */
    public function index(Request $request)
    {
        $reviews = Review::where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Retorno dinâmico (JSON ou Inertia)
        if ($request->wantsJson()) {
            return $this->json(['reviews' => $reviews]);
        }

        return Inertia::render('Dashboard/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * POST /dashboard/reviews
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'product_id' => 'required|integer|exists:products,id',
            'whatsapp' => 'nullable|string|max:20',
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);

        $data['user_id'] = $this->user->id;
        $data['status'] = $data['status'] ?? 'pending';

        $review = Review::create($data);

        return redirect()
            ->back()
            ->with([
                'message' => 'Avaliação criada com sucesso.',
                'review' => $review,
            ]);
    }

    /**
     * GET /dashboard/reviews/{review}
     */
    public function show(Review $review)
    {
        $this->authorizeOwnership($review);

        return $this->json(['review' => $review]);
    }

    /**
     * PUT/PATCH /dashboard/reviews/{review}
     */
    public function update(Request $request, Review $review)
    {
        $this->authorizeOwnership($review);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'product_id' => 'required|integer|exists:products,id',
            'whatsapp' => 'nullable|string|max:20',
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
        ]);

        $review->update($data);

        return $this->json([
            'message' => 'Avaliação atualizada com sucesso.',
            'review' => $review,
        ]);
    }

    /**
     * DELETE /dashboard/reviews/{review}
     */
    public function destroy(Review $review)
    {
        $this->authorizeOwnership($review);

        $review->delete();

        return $this->json(['message' => 'Avaliação removida com sucesso.']);
    }

    /**
     * Helper para verificar se pertence ao usuário autenticado.
     */
    protected function authorizeOwnership(Review $review)
    {
        if ($review->user_id !== $this->user->id) {
            abort(403, 'Esta avaliação não pertence ao usuário autenticado.');
        }
    }
}
