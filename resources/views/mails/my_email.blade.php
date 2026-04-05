<x-mail::message>
    # Hello!
<!--# Introduction

The body of your message.
-->
<!--
<x-mail::button :url="''">
Button Text
</x-mail::button>-->
{!! strip_tags($email->body, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><div><span><hr>') !!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
