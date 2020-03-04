<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Genre;
use App\MovieReaction;
use App\Comment;
use App\User;
use Elasticquent\ElasticquentTrait;

class Movie extends Model
{
    use ElasticquentTrait;
    
    protected $guarded = [
        'id'
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function reactions()
    {
        return $this->hasMany(MovieReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function usersWhoWatched()
    {
        return $this->belongsToMany(User::class, 'movie_user', 'movie_id', 'user_id');
    }
}
