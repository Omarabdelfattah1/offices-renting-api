<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Traits\ApiResponce;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    use ApiResponce;
    public function __invoke(){
        $tags = Tag::orderBy("name")->get();
        return $this->successWithData(TagResource::collection($tags));
    }
}
