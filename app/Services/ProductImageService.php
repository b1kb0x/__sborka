<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Throwable;

class ProductImageService
{
    public function __construct(
        protected ImageManager $images = new ImageManager(new Driver())
    ) {
    }

    public function uploadPrimary(Product $product, UploadedFile $file, ?string $alt = null): ProductImage
    {
        return DB::transaction(function () use ($product, $file, $alt) {
            $existing = $product->primaryImage()->first();

            if ($existing) {
                return $this->replace($existing, $file, $alt);
            }

            $fileName = $this->generateFileName($product, $file);
            $this->storeVariants($product, $file, $fileName);

            return ProductImage::create([
                'product_id' => $product->id,
                'file_name' => $fileName,
                'alt' => $alt,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        });
    }

    public function replace(ProductImage $image, UploadedFile $file, ?string $alt = null): ProductImage
    {
        $oldFileName = $image->file_name;
        $newFileName = $this->generateFileName($image->product, $file);

        $this->storeVariants($image->product, $file, $newFileName);

        try {
            $image->update([
                'file_name' => $newFileName,
                'alt' => $alt,
            ]);

            $this->deleteFilesByName($image->product_id, $oldFileName);

            return $image->fresh();
        } catch (Throwable $e) {
            $this->deleteFilesByName($image->product_id, $newFileName);
            throw $e;
        }
    }

    public function delete(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $this->deleteFilesByName($image->product_id, $image->file_name);
            $image->delete();
        });
    }

    public function deleteForProduct(Product $product): void
    {
        $product->images()->each(fn (ProductImage $image) => $this->delete($image));
    }

    protected function storeVariants(Product $product, UploadedFile $file, string $fileName): void
    {
        $disk = Storage::disk('public');
        $directory = 'products';
        $source = $this->images->read($file->getRealPath());

        $thumbnail = clone $source;
        $preview = clone $source;
        $original = clone $source;

        $thumbnail->cover(300, 300)->toWebp(82);
        $preview->cover(800, 800)->toWebp(84);
        $original->scaleDown(width: 1600, height: 1600)->toWebp(86);

        $disk->put(
            "{$directory}/{$product->id}_thumbnail_{$fileName}",
            (string) $thumbnail
        );

        $disk->put(
            "{$directory}/{$product->id}_preview_{$fileName}",
            (string) $preview
        );

        $disk->put(
            "{$directory}/{$product->id}_{$fileName}",
            (string) $original
        );
    }

    protected function deleteFilesByName(int $productId, string $fileName): void
    {
        $disk = Storage::disk('public');

        foreach ([
                     "products/{$productId}_thumbnail_{$fileName}",
                     "products/{$productId}_preview_{$fileName}",
                     "products/{$productId}_{$fileName}",
                 ] as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    protected function generateFileName(Product $product, UploadedFile $file): string
    {
        $base = Str::slug($product->title ?? $product->name ?? 'product');
        $hash = Str::lower(Str::random(8));

        return "{$base}-{$hash}.webp";
    }
}
