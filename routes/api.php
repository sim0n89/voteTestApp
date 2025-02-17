<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;

Route::prefix('v1')->group(function () {
    Route::post('/like', [LikeController::class, 'vote']);
    Route::get('/likes-count', [LikeController::class, 'getVotesCount']);
});
