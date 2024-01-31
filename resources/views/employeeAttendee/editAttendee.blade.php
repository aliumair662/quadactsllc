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
                        <div>Edit Attendee 
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Users List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data" action="{{route('updateAttendee')}}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Edit</h5>
                        <div class="form-row">
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher Number</label>
                                    <input name="voucher_number" id="text" placeholder="" type="text"" value="{{(isset($record)) ? $record->voucher_number : ''}}" class="form-control" readonly>
                                    <input name="id" id="id" value="{{(isset($record)) ? $record->id : ''}}" type="hidden" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Employee Name</label>
                                    <input name="name" id="text" placeholder="" type="text"" value="{{(isset($record)) ? $record->name : ''}}" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Date</label>
                                    <input name="voucher_date" id="text" placeholder="" type="text"" value="{{(isset($record)) ? $record->created_at : ''}}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Total Present</label>
                                    <input name="total_present" id="text" placeholder="" type="text"" value="{{(isset($record)) ? $record->total_present : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Total Absent</label>
                                    <input name="total_absent" id="text" placeholder="" type="text"" value="{{(isset($record)) ? $record->total_absent : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Total Leave</label>
                                    <input name="total_leave" id="password" placeholder="" type="text"" value="{{(isset($record)) ? $record->total_leave : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Half Days</label>
                                    <input name="half_days" id="password" placeholder="" type="text"" value="{{(isset($record)) ? $record->half_days : ''}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Holiday</label>
                                    <input name="holidays" id="phone" value="{{(isset($record)) ? $record->holiday : ''}}" type="text"" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Net Working Days</label>
                                    <input name="net_working_days" id="phone" value="{{(isset($record)) ? $record->net_working_days : ''}}" type="text"" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Basic Salary</label>
                                    <input name="basic_salary" id="address" value="{{(isset($record)) ? $record->basic_salary : ''}}" type="text"" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Total Salary</label>
                                    <input name="net_salary" id="address" value="{{(isset($record)) ? $record->net_salary : ''}}" type="text"" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end px-4 pb-3">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($record)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</x-app-layout>
