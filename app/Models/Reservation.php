<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'office_id',
        'start_date',
        'end_date',
        'status',
        'price'
    ];
    protected $casts = [
        'status'=> 'integer',
        'price'=> 'float',
        'start_date' =>'immutable_date',
        'end_date' =>'immutable_date',
    ];
    const STATUS_PENDING = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_CANCELED = 3;
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function office(){
        return $this->belongsTo(Office::class);
    }
    public function scopeFilterd($q){
        return $q->when(request('user_id'),fn($q)=> $q->where('user_id', request('user_id')))
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
        ->when(request('status'),fn($q) => $q->where('status',request('status')));
    }
}
