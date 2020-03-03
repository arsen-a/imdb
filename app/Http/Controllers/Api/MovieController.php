<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Movie;
use App\User;
use App\MovieReaction;

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
        // Query for popular movies
        if ($request->popular == true) {
            return Movie::orderBy('likes', 'desc')->take(10)->get();
        }

        // Only querying for movies with search term
        if ($request->search && !$request->genre) {
            return Movie::with('reactions')
                ->whereRaw('lower(title) like (?)', ["%{$request->search}%"])
                ->paginate(10);
        }

        // Only querying for movies with genres selected by client
        if ($request->genre && !$request->search) {
            $genres = explode(',', $request->genre);

            return Movie::with('genres', 'reactions')
                ->whereHas('genres', function ($q) use ($genres) {
                    $q->whereIn('genres.id', $genres);
                })
                ->paginate(10);
        }

        // Querying for movies with both genre selected and search term
        if ($request->genre && $request->search) {
            $genres = explode(',', $request->genre);

            return Movie::with('genres', 'reactions')
                ->whereHas('genres', function ($q) use ($genres) {
                    $q->whereIn('genres.id', $genres);
                })
                ->whereRaw('lower(title) like (?)', ["%{$request->search}%"])
                ->paginate(10);
        }

        // Default movie list for index 
        return Movie::with('genres', 'reactions')
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
                return response()->json(['message' => 'Movie ' . $movie->title . ' liked.'],  200);
            }

            if ($reaction->liked) {
                return response()->json(['message' => 'Movie ' . $movie->title . ' already liked.'],  200);
            }

            $reaction->liked = 1;
            $movie->likes += 1;
            $reaction->save();
            $movie->save();
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
                return response()->json(['message' => 'Movie ' . $movie->title . ' disliked.'],  200);
            }

            if ($reaction->disliked) {
                return response()->json(['message' => 'Movie ' . $movie->title . ' already disliked.'],  200);
            }

            $reaction->disliked = 1;
            $movie->dislikes += 1;
            $reaction->save();
            $movie->save();
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $movie = Movie::where('id', $id)->with('genres')->first();
        $movie->visit_count += 1;
        $movie->save();
        $movie->setRelation('comments', $movie->comments()->paginate(2));

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
