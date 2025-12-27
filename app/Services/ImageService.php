<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function upload(UploadedFile $file, string $folder, int $width = 1200): string
    {
        $filename = Str::random(40) . '.webp';
        $path = "$folder/$filename";

        $image = $this->manager->read($file);

        if ($image->width() > $width) {
            $image->scale(width: $width);
        }

        $encoded = $image->toWebp(quality: 80);

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}