<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Called by the browser directly (needed for real upload progress); protected by rate limit, type and size checks. */
class UploadController extends Controller
{
    /** Allowed MIME types per extension: both the extension and the detected content type must match. */
    private const TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];

    public function store(Request $request): JsonResponse
    {
        $request->validate(['upload_session' => ['required', 'uuid']]);

        $file = $request->file('file');
        $maxBytes = (int) config('portfolio.upload.max_mb') * 1024 * 1024;

        if (! $file instanceof UploadedFile) {
            $this->fail($this->tooLarge(null));
        }
        if (! $file->isValid()) {
            // PHP refused the file before Laravel saw it: it is over the server limit.
            $this->fail($this->tooLarge(null));
        }
        if ($file->getSize() > $maxBytes) {
            $this->fail($this->tooLarge($file->getSize()));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();
        if (! array_key_exists($extension, self::TYPES) || ! in_array($mime, self::TYPES[$extension], true)) {
            $this->fail("This file type isn't supported.");
        }

        $session = $request->string('upload_session')->toString();
        $active = Upload::query()->where('upload_session', $session)->whereNull('contact_message_id')->where('expires_at', '>', now())->count();
        if ($active >= (int) config('portfolio.upload.max_files')) {
            $this->fail('You can attach up to '.config('portfolio.upload.max_files').' files.');
        }

        $path = Storage::disk('uploads')->putFileAs('', $file, Str::random(40).'.'.$extension);

        $upload = Upload::create([
            'uuid' => (string) Str::uuid(),
            'upload_session' => $session,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 200, ''),
            'mime' => $mime,
            'size' => $file->getSize(),
            'path' => $path,
            'expires_at' => now()->addHours((int) config('portfolio.upload_ttl_hours')),
        ]);

        return response()->json(['uuid' => $upload->uuid, 'name' => $upload->original_name, 'size' => $upload->size], 201);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $request->validate(['upload_session' => ['required', 'uuid']]);

        $upload = Upload::query()
            ->where('uuid', $uuid)
            ->where('upload_session', $request->string('upload_session')->toString())
            ->whereNull('contact_message_id')
            ->firstOrFail();

        Storage::disk('uploads')->delete($upload->path);
        $upload->delete();

        return response()->json(null, 204);
    }

    private function tooLarge(?int $bytes): string
    {
        $limit = (int) config('portfolio.upload.max_mb');

        return $bytes === null
            ? "Files must be {$limit} MB or smaller."
            : round($bytes / 1024 / 1024).' MB — files must be '.$limit.' MB or smaller.';
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
