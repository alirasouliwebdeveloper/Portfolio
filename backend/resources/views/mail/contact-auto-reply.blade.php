<x-mail::message>
# Thanks, {{ $message->name }}

I received your message and I'll reply {{ $responseTime }}.

Here is a copy of what you sent:

> {{ $message->message }}

</x-mail::message>
