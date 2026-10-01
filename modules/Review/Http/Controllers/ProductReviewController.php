<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Review\Http\Requests\StoreReviewRequest;

class ProductReviewController
{
    /**
     * Display a listing of the resource.
     *
     * @param int $productId
     *
     * @return Response
     */
    public function index($productId)
    {
        return Review::withoutGlobalScope('approved')->where('product_id', $productId)->latest()->paginate(5);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param int $productId
     * @param StoreReviewRequest $request
     *
     * @return Response
     */
    public function store($productId, StoreReviewRequest $request)
    {
        // if (!setting('reviews_enabled')) {
        //     return response()->json(['success' => false, 'message' => 'Reviews are disabled'], 403);
        // }

        $userId = $request->header('X-User-Id') ?? $request->input('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 401);
        }

        // Get Name from request or fallback to generic User
        $reviewerName = $request->input('reviewer_name') ?? $request->input('name') ?? 'User';

        $review = Product::findOrFail($productId)
            ->reviews()
            ->updateOrCreate(
                ['reviewer_id' => $userId],
                [
                    'rating' => $request->rating,
                    'reviewer_name' => $reviewerName,
                    'comment' => $request->comment,
                    'is_approved' => 1, // Auto-approve so it shows up immediately matching Web behavior
                ]
            );

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ]);
    }
}
