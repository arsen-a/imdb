<?php

namespace App\Http\Controllers\Api;

use App\Events\MovieDisliked;
use App\Events\MovieLiked;
use App\Events\NewMovieAdded;
use App\Genre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MovieRequest;
use App\Image;
use App\Movie;
use App\User;
use App\MovieReaction;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Movie model
     * @param \Illuminate\Http\Request $request
     */
    public function index(Request $request)
    {
        // elasticsearch query for movies
        if (isset($request->elastic) && $request->elastic === 'on') {
            $movies = Movie::searchByQuery(['match' => ['title' => $request->search]]);
            info('upao sam');
            return response()->json(['movies' => $movies, 'elastic' => true], 200);
        }

        // Query for popular movies
        if ($request->popular == true) {
            return Movie::orderBy('likes', 'desc')->take(10)->get();
        }


        // Only querying for movies with search term
        if ($request->search && $request->elastic === true && !$request->genre) {
            return Movie::with('reactions', 'image')
                ->whereRaw('lower(title) like (?)', ["%{$request->search}%"])
                ->paginate(10);
        }

        // Only querying for movies with genres selected by client
        if ($request->genre && !$request->search) {
            $genres = explode(',', $request->genre);

            return Movie::with('genres', 'reactions', 'image')
                ->whereHas('genres', function ($q) use ($genres) {
                    $q->whereIn('genres.id', $genres);
                })
                ->paginate(10);
        }

        // Querying for movies with both genre selected and search term
        if ($request->genre && $request->search) {
            $genres = explode(',', $request->genre);

            return Movie::with('genres', 'reactions', 'image')
                ->whereHas('genres', function ($q) use ($genres) {
                    $q->whereIn('genres.id', $genres);
                })
                ->whereRaw('lower(title) like (?)', ["%{$request->search}%"])
                ->paginate(10);
        }

        // Default movie list for index 
        return Movie::with('genres', 'reactions', 'image')
            ->paginate(10);
    }

    /**
     * Handle incoming movie reaction. Store new or update existing one
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleReaction(Request $request)
    {
        // collect necessary user id and movie id for insertion 
        $uid = auth()->user()->id;
        $mid = $request->movie_id;
        $movie = Movie::find($mid);
        //get the reaction for specific user and movie
        $reaction = MovieReaction::where('movie_id', $mid)
            ->where('user_id', $uid)
            ->first();

        // handle incoming reaction
        if ($request->reaction == 'like') {
            if (!$reaction) {
                $nr = new MovieReaction;
                $nr->movie_id = $mid;
                $nr->user_id = $uid;
                $nr->liked = 1;
                $nr->save();

                $movie->likes = $movie->likes + 1;
                $movie->save();
                broadcast(new MovieLiked($movie));
                return response()->json(['message' => 'Movie ' . $movie->title . ' liked.'],  200);
            }

            if ($reaction->liked) {
                return response()->json(['message' => 'Movie ' . $movie->title . ' already liked.'],  202);
            }

            $reaction->liked = 1;
            $movie->likes += 1;
            $reaction->save();
            $movie->save();
            broadcast(new MovieLiked($movie));
            return response()->json(['message' => 'Movie ' . $movie->title . ' liked.'],  200);
        }
        if ($request->reaction == 'dislike') {
            if (!$reaction) {
                $nr = new MovieReaction;
                $nr->movie_id = $mid;
                $nr->user_id = $uid;
                $nr->liked = 1;
                $nr->save();

                $movie->dislikes = $movie->dislikes + 1;
                $movie->save();
                broadcast(new MovieDisliked($movie));
                return response()->json(['message' => 'Movie ' . $movie->title . ' disliked.'],  200);
            }

            if ($reaction->disliked) {
                return response()->json(['message' => 'Movie ' . $movie->title . ' already disliked.'],  202);
            }

            $reaction->disliked = 1;
            $movie->dislikes += 1;
            $reaction->save();
            $movie->save();
            broadcast(new MovieDisliked($movie));
            return response()->json(['message' => 'Movie ' . $movie->title . ' disliked.'],  200);
        }
    }

    /**
     * Handle incoming watch mark. Store new or update existing one
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleWatchMark(Request $request)
    {
        $mid = $request->movie_id;
        $user = User::with('moviesWatched')->find(auth()->user()->id);
        $hasWatched = $user->moviesWatched->contains($mid);

        if ($hasWatched) {
            $user->moviesWatched()->detach($mid);
            return response()->json(['message' => 'Deleted from watchlist.', 'watched' => false], 200);
        }

        $user->moviesWatched()->attach($mid);
        return response()->json(['message' => 'Added to watchlist.', 'watched' => true], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MovieRequest $request)
    {
        $imgName = implode('-', explode(' ', $request->title));
        \Image::make($request->image)
            ->resize(200, 200)
            ->save('images/movies/thumbnails/' . $imgName . '.jpg');
        \Image::make($request->image)
            ->resize(400, 400)
            ->save('images/movies/full_size/' . $imgName . '.jpg');

        $movData = $request->only('title', 'description');
        $genData = explode(',', $request->genres);

        $movie = new Movie;
        $movie->title = $movData['title'];
        $movie->description = $movData['description'];
        $movie->save();

        $img = new Image;
        $img->movie_id = $movie->id;
        $img->thumbnail = 'images/movies/thumbnails/' . $imgName . '.jpg';
        $img->full_size = 'images/movies/full_size/' . $imgName . '.jpg';
        $img->save();
        $movie->image = $img->id;
        $movie->save();

        Movie::with('genres', 'reactions', 'comments', 'image')->find($movie->id)->addToIndex();

        foreach ($genData as $genreId) {
            info($genreId);
            DB::insert('insert into genre_movie (genre_id, movie_id) values (?, ?)', [$genreId, $movie->id]);
        }

        event(new NewMovieAdded($movie));

        return response()->json(['message' => 'Movie ' . $movie->title . ' added successfully.', 'movie' => $movie], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $movie = Movie::where('id', $id)->with('genres', 'image')->first();
        $movie->visit_count += 1;
        $movie->save();
        $movie->setRelation('comments', $movie->comments()->orderBy('created_at', 'desc')->paginate(5));

        if ($movie->usersWhoWatched()->where('user_id', auth()->user()->id)->exists()) {
            return response()->json(['movie' => $movie, 'watched' => true], 200);
        }

        return response()->json(['movie' => $movie, 'watched' => false], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $genre
     * @return \Illuminate\Http\Movie
     */
    public function relatedMovies(Request $request)
    {
        $genres = $request->genres;

        return Movie::with('genres')
            ->whereHas('genres', function ($q) use ($genres) {
                $q->whereIn('genres.name', $genres);
            })
            ->take(10)
            ->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
