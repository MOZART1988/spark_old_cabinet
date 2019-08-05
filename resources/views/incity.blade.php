@extends('layouts.app')

@section('content')
    @markdown
    # Внутригородская доставка
    ---
    @endmarkdown

    @markdown
    ## Описание
    Запрос: `post`

    Путь: `https://api.spark-logistics.kz/v1/incity`

    ### Параметры:
    * `city_from = integer` id города <span class="label alert">Обязательный</span>
    * `cargo = array` Массив параметров товара <span class="label alert">Обязательный</span>
        * `weight = number` вес брутто, ед. изм. кг <span class="label alert">Обязательный</span>
        * `width = number` ширина упаковки, ед. изм. см <span class="label alert">Обязательный</span>
        * `height = number` высота упаковки, ед. изм. см <span class="label alert">Обязательный</span>
        * `length = number` ширина упаковки, ед. изм. см <span class="label alert">Обязательный</span>

    ### Ответ:

    ```{"delivery_cost":22983.3}```
    @endmarkdown

    @markdown
    ## Пример:

    ```javascript
    {
    "city_from": 1,
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