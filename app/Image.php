<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Movie;


class Image extends Model
{
    protected $guarded = ['id'];
    
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
