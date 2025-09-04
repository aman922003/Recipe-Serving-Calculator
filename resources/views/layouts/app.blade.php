{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U.S. Kitchen Unit Converter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-yellow-50 min-h-screen">
    @yield('content')
</body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U.S. Kitchen Tools</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-yellow-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-orange-500 text-white shadow-md">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="/" class="font-bold text-lg">Unit Converter</a>
            <a href="/recipe-serving" class="font-bold text-lg">Serving Calculator</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="mt-6">
        @yield('content')
    </main>
</body>
</html>
