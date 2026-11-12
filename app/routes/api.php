<?php

use App\Http\Controllers\Api\V1\Admin\DashboardController as ApiAdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\ReviewReportController as ApiAdminReviewReportController;
use App\Http\Controllers\Api\V1\Admin\UserController as ApiAdminUserController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\MyArtistController;
use App\Http\Controllers\Api\V1\MyVenueController;
use App\Http\Controllers\Api\V1\NetworkController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OpportunityController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProposalController;
use App\Http\Controllers\Api\V1\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'ok' => true,
    'version' => 'v1',
]));

Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/artists', [ArtistController::class, 'index']);
Route::get('/artists/{artist:slug}', [ArtistController::class, 'show']);

Route::get('/venues', [VenueController::class, 'index']);
Route::get('/venues/{venue:slug}', [VenueController::class, 'show']);

Route::get('/opportunities', [OpportunityController::class, 'index']);
Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show']);

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/me', [ProfileController::class, 'update']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/me/artists', [MyArtistController::class, 'index']);
    Route::post('/me/artists', [MyArtistController::class, 'store']);
    Route::patch('/me/artists/{artistProfile}', [MyArtistController::class, 'update']);

    Route::get('/me/venues', [MyVenueController::class, 'index']);
    Route::post('/me/venues', [MyVenueController::class, 'store']);
    Route::patch('/me/venues/{venueProfile}', [MyVenueController::class, 'update']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/opportunities/{opportunity}/applications', [ApplicationController::class, 'store']);
    Route::patch('/opportunities/{opportunity}/applications/{application}', [ApplicationController::class, 'updateStatus']);
    Route::delete('/opportunities/{opportunity}/applications/{application}', [ApplicationController::class, 'destroy']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversation}/proposals', [ProposalController::class, 'store']);
    Route::post('/conversations/{conversation}/proposals/{proposal}/respond', [ProposalController::class, 'respond']);

    Route::post('/artists/{artist:slug}/follow', [NetworkController::class, 'followArtist']);
    Route::delete('/artists/{artist:slug}/follow', [NetworkController::class, 'unfollowArtist']);
    Route::post('/artists/{artist:slug}/favorite', [NetworkController::class, 'favoriteArtist']);
    Route::delete('/artists/{artist:slug}/favorite', [NetworkController::class, 'unfavoriteArtist']);

    Route::post('/venues/{venue:slug}/follow', [NetworkController::class, 'followVenue']);
    Route::delete('/venues/{venue:slug}/follow', [NetworkController::class, 'unfollowVenue']);
    Route::post('/venues/{venue:slug}/favorite', [NetworkController::class, 'favoriteVenue']);
    Route::delete('/venues/{venue:slug}/favorite', [NetworkController::class, 'unfavoriteVenue']);
});

Route::middleware(['auth:sanctum', 'active', 'platform.admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', ApiAdminDashboardController::class);
        Route::get('/users', [ApiAdminUserController::class, 'index']);
        Route::post('/users/{user}/suspend', [ApiAdminUserController::class, 'suspend']);
        Route::post('/users/{user}/reactivate', [ApiAdminUserController::class, 'reactivate']);
        Route::get('/reports', [ApiAdminReviewReportController::class, 'index']);
        Route::post('/reports/{report}/hide', [ApiAdminReviewReportController::class, 'hide']);
        Route::post('/reports/{report}/dismiss', [ApiAdminReviewReportController::class, 'dismiss']);
        Route::post('/reviews/{review}/unhide', [ApiAdminReviewReportController::class, 'unhide']);
    });
