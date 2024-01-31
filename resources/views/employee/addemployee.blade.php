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
                        <div>New Employee
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('employeelist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Employee List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
 <!--HTML USED FOR CREATE NEW ROW -->
 <table   style="display: none">
                        <tbody class="new_row">
                        <tr>

                            <td class="text-center"><label class="sr_no">0</label></td>
                            <td class="text-center text-muted">
                                <select class="select-drop-down form-control" name="item_id[]" >
                                    <option value="">Select Item</option>
                                    @if(!empty($items))
                                        @foreach($items as $item)
                                            <option value="{{$item->id}}"> {{$item->name}}  </option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                            <td class="text-center d-flex"><input name="rate[]" id="rate" placeholder="Rate Per Pcs" value="{{(isset($employee)) ? '' : ''}}" type="text" class="form-control"></td>
                            <td class="text-center "><input name="additional_rate[]" id="additional_rate" placeholder="Additional Rate Per Pcs" value="{{(isset($employee)) ? '' : ''}}" type="text" class="form-control"></td>
                            <td><button class="btn btn-dark"  type="button" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
                       </tr>
                        <tbody>
                    </table>
                    <!--END OF HTML USED FOR CREATE NEW ROW -->
            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($employee)) ? route('updateemployee') : route('saveemployee')}}" method="post">
                    @csrf
                <div class="card-body"><h5 class="card-title">Employee Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Employee Code</label>
                                    <input name="code" id="code" placeholder="Employee Code" type="text" value="{{(isset($employee)) ? $employee->code : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Route" class="">Employee Name</label>
                                    <input name="name" id="name" placeholder="Employee Name" type="text" value="{{(isset($employee)) ? $employee->name : ''}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Phone</label>
                                    <input name="phone" id="phone" placeholder="Phone No" value="{{(isset($employee)) ? $employee->phone : ''}}" type="text" class="form-control">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">CNIC</label>
                                    <input name="cnic" id="cnic" placeholder="CNIC" value="{{(isset($employee)) ? $employee->cnic : ''}}" type="text" class="form-control">
                                    <input name="id" id="id" value="{{(isset($employee)) ? $employee->id : ''}}" type="hidden" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Salary</label>
                                    <input name="salery" id="salery" placeholder="Salary" value="{{(isset($employee)) ? $employee->salery : ''}}" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                            <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Department
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control"  name="department" id="department">
                                    @if(!empty($department))
                                    @foreach($department as $dep)
                                        <option value="{{$dep->id}}" {{(isset($employee)) ? ($employee->department == $dep->id) ? 'selected' : '' : 'selected'}}> {{$dep->name}}  </option>
                                        @endforeach
                                        @endif
                                    </select></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Advance Given</label>
                                    <input name="advance" id="salery" placeholder="Advance" value="{{(isset($employee)) ? $employee->advance : ''}}" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Production Method</label>
                                    <div class="pt-2">

                                        Group Production <input type="radio" value="0" class="mr-3" name="production_method" id="" {{(isset($employee)) ? ($employee->production_method ==0  ? 'checked' : '') : 'checked'}}>
                                        Single Production <input type="radio" value="1" name="production_method" id=""  {{(isset($employee)) ? ($employee->production_method ==1  ? 'checked' : '') : ''}}>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="position-relative form-group">
                                <label for="name" class="">Wages Type</label>
                                <div class="pt-2">
                                    Contractual  <input type="radio" value="0" class="mr-3" name="employee_type" id="" {{(isset($employee)) ? ($employee->employee_type ==0  ? 'checked' : '') : 'checked'}}>
                                    Salaried <input type="radio" value="1" name="employee_type" id=""  {{(isset($employee)) ? ($employee->employee_type ==1  ? 'checked' : '') : ''}}>
                                </div>
                            </div>
                        </div>

                    </div>
                <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Branch
                                        <a href="" title="Branch List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control"  name="branch" id="branch">
                                        <option value="1"  {{(isset($employee)) ? ($employee->branch == 1) ? 'selected' : '' : 'selected'}}>Default</option>
                                    </select></div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="pic" class="">Employee Picture</label>
                                    <input name="pic" id="pic" type="file" class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if(isset($employee))
                                    <div class="widget-content-left">
                                        <img width="100" class="rounded-circle" src="{{$employee->pic}}" alt="">
                                    </div>
                                @endif
                            </div>
                </div>

                <div class="form-row">
                <div class="table-responsive col-md-8">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover appended_table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-center">Production Rate</th>
                                    <th class="text-center">Additional Rate</th>

                                </tr>
                                </thead>
                                <tbody>
                                    @if(isset($employee))
                                        @php
                                            $i=1;
                                        @endphp
                                    @foreach($employee->employee_production_rates as $production_rate)
                                <tr>
                                    <td class="text-center">{{$i}}</td>
                                    <td class="text-center text-muted">
                                    <select class="js-example-basic-single form-control" name="item_id[]">
                                    @if(!empty($items))
                                    @foreach($items as $item)
                                    <option value="{{$item->id}}" {{(isset($employee->linked_items)) ? ($production_rate->itemid == $item->id) ? 'selected' : '' : 'selected'}}> {{$item->name}}  </option>
                                    @endforeach
                                        @endif
                                            </select>
                                    </td>
                                    <td class="text-center"><input name="rate[]" id="rate" placeholder="Rate Per Pcs" value="{{(isset($employee->employee_production_rates)) ? $production_rate->rate : ''}}" type="text" class="form-control"></td>
                                    <td class="text-center"><input name="additional_rate[]" id="additional_rate" placeholder="Additional Rate Per Pcs" value="{{(isset($employee->employee_production_rates)) ? $production_rate->additional_rate : ''}}" type="text" class="form-control"></td>
                                    <td><button class="btn btn-dark removeRow" type="button"><i class="fas fa-pencil-alt"></i></button></td>
                                </tr>
                                @php
                                    $i++;
                                @endphp
                                @endforeach
                               @endif
                                <tr class="btn-add-new">
                                <td>
                                    <button class="btn btn-primary add_row" type="button">+</button>
                                </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                </tr>
                                </tbody>
                            </table>
                        </div>
                </div>
                </div>
                <div class="d-block text-center card-footer">
                    <button type="submit" class="mt-2 btn btn-primary">{{(isset($employee)) ? 'Update' : 'Save'}}</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
