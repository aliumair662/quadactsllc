<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Styles -->
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="{{ asset('assets/scripts/main.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset('js/custom.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.2/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        table {
            transition: all .4s ease;
        }

        .select2,
        .select2-container--focus {
            width: 90% !important;
            margin-right: 3rem;
        }

        .select2-selection {
            height: 37px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            border: 1px solid #ced4da !important;
        }

        .select2-selection__arrow {
            top: 6px !important;
        }


        @media only screen and (max-width:750px) {

            .select2,
            .select2-container--focus {
                width: 100% !important;
            }
        }
    </style>
</head>


<div id="toast-container" class="toast-top-right" style="display: none;">
    <div class="toast" aria-live="polite" style="">
        <div class="toast-title"></div>
        <div class="toast-message"></div>
    </div>
</div>
<div id="overlay">
    <div class="w-100 d-flex justify-content-center align-items-center">
        <div class="spinner"></div>
    </div>
</div>

<body class="font-sans antialiased">
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
        @include('layouts.navigation')
        @include('layouts.setting')
        <div class="app-main">
            @include('layouts.sidebar')

            {{ $slot }}
        </div>

        <!-- Page Content -->



        <!--<div class="min-h-screen bg-gray-100">



            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                  {{--  {{ $header }} --}}
                </div>
            </header>


        </div>-->
    </div>




    @include('modals.addCustomer')
</body>

</html>
