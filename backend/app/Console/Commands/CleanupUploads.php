<?php

namespace App\Console\Commands;

use App\Models\Upload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupUploads extends Command
{
    protected $signature = 'uploads:cleanup';

    protected $description = 'Delete uploads that were never attached to a message and have expired';

    public function handle(): int
    {
        $count = 0;

        Upload::query()->whereNull('contact_message_id')->where('expires_at', '<', now())->each(function (Upload $upload) use (&$count) {
            Storage::disk('uploads')->delete($upload->path);
            $upload->delete();
            $count++;
        });

        $this->info("Deleted {$count} expired uploads.");

        return self::SUCCESS;
    }
}
