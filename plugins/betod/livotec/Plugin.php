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
        File::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                // Chỉ xử lý file ảnh
                if (in_array($model->content_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {

                    $localPath = $model->getLocalPath();

                    if ($localPath && file_exists($localPath)) {
                        $cloudUrl = UploadImagesToCloudinary::uploadSingle($localPath);

                        if ($cloudUrl) {
                            // Nếu muốn lưu lại URL
                            $model->cloudinary_url = $cloudUrl;
                            $model->saveQuietly();

                            \Log::info("🌩 Uploaded to Cloudinary: " . $cloudUrl);
                        }
                    } else {
                        \Log::error("❌ File path not found: " . $model->id);
                    }
                }
            });
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
