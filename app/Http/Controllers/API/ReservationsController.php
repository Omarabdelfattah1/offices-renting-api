<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    public function index(){
        $reservations = Reservation::with(['office','user'])
        ->when(request('user_id'),fn($q)=> $q->where('user_id', request('user_id')))
        ->when(request('office_id'),fn($q) => $q->where('office_id',request('office_id')))
        ->when(request('from_time') && request('to_time'),
        function($q){
            return $q->where(function($q){
                return $q->where(function($q){
                    return $q->where('start_date','>=',request('from_time'))
                    ->where('start_date','<=',request('to_time'));
                })->orWhere(function($q){
                    return $q->where('end_date','<=',request('to_time'))
                    ->where('end_date','>=',request('from_time'));
                });
            });
        })
        ->when(request('status'),fn($q) => $q->where('status',request('status')))
        ->paginate();
        return $this->successWithData(ReservationCollection::make($reservations));
    }
    public function my_reservations(){
        $reservations = Reservation::with('office')
        ->filterd()
        ->where('user_id', auth()->id())->paginate();
        return $this->successWithData(ReservationCollection::make($reservations));
    }
    public function office_reservations(){
        $reservations = Reservation::with(['user','office'])
        ->filterd()
        ->whereHas('office', function($q){
            return $q->where('offices.user_id', auth()->id());
        })->paginate();
        return $this->successWithData(ReservationCollection::make($reservations));
    }
    public function store(StoreReservationRequest $request){
        $reservation = Reservation::create($request->all());
        return $this->success();
    }
}
