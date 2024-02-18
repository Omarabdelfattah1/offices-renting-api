<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = ['path','resource_type','resource_id'] ;
    public function resource(){
        return $this->morphTo();
    }
}
