<?php

namespace App\Http\Controllers\API;

use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqCategoryController extends BaseController
{
    /**
     * Display a listing of the FAQ categories.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Only allow admin users to strore FAQ categories
        if (!$user) {
            return $this->sendError('Invalid request', ['error' => 'User not found'], 401);
        }

        if ($user->role !== 'admin') {
            return $this->sendError('Access denied', [], 403);
        }

        $faqCategories = FaqCategory::select('id', 'name')->get();
        return $this->sendResponse($faqCategories, 'FAQ categories retrieved successfully.');
    }

    /**
     * Store a newly created FAQ category in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Only allow admin users to strore FAQ categories
        if (!$user) {
            return $this->sendError('Invalid request', ['error' => 'User not found'], 401);
        }

        if ($user->role !== 'admin') {
            return $this->sendError('Access denied', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:faq_categories,name|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $data = $validator->validated();

        try {
            $faqCategory = FaqCategory::create([
                'name' => $data['name'],
            ]);

            if (!$faqCategory) {
                return $this->sendError('Failed to store FAQ category.', ['error' => 'Unable to create FAQ category.'], 500);
            }

            return $this->sendResponse($faqCategory, 'FAQ category stored successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while creating FAQ category', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified FAQ category in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Only allow admin users to edit FAQ categories
        if (!$user) {
            return $this->sendError('Invalid request', ['error' => 'User not found'], 401);
        }

        if ($user->role !== 'admin') {
            return $this->sendError('Access denied', [], 403);
        }

        $faqCategory = FaqCategory::find($id);
        if (!$faqCategory) {
            return $this->sendError('FAQ category not found.', ['error' => 'Invalid FAQ category ID.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:faq_categories,name,' . $id . '|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }


        try {

            $faqCategory->update($validator->validated());

            return $this->sendResponse($faqCategory, 'FAQ category updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while updating FAQ category', ['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the specified FAQ category in storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        // Only allow admin users to edit FAQ categories
        if (!$user) {
            return $this->sendError('Invalid request', ['error' => 'User not found'], 401);
        }

        if ($user->role !== 'admin') {
            return $this->sendError('Access denied', [], 403);
        }

        $faqCategory = FaqCategory::find($id);
        if (!$faqCategory) {
            return $this->sendError('FAQ category not found.', ['error' => 'Invalid FAQ category ID.'], 404);
        }

        try {

            $faqCategory->delete();

            return $this->sendResponse([], 'FAQ category deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while deleting FAQ category', ['error' => $e->getMessage()], 500);
        }
    }

}
