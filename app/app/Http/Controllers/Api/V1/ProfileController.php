<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(ProfileUpdateRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'locale', 'notify_email']);

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $preferences = $user->preferences ?? [];
        if ($request->filled('locale')) {
            $preferences['locale'] = $request->string('locale')->toString();
        }
        if ($request->exists('notify_email')) {
            $preferences['notify_email'] = $request->boolean('notify_email');
        }
        $user->preferences = $preferences;

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return UserResource::make($user->fresh());
    }
}
