<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SparkLogistics API</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto|Roboto+Mono:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>

<header class="header">
    <div class="row">
        <div class="columns">
            <h1 class="logo"><strong><a href="/">Spark Logistics</a></strong></h1>
            <h5>Публичный API для калькуляции стоимости доставки</h5>
        </div>
    </div>
</header>

<section id="app" class="main">
    <div class="row">
        <div class="columns">
            <div class="callout primary">
                <p class="title">
                    Чтобы получить подключиться к более выгодным тарифам
                    или получить ключ доступа к закрытой части API обратитесь в
                    <a href="https://spark-logistics.kz/contacts">наш офис</a>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="columns small-3">
            @include('partials.aside')
        </div>

        <div class="columns small-9">
            @yield('content')
        </div>
    </div>
</section>

<script src="/js/manifest.js"></script>
<script src="/js/vendor.js"></script>
<script src="/js/app.js"></script>

</body>
</html>
