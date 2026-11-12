<?php

namespace Tests\Feature;

use App\Mail\AppNotificationMail;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\InAppNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_sent_when_notify_email_enabled(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'preferences' => ['notify_email' => true],
        ]);

        app(InAppNotifier::class)->notify(
            $user,
            'test.mail',
            'test:mail:1',
            'Assunto teste',
            'Corpo do alerta',
            ['url' => 'http://localhost:8080/dashboard'],
        );

        Mail::assertSent(AppNotificationMail::class, function (AppNotificationMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->notification->title === 'Assunto teste';
        });
    }

    public function test_email_skipped_when_notify_email_disabled(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'preferences' => ['notify_email' => false],
        ]);

        app(InAppNotifier::class)->notify(
            $user,
            'test.mail',
            'test:mail:off',
            'Sem e-mail',
            'Só in-app',
        );

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $user->id,
            'type' => 'test.mail',
        ]);

        Mail::assertNothingSent();
    }

    public function test_idempotent_notify_does_not_resend_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'preferences' => ['notify_email' => true],
        ]);

        $notifier = app(InAppNotifier::class);

        $notifier->notify($user, 'test.mail', 'test:mail:idem', 'Uma', 'Corpo');
        $notifier->notify($user, 'test.mail', 'test:mail:idem', 'Uma', 'Corpo');

        $this->assertEquals(1, AppNotification::query()->where('user_id', $user->id)->count());
        Mail::assertSent(AppNotificationMail::class, 1);
    }
}
