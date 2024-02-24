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
}
