<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FaqService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    protected FaqService $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->faqService->getAll(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->faqService->getById($id),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'category_id' => 'required|exists:faq_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => $this->faqService->create($data),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create FAQ',
            ], 500);
        }
    }

    public function update(Request $request, int $id, FaqService $faqService): JsonResponse {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'subtitle' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:faq_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => $faqService->update($id, $data),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update FAQ',
            ], 500);
        }
    }
    
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->faqService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete FAQ',
            ], 500);
        }
    }

}
