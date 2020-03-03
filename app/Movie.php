<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Genre;
use App\MovieReaction;

class Movie extends Model
{
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function reactions()
    {
        return $this->hasMany(MovieReaction::class);
    }
}
