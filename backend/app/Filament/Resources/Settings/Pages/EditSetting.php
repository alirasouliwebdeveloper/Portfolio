<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Support\MailSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    public function mount(int|string|null $record = null): void
    {
        parent::mount(Setting::ensure()->getKey());
    }

    public function getTitle(): string
    {
        return 'Site settings';
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Sends a short email right now, with the saved settings, to every contact-form recipient. Save your changes first.')
                ->action(function (): void {
                    $recipients = Setting::current()->contactRecipients();

                    if ($recipients === []) {
                        Notification::make()->danger()->title('No recipient: set the contact or notification email first.')->send();

                        return;
                    }

                    // Use what was just saved, not a mailer built earlier in this request.
                    MailSettings::apply();
                    app('mail.manager')->forgetMailers();

                    try {
                        foreach ($recipients as $recipient) {
                            Mail::raw('This is a test email from the alirasouli.ir admin. Contact-form messages will arrive the same way.', function ($message) use ($recipient) {
                                $message->to($recipient)->subject('Test email — alirasouli.ir');
                            });
                        }
                    } catch (Throwable $e) {
                        Notification::make()->danger()->title('Sending failed')->body($e->getMessage())->persistent()->send();

                        return;
                    }

                    Notification::make()->success()->title('Test email sent to '.implode(', ', $recipients))->send();
                }),
        ];
    }
}
