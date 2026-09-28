<x-mail::message>
# New message from {{ $message->name }}

**Email:** {{ $message->email }}
@if ($message->company)
**Company:** {{ $message->company }}
@endif
@if ($message->phone)
**Phone:** {{ $message->phone }}
@endif
**Needs:** {{ $message->need }}
@if ($message->budget)
**Budget:** {{ $message->budget }}
@endif
@if ($message->timeline)
**Timeline:** {{ $message->timeline }}
@endif
@if ($message->service_slug)
**From service page:** {{ $message->service_slug }}
@endif

> {{ $message->message }}

@if ($fileCount > 0)
{{ $fileCount }} file(s) attached — open them from the admin panel.
@endif

<x-mail::button :url="$adminUrl">
Open in admin
</x-mail::button>
</x-mail::message>
