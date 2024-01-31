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
                        <div>Employee Attendee
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>
                        </div>
                    </div>

                    <div class="page-title-actions">
                        <a href="{{ route('employeeAttendeeList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Attendence List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>
                </div>

            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{route('searchMonthAttendeeList')}}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Month</label>

                                <select class="js-example-basic-single form-control" name="month_year" required>
                                    <option value="">Select Month</option>
                                    @foreach($monthList as $month)
                                    <option value="{{$month['year']}}-{{$month['month']}}" {{(isset($month)) ? 'Selected' : '' }}>{{$month['month_name']}} - {{$month['year']}} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-2 mr-3">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" class="form-control" value="{{(isset($from_date)) ? $from_date : ''}}" name="from_date">
                            </div>
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Date</label>
                                <input type="date" class="form-control" value="{{(isset($to_date)) ? $to_date : ''}}" name="to_date">
                            </div>

                            <div class="col-2 align-self-end ml-2" style="margin-bottom: 1.1rem;">
                                <div class="page-title-actions">
                                    <a href="">
                                        <button type="submit" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow btn btn-dark" data-original-title="Search">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <form action="{{route('employeeAttendence')}}" method="post" class="Q-form">
                @csrf
                <input type="hidden" value="{{$month_year}}" name="month_year">
                <input type="hidden" class="form-control" value="{{(isset($to_date)) ? $to_date : ''}}" name="to_date">
                <input type="hidden" class="form-control" value="{{(isset($from_date)) ? $from_date : ''}}" name="from_date">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-card mb-3 card">
                            <div class="form-group mx-3 my-3">
                                <label for="" class="form-label"><b>Voucher Number</b></label>
                                <input type="text" name="voucher_number" value="{{(isset($voucher_number)) ? $voucher_number : Config::get('constants.EMPLOYEE_SALARY_SHEET').$voucher_number}}" readonly class="form-control " style="width: 120px;">
                            </div>
                            <div class="card-header">Employee Salary & Attendence Sheet
                                <div class="btn-actions-pane-right">
                                    <div role="group" class="btn-group-sm btn-group">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="align-middle px-3 mb-0 table table-borderless table-striped table-hover table-bordered">
                                    <thead>
                                        <tr class="text-center">
                                            <th rowspan="3" style="font-size: 1.2rem;" class=" align-middle">Employees</th>
                                        </tr>
                                        <tr>
                                            @if(!empty($allDaysName))
                                            @foreach($allDaysName as $DaysName)
                                            <th>{{$DaysName}}</th>
                                            @endforeach
                                            @endif
                                            <th>Total Present</th>
                                            <th>Total Absent</th>
                                            <th>Total Leave</th>
                                            <th>Half Days</th>
                                            <th>Holiday</th>
                                            <th>Basic Salary</th>
                                            <th>Net Working Days</th>
                                            <th>Gross Salary</th>
                                            <th>Deduction</th>
                                            <th>Net Salary</th>
                                            <th>Salary Paid</th>
                                        </tr>
                                        <tr>
                                            @if(!empty($allDaysMonth))
                                            @foreach($allDaysMonth as $DaysMonth)
                                            <th>{{$DaysMonth}}</th>
                                            @endforeach
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @if(!empty($employees))
                                        @php
                                        $i=1;
                                        @endphp
                                        @foreach($employees as $list)
                                        <tr>
                                            <td class=" text-muted">{{$list->name}}</td>

                                            @if(!empty($allDates))
                                            @foreach($allDates as $Date)
                                            <td><select class="js-example-basic-single form-control attendece_{{$list->id}}" name="attendece[]" onchange="CalculateEmployeeSalary({{$list->id}});">
                                                    <option value="{{$Date}}~{{$list->id}}~1" {{in_array($Date.'~'.$list->id.'~1',$allAttendance) ? 'selected' : ''}}>Present</option>
                                                    <option value="{{$Date}}~{{$list->id}}~2" {{in_array($Date.'~'.$list->id.'~2',$allAttendance) ? 'selected' : ''}}>Absent</option>
                                                    <option value="{{$Date}}~{{$list->id}}~3" {{in_array($Date.'~'.$list->id.'~3',$allAttendance) ? 'selected' : ''}}>Leave</option>
                                                    <option value="{{$Date}}~{{$list->id}}~4" {{in_array($Date.'~'.$list->id.'~4',$allAttendance) ? 'selected' : ''}}>Half Days</option>
                                                    <option value="{{$Date}}~{{$list->id}}~5" {{in_array($Date.'~'.$list->id.'~5',$allAttendance) ? 'selected' : ''}}>Holiday</option>
                                                </select></td>
                                            @endforeach
                                            @endif
                                            <td><input type="text" style="width: 80px;" id="total_present_{{$list->id}}" name="total_present_{{$list->id}}" class="form-control" value="{{(isset($list->total_present)) ? $list->total_present : ''}}" readonly></td>
                                            <td><input type="text" style="width: 80px;" id="total_absent_{{$list->id}}" name="total_absent_{{$list->id}}" class="form-control" value="{{(isset($list->total_present)) ? $list->total_absent : ''}}" readonly></td>
                                            <td><input type="text" style="width: 80px;" id="total_leave_{{$list->id}}" name="total_leave_{{$list->id}}" class="form-control" value="{{(isset($list->total_present)) ? $list->total_leave : ''}}" readonly></td>
                                            <td><input type="text" style="width: 80px;" id="total_half_days_{{$list->id}}" name="total_half_days_{{$list->id}}" class="form-control" value="{{(isset($list->half_days)) ? $list->half_days : ''}}" readonly></td>
                                            <td><input type="text" style="width: 80px;" id="total_holidays_{{$list->id}}" name="total_holidays_{{$list->id}}" class="form-control" value="{{(isset($list->holiday)) ? $list->holiday : ''}}" readonly></td>
                                            <td><input type="text" style="width: 100px;" id="total_basic_salary_{{$list->id}}" name="total_basic_salary_{{$list->id}}" class="form-control" value="{{$list->salery}}" readonly></td>
                                            <td><input type="text" style="width: 100px;" id="total_working_days_{{$list->id}}" name="total_working_days_{{$list->id}}" class="form-control" value="{{(isset($list->net_working_days)) ? $list->net_working_days : ''}}" readonly></td>
                                            <td><input type="text" style="width: 100px;" id="gross_salary_{{$list->id}}" name="gross_salary_{{$list->id}}" class="form-control" value="{{(isset($list->gross_salary)) ? $list->gross_salary : ''}}" readonly></td>
                                            <td><input type="text" style="width: 100px;" id="total_deduction_{{$list->id}}" name="total_deduction_{{$list->id}}" class="form-control" value="{{(isset($list->total_deduction)) ? $list->total_deduction : ''}}" onchange="CalculateEmployeeSalary({{$list->id}});"></td>
                                            <td><input type="text" style="width: 100px;" id="net_salary_{{$list->id}}" name="net_salary_{{$list->id}}" class="form-control" value="{{(isset($list->net_salary)) ? $list->net_salary : ''}}" readonly></td>
                                            <td><input type="text" style="width: 100px;" id="salary_paid_{{$list->id}}" name="salary_paid_{{$list->id}}" class="form-control" value="{{(isset($list->salary_paid_)) ? $list->salary_paid_ : ''}}" ></td>
                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                        @endif

                                    </tbody>
                                </table>
                                <div class="justify-content-end align-items-center mr-5 d-flex">
                                    <div class="d-flex mr-3 pt-1">
                                        <a href="" onclick="deleteRecord('{{route('deleteAllEmployeeAttendee','some')}}');" class="btn btn-danger">Delete</a>
                                    </div>
                                    <div class="d-flex align-items-baseline pb-3">
                                        Add to ledger account &nbsp;<input type="checkbox" value="1" name="ledger_entry" class="mr-3 mt-4" {{(isset($ledger_entry)) ? ($ledger_entry == 1 ? 'checked' : '') : ''}}>
                                    </div>
                                    <div>
                                        <input type="submit" class="btn btn-primary px-4" value="{{ (isset($ledger_entry) && $ledger_entry == 1 ) ? 'Update' : 'Save'}}">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mr-3 card-footer">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
