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
        return $q->filterdByOfficeId(request('office_id'))
        ->filterdByStatus(request('status'))
        ->filterdByTime(request('from_time'),request('to_time'));
    }
    public function scopeFilterdByTime($q,$from_time,$to_time){

        return $q->when($from_time && $to_time,
                    function($q) use($from_time,$to_time){
                        return $q->where(function($q) use($from_time,$to_time){
                            return $q->where(function($q) use($from_time,$to_time){
                                return $q->where('start_date','>=',$from_time)
                                ->where('start_date','<=',$to_time);
                            })->orWhere(function($q) use($from_time,$to_time){
                                return $q->where('end_date','<=',$to_time)
                                ->where('end_date','>=',$from_time);
                            })->orWhere(function($q) use($from_time,$to_time){
                                return $q->where('start_date','<=',$from_time)
                                ->where('end_date','>=',$to_time);
                            });
                        });
                    });
    }
    public function scopeFilterdByOfficeId($q,$office_id){
        return $q->when($office_id,fn($q) => $q->where('office_id',$office_id));
    }
    public function scopeFilterdByStatus($q,$status){
        return $q->when($status,fn($q) => $q->where('status',$status));
    }
}
