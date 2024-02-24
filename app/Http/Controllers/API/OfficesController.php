<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeCollection;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Traits\ApiResponce;
use Illuminate\Http\Request;

class OfficesController extends Controller
{
    use ApiResponce;
    public function index(){
        $offices = Office::where('approval_status',1)
        ->where('hidden',false)
        ->when(request('host_id'),fn($q) => $q->where('user_id',request('host_id')))
        ->when(request('user_id'),fn($q) => $q->whereRelation('reservations','user_id','=',request('user_id')))
        ->withCount(['reservations'=>fn($q)=> $q->where('status',Reservation::STATUS_ACTIVE)])
        ->with(['images','tags','user']);
        return $this->successWithData(new OfficeCollection($offices->latest('id')->paginate(10)));
    }
}
