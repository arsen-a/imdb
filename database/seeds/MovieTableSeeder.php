<?php

use App\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Movie;

class MovieTableSeeder extends Seeder
{
    public function run()
    {
        factory(Movie::class, 15)->create();
    }
}
