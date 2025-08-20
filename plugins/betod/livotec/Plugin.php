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
        $this->registerConsoleCommand('livotec.upload-images', \Betod\Livotec\Console\UploadImagesToCloudinary::class);
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        File::created(function ($file) {
            // Chỉ xử lý file ảnh
            if (in_array($file->content_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $localPath = storage_path('app/uploads/public/' . $file->getDiskPath());

                $cloudUrl = UploadImagesToCloudinary::uploadSingle($localPath);

                if ($cloudUrl) {
                    // bạn có thể lưu vào custom column hoặc log
                    \Log::info("🌩 Uploaded to Cloudinary: " . $cloudUrl);
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
