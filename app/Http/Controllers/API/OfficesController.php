<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Resources\OfficeCollection;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Traits\Uploadable;
use Illuminate\Support\Facades\DB;

class OfficesController extends Controller
{
    public function index(){
        $offices = Office::where('approval_status',1)
        ->where('hidden',false)
        ->when(request('host_id'),fn($q) => $q->where('user_id',request('host_id')))
        ->when(request('user_id'),fn($q) => $q->whereRelation('reservations','user_id','=',request('user_id')))
        ->when(request('lat')&& request('lng'),fn($q) => $q->nearestFirst(request('lat'),request('lng')),
        fn($q) => $q->orderBy('id'))
        ->withCount(['reservations'=>fn($q)=> $q->where('status',Reservation::STATUS_ACTIVE)])
        ->with(['images','tags','user']);
        return $this->successWithData(new OfficeCollection($offices->paginate(10)));
    }
    public function show(Office $office){
        return $this->successWithData(new OfficeResource($office->load(['images','tags'])
        ->loadCount(['reservations'=> fn($q)=> $q->where('status',Reservation::STATUS_ACTIVE)])));
    }
    public function store(StoreOfficeRequest $request){
        try{
            DB::beginTransaction();
            $office = auth()->user()->offices()->create($request->validated());
            if($request->tags){
                $office->tags()->sync($request->tags);
            }
            if($request->images){
                $office->images()->createMany(Uploadable::uploadMultipleFiles($request->images));
            }
            DB::commit();
            return $this->successWithData(new OfficeResource($office->load(['tags','images'])));
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage(),500,$e);
        }
}
}
