<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterImagesRequest;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Traits\Uploadable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImagesController extends Controller
{
    use Uploadable;
    public function __construct(){
        if(!in_array(request()->route('resource_type'),Image::TYPES)|| (!is_int(request()->route('resource_id'))&& !ctype_digit(request()->route('resource_id')))){
            dd(request()->route('resource_type'),request()->route('resource_id'));
            abort(404);
        }
    }
    public function index($resource_type, $resource_id){
        $images = Image::where("resource_type", $resource_type)
        ->where("resource_id", $resource_id);
        return $this->successWithData($images->paginate());
    }
    public function store($resource_type, $resource_id,StoreImageRequest $request){
        $data = [
            "resource_type"=> $resource_type,
            "resource_id"=> $resource_id,
            "path"=> self::uploadFile($request->image),
        ];
        $image = Image::create($data);
        return $this->successWithData(ImageResource::make($image),201);
    }
    public function destroy($resource_type, $resource_id,Image $image){
        abort_if($image->resource_type != $resource_type|| $image->resource_id != $resource_id,404);
        $is_only_image = Image::where("resource_type", $resource_type)->where("resource_id", $resource_id)->count() <= 1;
        if($is_only_image){
            return $this->error(trans("validation.it has no other models",["model" => substr($resource_type,0,strlen($resource_type) -1)]));
        }
        $model = DB::table($resource_type)->where("id", $resource_id)->first();
        if($model->featured_image_id == $image->id){
            return $this->error(trans("validation.it is featured"));
        }
        Storage::delete($image->path);
        $image->delete();
        return $this->successWithData(ImageResource::make($image));
    }
}
