<x-app-layout>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-car icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Dashboard
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content bg-midnight-bloom">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Total Sales</div>
                                <div class="widget-subheading">Till Now</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white">
                                    <span>{{ Config::get('constants.DEFAULT_CURRENCY') . $data['netSales'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content bg-arielle-smile">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Customers</div>
                                <div class="widget-subheading">Active Till Now</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white"><span>{{ $data['customers'] }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content bg-grow-early">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Vendors</div>
                                <div class="widget-subheading">Active Till Now</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-white"><span>{{ $data['vendors'] }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-xl-none d-lg-block col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content bg-premium-dark">
                        <div class="widget-content-wrapper text-white">
                            <div class="widget-content-left">
                                <div class="widget-heading">Products Sold</div>
                                <div class="widget-subheading">Revenue streams</div>
                            </div>
                            <div class="widget-content-right">
                                <div class="widget-numbers text-warning"><span>$14M</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-6">
                    <div class="mb-3 card">
                        <div class="card-header-tab card-header-tab-animation card-header">
                            <div class="card-header-title">
                                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                                Sales Report
                            </div>
                            <ul class="nav">
                                <li class="nav-item"><a href="javascript:void(0);" class="active nav-link">Last 7
                                        Months</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tabs-eg-77">
                                    <div class="card mb-3 widget-chart widget-chart2 text-left w-100">
                                        <div class="widget-chat-wrapper-outer">
                                            <div class="widget-chart-wrapper widget-chart-wrapper-lg opacity-10 m-0">
                                                <canvas id="canvas-custom"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-6">
                    <div class="mb-3 card">
                        <div class="card-header-tab card-header-tab-animation card-header">
                            <div class="card-header-title">
                                <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                                Top 10 Customers
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tabs-eg-77">
                                    <div class="card mb-3 widget-chart widget-chart2 text-left w-100">
                                        <div class="scroll-area-sm">
                                            <div class="scrollbar-container">
                                                <ul
                                                    class="rm-list-borders rm-list-borders-scroll list-group list-group-flush">
                                                    @if (!empty($data['top10SalesCustomers']))
                                                        @foreach ($data['top10SalesCustomers'] as $customer)
                                                            <li class="list-group-item">
                                                                <div class="widget-content p-0">
                                                                    <div class="widget-content-wrapper">
                                                                        <div class="widget-content-left mr-3">
                                                                            <img width="42" class="rounded-circle"
                                                                                src="assets/images/avatars/9.jpg"
                                                                                alt="">
                                                                        </div>
                                                                        <div class="widget-content-left">
                                                                            <div class="widget-heading">
                                                                                {{ $customer->name }}</div>
                                                                            <div class="widget-subheading">
                                                                                {{ $customer->phone }}</div>
                                                                        </div>
                                                                        <div class="widget-content-right">
                                                                            <div class="font-size-xlg text-muted">
                                                                                <small
                                                                                    class="opacity-5 pr-1">{{ Config::get('constants.DEFAULT_CURRENCY') }}</small>
                                                                                <span>{{ $customer->sale }}</span>
                                                                                <small class="text-danger pl-2">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @endif

                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total Cash</div>
                                    <div class="widget-subheading">Till Now</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-success">
                                        {{ Config::get('constants.DEFAULT_CURRENCY') }}{{ $data['totalCash'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="widget-heading"><a href="{{ route('accountReceivable') }}">Account
                                            Receivable</a></div>
                                    <div class="widget-subheading">Revenue streams</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-warning">
                                        {{ Config::get('constants.DEFAULT_CURRENCY') }}{{ $data['accountReceivable'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="widget-heading"><a href="{{ route('accountPayable') }}">Account
                                            Payable</a></div>
                                    <div class="widget-subheading">People Interested</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-danger">
                                        {{ Config::get('constants.DEFAULT_CURRENCY') }}{{ $data['accountPayable'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-xl-none d-lg-block col-md-6 col-xl-4">
                    <div class="card mb-3 widget-content">
                        <div class="widget-content-outer">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Income</div>
                                    <div class="widget-subheading">Expected totals</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers text-focus">$147</div>
                                </div>
                            </div>
                            <div class="widget-progress-wrapper">
                                <div class="progress-bar-sm progress-bar-animated-alt progress">
                                    <div class="progress-bar bg-info" role="progressbar" aria-valuenow="54"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 54%;"></div>
                                </div>
                                <div class="progress-sub-label">
                                    <div class="sub-label-left">Expenses</div>
                                    <div class="sub-label-right">100%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Incoming Deadlines of customer Reciept Checks
                            <div class="btn-actions-pane-right">
                                {{-- <div role="group" class="btn-group-sm btn-group">
                                    <button class="active btn btn-focus">Last Week</button>
                                    <button class="btn btn-focus">All Month</button>
                                </div> --}}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Customer Name</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Payment Type</th>
                                        <th class="text-center">Bank Name</th>
                                        <th class="text-center">Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($customerReceipts))
                                        @foreach ($customerReceipts as $receipt)
                                            <tr>
                                                <td class="text-center text-muted">{{ $receipt->voucher_number }}</td>
                                                {{-- <td>
                                                    <div class="widget-content p-0">
                                                        <div class="widget-content-wrapper">
                                                            <div class="widget-content-left mr-3">
                                                                <div class="widget-content-left">
                                                                    <img width="40" class="rounded-circle"
                                                                        src="{{ $user->avatar }}" alt="">
                                                                </div>
                                                            </div>
                                                            <div class="widget-content-left flex2">
                                                                <div class="widget-heading">{{ $user->name }}</div>
                                                                <div class="widget-subheading opacity-7">
                                                                    {{ $user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td> --}}

                                                <td class="text-center">{{ $receipt->name }}</td>
                                                <td class="text-center">{{ $receipt->amount }}</td>
                                                <td class="text-center">Cheque</td>
                                                <td class="text-center">{{ $receipt->bank_name }}</td>
                                                <td class="text-center">
                                                    <div class="badge badge-danger">
                                                        {{ \Carbon\Carbon::parse($receipt->received_date)->format('d-m-Y') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!--<div class="d-block text-center card-footer">
                            <button class="mr-2 btn-icon btn-icon-only btn btn-outline-danger"><i class="pe-7s-trash btn-icon-wrapper"> </i></button>
                            <button class="btn-wide btn btn-success">Save</button>
                        </div>-->
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Recent Users
                            <div class="btn-actions-pane-right">
                                {{-- <div role="group" class="btn-group-sm btn-group">
                                    <button class="active btn btn-focus">Last Week</button>
                                    <button class="btn btn-focus">All Month</button>
                                </div> --}}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Name</th>
                                        <th class="text-center">Phone</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($data['users']))
                                        @foreach ($data['users'] as $user)
                                            <tr>
                                                <td class="text-center text-muted">#{{ $user->id }}</td>
                                                <td>
                                                    <div class="widget-content p-0">
                                                        <div class="widget-content-wrapper">
                                                            <div class="widget-content-left mr-3">
                                                                <div class="widget-content-left">
                                                                    <img width="40" class="rounded-circle"
                                                                        src="{{ $user->avatar }}" alt="">
                                                                </div>
                                                            </div>
                                                            <div class="widget-content-left flex2">
                                                                <div class="widget-heading">{{ $user->name }}</div>
                                                                <div class="widget-subheading opacity-7">
                                                                    {{ $user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $user->phone }}</td>
                                                <td class="text-center">
                                                    <div class="badge badge-warning">
                                                        {{ $user->status == 1 ? 'Active' : 'InActive' }}</div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!--<div class="d-block text-center card-footer">
                            <button class="mr-2 btn-icon btn-icon-only btn btn-outline-danger"><i class="pe-7s-trash btn-icon-wrapper"> </i></button>
                            <button class="btn-wide btn btn-success">Save</button>
                        </div>-->
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card-shadow-danger mb-1 widget-chart widget-chart2 text-left card">
                        <div class="widget-content">
                            <div class="widget-content-outer">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left pr-2 fsize-1">
                                        <div class="widget-numbers mt-0 fsize-3 text-danger">71%</div>
                                    </div>
                                    <div class="widget-content-right w-100">
                                        <div class="progress-bar-xs progress">
                                            <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="71"
                                                aria-valuemin="0" aria-valuemax="100" style="width: 71%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-left fsize-1">
                                    <div class="text-muted opacity-6">To Do</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (isset($todo))
                        @foreach ($todo as $list)
                            <div
                                class="card-shadow-danger mb-1 widget-chart widget-chart2 text-left card border border-danger">
                                <div class="widget-content">
                                    <div class="widget-content-outer">
                                        <div class="widget-content-left fsize-1">
                                            <div class="text-muted opacity-6"><b>Title: </b> {{ $list->title }}</div>
                                            <div class="text-muted opacity-6"><b>Desc: </b>{{ $list->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card-shadow-success mb-1 widget-chart widget-chart2 text-left card">
                        <div class="widget-content">
                            <div class="widget-content-outer">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left pr-2 fsize-1">
                                        <div class="widget-numbers mt-0 fsize-3 text-success">54%</div>
                                    </div>
                                    <div class="widget-content-right w-100">
                                        <div class="progress-bar-xs progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                aria-valuenow="54" aria-valuemin="0" aria-valuemax="100"
                                                style="width: 54%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-left fsize-1">
                                    <div class="text-muted opacity-6">In Progress</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (isset($inprogress))
                        @foreach ($inprogress as $list)
                            <div
                                class="card-shadow-danger mb-1 widget-chart widget-chart2 text-left card border border-success">
                                <div class="widget-content">
                                    <div class="widget-content-outer">
                                        <div class="widget-content-left fsize-1">
                                            <div class="text-muted opacity-6"><b>Title: </b> {{ $list->title }}</div>
                                            <div class="text-muted opacity-6"><b>Desc: </b>{{ $list->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card-shadow-warning mb-1 widget-chart widget-chart2 text-left card">
                        <div class="widget-content">
                            <div class="widget-content-outer">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left pr-2 fsize-1">
                                        <div class="widget-numbers mt-0 fsize-3 text-warning">32%</div>
                                    </div>
                                    <div class="widget-content-right w-100">
                                        <div class="progress-bar-xs progress">
                                            <div class="progress-bar bg-warning" role="progressbar"
                                                aria-valuenow="32" aria-valuemin="0" aria-valuemax="100"
                                                style="width: 32%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-left fsize-1">
                                    <div class="text-muted opacity-6">Ready</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (isset($ready))
                        @foreach ($ready as $list)
                            <div
                                class="card-shadow-danger mb-1 widget-chart widget-chart2 text-left card border border-warning">
                                <div class="widget-content">
                                    <div class="widget-content-outer">
                                        <div class="widget-content-left fsize-1">
                                            <div class="text-muted opacity-6"><b>Title: </b> {{ $list->title }}</div>
                                            <div class="text-muted opacity-6"><b>Desc: </b>{{ $list->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card-shadow-info mb-1 widget-chart widget-chart2 text-left card">
                        <div class="widget-content">
                            <div class="widget-content-outer">
                                <div class="widget-content-wrapper">
                                    <div class="widget-content-left pr-2 fsize-1">
                                        <div class="widget-numbers mt-0 fsize-3 text-success">89%</div>
                                    </div>
                                    <div class="widget-content-right w-100">
                                        <div class="progress-bar-xs progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                aria-valuenow="89" aria-valuemin="0" aria-valuemax="100"
                                                style="width: 89%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-left fsize-1">
                                    <div class="text-muted opacity-6">Complete</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (isset($complete))
                        @foreach ($complete as $list)
                            <div
                                class="card-shadow-danger mb-1 widget-chart widget-chart2 text-left card border border-success">
                                <div class="widget-content">
                                    <div class="widget-content-outer">
                                        <div class="widget-content-left fsize-1">
                                            <div class="text-muted opacity-6"><b>Title: </b> {{ $list->title }}</div>
                                            <div class="text-muted opacity-6"><b>Desc: </b>{{ $list->description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>


        </div>
        <div class="app-wrapper-footer">
            <div class="app-footer">
                <div class="app-footer__inner">
                    <div class="app-footer-left">
                        <ul class="nav">
                            <li class="nav-item">
                                <a href="javascript:void(0);" class="nav-link">
                                    Footer Link 1
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0);" class="nav-link">
                                    Footer Link 2
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="app-footer-right">
                        <ul class="nav">
                            <li class="nav-item">
                                <a href="javascript:void(0);" class="nav-link">
                                    Footer Link 3
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="javascript:void(0);" class="nav-link">
                                    <div class="badge badge-success mr-1 ml-0">
                                        <small>NEW</small>
                                    </div>
                                    Footer Link 4
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js"
        integrity="sha512-Wt1bJGtlnMtGP0dqNFH1xlkLBNpEodaiQ8ZN5JLA5wpc1sUlk/O5uuOMNgvzddzkpvZ9GLyYNa8w2s7rqiTk5Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        var month = @json($data['month']);
        var data = @json($data['sales']);
        var expense = @json($data['expense']);
        var purchase = @json($data['purchase']);
        const ctx = document.getElementById("canvas-custom").getContext("2d");
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: month,
                datasets: [{
                        label: 'Sales',
                        data: data,
                        backgroundColor: [
                            'red',


                        ],
                        borderColor: [
                            'red',


                        ],
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: expense,
                        backgroundColor: [
                            'blue'

                        ],
                        borderColor: [
                            'blue'

                        ],
                        borderWidth: 1
                    },
                    {
                        label: 'Purchases',
                        data: purchase,
                        backgroundColor: [
                            'green'

                        ],
                        borderColor: [
                            'green'

                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: !0,
                legend: {
                    position: "top"
                },
                title: {
                    display: !1,
                    text: "Chart.js Bar Chart"
                }
            }
        });
    </script>

</x-app-layout>
