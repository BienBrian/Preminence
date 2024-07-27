<x-mail::message>
    # Hello!
<!--# Introduction

The body of your message.
-->
<!--
<x-mail::button :url="''">
Button Text
</x-mail::button>-->
{!! $email->body !!}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
