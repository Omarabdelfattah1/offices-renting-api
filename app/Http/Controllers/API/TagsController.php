<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function __invoke(){
        $tags = Tag::orderBy("name")->get();
        return $this->successWithData(TagResource::collection($tags));
    }
}
