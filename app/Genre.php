<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Movie;

class Genre extends Model
{
    public $timestamps = false;

    public function movie()
    {
        return $this->belongsToMany(Movie::class);
    }
}
