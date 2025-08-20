<?php

namespace Betod\Livotec\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Exception\NotFound;

class UploadImagesToCloudinary extends Command
{
    protected $name = 'livotec:upload-images';

    protected $description = 'Upload toàn bộ ảnh từ storage/app/uploads/public lên Cloudinary (folder livotec)';

    public function handle()
    {
        $this->info('🚀 Bắt đầu upload ảnh từ storage/app/uploads/public');
        Log::info('🚀 Bắt đầu upload toàn bộ ảnh từ storage/app/uploads/public');

        $folderPath = storage_path('app/uploads/public');

        if (!File::exists($folderPath)) {
            $this->error("❌ Thư mục không tồn tại: {$folderPath}");
            Log::error("❌ Thư mục không tồn tại: {$folderPath}");
            return 1;
        }

        $allFiles = File::allFiles($folderPath);

        Log::info('📂 Tổng số file tìm thấy: ' . count($allFiles));
        $this->info('📂 Tổng số file tìm thấy: ' . count($allFiles));

        if (empty($allFiles)) {
            $this->warn('⚠️ Không có file để upload');
            return 0;
        }

        $chunks = array_chunk($allFiles, 20);

        $uploaded = [];
        $skipped = [];

        foreach ($chunks as $index => $filesToUpload) {
            $this->info("📦 Batch #" . ($index + 1));
            foreach ($filesToUpload as $file) {
                $filePath = $file->getRealPath();
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $publicId = 'livotec/' . $filename;

                if ($this->cloudinaryExists($publicId)) {
                    $skipped[] = $file->getFilename();
                    Log::info("⚠️  Bỏ qua (đã tồn tại): {$publicId}");
                    continue;
                }

                try {
                    $result = Cloudinary::upload($filePath, [
                        'public_id' => $filename,
                        'folder' => 'livotec',
                    ]);

                    $uploaded[] = [
                        'filename' => $file->getFilename(),
                        'url' => $result->getSecurePath(),
                    ];

                    Log::info("✅ Upload thành công: {$file->getFilename()} → " . $result->getSecurePath());
                } catch (\Exception $e) {
                    Log::error("❌ Lỗi upload {$file->getFilename()}: " . $e->getMessage());
                    $this->error("❌ Lỗi upload {$file->getFilename()}: " . $e->getMessage());
                }
            }
        }

        Log::info("🧾 Tổng kết: Uploaded: " . count($uploaded) . " | Skipped: " . count($skipped));
        $this->info("🧾 Tổng kết: Uploaded: " . count($uploaded) . " | Skipped: " . count($skipped));

        return 0;
    }

    private function cloudinaryExists($publicId)
    {
        try {
            $api = new AdminApi();
            $asset = $api->asset($publicId);
            return !empty($asset);
        } catch (NotFound $e) {
            return false;
        } catch (\Exception $e) {
            Log::error("❗ Lỗi kiểm tra Cloudinary [{$publicId}]: " . $e->getMessage());
            return false;
        }
    }
}
