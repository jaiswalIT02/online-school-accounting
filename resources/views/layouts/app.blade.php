<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'KGBVDKJ') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @php
    $sessionYear = loadSessionYear();
    $loadAccountType = loadAccountType();
    @endphp
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'KGBVDKJ') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ledgers.index') }}">Ledgers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tax_ledgers.index') }}">Tax&nbsp;Ledgers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reports.index') }}">Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cashbooks.index') }}">Cashbooks</a>
                        </li>

                        @php
                        $accountType = request('account_type', 3);
                        @endphp

                        @if($accountType == 3)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('receipt_payments.index', ['account_type' => 4]) }}">
                                Receipt & Payment
                            </a>
                        </li>
                        @elseif($accountType == 2)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('receipt.create', ['account_type' => 4, 'id' => 4]) }}">
                                Receipt
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payment.create', ['account_type' => 4, 'id' => 4]) }}">
                                Payment
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('receipt_payments.index', ['account_type' => 3]) }}">
                                Receipt&nbsp;&&nbsp;Payment
                            </a>
                        </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a id="navbarComponentsDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Components &amp; Vendors</a>
                            <div class="dropdown-menu" aria-labelledby="navbarComponentsDropdown">
                                <a class="dropdown-item" href="{{ route('articles.index') }}">Components</a>
                                <a class="dropdown-item" href="{{ route('beneficiaries.index') }}">Vendors</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarStockDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Stock</a>
                            <div class="dropdown-menu" aria-labelledby="navbarStockDropdown">
                                <a class="dropdown-item" href="{{ route('items.index') }}">Items</a>
                                <a class="dropdown-item" href="{{ route('stocks.index') }}">Stocks</a>
                                <a class="dropdown-item" href="{{ route('stock_ledgers.index') }}">Stock Ledgers</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('funds.index') }}">Funds</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('students.index') }}">Students</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.index') }}">Staff</a>
                        </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">

                        @auth
                        <form method="GET" action="{{ url()->current() }}" class="d-flex">

                            <select name="account_type"
                                id="account_type"
                                class="form-control me-2"
                                onchange="this.form.submit()" style="width: 120px;">

                                @foreach ($loadAccountType as $account)
                                <option value="{{ $account->id }}"
                                    {{ request('account_type') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                                @endforeach
                            </select>

                            <select name="session_id"
                                id="session_id"
                                class="form-control"
                                onchange="this.form.submit()" style="width: 120px;">

                                @foreach ($sessionYear as $year)
                                <option value="{{ $year->id }}"
                                    {{ request('session_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->session_name }}
                                </option>
                                @endforeach
                            </select>

                        </form>
                        @endauth

                        @guest
                        @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @endif
                        @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user me-2"></i> {{ __('Profile') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>