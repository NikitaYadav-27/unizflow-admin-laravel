<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Server Error</title>
    <style>
        body {
            height: 100%;
            background: #fafafa;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #777;
            font-weight: 300;
        }
        .wrap {
            max-width: 600px;
            margin: 5rem auto;
            padding: 2rem;
            background: #fff;
            text-align: center;
            border: 1px solid #efefef;
            border-radius: 0.5rem;
        }
        h1 {
            font-size: 3rem;
            color: #222;
        }
        p {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>500</h1>
        <p>{{ $exception->getMessage() ?: 'Server Error' }}</p>
    </div>
</body>
</html>

