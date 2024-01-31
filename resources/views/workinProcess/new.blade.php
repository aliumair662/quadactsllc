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
                        <div>Work In Process
                            <div class="page-title-subheading">Move Inventory From Raw Meterial to Work In Process .
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('workProcessList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Work In Process List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <!--HTML USED FOR CREATE NEW ROW -->
            <table style="display: none;">
                <tbody class="new_row">
                    <tr>

                        <td class="text-center"><label class="sr_no">1</label></td>
                        <td class="text-center text-muted">
                            <select class="select-drop-down form-control" id="check_select" name="item_id[]" required>
                                <option value="">Select Item</option>
                                @if(!empty($items))
                                @foreach($items as $item)
                                <option value="{{$item->id}}"> {{$item->name}} </option>
                                @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="text-center"><input name="item_price[]" id="item_price" placeholder="Price" value="" type="text" class="form-control item_price" onchange="calculateInvoiceSum();"></td>
                        <td class="text-center"><input name="item_qty[]" id="item_qty" placeholder="Quantity" value="" type="number" class="form-control item_qty" onchange="calculateInvoiceSum();"></td>
                        <td class="text-center"><input name="amount[]" id="amount" placeholder="Total Amount" value="" type="number" class="form-control amount" readonly></td>
                        <td><button class="btn btn-dark" type="button" onclick="removeRow(this);calculateInvoiceSum();"><i class="fas fa-times"></i></button></td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->

            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($record)) ? route('updateWorkingProcess') : route('saveWorkingProcess')}}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{(isset($record)) ? $record->id : ''}}">
                    <div class="card-body">
                        <h5 class="card-title">Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice #</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text" value="{{(isset($record)) ? $record->voucher_number : Config::get('constants.WORKING_PROCESS_INVENTORY').$invoice_number}}" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice Date</label>
                                    <input name="invoice_date" id="invoice_date" placeholder="" type="date" value="{{(isset($record)) ? $record->voucher_date : date('Y-m-d')}}" class="form-control">
                                </div>
                            </div>


                        </div>
                        <div class="form-row">
                            <div class="table-responsive col-md-12">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Items</th>
                                            <th class="text-center">Purchase Price</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($record))
                                        @if(!empty(unserialize($record->item_detail)))
                                        @php
                                        $i=1;
                                        @endphp
                                        @foreach(unserialize($record->item_detail) as $invoiceItem)
                                        <tr>
                                            <td class="text-center">{{$i}}</td>
                                            <td class="text-center text-muted">
                                                <select class="js-example-basic-single form-control" id="check_select" aria-placeholder="Select Item" name="item_id[]" onchange="calculateInvoice();" required>
                                                    @if(!empty($items))
                                                    @foreach($items as $item)
                                                    <option value="{{$item->id}}" {{(isset($record)) ? ($item->id == $invoiceItem['item_id']) ? 'Selected' : '' : ''}}> {{$item->name}} </option>
                                                    @endforeach
                                                    @endif

                                                </select>
                                            </td>
                                            <td class="text-center"><input name="item_price[]" id="item_price" placeholder="Price" value="{{(isset($record)) ? $invoiceItem['item_price'] : ''}}" type="text" class="form-control item_price" onchange="calculateInvoiceSum();"></td>
                                            <td class="text-center"><input name="item_qty[]" id="item_qty" placeholder="Quantity" value="{{(isset($record)) ? $invoiceItem['item_qty'] : ''}}" type="number" class="form-control item_qty" onchange="calculateInvoiceSum();"></td>
                                            <td class="text-center"><input name="amount[]" id="amount" placeholder="Total Amount" value="{{(isset($record)) ? $invoiceItem['amount'] : ''}}" type="number" class="form-control amount"></td>

                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                        @endif
                                        @endif

                                        <tr class="btn-add-new">
                                            <td>
                                                <button class="btn btn-primary add_row" type="button"><i class="fas fa-plus"></i></button>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                    </tbody>
                                </table>





                            </div>
                        </div>


                        <div class="form-row">
                            <div class="col-md-10">
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Total</label>
                                    <input name="net_total" id="net_total" placeholder="" type="text" value="{{(isset($record)) ? $record->net_total : ''}}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($record)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
