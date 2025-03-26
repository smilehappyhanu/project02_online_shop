<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index (Request $request) {
        $brands = Brand::latest('id');
        if(!empty($request->get('keyword_search'))) {
            $brands = $brands->where('name','like','%'.$request->keyword_search.'%');
        }
        $brands = $brands->paginate(10);

        return view('admin.brand.list',compact('brands'));
    }

    public function create () {
        return view ('admin.brand.create');
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands',
            'status' => 'required'
        ]);
        if($validator->passes()) {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();
            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit ($id, Request $request) {
        $brand = Brand::find($id);
        if (empty($brand)) {
            $request->session()->flash('error','Record not found');
            return redirect()->route('brands.index');
        }
        return view('admin.brand.edit',compact('brand'));
    }

    public function update ($id, Request $request) {
        $brand = Brand::find($id);
        if (empty($brand)) {
            $request->session()->flash('error','Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
            'status' => 'required'
        ]);
        if($validator->passes()) {
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();
            $request->session()->flash('success','Brand updated successfully.');
            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully.'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy ($id, Request $request) {
        $brand = Brand::find($id);
        if(empty($brand)) {
            $request->session()->flash('error','Record not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $brand->delete();
        $request->session()->flash('success','Brand deleted successfully.');
        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully.'
        ]);
    }
}
