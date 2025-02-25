<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/x-icon" href=" {{ asset('images/finishline.ico') }}">
    <title>{{ isset($method) ? strtoupper($method).' - ' : '' }}NDS FINISH LINE</title>

    @include('layouts.link')

    @yield('custom-link')

    <script defer src="{{ asset('alpinejs/dist/cdn.min.js') }}"></script>
</head>
<body>
    @php
        $thisOrderDate = "";
        if (isset($orderDate) && $orderDate) {
            $thisOrderDate = $orderDate;
        }
    @endphp
    @include('layouts.navbar', ["orderDate" => $thisOrderDate])

    @include('profile')
    @include('history')
    @include('undo')

    <audio controls id="alert-sound" class="d-none">
        <source src="{{ asset('audio/r2d2.mp3') }}" type="audio/mpeg">
    </audio>

    <main role="main" class="main flex-shrink-0 container-fluid mt-3">
        @yield('content')
    </main>

    @yield('footer')

    @include('layouts.script')

    @yield('custom-script')
    @stack('scripts')
</body>
</html>
