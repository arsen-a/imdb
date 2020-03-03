<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Genre;

class Movie extends Model
{
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
