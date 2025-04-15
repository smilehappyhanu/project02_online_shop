<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Page;

class PageController extends Controller
{
    public function index (Request $request) {
        $pages = Page::latest();
        $pages = $pages->where('name','like','%'.$request->keyword_search.'%');
        $pages = $pages->orWhere('slug','like','%'.$request->keyword_search.'%');
        $pages = $pages->paginate(10);

        return view('admin.page.list',compact('pages'));
    }

    public function create() {
        return view('admin.page.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:pages'
        ]);
        if($validator->passes()) {
            $page = new Page;
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->description;
            $page->save();

            session()->flash('success','Pages created successfully.');
            return response()->json([
                'status' => true
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy() {

    }
}
