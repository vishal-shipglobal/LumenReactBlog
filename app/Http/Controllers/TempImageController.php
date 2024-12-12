<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TempImageController extends Controller
{
    /**
     * Store a newly created blog.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please fix the validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        //Upload Image Here
        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;

        //Store Image Info Here
        $tempImage = new TempImage();
        $tempImage->name = $imageName;
        $tempImage->save();
        
        $image->move(base_path('public/uploads/temp/'), $imageName);

        return response()->json([
            'status' => true,
            'message' => 'Image Uploaded successfully',
            'image' => $tempImage
        ]);
    }
}
