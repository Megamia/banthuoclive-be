<?php

namespace Betod\Livotec\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Backend\Classes\Controller;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
        ]);

        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();

        return response()->json([
            'url' => $uploadedFileUrl,
        ]);
    }
}
