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
                        <div>Employee
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newemployee') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Item">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{route('searchEmployee')}}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">

                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">Search by Name</label>
                                <input type="text" name="employee_name" class="form-control" value="{{(isset($employee_name)) ? $employee_name : ''}}">
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
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Employee List
                        </div>
                        <div class="table-responsive p-3">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Code</th>
                                    <th class="">Name</th>
                                    <th class="">CNIC</th>
                                    <th class="">Salary</th>
                                    <th>Balance</th>
                                    <th class="">Department</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($employee))
                                @foreach($employee as $emp)
                                <tr class="{{($emp->status == 0) ? 'disabled' : ''}}">
                                    <td class="text-left">{{$emp->id}}</td>
                                    <td class="text-left">{{$emp->code}}</td>
                                    <td>
                                        <div class="widget-content p-0">
                                            <div class="widget-content-wrapper">
                                                <div class="widget-content-left">

                                                </div>
                                                <div class="widget-content-left flex2">
                                                    <div class="widget-heading">{{$emp->name}}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="">{{$emp->cnic}}</td>
                                    <td class="">{{$emp->salery}}</td>
                                    <td>{{$emp->balance}}</td>
                                    <td class="">{{$emp->department_name}}</td>



                                    <td class="text-center">
                                        @if($emp->status == 1)
                                        <div class="mb-2 mr-2 btn-group">
                                        <button class="btn btn-outline-success">Edit</button>
                                           <button type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                <a href="{{route('ledger',$emp->general_ledger_account_id)}}"><button type="button" tabindex="0" class="dropdown-item">Ledger</button></a>
                                                <a href="{{route('editemployee',$emp->id)}}"><button type="button" tabindex="0" class="dropdown-item">Edit</button></a>
                                                <a href="#" onclick="deleteRecord('{{route('deleteemployee',$emp->id)}}');"><button type="button" tabindex="0" class="dropdown-item">Delete</button></a>
                                                <a href="{{route('employeeAdvance',$emp->id)}}"><button type="button" tabindex="0" class="dropdown-item">Advance history</button></a>
                                            </div>

                                        </div>
                                        @endif
                                        @if($emp->status == 0)
                                        <i class="fa fa-ban"></i>
                                        @endif

                                    </td>
                                </tr>

                                @endforeach
                                @endif

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
                            {{ $employee->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>

