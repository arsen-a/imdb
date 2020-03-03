<?php

namespace App\Http\Controllers\Api;

use App\Genre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Movie;
use Illuminate\Pagination\LengthAwarePaginator;

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
        // Only querying for movies with search term
        if ($request->search && !$request->genre) {
            // 
            return Movie::whereRaw('lower(title) like (?)', ["%{$request->search}%"])->paginate(10);
        }

        // Only querying for movies with genres selected by client
        if ($request->genre && !$request->search) {
            $genres = explode(',', $request->genre);
            return Movie::with('genres')->whereHas('genres', function ($q) use ($genres) {
                $q->whereIn('genres.id', $genres);
            })->paginate(10);
        }

        // Querying for movies with both genre selected and search term
        if ($request->genre && $request->search) {
            $genres = explode(',', $request->genre);
            return Movie::with('genres')
                ->whereHas('genres', function ($q) use ($genres) {
                    $q->whereIn('genres.id', $genres);
                })
                ->whereRaw('lower(title) like (?)', ["%{$request->search}%"])
                ->paginate(10);
        }

        return Movie::with('genres')->paginate(10);
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
        return Movie::where('id', $id)->with('genres')->first();
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
