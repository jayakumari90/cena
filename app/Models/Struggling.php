<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Struggling extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'id',
        'options',
        'created_at',
        'updated_at'
    ];
    public $hidden = ['created_at','updated_at'];
}
