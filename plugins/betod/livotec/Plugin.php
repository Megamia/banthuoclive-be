<?php
namespace Betod\Livotec;

use System\Classes\PluginBase;
use System\Models\File;
use Betod\Livotec\Console\UploadImagesToCloudinary;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        $this->registerConsoleCommand('livotec.upload-images', UploadImagesToCloudinary::class);
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        File::created(function ($file) {
            // Chỉ xử lý file ảnh
            if (in_array($file->content_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {

                $localPath = $file->getLocalPath(); // ✅ dùng hàm built-in

                if ($localPath && file_exists($localPath)) {
                    $cloudUrl = UploadImagesToCloudinary::uploadSingle($localPath);

                    if ($cloudUrl) {
                        // log trong backend + file system
                        \Log::info("🌩 Uploaded to Cloudinary: " . $cloudUrl);
                    }
                } else {
                    \Log::error("❌ File path not found: " . $file->id);
                }
            }
        });
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
