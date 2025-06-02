<?php 
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

trait uploadImages {
        
    public function uploadImages(Request $request, string $inputName = 'image'): ?array
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }

        $uploaded = $request->file($inputName);
        $paths = [];

        // حالة رفع صورة واحدة
        if ($uploaded instanceof UploadedFile) {
            if ($uploaded->isValid()) {
                $fileName = Str::uuid() . '.' . $uploaded->getClientOriginalExtension();
                $uploaded->storeAs('images', $fileName, 'public');
                $paths[] = 'images/' . $fileName;
            }
        }

        // حالة رفع صور متعددة
        if (is_array($uploaded)) {
            foreach ($uploaded as $image) {
                if ($image->isValid()) {
                    $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('images', $fileName, 'public');
                    $paths[] = 'images/' . $fileName;
                }
            }
        }

        return $paths;

        // dd($request->all());
    }


    
        

        public function deleteImages($request ,$id){

                $images = $request->file('image');
                if ($images) {

                        foreach ($images as $image) {
                        Storage::disk('public')->delete($image);
                        }
                }
        }
}