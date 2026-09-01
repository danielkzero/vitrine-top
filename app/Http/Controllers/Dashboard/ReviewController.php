<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends BaseController
{
    public function index(Request $request)
    {
        $reviews = Review::where('user_id', $this->user->id)
            ->with('product:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->json(['reviews' => $reviews]);
        }

        return Inertia::render('Dashboard/Reviews/Index', [
            'reviews' => $reviews,
            'stats' => [
                'total' => $reviews->count(),
                'approved' => $reviews->where('status', 'approved')->count(),
                'pending' => $reviews->where('status', 'pending')->count(),
                'rejected' => $reviews->where('status', 'rejected')->count(),
                'average_rating' => round((float) ($reviews->avg('rating') ?? 0), 1),
            ],
        ]);
    }

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
                'message' => 'Avaliacao criada com sucesso.',
                'review' => $review,
            ]);
    }

    public function show(Review $review)
    {
        $this->authorizeOwnership($review);

        return $this->json(['review' => $review]);
    }

    public function update(Request $request, Review $review)
    {
        $this->authorizeOwnership($review);

        $data = $request->validate([
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'product_id' => 'sometimes|integer|exists:products,id',
            'whatsapp' => 'nullable|string|max:20',
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:pending,approved,rejected'],
        ]);

        $review->update($data);

        if ($request->wantsJson()) {
            return $this->json([
                'message' => 'Avaliacao atualizada com sucesso.',
                'review' => $review,
            ]);
        }

        return back()->with('success', 'Avaliacao atualizada com sucesso.');
    }

    public function destroy(Request $request, Review $review)
    {
        $this->authorizeOwnership($review);

        $review->delete();

        if ($request->wantsJson()) {
            return $this->json(['message' => 'Avaliacao removida com sucesso.']);
        }

        return back()->with('success', 'Avaliacao removida com sucesso.');
    }

    protected function authorizeOwnership(Review $review)
    {
        if ($review->user_id !== $this->user->id) {
            abort(403, 'Esta avaliacao nao pertence ao usuario autenticado.');
        }
    }
}
