<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $page->name ?? 'CMS Page' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            padding-top: 60px;
        }

        .cms-container {
            max-width: 960px;
            margin: auto;
        }

        .cms-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>

    <main class="cms-container mt-4">
        <div class="cms-content p-4">
            {!! $page->content ?? '<p>No content found.</p>' !!}
        </div>
    </main>

</body>

</html>
