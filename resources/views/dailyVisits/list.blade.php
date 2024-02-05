<x-app-layout>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWCT-3sgRjyld2IKVaHtG8EeVoF6G7JMY&libraries=places">
    </script>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-users icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Daily Visits
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newDailyVisit') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Invoice">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            {{-- <div class="text-right">
                <a href="{{ route('salePagePdf', ['from_date' => isset($from_date) ? $from_date : 'none', 'to_date' => isset($to_date) ? $to_date : 'none', 'customer_id' => isset($customer_id) ? $customer_id : 'none', 'invoice_number' => isset($invoice_number) ? $invoice_number : 'none']) }}"
                    target="_blank" class="btn btn-outline-success mb-2">Download PDF</a>

            </div> --}}
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchDailyVisit') }}" method="post">
                        @csrf
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : (isset($_GET['queries']['from']) ? $_GET['queries']['from_date'] : '') }}">
                            </div>
                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : (isset($_GET['queries']['to']) ? $_GET['queries']['to_date'] : '') }}">
                            </div>

                            {{-- <div class="form-group col-2 pl-1 pt-1">
                                <div class="form-group">
                                    <label for="branch" class="">
                                        Customer
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Customer"
                                        name="customer_id" id="customer_id">
                                        <option value="">Select Customer</option>
                                        @if (!empty($customers))
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ isset($customer_id) ? ($customer->id == $customer_id ? 'Selected' : '') : '' }}>
                                                    {{ $customer->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div> --}}
                            <div class="form-group col-2 pl-1 pt-1">
                                <div class="form-group">
                                    <label for="branch" class="">
                                        Salesman
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Salesman"
                                        name="user_id" id="user_id">
                                        <option value="">Select Salesman</option>
                                        @if (!empty($users))
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ isset($user_id) ? ($user->id == $user_id ? 'Selected' : '') : '' }}>
                                                    {{ $user->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-2 ml-1">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Invoice</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : (isset($_GET['queries']['invoice_number']) ? $_GET['queries']['invoice_number'] : '') }}"
                                    placeholder="Invoice No.">
                            </div>
                            <div class="col-2 align-self-end ml-2 pb-3" style="margin-bottom: 1.1rem;">
                                <div class="page-title-actions">
                                    <a href="">
                                        <button type="submit" data-toggle="tooltip" title=""
                                            data-placement="bottom" class="btn-shadow btn btn-dark"
                                            data-original-title="Search">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Daily Visits List
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Visit ID</th>
                                        @if (Auth::user()->is_admin === 1)
                                            <th class="text-center">User Name</th>
                                        @endif
                                        <th class="text-center">Business Name</th>
                                        <th class="text-center">Phone</th>
                                        {{-- <th class="text-center">Email</th>
                                        <th class="text-center">Address</th> --}}
                                        {{-- <th class="text-center">Longituted</th>
                                        <th class="text-center">Latitude</th> --}}
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Location</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($daily_visits))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($daily_visits as $list)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">{{ $list->invoice_number }}</td>
                                                @if (Auth::user()->is_admin === 1)
                                                    <td class="text-center text-muted"> {{ $list->user_name }}</td>
                                                @endif
                                                <td class="text-center">{{ $list->name }}</td>
                                                <td class="text-center">{{ $list->phone }} </td>
                                                {{-- <td class="text-center">{{ $list->email }} </td>
                                                <td class="text-center">{{ $list->address }} </td>
                                                <td class="text-center">{{ $list->longitute }} </td>
                                                <td class="text-center">{{ $list->latitude }} </td> --}}
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->invoice_date)->format('d-m-Y') }}
                                                </td>
                                                <td class="text-center" id="map_location">{{ $list->location }} </td>
                                                <td class="text-center">
                                                    <div class="mb-2 mr-2 btn-group">
                                                        <button class="btn btn-outline-success">Edit</button>
                                                        <button type="button" aria-haspopup="true"
                                                            aria-expanded="false" data-toggle="dropdown"
                                                            class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span
                                                                class="sr-only">Toggle Dropdown</span>
                                                        </button>
                                                        <div tabindex="-1" role="menu" aria-hidden="true"
                                                            class="dropdown-menu" x-placement="bottom-start"
                                                            style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                            <a href="{{ route('editDailyVisit', $list->id) }}"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Edit</button></a>
                                                            <a href="#"
                                                                onclick="deleteRecord('{{ route('deleteDailyVisit', $list->id) }}');"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Delete</button></a>
                                                            <a onclick="openGoogleMaps();" class=""
                                                                target="_blank"><button type="button" tabindex="0"
                                                                    class="dropdown-item">Map</button></a>
                                                            <a href="{{ route('dailyVisitRecordPdf', $list->id) }}"
                                                                target="_blank"><button type="button" tabindex="0"
                                                                    class="dropdown-item">PDF</button></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @endif

                                    {{-- <tr>
                                        <td colspan="3">Net Totals</td>
                                        <td class="text-center">{{ isset($net_total) ? $net_total : '' }}</td>
                                        <td class="text-center">{{ isset($net_qty) ? $net_qty : '' }}</td>
                                        <td colspan="2"></td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            <div>
                                {{ $daily_visits->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
