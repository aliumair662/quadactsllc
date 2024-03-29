<x-app-layout>
    <style>
        .disabled-tr {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>

    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-users icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Packages
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                    @if (Auth::user()->is_admin !== 0)
                        <div class="page-title-actions">
                            <a href="{{ route('newPackage') }}">
                                <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                    class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Package">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </a>

                        </div>
                    @endif
                </div>
            </div>
            {{-- <div class="text-right">
                    <a href="{{route('quotationPagePdf',['from_date'=>(isset($from_date)) ? $from_date : 'none','to_date'=>(isset($to_date)) ? $to_date : 'none','customer_name'=>(isset($customer_name)) ? $customer_name : 'none','invoice_number'=>(isset($invoice_number)) ? $invoice_number : 'none' ])}}" target="_blank" class="btn btn-outline-success mb-2 pdf">Download PDF</a>

                </div> --}}
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchPackages') }}" method="post">
                        @csrf
                        <div class="row no-gutters">
                            <div class="form-group col-sm-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : (isset($_GET['queries']['from']) ? $_GET['queries']['from_date'] : '') }}">
                            </div>
                            <div class="form-group col-sm-2 ml-1">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : (isset($_GET['queries']['to']) ? $_GET['queries']['to_date'] : '') }}">
                            </div>
                            <div class="form-group col-sm-2 ml-1">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Packages</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : (isset($_GET['queries']['invoice_number']) ? $_GET['queries']['invoice_number'] : '') }}"
                                    placeholder="Packages No.">
                            </div>
                            <div class="col-2 align-self-end ml-2 pb-3" style="">
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
                        <div class="card-header">Packages List
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
                                        <th class="text-center">Package #</th>
                                        {{-- @if (Auth::user()->is_admin === 1)
                                        <th class="text-center">User Name</th>
                                        @endif --}}
                                        <th class="text-center">Package Date</th>
                                        {{-- <th class="text-center">Customer Name</th> --}}
                                        <th class="text-center">Net Total</th>
                                        <th class="text-center">Net Qty</th>
                                        <th class="text-center">Net Profit</th>
                                        <th class="text-center">Profit %</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($lists))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($lists as $list)
                                            <tr class="{{ $list->cancel_status === 1 ? 'disabled-tr' : '' }}">
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">{{ $list->invoice_number }}</td>
                                                {{-- @if (Auth::user()->is_admin === 1)
                                        <td class="text-center text-muted"> {{ $list->user_name }}</td>
                                        @endif --}}
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->invoice_date)->format('d-m-Y') }}
                                                </td>
                                                {{-- <td class="text-center">{{isset($list->customer_name) ? $list->customer_name : "testing"}}</td> --}}
                                                <td class="text-center">{{ $list->net_total }} </td>
                                                <td class="text-center">{{ $list->net_qty }} </td>
                                                <td class="text-center">{{ $list->net_profit }} </td>
                                                @if (!empty($list->profit_percent) && $list->profit_percent >= 30)
                                                    <td class="text-center"><span
                                                            class="badge badge-success">{{ $list->profit_percent }} %
                                                        </span>
                                                    </td>
                                                @elseif (!empty($list->profit_percent) && $list->profit_percent < 30)
                                                    <td class="text-center"><span
                                                            class="badge badge-danger">{{ $list->profit_percent }} %
                                                        </span>
                                                    </td>
                                                @elseif (empty($list->profit_percent))
                                                    <td class="text-center"><span>
                                                        </span>
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    <div class="mb-2 mr-2 btn-group">
                                                        @if (Auth::user()->is_admin == 0)
                                                            <a
                                                                href="{{ route('newQuotation', ['pkg_id' => $list->id, 'file_name' => 'packages']) }}"><button
                                                                    type="button" tabindex="0"
                                                                    class="btn btn-outline-success">Create
                                                                    Quotation</button></a>
                                                        @else
                                                            <button class="btn btn-outline-success">Edit</button>
                                                            <button type="button" aria-haspopup="true"
                                                                aria-expanded="false" data-toggle="dropdown"
                                                                class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span
                                                                    class="sr-only">Toggle Dropdown</span>
                                                            </button>
                                                            <div tabindex="-1" role="menu" aria-hidden="true"
                                                                class="dropdown-menu" x-placement="bottom-start"
                                                                style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                                <a
                                                                    href="{{ route('newQuotation', ['pkg_id' => $list->id, 'file_name' => 'packages']) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Create
                                                                        Quotation</button></a>
                                                                <a href="#"
                                                                    onclick="deleteRecord('{{ route('deletePackage', $list->id) }}');"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Delete</button></a>

                                                                {{-- <a href="{{route('quotationRecordPdf',$list->id)}}" class="pdf" target="_blank"><button type="button" tabindex="0" class="dropdown-item">PDF</button></a>
                                                    <a href="https://wa.me/?text={{route('quotationRecordPdf',$list->id)}}" class="" target="_blank"><button type="button" tabindex="0" class="dropdown-item">Share</button></a> --}}
                                                                {{-- <a href="{{route('quatationToSalesInvoice',$list->id)}}" ><button type="button" tabindex="0" class="dropdown-item">Create Invoice</button></a>
                                                    <a href="{{route('cancelQuotation', $list->id)}}" ><button type="button" tabindex="0" class="dropdown-item">Cancel</button></a> --}}
                                                        @endif
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
                                        @if (Auth::user()->is_admin !== 0)
                                        <td colspan="5">Net Totals</td>
                                        @endif
                                        @if (Auth::user()->is_admin == 0)
                                        <td colspan="4">Net Totals</td>
                                        @endif
                                        <td class="text-center">{{(isset($net_total)) ? $net_total : ''}}</td>
                                        <td class="text-center">{{(isset($net_qty)) ? $net_qty : ''}}</td>
                                        <td colspan="2"></td>
                                    </tr> --}}
                        </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mr-3 card-footer">
                        <div>
                        </div>
                        {{ $lists->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
