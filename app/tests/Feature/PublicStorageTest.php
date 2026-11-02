<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_public_disk_file_is_served_without_symlink(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('foto.png', 80, 80)->store('artists/photos', 'public');

        $this->get('/storage/'.$path)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_missing_and_unsafe_storage_paths_return_not_found(): void
    {
        Storage::fake('public');

        $this->get('/storage/artists/photos/nao-existe.jpg')->assertNotFound();
        $this->get('/storage/../.env')->assertNotFound();
    }
}
