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
                        <div>New Production
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('productionlist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Production List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($production)) ? route('updateProduction') : route('saveproduction')}}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{(isset($production)) ? $production->id : ''}}">
                <div class="card-body"><h5 class="card-title">Production Information</h5>
                        <div class="form-row">
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Production #</label>
                                    <input name="production_number" id="production_number" placeholder="" type="text" value="{{(isset($production)) ? $production->production_number : Config::get('constants.PRODUCTION_INVOICE_PREFIX').$production_number}}" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Production Date</label>
                                    <input name="production_date" id="production_date" placeholder="" type="date" value="{{(isset($production)) ? $production->production_date : ((isset($production_date)) ? $production_date : date('Y-m-d'))}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                            <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Production Item
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                <select class="js-example-basic-single form-control" aria-placeholder="Select Item" name="itemid" id="itemid" onchange="getItemEmployeeRates(this.value);" required>
                                    <option value=""> Select Item  </option>
                                    @if(!empty($items))
                                        @foreach($items as $item)
                                            <option value="{{$item->id}}" {{(isset($production)) ? ($production->itemid == $item->id) ? 'Selected' : '' : ''}}> {{$item->name}}  </option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Single Production  Employee
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" aria-placeholder="Select Employee" name="single_employee_id" id="single_employee_id"  onchange="getItemEmployeeRates($('#itemid').val());">
                                        <option value="" > Select Employee  </option>
                                        @if(!empty($employees))
                                            @foreach($employees as $employee)
                                                <option value="{{$employee->id}}" {{(isset($production)) ? ($production->employee_id == $employee->id) ? 'Selected' : '' : ''}}> {{$employee->name}}    </option>
                                            @endforeach
                                        @endif

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Production Qty</label>
                                    <input name="production_qty" id="production_qty" placeholder="" type="number" value="{{(isset($production)) ? $production->production_qty :0}}" class="form-control" onchange="updateProductionQty();">
                                </div>
                            </div>
                        </div>
                <div class="form-row">
                <div class="table-responsive col-md-12">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover" id="production_table">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Employee Name</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center">Additional Rate</th>
                                    <th class="text-center">Net Rate</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($production_details))
                                    @if(!empty($production_details))
                                        @php
                                            $i=1;
                                        @endphp
                                        @foreach($production_details as $detail)

                                            <tr>
                                                <td class="text-center">{{$i}}</td>
                                                <td class="text-center text-muted">
                                                    <input name="employee_name[]" id="employee_name" placeholder="Employee Name" value="{{$detail['employee_name']}}" type="text" class="form-control">
                                                    <input name="employee_id[]" id="employee_id" placeholder="employee_id" value="{{$detail['employee_id']}}" type="hidden" class="form-control">
                                                  </td>
                                             <td class="text-center"><input name="rate[]" id="rate" placeholder="Rate" value="{{(array_key_exists("rate",$detail)) ? $detail['rate'] : 0}}" type="text" class="form-control rate" readonly></td>
                                             <td class="text-center"><input name="additional_rate[]" id="additional_rate" placeholder="additional Rate" value="{{(array_key_exists("additional_rate",$detail)) ? $detail['additional_rate'] : 0}}" type="text" class="form-control additional_rate" readonly></td>
                                             <td class="text-center"><input name="item_price[]" id="item_price" placeholder="Rate" value="{{$detail['item_price']}}" type="text" class="form-control item_price" readonly></td>
                                         <td class="text-center"><input name="item_qty[]" id="item_qty" placeholder="Quantity" value="{{$detail['item_qty']}}" type="text" class="form-control item_qty" readonly></td>
                                        <td class="text-center"><input name="amount[]" id="amount" placeholder="Total Amount" value="{{$detail['amount']}}" type="text" class="form-control amount" readonly></td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @endif
                                @endif


                                </tbody>
                            </table>
                        </div>
                </div>
                    <div class="form-row">
                        <div class="col-md-10">
                            <label for="exampleEmail11" class="">Notes</label>

                            <textarea name="note" id="note" placeholder="" type="text"  class="form-control" >{{(isset($production)) ? $production->note :''}}</textarea>
                        </div>
                        <div class="col-md-2">
                            <div class="position-relative form-group">
                                <label for="exampleEmail11" class="">Net Total</label>
                                <input name="net_total" id="net_total" placeholder="" type="text" value="{{(isset($production)) ? $production->net_total : 0 }}" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-block text-center card-footer">
                    <button type="submit" class="mt-2 btn btn-primary">{{(isset($production)) ? 'Update' : 'Save'}}</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
