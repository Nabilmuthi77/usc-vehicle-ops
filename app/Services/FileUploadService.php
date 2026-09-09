<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Penyimpanan berkas unggahan (NFR Keamanan & §14 Risiko).
 *
 * Foto dikompresi dan dibatasi lebarnya agar akumulasi nota serta foto
 * inspeksi tidak cepat memenuhi disk server lokal.
 */
class FileUploadService
{
    /** Simpan berkas apa adanya (mis. PDF dokumen kendaraan). */
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, $this->disk());
    }

    /**
     * Simpan gambar dengan kompresi; berkas non-gambar disimpan apa adanya.
     */
    public function storeImage(UploadedFile $file, string $directory): string
    {
        if (! $this->isCompressibleImage($file)) {
            return $this->store($file, $directory);
        }

        $maxWidth = (int) config('usc_vehicle_ops.uploads.image_max_width', 1600);
        $quality = (int) config('usc_vehicle_ops.uploads.image_quality', 75);

        $image = (new ImageManager(new Driver()))->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        $path = rtrim($directory, '/').'/'.Str::uuid()->toString().'.jpg';

        Storage::disk($this->disk())->put($path, (string) $image->encode(new JpegEncoder($quality)));

        return $path;
    }

    /** Hapus berkas lama saat diganti; aman dipanggil dengan nilai null. */
    public function delete(?string $path): void
    {
        if (filled($path) && Storage::disk($this->disk())->exists($path)) {
            Storage::disk($this->disk())->delete($path);
        }
    }

    /** Ganti berkas lama dengan yang baru dan kembalikan path baru. */
    public function replaceImage(?string $oldPath, UploadedFile $file, string $directory): string
    {
        $newPath = $this->storeImage($file, $directory);

        $this->delete($oldPath);

        return $newPath;
    }

    public function url(?string $path): ?string
    {
        return filled($path) ? Storage::disk($this->disk())->url($path) : null;
    }

    private function disk(): string
    {
        return (string) config('usc_vehicle_ops.uploads.disk', 'public');
    }

    private function isCompressibleImage(UploadedFile $file): bool
    {
        return in_array(
            strtolower($file->getClientOriginalExtension()),
            ['jpg', 'jpeg', 'png'],
            true,
        );
    }
}
