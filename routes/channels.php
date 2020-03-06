<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('comment', function ($user) {
    return true;
});
Broadcast::channel('likes', function  ($user) {
    return true;
});
Broadcast::channel('dislikes', function  ($user) {
    return true;
});