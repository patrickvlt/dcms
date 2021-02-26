<?php

return
'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link href="/css/dcms/dcms.css" rel="stylesheet" type="text/css" />
    <title>{{ env("APP_NAME") }}</title>
</head>

<body>
    {!! $form !!}
</body>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script>
    var dcmsLanguage = "{{ App::getLocale() }}";
    var dcmsMaxSizeServer = {!! MaxSizeServer("kb") !!};
</script>
<script src="/js/dcms/dcms.js"></script>
</html>
';
