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
                        <div>Attendee List
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>

                        </div>
                    </div>
                    <!-- employeeAttendeeList -->
                    <div class="page-title-actions">
                        <a href="{{ route('employeeAttendeeAdd') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add Salary">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>

                </div>
            </div>
            <div class="text-right">
                <a href="{{ route('attendeePdf', ['employee_name' => isset($employee_name) ? $employee_name : 'none', 'invoice_number' => isset($invoice_number) ? $invoice_number : 'none']) }}"
                    target="_blank" class="btn btn-outline-success mb-2">Download PDF</a>

            </div>

            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchAttendeeRecords') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Employee</label>
                                <input type="text" name="employee_name" class="form-control"
                                    value="{{ isset($employee_name) ? $employee_name : (isset($_GET['queries']['name']) ? $_GET['queries']['name'] : '') }}"
                                    placeholder="name">
                            </div>
                            <div class="form-group col-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Voucher</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : (isset($_GET['queries']['invoice_number']) ? $_GET['queries']['invoice_number'] : '') }}"
                                    placeholder="Invoice No.">
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
                        <div class="card-header">Attendee List
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Voucher #</th>
                                        <th class="text-center">From Date</th>
                                        <th class="text-center">To Date</th>
                                        <th class="text-center">Employee Name</th>
                                        <th class="text-center">Total Present</th>
                                        <th class="text-center">Total Absent</th>
                                        <th class="text-center">Total Leave</th>
                                        <th class="text-center">Half Days</th>
                                        <th class="text-center">Holiday</th>
                                        <th class="text-center">Basic Salary</th>
                                        <th class="text-center">Net Working Days</th>
                                        <th class="text-center">Gross Salary</th>
                                        <th class="text-center">Deduction</th>
                                        <th class="text-center">Net Salary</th>
                                        <th class="text-center">Salary Paid</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($lists))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($lists as $list)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">{{ $list->voucher_number }}</td>

                                                <td class="text-center text-muted">
                                                    {{ \Carbon\Carbon::parse($list->from_date)->format('d-m-Y') }}</td>
                                                <td class="text-center text-muted">
                                                    {{ \Carbon\Carbon::parse($list->to_date)->format('d-m-Y') }}</td>
                                                <td class="text-center">{{ $list->name }}</td>
                                                <td class="text-center">{{ $list->total_present }} </td>
                                                <td class="text-center">{{ $list->total_absent }} </td>
                                                <td class="text-center">{{ $list->total_leave }} </td>
                                                <td class="text-center">{{ $list->half_days }} </td>
                                                <td class="text-center">{{ $list->holiday }} </td>
                                                <td class="text-center">{{ $list->basic_salary }} </td>
                                                <td class="text-center">{{ $list->net_working_days }} </td>
                                                <td class="text-center">{{ $list->gross_salary }} </td>
                                                <td class="text-center">{{ $list->total_deduction }} </td>
                                                <td class="text-center">{{ $list->net_salary }}</td>
                                                <td class="text-center">{{ $list->salary_paid }}</td>
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
                                                            {{-- <a href="#" onclick="deleteRecord('{{route('deleteAttendee',$list->id)}}');"><button type="button" tabindex="0" class="dropdown-item">Delete</button></a> --}}
                                                            <a href="{{ route('singleRecordPdf', $list->id) }}"><button
                                                                    type="button" tabindex="0"
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
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            {{ $lists->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
