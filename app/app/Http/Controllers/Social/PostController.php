<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\ArtistProfile;
use App\Models\Post;
use App\Models\VenueProfile;
use App\Services\Social\CreatePost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Post::class);

        $user = $request->user();

        $artists = ArtistProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));
            })
            ->orderBy('display_name')
            ->get();

        $venues = VenueProfile::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('managers', fn ($q) => $q->where('user_id', $user->id));
            })
            ->orderBy('display_name')
            ->get();

        return view('social.posts.create', [
            'artists' => $artists->filter(fn (ArtistProfile $p) => $user->can('update', $p))->values(),
            'venues' => $venues->filter(fn (VenueProfile $p) => $user->can('update', $p))->values(),
        ]);
    }

    public function store(StorePostRequest $request, CreatePost $createPost): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $post = $createPost->handle($request->user(), [
            'body' => $request->validated('body'),
            'publisher_type' => $request->validated('publisher_type'),
            'publisher_id' => $request->validated('publisher_id'),
            'is_public' => $request->boolean('is_public', true),
        ]);

        $url = $post->publisherUrl() ?? route('network.index');

        return redirect($url)->with('status', 'publicacao-criada');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $url = $post->publisherUrl() ?? route('network.index');
        $post->delete();

        return redirect($url)->with('status', 'publicacao-removida');
    }
}
