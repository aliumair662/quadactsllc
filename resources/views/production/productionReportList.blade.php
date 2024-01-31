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
                        <div>Production Report
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newproduction') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Item">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchproductionReport') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : '' }}" required>
                            </div>
                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : '' }}" required>
                            </div>

                            <div class="form-group col-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Employee</label>
                                <select class="js-example-basic-single form-control" aria-placeholder="Select Item"
                                    name="employeeid" required>
                                    <option value=""> Select Employee </option>
                                    @if (!empty($employees))
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ isset($employeeid) ? ($employeeid == $employee->id ? 'Selected' : '') : '' }}>
                                                {{ $employee->name }} </option>
                                        @endforeach
                                    @endif

                                </select>

                            </div>
                            <div class="form-group col-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Item</label>
                                <select class="js-example-basic-single form-control" aria-placeholder="Select Item"
                                    name="itemid">
                                    <option value=""> Select Item </option>
                                    @if (!empty($items))
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}"
                                                {{ isset($itemid) ? ($itemid == $item->id ? 'Selected' : '') : '' }}>
                                                {{ $item->name }} </option>
                                        @endforeach
                                    @endif

                                </select>

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
                        <div class="card-header">Production Report List
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Employee Name</th>
                                        <th class="text-center">Production # </th>
                                        <th class="text-center">Production Date</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Rate</th>
                                        <th class="text-center">Additional Rate</th>
                                        <th class="text-center">Net Rate</th>

                                        <th class="text-center">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($allProductionList))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($allProductionList as $production)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">
                                                    {{ $production['production_number'] }}</td>

                                                <td class="text-center text-muted">
                                                    {{ \Carbon\Carbon::parse($production['production_date'])->format('d-m-Y') }}
                                                </td>
                                                <td class="text-center text-muted">{{ $production['employee_name'] }}
                                                </td>
                                                <td class="text-center text-muted">{{ $production['item_name'] }}</td>
                                                <td class="text-center text-muted">{{ $production['item_qty'] }}</td>
                                                <td class="text-center text-muted">
                                                    {{ array_key_exists('rate', $production) ? $production['rate'] : 0 }}
                                                </td>
                                                <td class="text-center text-muted">
                                                    {{ array_key_exists('additional_rate', $production) ? $production['additional_rate'] : 0 }}
                                                </td>
                                                <td class="text-center text-muted">{{ $production['item_price'] }}</td>
                                                <td class="text-center text-muted">{{ $production['amount'] }}</td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                        <tr>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted"></td>

                                            <td class="text-center text-muted font-weight-bold">Net Total</td>
                                            <td class="text-center text-muted font-weight-bold">{{ $net_qty }}
                                            </td>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted"></td>
                                            <td class="text-center text-muted font-weight-bold"
                                                id="actual_production_amount">{{ $net_total }}</td>
                                        </tr>
                                        @if (isset($employeeid) && $companyinfo->auto_post_production == 0)
                                            <tr>
                                                <td class="text-center text-muted" colspan="10">
                                                    <form class="Q-form"
                                                        action="{{ route('postEmployeeProductionManually') }}"
                                                        method="post">
                                                        @csrf
                                                        <input type="hidden" name="employeeid" id="employeeid"
                                                            value="{{ $employeeid }}">
                                                        <input type="hidden" name="actual_production_amount"
                                                            id="actual_production_amount"
                                                            value="{{ $net_total }}">
                                                        <input type="hidden" name="production_details"
                                                            id="production_details"
                                                            value="{{ serialize($allProductionList) }}">
                                                        <input type="hidden" name="from_date" class="form-control"
                                                            value="{{ isset($from_date) ? $from_date : '' }}">
                                                        <input type="hidden" name="to_date" class="form-control"
                                                            value="{{ isset($to_date) ? $to_date : '' }}">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Production Information</h5>
                                                            <div class="form-row">
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Employee</label>
                                                                        <input name="employee_name" id="employee_name"
                                                                            placeholder="" type="text"
                                                                            value="{{ $employee_name }}"
                                                                            class="form-control" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Post #</label>
                                                                        <input name="voucher_number"
                                                                            id="voucher_number" placeholder=""
                                                                            type="text"
                                                                            value="{{ Config::get('constants.PRODUCTION_POST_INVOICE_PREFIX') . $voucher_number }}"
                                                                            class="form-control" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Post Date</label>
                                                                        <input name="production_date"
                                                                            id="production_date" placeholder=""
                                                                            type="date" value=""
                                                                            class="form-control" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <div class="form-check-inline">
                                                                            <label
                                                                                class="form-check-label font-weight-bold">
                                                                                <input type="radio"
                                                                                    class="form-check-input amount_type"
                                                                                    name="amount_type" value="1"
                                                                                    checked
                                                                                    onchange="deductEmployeeAmount();">Original
                                                                                Amount
                                                                            </label>
                                                                        </div>
                                                                        <div
                                                                            class="form-check-inline font-weight-bold">
                                                                            <label class="form-check-label">
                                                                                <input type="radio"
                                                                                    class="form-check-input amount_type"
                                                                                    name="amount_type" value="2"
                                                                                    onchange="deductEmployeeAmount();">Double
                                                                                Amount
                                                                            </label>
                                                                        </div>

                                                                        <input name="gross_total" id="gross_total"
                                                                            placeholder="" type="text"
                                                                            value="{{ $net_total }}"
                                                                            class="form-control"
                                                                            onchange="deductEmployeeAmount();"
                                                                            readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Employee Advance</label>
                                                                        <input name="total_Advance" id="total_Advance"
                                                                            placeholder="" type="number"
                                                                            value="{{ $EmployeeAdvanceSum }}"
                                                                            class="form-control" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">

                                                                </div>



                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Deduction</label>
                                                                        <input name="deduction_amount"
                                                                            id="deduction_amount" placeholder=""
                                                                            type="number" value="0"
                                                                            class="form-control"
                                                                            onchange="deductEmployeeAmount();">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Total Payment </label>
                                                                        <input name="total_payment_received_period"
                                                                            id="total_payment_received_period"
                                                                            placeholder="" type="number"
                                                                            value="{{ $EmployeePaymentSum }}"
                                                                            class="form-control" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6"></div>

                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11">Plus Additional
                                                                            Amount </label>
                                                                        <input name="additional_amount"
                                                                            id="additional_amount" type="number"
                                                                            value="0" class="form-control"
                                                                            onchange="deductEmployeeAmount();">
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="form-row">
                                                                <div class="col-md-9"></div>
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11" class="">Net
                                                                            Total</label>
                                                                        <input name="net_total" id="net_total"
                                                                            placeholder="" type="text"
                                                                            value="{{ $net_total }}"
                                                                            class="form-control" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col-md-9"></div>
                                                                <div class="col-md-3">
                                                                    <div class="position-relative form-group">
                                                                        <label for="exampleEmail11"
                                                                            class="">Cash Paid </label>
                                                                        <input name="cash_paid" id="cash_paid"
                                                                            placeholder="" type="text"
                                                                            value="{{ $net_total }}"
                                                                            class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="d-block text-center card-footer">
                                                            <button type="submit" class="mt-2 btn btn-primary">Post
                                                                Production and Paid Amount To Employee Ledger </button>
                                                        </div>
                                                    </form>

                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            @if (!empty($employee_name) && $companyinfo->auto_post_production == 0)
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-header">Employee Payments From
                                {{ isset($from_date) ? $from_date : '' }} To:
                                {{ isset($to_date) ? $to_date : '' }}
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
                                            <th class="text-center">Voucher #</th>
                                            <th class="text-center">Employee Name</th>
                                            <th class="text-center">Notes</th>
                                            <th class="text-center">Net Total</th>

                                            <th class="text-center">Voucher Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($EmployeePaymentList))
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($EmployeePaymentList as $payment)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $i }}</td>
                                                    <td class="text-center text-muted">{{ $payment->voucher_number }}
                                                    </td>
                                                    <td class="text-center">{{ $employee_name }}</td>
                                                    <td class="text-center">{{ $payment->note }}</td>
                                                    <td class="text-center ">
                                                        {{ $payment->credit > 0 ? $payment->credit : $payment->debit }}
                                                    </td>
                                                    </td>
                                                    <td class="text-center">{{ $payment->voucher_date }}</td>
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
                                                                <a href="{{ route('employeePaymentList') }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Edit</button></a>

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php
                                                    $i++;
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td class="text-center text-muted font-weight-bold" colspan="4">Net
                                                    Total</td>
                                                <td class="text-center font-weight-bold">{{ $EmployeePaymentSum }}
                                                </td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mr-3 card-footer">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-header">Employee Production From
                                {{ isset($from_date) ? $from_date : '' }} To:
                                {{ isset($to_date) ? $to_date : '' }}
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
                                            <th class="text-center">Voucher #</th>
                                            <th class="text-center">Employee Name</th>

                                            <th class="text-center">Net Total</th>

                                            <th class="text-center">Voucher Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($EmployeePostPorductionList))
                                            @php
                                                $i = 1;

                                                $net_total = 0;

                                            @endphp
                                            @foreach ($EmployeePostPorductionList as $list)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $i }}</td>
                                                    <td class="text-center text-muted">{{ $list->voucher_number }}
                                                    </td>
                                                    <td class="text-center">{{ $employee_name }}</td>

                                                    <td class="text-center">{{ $list->net_total }}</td>
                                                    </td>
                                                    <td class="text-center">{{ $list->voucher_date }}</td>
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
                                                                <a href="#"
                                                                    onclick="deleteRecord('{{ route('deletpostproduction', $list->id) }}');"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Delete</button></a>
                                                                <a href="{{ route('postProductionPdf', $list->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">PDF</button></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php
                                                    $i++;
                                                    $net_total += $list->net_total;
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td class="text-center text-muted font-weight-bold" colspan="3">Net
                                                    Total</td>
                                                <td class="text-center font-weight-bold">{{ $net_total }}</td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                            </tr>

                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mr-3 card-footer">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="card-header">Employee Advance Return From
                                {{ isset($from_date) ? $from_date : '' }} To:
                                {{ isset($to_date) ? $to_date : '' }}
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
                                            <th class="text-center">Voucher No</th>
                                            <th class="text-center">Employee Name</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Voucher Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $net_total = 0;
                                        @endphp
                                        @if (!empty($EmployeeAdvanceReturnList))
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($EmployeeAdvanceReturnList as $list)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $list->id }}</td>
                                                    <td class="text-center">{{ $list->voucher_number }}</td>
                                                    <td class="text-center">{{ $list->name }} </td>
                                                    <td class="text-center">{{ $list->amount }}</td>
                                                    <td class="text-center">{{ $list->voucher_date }}</td>
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
                                                                <a href="{{ route('editAdvanceReturn', $list->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Edit</button></a>
                                                                <a
                                                                    href="{{ route('deleteAdvanceReturn', $list->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Delete</button></a><a
                                                                    href="{{ route('recordPdf', $list->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">PDF</button></a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @php
                                                    $i++;
                                                    $net_total += $list->amount;
                                                @endphp
                                            @endforeach
                                            <tr>
                                                <td class="text-center text-muted font-weight-bold" colspan="3">Net
                                                    Total</td>
                                                <td class="text-center font-weight-bold">{{ $EmployeePaymentSum }}
                                                </td>
                                                <td class="text-center"></td>
                                                <td class="text-center"></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mr-3 card-footer">

                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

</x-app-layout>
