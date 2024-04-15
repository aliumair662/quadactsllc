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
            width: 100% !important;
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

<style>
    .product-img-tabs {
        display: flex;
    }

    .product-img-tabs .nav-tabs {
        border-bottom: 0;
        width: 70px;
        flex-direction: column;
    }

    .product-img-tabs .nav-tabs .nav-item {
        margin-bottom: 30px;
    }

    a,
    button {
        color: inherit;
        outline: none;
        border: none;
        background: transparent;
    }

    .product-img-tabs .nav-links img {
        opacity: 50%;
    }

    img {
        max-width: 100%;
    }

    .product-img-tabs .tab-content {
        margin-left: 30px;
    }

    .product-top {
        display: flex;
    }

    .product-details-title {
        font-size: 26px;
        font-weight: 700;
        color: black;
    }

    .mb-30 {
        margin-bottom: 30px;
    }

    .product-price .old-price {
        color: #cfcfcf;
        font-size: 20px;
        font-weight: 700;
        text-decoration: line-through;
    }

    .product-price .new-price {
        color: green;
        font-size: 36px;
        font-weight: 700;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .product-paragraph p {
        font-size: 16px;
        color: #777;
        font-weight: 400;
    }

    .product-quantity-wapper {
        overflow: hidden;
    }

    .product-quantity-wapper .heart-icon {
        border: 1px solid #e2e2e2;
        padding: 16px 20px;
    }

    .product-details-meta .categories a {
        font-size: 16px;
        font-weight: 400;
        color: #777;
    }

    .product-details-meta .tag span {
        font-size: 16px;
        font-weight: 700;
        color: var(--tp-common-black);
    }

    .product-details-meta .tag a {
        font-size: 16px;
        font-weight: 400;
        color: #777;
    }

    .product-details-share span {
        font-weight: 700;
        font-size: 16px;
        color: var(--tp-common-black);
        margin-right: 10px;
    }

    .product-details-share a {
        color: #999;
        font-size: 15px;
        margin-right: 15px;
    }

    .breadcrumb__title {
        font-size: 80px;
        font-weight: 700;
        color: black;
    }
</style>

<body>
    <header style=" background-color: white; text-align: center; padding-bottom:1rem">
        <img src="/item_pic/itsol.jpg" width="20%" alt="theme-pure">
    </header>
    <section class="breadcrumb__area include-bg"
        style="padding-top:150px !important; padding-bottom:150px !important; background-image: url(&quot;/item_pic/ab-slider.jpg&quot;);">
        <div class="container">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="breadcrumb__content p-relative z-index-1">
                        <h3 class="breadcrumb__title">Our Catalog</h3>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="app-main">
        <div class="app-main__outer" style="padding: 0%">
            <div class="app-main__inner" style="background: darkgray">
                @for ($i = 0; $i < count($catalogs); $i++)
                    @if ($i % 2 === 0)
                        <div class="mt-5 mb-5" style="background: white !important; padding: 3%;">
                            <div class="shop-details-area pt-120 pb-120">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xxl-6 col-xl-6 col-lg-5 col-12">
                                            <div class="product-img-tabs">
                                                <div class="tab-content" id="myTabContent">
                                                    <div class="tab-pane fade show active" id="home"
                                                        role="tabpanel" aria-labelledby="home-tab"><img
                                                            src="{{ $catalogs[$i]->pic }}" width="490px" height="560px"
                                                            alt="theme-pure">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-lg-7 col-12">
                                            <div class="product-details-content">
                                                {{-- <div class="product-top mb-10">
                                            <div class="product-tag"><a href="#">Security, CCTV</a></div>
                                            <div class="product-rating mr-5"><a href="#"><i
                                                        class="fas fa-star"></i></a><a href="#"><i
                                                        class="fas fa-star"></i></a><a href="#"><i
                                                        class="fas fa-star-half-alt"></i></a>
                                            </div>
                                            <div class="product-reviews"><a href="#">10 reviews</a></div>
                                        </div> --}}
                                                <h3 class="product-details-title mb-20">{{ $catalogs[$i]->name }}
                                                </h3>
                                                <div class="product-price mb-30">
                                                    {{-- <span class="old-price pr-10">$90.35
                                            </span> --}}
                                                    <span class="new-price">AED {{ $catalogs[$i]->sele_price }}</span>
                                                </div>
                                                <div class="product-paragraph">
                                                    <div class="mb-25">
                                                        <?php echo $catalogs[$i]->note_html; ?>
                                                    </div>
                                                </div>
                                                <div class="product-details-meta">
                                                    <div class="sku mb-5"><span><b>Product Code:</b></span><span
                                                            style="color: red;">
                                                            {{ $catalogs[$i]->code }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 mb-5" style="background: lightgreen !important; padding:3%">
                            <div class="shop-details-area pt-120 pb-120">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-xxl-6 col-xl-6 col-lg-7 col-12">
                                            <div class="product-details-content">
                                                {{-- <div class="product-top mb-10">
                                                <div class="product-tag"><a href="#">Security, CCTV</a></div>
                                                <div class="product-rating mr-5"><a href="#"><i
                                                            class="fas fa-star"></i></a><a href="#"><i
                                                            class="fas fa-star"></i></a><a href="#"><i
                                                            class="fas fa-star-half-alt"></i></a>
                                                </div>
                                                <div class="product-reviews"><a href="#">10 reviews</a></div>
                                            </div> --}}
                                                <h3 class="product-details-title mb-20" style="color: black">
                                                    {{ $catalogs[$i]->name }}</h3>
                                                <div class="product-price mb-30">
                                                    {{-- <span class="old-price pr-10">$90.35
                                                </span> --}}
                                                    <span class="new-price" style="color:white">AED
                                                        {{ $catalogs[$i]->sele_price }}</span>
                                                </div>
                                                <div class="product-paragraph">
                                                    <div class="note-container" class="mb-25">
                                                        <?php echo $catalogs[$i]->note_html; ?>
                                                    </div>
                                                    {{-- <p class="mb-25">Priyoshop has brought to you the
                                                    Hijab
                                                    3 Pieces Combo Pack
                                                    PS23.
                                                    It is a
                                                    completely modern design and you feel comfortable to put on this
                                                    hijab.
                                                    <br>Buy
                                                    it at the
                                                    best price.
                                                </p> --}}
                                                </div>
                                                <div class="product-details-meta">
                                                    <div class="sku mb-5"><b style="color: black">Product
                                                            Code:</b><span style="color: red;">
                                                            {{ $catalogs[$i]->code }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-xl-6 col-lg-5 col-12">
                                            <div class="product-img-tabs">
                                                <div class="tab-content" id="myTabContent">
                                                    <div class="tab-pane fade show active" id="home"
                                                        role="tabpanel" aria-labelledby="home-tab"><img
                                                            src="{{ $catalogs[$i]->pic }}" width="490px"
                                                            height="560px" alt="theme-pure">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    <footer style=" background-color: lightgreen; text-align: center;padding: 1rem 0; height: 15%;">
        <h3 style="margin-top: 1.5%;"><span style="color: black"> &copy; 2024 </span><span
                style="color: white"><b>Quad</b></span><span style="color: black"><b>acts</b></span>
        </h3>
    </footer>
</body>
<script>
    var containers = document.querySelectorAll('.note-container');


    containers.forEach(function(container) {

        var elements = container.querySelectorAll('*');


        elements.forEach(function(element) {

            element.style.backgroundColor = '';
            element.classList.remove('bg-color-class');
        });

        elements.forEach(function(element) {
            element.style.color = 'black';
        });
    });
</script>
