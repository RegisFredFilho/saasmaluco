<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewReportController as AdminReviewReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ArtistMemberInviteController;
use App\Http\Controllers\ArtistProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\Discovery\ArtistDiscoveryController;
use App\Http\Controllers\Discovery\OpportunityDiscoveryController;
use App\Http\Controllers\Discovery\VenueDiscoveryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VenueProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/artistas', [ArtistDiscoveryController::class, 'index'])
    ->name('artists.index');
Route::get('/artistas/{artistProfile:slug}', [ArtistProfileController::class, 'show'])
    ->name('artists.show');

Route::get('/estabelecimentos', [VenueDiscoveryController::class, 'index'])
    ->name('venues.index');
Route::get('/estabelecimentos/{venueProfile:slug}', [VenueProfileController::class, 'show'])
    ->name('venues.show');

Route::get('/oportunidades', [OpportunityDiscoveryController::class, 'index'])
    ->name('opportunities.index');
Route::get('/oportunidades/{opportunity}', [OpportunityController::class, 'show'])
    ->name('opportunities.show');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', [
            'artistProfiles' => auth()->user()->ownedArtistProfiles()->latest()->take(5)->get(),
            'venueProfiles' => auth()->user()->ownedVenueProfiles()->latest()->take(5)->get(),
        ]);
    })->name('dashboard');

    Route::get('/rede', [NetworkController::class, 'index'])->name('network.index');
    Route::get('/candidaturas', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/notificacoes', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificacoes/lidas', [AppNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notificacoes/{notification}', [AppNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/convites', [ArtistMemberInviteController::class, 'index'])->name('invites.index');
    Route::post('/convites/{invite}/aceitar', [ArtistMemberInviteController::class, 'accept'])->name('invites.accept');
    Route::post('/convites/{invite}/recusar', [ArtistMemberInviteController::class, 'decline'])->name('invites.decline');
    Route::delete('/convites/{invite}', [ArtistMemberInviteController::class, 'cancel'])->name('invites.cancel');
    Route::get('/negociacoes', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/negociacoes/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/negociacoes/{conversation}/mensagens', [MessageController::class, 'store'])
        ->name('conversations.messages.store');
    Route::post('/negociacoes/{conversation}/propostas', [ProposalController::class, 'store'])
        ->name('conversations.proposals.store');
    Route::post('/negociacoes/{conversation}/propostas/{proposal}/responder', [ProposalController::class, 'respond'])
        ->name('conversations.proposals.respond');

    Route::get('/agenda', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/agenda/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/agenda/{booking}/concluir', [BookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/agenda/{booking}/cancelar', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/agenda/{booking}/avaliacoes', [ReviewController::class, 'store'])->name('bookings.reviews.store');
    Route::post('/avaliacoes/{review}/denunciar', [ReviewController::class, 'report'])->name('reviews.report');
    Route::post('/avaliacoes/{review}/ocultar', [ReviewController::class, 'hide'])->name('reviews.hide');

    Route::post('/oportunidades/{opportunity}/candidaturas', [ApplicationController::class, 'store'])
        ->name('opportunities.applications.store');
    Route::patch('/oportunidades/{opportunity}/candidaturas/{application}/status', [ApplicationController::class, 'updateStatus'])
        ->name('opportunities.applications.status');
    Route::delete('/oportunidades/{opportunity}/candidaturas/{application}', [ApplicationController::class, 'withdraw'])
        ->name('opportunities.applications.withdraw');

    Route::post('/artistas/{artistProfile:slug}/seguir', [FollowController::class, 'storeArtist'])
        ->name('artists.follow');
    Route::delete('/artistas/{artistProfile:slug}/seguir', [FollowController::class, 'destroyArtist'])
        ->name('artists.unfollow');
    Route::post('/artistas/{artistProfile:slug}/favoritar', [FavoriteController::class, 'storeArtist'])
        ->name('artists.favorite');
    Route::delete('/artistas/{artistProfile:slug}/favoritar', [FavoriteController::class, 'destroyArtist'])
        ->name('artists.unfavorite');

    Route::post('/estabelecimentos/{venueProfile:slug}/seguir', [FollowController::class, 'storeVenue'])
        ->name('venues.follow');
    Route::delete('/estabelecimentos/{venueProfile:slug}/seguir', [FollowController::class, 'destroyVenue'])
        ->name('venues.unfollow');
    Route::post('/estabelecimentos/{venueProfile:slug}/favoritar', [FavoriteController::class, 'storeVenue'])
        ->name('venues.favorite');
    Route::delete('/estabelecimentos/{venueProfile:slug}/favoritar', [FavoriteController::class, 'destroyVenue'])
        ->name('venues.unfavorite');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('artist-profiles', ArtistProfileController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::post('artist-profiles/{artistProfile}/media', [ArtistProfileController::class, 'storeMedia'])
        ->name('artist-profiles.media.store');
    Route::delete('artist-profiles/{artistProfile}/media/{media}', [ArtistProfileController::class, 'destroyMedia'])
        ->name('artist-profiles.media.destroy');

    Route::post('artist-profiles/{artistProfile}/experiences', [ArtistProfileController::class, 'storeExperience'])
        ->name('artist-profiles.experiences.store');
    Route::delete('artist-profiles/{artistProfile}/experiences/{experience}', [ArtistProfileController::class, 'destroyExperience'])
        ->name('artist-profiles.experiences.destroy');

    Route::post('artist-profiles/{artistProfile}/availabilities', [ArtistProfileController::class, 'storeAvailability'])
        ->name('artist-profiles.availabilities.store');
    Route::delete('artist-profiles/{artistProfile}/availabilities/{availability}', [ArtistProfileController::class, 'destroyAvailability'])
        ->name('artist-profiles.availabilities.destroy');

    Route::post('artist-profiles/{artistProfile}/invites', [ArtistMemberInviteController::class, 'store'])
        ->name('artist-profiles.invites.store');
    Route::delete('artist-profiles/{artistProfile}/members/{member}', [ArtistMemberInviteController::class, 'destroyMember'])
        ->name('artist-profiles.members.destroy');

    Route::resource('venue-profiles', VenueProfileController::class)
        ->only(['index', 'create', 'store', 'edit', 'update']);

    Route::post('venue-profiles/{venueProfile}/media', [VenueProfileController::class, 'storeMedia'])
        ->name('venue-profiles.media.store');
    Route::delete('venue-profiles/{venueProfile}/media/{media}', [VenueProfileController::class, 'destroyMedia'])
        ->name('venue-profiles.media.destroy');

    Route::post('venue-profiles/{venueProfile}/opportunities', [OpportunityController::class, 'store'])
        ->name('venue-profiles.opportunities.store');
    Route::put('venue-profiles/{venueProfile}/opportunities/{opportunity}', [OpportunityController::class, 'update'])
        ->name('venue-profiles.opportunities.update');
    Route::patch('venue-profiles/{venueProfile}/opportunities/{opportunity}/status', [OpportunityController::class, 'updateStatus'])
        ->name('venue-profiles.opportunities.status');
});

Route::middleware(['auth', 'active', 'platform.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/usuarios/{user}/suspender', [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/usuarios/{user}/reativar', [AdminUserController::class, 'reactivate'])->name('users.reactivate');
        Route::get('/denuncias', [AdminReviewReportController::class, 'index'])->name('reports.index');
        Route::post('/denuncias/{report}/ocultar', [AdminReviewReportController::class, 'hide'])->name('reports.hide');
        Route::post('/denuncias/{report}/arquivar', [AdminReviewReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('/avaliacoes/{review}/reexibir', [AdminReviewReportController::class, 'unhide'])->name('reviews.unhide');
    });

require __DIR__.'/auth.php';
