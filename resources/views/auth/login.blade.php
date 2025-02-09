<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/x-icon" href="{{ asset('images/packingline.ico') }}">
    <title>NDS PACKING LINE</title>

    @include('layouts.link')

    <script defer src="{{ asset('alpinejs/dist/cdn.min.js') }}"></script>
</head>

<body>
    <div class="login-card card">
        <div class="row align-items-center g-0">
            <div class="col-md-6">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <img src="{{ asset('images/Frame 1.png') }}" class="img-fluid mt-auto mb-auto" alt="...">
                    <p class="fs-5 mt-3 w-75 mb-0 bg-danger text-light text-center rounded-3">FINISH LINE</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-body my-5">
                    <h2 class="text-center text-danger fw-bold mb-3">LOGIN</h2>
                    <form method="POST" action="{{ url('login/authenticate') }}" onsubmit="login(this, event)"
                        class="login-form mx-3">
                        @csrf
                        <div class="mb-3 position-relative">
                            <label for="username">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" id="username">
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3 position-relative">
                            <label for="username">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" id="password">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <input type="checkbox" class="form-check-input @error('remember') is-invalid @enderror" value="true" name="remember" id="remember">
                            <label class="form-check-label">
                                Remember Me
                            </label>
                            @error('remember')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mt-3 mb-3">
                            <button type="submit" class="btn btn-danger w-100 mt-3 mb-3">LOGIN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.script')

    <script>
        $('#remember').prop('checked', true);
    </script>
</body>

</html>
