<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DishCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
        'created_at',
        'updated_at'
    ];

   protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getrestro()
    {
        return $this->belongsTo('App\Models\User', 'restro_id');
    }
}
