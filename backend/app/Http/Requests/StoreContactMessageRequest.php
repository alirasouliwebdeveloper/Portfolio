<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Models\Upload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $options = Setting::current()->contact_options ?? [];
        $in = fn (string $key) => filled($options[$key] ?? null) ? [Rule::in($options[$key])] : [];

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'need' => ['required', 'string', 'max:100', ...$in('needs')],
            'budget' => ['nullable', 'string', 'max:100', ...$in('budgets')],
            'timeline' => ['nullable', 'string', 'max:100', ...$in('timelines')],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'service_slug' => ['nullable', 'string', 'max:100'],
            'upload_session' => ['nullable', 'uuid', 'required_with:upload_uuids'],
            'upload_uuids' => ['nullable', 'array', 'max:'.config('portfolio.upload.max_files')],
            'upload_uuids.*' => [
                'uuid',
                Rule::exists('uploads', 'uuid')
                    ->where('upload_session', $this->input('upload_session'))
                    ->whereNull('contact_message_id')
                    ->where(fn ($query) => $query->where('expires_at', '>', now())),
            ],
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
            'visitor_ip' => ['nullable', 'ip'],
            'user_agent' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'message.min' => 'Please write at least 10 characters.',
            'message.max' => 'Please keep it under 5000 characters.',
            'need.in' => 'Please choose one of the options.',
            'upload_uuids.*.exists' => 'One of the attached files is no longer available. Please add it again.',
        ];
    }

    /** Uploads picked for this message (already validated to belong to the session and be unattached). */
    public function uploads()
    {
        return Upload::query()->whereIn('uuid', (array) $this->input('upload_uuids', []))->get();
    }
}
