<?php

namespace App\Listeners;

use App\Events\NewMovieAdded;
use App\Mail\NewMovieMail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendNewMovieEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewMovieAdded  $event
     * @return void
     */
    public function handle(NewMovieAdded $event)
    {
        Mail::to('arsen@imdbproj.com')->send(new NewMovieMail($event->movie));
    }

    public function failed(Exception $exception)
    {
        // usually would send new notification to admin/user
        info($exception);
    }
}
