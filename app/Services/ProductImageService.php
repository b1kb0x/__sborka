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
use Intervention\Image\Encoders\WebpEncoder;
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
        $oldPaths = $this->pathsForStoredFile(
            $image->product_id,
            (string) ($image->getOriginal('file_name') ?: $image->file_name)
        );
        $newFileName = $this->generateFileName($image->product, $file);
        $newPaths = $this->pathsForStoredFile($image->product_id, $newFileName);

        $this->storeVariants($image->product, $file, $newFileName);

        try {
            $image->update([
                'file_name' => $newFileName,
                'alt' => $alt,
            ]);

            $this->deletePaths($oldPaths);

            return $image->fresh();
        } catch (Throwable $e) {
            $this->deletePaths($newPaths);
            throw $e;
        }
    }

    public function delete(ProductImage $image): void
    {
        $paths = $this->pathsForStoredFile(
            $image->product_id,
            (string) ($image->getOriginal('file_name') ?: $image->file_name)
        );

        DB::transaction(function () use ($image) {
            $image->delete();
        });

        $this->deletePaths($paths);
    }

    public function pathsForProduct(Product $product): array
    {
        $images = $product->relationLoaded('images')
            ? $product->images
            : $product->images()->get();

        return $images
            ->flatMap(fn (ProductImage $image) => $this->pathsForStoredFile(
                $image->product_id,
                (string) ($image->getOriginal('file_name') ?: $image->file_name)
            ))
            ->values()
            ->all();
    }

    public function deletePaths(array $paths): void
    {
        $disk = Storage::disk('public');

        foreach (array_unique($paths) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    protected function storeVariants(Product $product, UploadedFile $file, string $fileName): void
    {
        $disk = Storage::disk('public');
        $directory = 'products';
        $source = $this->images->decode($file->getRealPath());

        $thumbnail = clone $source;
        $preview = clone $source;
        $original = clone $source;

        $thumbnail = $thumbnail->cover(300, 300)->encode(new WebpEncoder(82));
        $preview = $preview->cover(800, 800)->encode(new WebpEncoder(84));
        $original = $original->scaleDown(width: 1600, height: 1600)->encode(new WebpEncoder(86));

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

    protected function pathsForImage(ProductImage $image): array
    {
        return $this->pathsForStoredFile(
            $image->product_id,
            (string) ($image->getOriginal('file_name') ?: $image->file_name)
        );
    }

    protected function pathsForStoredFile(int $productId, string $fileName): array
    {
        return [
            "products/{$productId}_thumbnail_{$fileName}",
            "products/{$productId}_preview_{$fileName}",
            "products/{$productId}_{$fileName}",
        ];
    }

    protected function generateFileName(Product $product, UploadedFile $file): string
    {
        $base = Str::slug($product->title ?? $product->name ?? 'product');
        $hash = Str::lower(Str::random(8));

        return "{$base}-{$hash}.webp";
    }
}
