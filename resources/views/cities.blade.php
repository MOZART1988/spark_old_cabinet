@extends('layouts.app')

@section('content')
    @markdown
    # Список доступных городов
    ---

    Запрос: `get`

    Путь: `https://api.spark-logistics.kz/v1/cities`

    ```javascript
    {
        "1": "Алматы",
        "3": "....",
        "22": "Степногорск"
    }
    ```
    ---
    @endmarkdown
@endsection