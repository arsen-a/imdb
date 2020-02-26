<?php

use App\Genre;
use Illuminate\Database\Seeder;

class GenreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = [
            'action',
            'adenture',
            'comedy',
            'crime',
            'drama',
            'fantasy',
            'historical',
            'horror',
            'romance',
            'western',
        ];

        foreach ($genres as $genre) {
            Genre::create(['name' => $genre]);
        }
    }
}
