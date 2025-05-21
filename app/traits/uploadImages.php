<?php 
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait uploadImages {
        
        public function uploadImages(Request $request){

                if($request->hasFile('image')){

                     $images =[];

                        $images = is_array($request->file('image')) 
                        ? $request->file('image') 
                        : [$request->file('image')];

                        // $images = $request->file('image');

                        foreach ($images as $index=> $image) {

                                $name = Str::slug($request->input('name'));

                                $imagename = $name. '.' .$index. '.' . $images->getClientOriginalExtention();

                                $path = $images->storeAs('uploads', 'public' );

                                $images[]= $path;

                                // dd($path);

                        };

        } }

    //     public function uploadImages(array $images, string $folder = 'uploads', string $disk = 'public'): array
    // {

    //     if($request->hasFile('image')){
    //     $uploadedPaths = [];

    //     foreach ($images as $image) {
    //         $uploadedPaths[] = $this->uploadImage($image, $folder, $disk);
    //     }

    //     return $uploadedPaths;
    // }}

        

        public function deleteImages($request ,$id){

                $images = $request->file('image');
                if ($images) {

                        foreach ($images as $image) {
                        Storage::disk('public')->delete($image);
                        }
                }
        }
}