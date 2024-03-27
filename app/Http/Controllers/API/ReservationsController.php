<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;

class ReservationsController extends Controller
{
    public function index(){
        $reservations = Reservation::with(['office','user'])
        ->filterd()
        ->when(request('user_id'),fn($q)=> $q->where('user_id', request('user_id')))
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
        $data = $request->validated();
        $reservation = Reservation::create($data);
        return $this->success();
    }
}
