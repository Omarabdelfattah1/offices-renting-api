<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'lat',
        'lng',
        'address_line1',
        'address_line2',
        'approval_status',
        'hidden',
        'price_per_day',
        'monthly_discount'
    ];
    protected $casts = [
        'lat' => 'decimal:8',
        'lng'=> 'decimal:8',
        'approval_status'=> 'integer',
        'hidden'=> 'bool',
        'price_per_day'=> 'float',
        'monthly_discount'=> 'float',
    ];
    const APPROVAL_APPROVED = 1;
    const APPROVAL_PENDING = 2;
    const APPROVAL_REJECTED = 3;

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
    public function images(){
        return $this->morphMany(Image::class,'resource');
    }
    public function tags(){
        return $this->belongsToMany(Tag::class);
    }
}
