<x-app-layout>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-users icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Purchases
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newPurchase') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Invoice">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="text-right">
                <a href="{{ route('purchasePagePdf', ['from_date' => isset($from_date) ? $from_date : 'none', 'to_date' => isset($to_date) ? $to_date : 'none', 'vendor_id' => isset($vendor_id) ? $vendor_id : 'none', 'invoice_number' => isset($invoice_number) ? $invoice_number : 'none']) }}"
                    target="_blank" class="btn btn-outline-success mb-2">Download PDF</a>

            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchPurchase') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : (isset($_GET['queries']['from']) ? $_GET['queries']['from'] : '') }}">
                            </div>
                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : (isset($_GET['queries']['to']) ? $_GET['queries']['to'] : '') }}">
                            </div>

                            <div class="form-group pt-1">
                                <label for="branch" class="">
                                    Vendor
                                    <!-- <a href="" title="category List"><i  class="fa fa-list"></i></a> -->
                                </label>
                                <select class="js-example-basic-single form-control" placeholder="Select Customer"
                                    name="vendor_id" id="customer_id">
                                    <option value="">Select Vendor</option>
                                    @if (!empty($vendors))
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}"
                                                {{ isset($vendor_id) ? ($vendor->id == $vendor_id ? 'Selected' : '') : '' }}>
                                                {{ $vendor->name }} </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Invoice</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : (isset($_GET['queries']['invoice_number']) ? $_GET['queries']['invoice_number'] : '') }}"
                                    placeholder="invoice">
                            </div>
                            <div class="col-2 align-self-end ml-2" style="margin-bottom: 1.1rem;">
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
                        <div class="card-header">Purchases Invoice List
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
                                        <th class="text-center">Invoice #</th>
                                        <th class="text-center">Vendor Name</th>
                                        <th class="text-center">Net Total</th>
                                        {{-- <th class="text-center">Net Pcs</th> --}}
                                        <th class="text-center">Net Qty</th>
                                        <th class="text-center">Invoice Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($purchaselist))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($purchaselist as $list)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">{{ $list->invoice_number }}</td>
                                                <td class="text-center">{{ $list->vendor_name }}</td>
                                                <td class="text-center">{{ $list->net_total }} </td>
                                                {{-- <td class="text-center">{{$list->net_pcs}} </td> --}}
                                                <td class="text-center">{{ $list->net_qty }} </td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->invoice_date)->format('d-m-Y') }}
                                                </td>
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
                                                            <a href="{{ route('editPurchase', $list->id) }}"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Edit</button></a>
                                                            <a href="#"
                                                                onclick="deleteRecord('{{ route('deletePurchase', $list->id) }}');"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Delete</button></a>
                                                            <a href="{{ route('purchasePdf', $list->id) }}"
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
                                    <tr>
                                        <td colspan="3">Net Totals</td>
                                        <td class="text-center">{{ isset($net_total) ? $net_total : '' }}</td>
                                        {{-- <td class="text-center">{{(isset($net_pcs)) ? $net_pcs : ''}}</td> --}}
                                        <td class="text-center">{{ isset($net_qty) ? $net_qty : '' }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            <!-- <div class="col-lg-12">
                                <nav class="float-right" aria-label="Page navigation example">
                                    <ul class="pagination">
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Previous"><span aria-hidden="true">«</span><span class="sr-only">Previous</span></a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">1</a></li>
                                        <li class="page-item active"><a href="javascript:void(0);" class="page-link">2</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">3</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">4</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">5</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Next"><span aria-hidden="true">»</span><span class="sr-only">Next</span></a></li>
                                    </ul>
                                </nav>
                            </div> -->
                            <!-- <div class="form-group pr-2">
                                <label for="">Net Total</label>
                                <input type="text" class="form-control mb-4" value="{{ isset($net_total) ? $net_total : '' }}" style="width: 130px;" readonly>
                            </div>
                            <div class="form-group pr-2">
                                <label for="">Net Pcs</label>
                                <input type="text" class="form-control mb-4" value="{{ isset($net_pcs) ? $net_pcs : '' }}" style="width: 130px;" readonly>
                            </div>
                            <div class="form-group pr-2 mr-5">
                                <label for="">Net Qty</label>
                                <input type="text" value="{{ isset($net_qty) ? $net_qty : '' }}" class="form-control mb-4" style="width: 130px;" readonly>
                            </div> -->
                            @if (isset($queries) && !empty($queries))
                                {{ $purchaselist->appends(['queries' => $queries])->links() }}
                            @elseif(isset($_GET['queries']))
                                {{ $purchaselist->appends(['queries' => $_GET['queries']])->links() }}
                            @else
                                {{ $purchaselist->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
