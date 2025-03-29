<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Image;

class CategoryController extends Controller
{
    private $category;
    public function __construct(Category $category) {
        $this->category = $category;
    }
    public function index(Request $request) {
        // Get all data 
        $categories = $this->category->latest();
        if(!empty($request->get('keyword_search'))) {
            // get list search base search condition
            $categories = $categories->where('name','like','%'.$request->get('keyword_search') .'%');
        }
        $categories = $categories->latest()->paginate(10);
        return view('admin.category.list',compact('categories'));
    }
    public function create() {
        return view('admin.category.create');
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if($validator->passes()) {
            $category = $this->category->create([
                'name' => $request->name,
                'slug' => $request->slug,
                'status' => $request->status,
                'showHome' => $request->showHome,
            ]);
            // save image here
            if(!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);
                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path()."/temp/".$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate image thumbnail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                $img->resize(450,600);
                $img->save($dPath);
                $category->image = $newImageName;
                $category->save();
                

            }
            $request->session()->flash('success','Category updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit($categoryId, Request $request) {
        $category = Category::find($categoryId);
        if(empty($category)) {
            return redirect()->route('categories.index');
        }
        return view('admin.category.edit',compact('category'));
    }

    public function update($categoryId, Request $request) {
        $category = Category::find($categoryId);

        if(empty($category)) {
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found.'
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
        ]);
        if($validator->passes()) {
            $category = $this->category->find($categoryId)->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'status' => $request->status,
                'showHome' => $request->showHome
            ]);
            $category = $this->category->find($categoryId);
            $oldImage = $category->image;

            // save image here
            if(!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);
                $newImageName = $category->id.'-'. time().'.'.$ext;
                $sPath = public_path()."/temp/".$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // Generate image thumbnail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                // $img->resize(450,600);
                $img->fit(450,600,function($constraint){
                    $constraint->upsize();
                });
                $img->save($dPath);
                $category->image = $newImageName;
                $category->save();
                
                // Delete old image here
                File::delete(public_path().'/uploads/category/thumb/'.$oldImage);
                File::delete(public_path().'/uploads/category/'.$oldImage);


            }
            $request->session()->flash('success','Category added successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Category added successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function destroy($categoryId, Request $request) {
        $category = Category::find($categoryId);
        if(empty($category)) {
            $request->session()->flash('error','Category not found');
            return redirect()->route('categories.index');
        }
        File::delete(public_path().'/uploads/category/thumb/'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);
        $category->delete();
        $request->session()->flash('success','Category deleted successfully');
        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
