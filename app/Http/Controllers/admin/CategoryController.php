<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

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
