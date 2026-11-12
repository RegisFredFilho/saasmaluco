<?php

namespace App\Services\Social;

use App\Models\ArtistProfile;
use App\Models\Post;
use App\Models\User;
use App\Models\VenueProfile;
use App\Policies\PostPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePost
{
    public function __construct(private PostPolicy $policy) {}

    /**
     * @param  array{body: string, publisher_type: string, publisher_id?: int|null, is_public?: bool}  $data
     */
    public function handle(User $author, array $data): Post
    {
        $publisher = $this->resolvePublisher($author, $data['publisher_type'], $data['publisher_id'] ?? null);

        if (! $this->policy->canPublishAs($author, $publisher)) {
            throw new AuthorizationException('Você não pode publicar em nome deste perfil.');
        }

        return Post::query()->create([
            'author_user_id' => $author->id,
            'publisher_type' => $publisher::class,
            'publisher_id' => $publisher->getKey(),
            'body' => $data['body'],
            'is_public' => $data['is_public'] ?? true,
        ]);
    }

    private function resolvePublisher(User $author, string $type, mixed $id): Model
    {
        return match ($type) {
            'user' => $author,
            'artist' => ArtistProfile::query()->findOrFail((int) $id),
            'venue' => VenueProfile::query()->findOrFail((int) $id),
            default => throw ValidationException::withMessages([
                'publisher_type' => ['Tipo de publicador inválido.'],
            ]),
        };
    }
}
