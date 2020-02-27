<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(MovieTableSeeder::class);
        $this->call(GenreTableSeeder::class);

        for ($i = 0; $i < 2; $i++) {
            foreach (App\Movie::all() as $movie) {
                $genre = App\Genre::find(rand(1, 10));
                $movie->genre()->attach($genre->id);
                $movie->save();
            }
        }
    }
}
