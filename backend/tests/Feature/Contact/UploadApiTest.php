<?php

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('uploads');
    $this->session = (string) Str::uuid();
});

function upload(UploadedFile $file, ?string $session = null)
{
    return test()->post('/api/v1/uploads', ['file' => $file, 'upload_session' => $session ?? test()->session], ['Accept' => 'application/json']);
}

it('stores an allowed file on the private disk with a random name', function () {
    $response = upload(UploadedFile::fake()->create('brief.pdf', 200, 'application/pdf'))->assertCreated();

    $upload = Upload::firstOrFail();
    expect($response->json())->toMatchArray(['uuid' => $upload->uuid, 'name' => 'brief.pdf'])
        ->and($upload->path)->not->toContain('brief')
        ->and($upload->contact_message_id)->toBeNull()
        ->and($upload->expires_at->isFuture())->toBeTrue();
    Storage::disk('uploads')->assertExists($upload->path);
});

it('accepts every allowed type', function (string $name, string $mime) {
    upload(UploadedFile::fake()->create($name, 50, $mime))->assertCreated();
})->with([
    'pdf' => ['a.pdf', 'application/pdf'],
    'doc' => ['a.doc', 'application/msword'],
    'docx' => ['a.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'zip' => ['a.zip', 'application/zip'],
    'png' => ['a.png', 'image/png'],
    'jpg' => ['a.jpg', 'image/jpeg'],
]);

it('rejects unsupported file types with the designed message', function (string $name, string $mime) {
    upload(UploadedFile::fake()->create($name, 50, $mime))
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', "This file type isn't supported.");
    expect(Upload::count())->toBe(0);
})->with([
    'video' => ['store-demo.mov', 'video/quicktime'],
    'executable' => ['run.exe', 'application/x-msdownload'],
    'php' => ['shell.php', 'application/x-php'],
]);

it('checks the real content type, not just the extension', function () {
    // A real file on disk, so the content type is detected from the bytes (not from the name).
    $path = tempnam(sys_get_temp_dir(), 'upl');
    file_put_contents($path, '<?php echo "not an image";');

    upload(new UploadedFile($path, 'photo.png', 'image/png', null, true))->assertUnprocessable()->assertJsonPath('errors.file.0', "This file type isn't supported.");
});

it('rejects files over 10 MB with the size in the message', function () {
    upload(UploadedFile::fake()->create('brand-assets.zip', 24 * 1024, 'application/zip'))
        ->assertUnprocessable()
        ->assertJsonPath('errors.file.0', '24 MB — files must be 10 MB or smaller.');
});

it('allows at most 5 files per upload session', function () {
    foreach (range(1, 5) as $i) {
        upload(UploadedFile::fake()->create("f{$i}.pdf", 10, 'application/pdf'))->assertCreated();
    }

    upload(UploadedFile::fake()->create('f6.pdf', 10, 'application/pdf'))->assertUnprocessable()->assertJsonValidationErrors('file');
    upload(UploadedFile::fake()->create('other.pdf', 10, 'application/pdf'), (string) Str::uuid())->assertCreated();
});

it('requires an upload session id', function () {
    $this->post('/api/v1/uploads', ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('upload_session');
});

it('deletes an unattached upload of the same session only', function () {
    $id = upload(UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'))->json('uuid');
    $path = Upload::firstOrFail()->path;

    $this->deleteJson("/api/v1/uploads/{$id}", ['upload_session' => (string) Str::uuid()])->assertNotFound();
    Storage::disk('uploads')->assertExists($path);

    $this->deleteJson("/api/v1/uploads/{$id}", ['upload_session' => $this->session])->assertNoContent();
    Storage::disk('uploads')->assertMissing($path);
    expect(Upload::count())->toBe(0);
});

it('rate limits uploads to 20 per hour per IP', function () {
    foreach (range(1, 20) as $i) {
        $session = (string) Str::uuid();
        upload(UploadedFile::fake()->create("f{$i}.pdf", 1, 'application/pdf'), $session)->assertCreated();
    }

    upload(UploadedFile::fake()->create('f21.pdf', 1, 'application/pdf'), (string) Str::uuid())->assertTooManyRequests();
});

it('answers CORS only for the frontend origin', function () {
    config(['cors.allowed_origins' => ['http://localhost:3000']]);

    $this->call('OPTIONS', '/api/v1/uploads', server: ['HTTP_ORIGIN' => 'http://localhost:3000', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'])
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');

    // A foreign origin is never echoed back, so the browser blocks the request.
    $foreign = $this->call('OPTIONS', '/api/v1/uploads', server: ['HTTP_ORIGIN' => 'https://evil.example', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST']);
    expect($foreign->headers->get('Access-Control-Allow-Origin'))->not->toBe('https://evil.example');
});
