<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Movie;
use App\User;

class MovieReaction extends Model
{
    protected $primaryKey = 'movie_id';
    public $incrementing = false;
    protected $fillable = [
        'movie_id',
        'user_id',
        'liked',
        'disliked',
        'created_at',
        'updated_at'
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
