<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;

class Comment extends Model
{
    use ElasticquentTrait;
    
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
