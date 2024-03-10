<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeFormRequestValidation;
use App\Http\Resources\OfficeCollection;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\OfficePendingNotification;
use App\Traits\Uploadable;
use Illuminate\Support\Facades\DB;

class OfficesController extends Controller
{
    public function index(){
        $offices = Office::when(request('user_id') && auth()->user() && request('user_id') == auth()->id(),
        fn($builder) => $builder,
        fn($builder) => $builder->where('approval_status',Office::APPROVAL_APPROVED)->where('hidden',false))
        ->when(request('host_id'),fn($q) => $q->where('user_id',request('host_id')))
        ->when(request('visitor_id'),fn($q) => $q->whereRelation('reservations','user_id','=',request('visitor_id')))
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
    public function store(OfficeFormRequestValidation $request){
        try{
            DB::beginTransaction();
            $office = auth()->user()->offices()->create($request->validated());
            if($request->tags){
                $office->tags()->attach($request->tags);
            }
            if($request->images){
                $office->images()->createMany(Uploadable::uploadMultipleFiles($request->images));
            }
            User::where('role',User::ROLE_SUPPER_ADMIN)->first()->notify(new OfficePendingNotification($office));
            DB::commit();
            return $this->successWithData(new OfficeResource($office->load(['tags','images'])),201);
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage(),500,$e);
        }
    }
    public function update(OfficeFormRequestValidation $request, Office $office){
        try{
            DB::beginTransaction();
            $office->fill($request->validated());
            if($isDirty = $office->isDirty(['lat','lng','address_line1','price'])){
                $office->approval_status = Office::APPROVAL_PENDING;
            }
            $office->save();
            if($request->tags){
                $office->tags()->sync($request->tags);
            }
            if($request->images){
                $office->images()->createMany(Uploadable::uploadMultipleFiles($request->images));
            }
            if($isDirty){
                User::where('role',User::ROLE_SUPPER_ADMIN)->first()->notify(new OfficePendingNotification($office));
            }
            DB::commit();
            return $this->successWithData(new OfficeResource($office->load(['tags','images'])));
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage(),500,$e);
        }
    }
    public function destroy(Office $office){
        $user = auth()->user();
        abort_if($office->user_id != $user->id && $user->role != User::ROLE_SUPPER_ADMIN,403);
        if ($office->reservations()->count() > 0){
            return $this->error(trans('validation.it has models',['models' => 'reservations']),422);
        }
        return $this->success('success');
    }
}
