<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>main</title>
</head>
<body>
    <ul>
    @foreach ($data as $item)
        <li>
            <img src="{{ $item['baseUrl'] }}" />
            <br>
            {{ $item['fileName'] }}
        </li>    
    @endforeach
    </ul>
</body>
</html>