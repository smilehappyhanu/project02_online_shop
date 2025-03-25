<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SubCategory;

class SubCategoryController extends Controller
{
    private $subCategory;
    public function __construct (SubCategory $subCategory) {
        $this->subCategory = $subCategory;
    }
    public function index (Request $request) {
        $subCategories = $this->subCategory->select('sub_categories.*','categories.name as categoryName')
                                            ->latest('sub_categories.id')
                                            ->leftJoin('categories','categories.id','sub_categories.category_id');
        if(!empty($request->get('keyword_search'))) {
            $subCategories = $subCategories->where('sub_categories.name','like','%' .$request->get('keyword_search').'%');
            $subCategories = $subCategories->orWhere('categories.name','like','%' .$request->get('keyword_search').'%');
        }
        $subCategories = $subCategories->paginate(10);
        return view('admin.sub_category.list',compact('subCategories'));
    }

    public function create () {
        $categories = Category::orderBy('name','ASC')->get();
        return view('admin.sub_category.create',compact('categories'));
    }
    public function store (Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);
        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub-category added successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Sub-category added successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
