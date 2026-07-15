<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller {
    public function tinymceImage(Request $request) {
        try {
            if ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                ]);
                $path = $request->file('file')->store('tinymce', 'public');
                $url = asset(Storage::url($path));
                return response()->json(['location' => $url]);
            }
            return response()->json(['error' => 'No file uploaded'], 400);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        }
    }
}
