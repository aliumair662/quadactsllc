<x-app-layout>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-plus icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Employee Payments
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{route('vendorpaymentlist')}}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Employee  Payment List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>



            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{isset($employeePayment) ? route('updateEmployeePayment'): route('saveEmployeePayment')}}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">{{isset($employeePayment) ? 'Edit Employee Payment' : 'Add Employee Payment'}}</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher #</label>
                                    <input name="voucher_number" id="voucher_no" placeholder="" type="text" value="{{(isset($employeePayment)) ? $employeePayment->voucher_number : Config::get('constants.EMPLOYEE_PAYMENT_PREFIX').$voucher_no}}" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Date</label>
                                    <input name="voucher_date" id="invoice_date" placeholder="" type="date" value="{{isset($employeePayment) ? $employeePayment->voucher_date : date('Y-m-d')}}" class="form-control">
                                </div>
                                <input type="hidden" name="id" value="{{isset($employeePayment) ? $employeePayment->id : ''}}">
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Employee Name
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Employee Name" name="employee_id" id="employee_id">
                                        <option disabled selected>Select</option>
                                        @if(!empty($employees))
                                        @foreach($employees as $employee)
                                        <option value="{{$employee->id}}" {{isset($employeePayment) ? $employeePayment->employee_id == $employee->id ? 'selected':'':''}}> {{$employee->name}} </option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-5">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Amount</label>
                                    <input name="amount" id="net_total" placeholder="" type="text" value="{{isset($employeePayment) ? $employeePayment->amount : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-5 pl-3 row pb-3">
                              <div class="col-12">
                              <label for="" class="">Payment Type</label>
                              </div>
                                <div class="col-md-4 d-flex align-items-center">
                                  <span>  Regular</span>
                                    <input type="radio" value="0" name="status" class="ml-2" {{(isset($employeePayment)) ? ($employeePayment->payment_type == 0 ? 'checked': '') : 'checked'}}>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <span>Advance/Loan</span>
                                    <input type="radio" value="1" name="status" class="ml-2" {{(isset($employeePayment)) ? ($employeePayment->payment_type == 1 ? 'checked': '') : ''}}>
                                </div>
                             
                            </div>
                            <div class="col-md-12">
                                <label for="exampleEmail11" class="">Notes</label>

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{isset($employeePayment) ? $employeePayment->note : ''}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($employeePayment)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
