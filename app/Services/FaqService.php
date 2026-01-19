<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Str;

class FaqService
{
    /**
     * Format FAQ response structure.
     */
    private function formatFaqResponse(Faq $faq): array
    {
        return [
            'id' => $faq->id,
            'title' => $faq->title,
            'subtitle' => $faq->subtitle,
            'imageURL' => $faq->image_url
                ? asset(config('files.faq_file_path') . $faq->image_url)
                : null,
            'category' => $faq->category->name,
            'categoryID' => $faq->category->id,
        ];
    }

    // Get list of FAQs
    public function getAll(): array
    {
        return Faq::with('category')
            ->get()
            ->map(fn ($faq) => $this->formatFaqResponse($faq))
            ->toArray();
    }

    // Get details of 1 FAQ
    public function getById(int $id): array
    {
        $faq = Faq::with('category')->findOrFail($id);

        return $this->formatFaqResponse($faq);
    }

    // Create 1 FAQ
    public function create(array $data): array
    {
        if (!empty($data['image'])) {
            $data['image_url'] = $this->storeImage($data['image']);
        }

        unset($data['image']);

        $faq = Faq::create($data);
        $faq->load('category');

        return $this->formatFaqResponse($faq);
    }

    // Update 1 FAQ
    public function update(int $id, array $data): array
    {
        $faq = Faq::findOrFail($id);

        // Handle image removal
        if (!empty($data['remove_image']) && $faq->image_url) {
            $this->deleteImage($faq->image_url);
            $faq->image_url = null;
        }

        // Handle image upload
        if (!empty($data['image'])) {
            if ($faq->image_url) {
                $this->deleteImage($faq->image_url);
            }

            $fileName = $this->storeImage($data['image']);
            $faq->image_url = $fileName;
        }

        // Update other fields
        $faq->fill(collect($data)->except(['image', 'remove_image'])->toArray());
        $faq->save();

        $faq->load('category');

        return $this->formatFaqResponse($faq);
    }

    // Delete 1 FAQ
    public function delete(int $id): void
    {
        $faq = Faq::findOrFail($id);

        if ($faq->image_url) {
            $this->deleteImage($faq->image_url);
        }

        $faq->delete();
    }

    // Store image to disk
    private function storeImage($image): string
    {
        $fileName = (string) Str::uuid() . '.' . $image->getClientOriginalExtension();

        $path = public_path(config('files.faq_file_path'));

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $image->move($path, $fileName);

        return $fileName;
    }

    // Delete image from disk
    private function deleteImage(string $fileName): void
    {
        $path = public_path(config('files.faq_file_path') . $fileName);

        if (file_exists($path)) {
            unlink($path);
        }
    }
    
}
