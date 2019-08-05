@extends('layouts.app')

@section('content')
    @markdown
    # Экспресс доствка по городам Казахстана и населенным пунктам
    ---
    @endmarkdown

    @markdown
    ## Описание
    Запрос: `post`

    Путь: `https://api.spark-logistics.kz/v1/express`

    ### Параметры:
    * `city_from = integer` id города откуда <span class="label alert">Обязательный</span>
    * `city_to = integer` id города куда <span class="label alert">Обязательный</span>
    * `insurance = boolean` страховка <span class="label alert">Обязательный</span>
    * `total_cost = number` общая стомостоть товаров, нужна если страховка. <span class="label success">Не обязательный</span>
    * `cargo = array` Массив параметров товара <span class="label alert">Обязательный</span>
        * `weight = number` вес брутто, ед. изм. кг <span class="label alert">Обязательный</span>
        * `width = number` ширина упаковки, ед. изм. см <span class="label alert">Обязательный</span>
        * `height = number` высота упаковки, ед. изм. см <span class="label alert">Обязательный</span>
        * `length = number` ширина упаковки, ед. изм. см <span class="label alert">Обязательный</span>

    ### Ответ:

    Если есть страховка

    ```{"delivery_cost":22983.3,"insurance_cost":15000}```

    Если нет страховки

    ```{"delivery_cost":22983.3}```
    @endmarkdown

    @markdown
    ## Пример:

    ```javascript
    {
        "city_from": 1,
        "city_to": 2,
        "insurance": false,
        "total_cost": 5000000,
        "cargo": [
            {
                "weight": 10.3,
                "width": 100,
                "height": 100,
                "length": 100
            },
            {
                "weight": 4,
                "width": 80,
                "height": 40,
                "length": 60
            },
            {
                "weight": 1,
                "width": 40,
                "height": 20,
                "length": 60
            }
        ]
    }
    ```
    @endmarkdown
@endsection