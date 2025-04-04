<?php

use App\Models\Category;

function getCategories () {
    return Category::orderBy('name','ASC')
                     ->with('sub_category')
                     ->where('showHome','Yes')
                     ->where('status',1)
                     ->orderBy('id','DESC')
                     ->get();
}

