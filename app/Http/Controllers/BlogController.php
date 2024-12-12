<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class BlogController extends Controller
{
    /**
     * Display a listing of blogs.
     */
    public function index(Request $request)
    {
        $blogs = Blog::orderBy('created_at', 'desc');

        if($request->has('search')) {
            $blogs->where('title', 'like', '%' . $request->search . '%');
        }

        $blogs = $blogs->get();

        return response()->json([
            'status' => true,
            'data' => $blogs,
        ]);
    }

    /**
     * Display the specified blog.
     */
    public function show($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $blog['date'] = date('d M Y', strtotime($blog->created_at));
        
        return response()->json([
            'status' => true,
            'data' => $blog,
        ]);
    }

    /**
     * Store a newly created blog.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please fix the validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $blog = Blog::create([
            'title' => $request->title,
            'shortDesc' => $request->shortDesc,
            'image' => $request->image,
            'description' => $request->description,
            'author' => $request->author,
        ]);

        $tempImage = TempImage::find($request->image_id);
        if($tempImage) {
            $imageExtArray = explode('.', $tempImage->name);
            $ext = end($imageExtArray);
            $imageName = time() . '-' . $blog->id . '.' . $ext;
            $blog->image = $imageName;
            $blog->save();

            $sourcePath = base_path('public/uploads/temp/' . $tempImage->name);
            $destinationPath = base_path('public/uploads/blogs/' . $imageName);
            File::copy($sourcePath, $destinationPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'Blog added successfully',
            'data' => $blog,
        ]);
    }

    /**
     * Update the specified blog.
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:10',
            'author' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please fix the validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $blog->update([
            'title' => $request->title,
            'shortDesc' => $request->shortDesc,
            'image' => $request->image,
            'description' => $request->description,
            'author' => $request->author,
        ]);

        $tempImage = TempImage::find($request->image_id);
        if($tempImage) {

            File::delete(base_path('public/uploads/blogs/' . $blog->image));
                
            $imageExtArray = explode('.', $tempImage->name);
            $ext = end($imageExtArray);
            $imageName = time() . '-' . $blog->id . '.' . $ext;
            $blog->image = $imageName;
            $blog->save();

            $sourcePath = base_path('public/uploads/temp/' . $tempImage->name);
            $destinationPath = base_path('public/uploads/blogs/' . $imageName);
            File::copy($sourcePath, $destinationPath);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog,
        ]);
    }

    /**
     * Remove the specified blog.
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $blog->delete();

        return response()->json([
            'status' => true,
            'message' => 'Blog deleted successfully',
        ]);
    }
}
