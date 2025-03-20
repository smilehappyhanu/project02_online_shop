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
                'status' => $request->status
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
    public function edit() {
        
    }
    public function update() {
        
    }
    public function destroy() {
        
    }
}
