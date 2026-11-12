<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Rock', 'Pop', 'MPB', 'Samba', 'Pagode', 'Sertanejo', 'Funk',
            'Eletrônica', 'Jazz', 'Blues', 'Reggae', 'Hip Hop', 'Gospel',
            'Forró', 'Axé', 'Indie', 'Metal', 'Clássica',
        ];

        foreach ($names as $name) {
            Genre::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
