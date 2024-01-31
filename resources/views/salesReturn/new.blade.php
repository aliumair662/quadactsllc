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
                        <div>Sales Return
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('saleReturnList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Sales Return Invoice List">
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
                            <select class="select-drop-down form-control" name="item_id[]" required>
                                <option value="">Select Item</option>
                                @if (!empty($items))
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}"> {{ $item->name }} </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="text-center" style="min-width: 150px;"><input name="item_price[]" id="item_price"
                                placeholder="Price" value="" type="text" class="form-control item_price"
                                onchange="calculateInvoiceSum();"></td>
                        <td class="text-center" style="min-width: 150px;"><input name="item_pcs[]" id="item_pcs"
                                placeholder="PCS" value="" type="text" class="form-control item_pcs"
                                onchange="pcsSum();"></td>
                        <td class="text-center" style="min-width: 150px;"><input name="item_qty[]" id="item_qty"
                                placeholder="Pcs" value="" type="text" class="form-control item_qty item_qt"
                                onchange="calculateInvoiceSum(); qtySum();"></td>
                        <td class="text-center" style="min-width: 150px;"><input name="amount[]" id="amount"
                                placeholder="Total Amount" value="" type="text" class="form-control amount"
                                readonly></td>
                        <td><button class="btn btn-dark" type="button"
                                onclick="removeRow(this);calculateInvoiceSum();"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form"
                    action="{{ isset($saleReturn) ? route('updateSaleReturn') : route('saveSaleReturn') }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="id" id="id"
                        value="{{ isset($saleReturn) ? $saleReturn->id : '' }}">
                    <div class="card-body">
                        <h5 class="card-title">Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice #</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                                        value="{{ isset($saleReturn) ? $saleReturn->invoice_number : Config::get('constants.SALE_INVOICE_RETURN_PREFIX') . $invoice_number }}"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice Date</label>
                                    <input name="invoice_date" id="invoice_date" placeholder="" type="date"
                                        value="{{ isset($saleReturn) ? $saleReturn->invoice_date : date('Y-m-d') }}"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Customer
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Customer"
                                        name="customer_id" id="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @if (!empty($customers))
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ isset($saleReturn) ? ($saleReturn->customer_id == $customer->id ? 'Selected' : '') : '' }}>
                                                    {{ $customer->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
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
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Pcs</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($saleReturn))
                                            @if (!empty(unserialize($saleReturn->items_detail)))
                                                @php
                                                    $i = 1;
                                                @endphp
                                                @foreach (unserialize($saleReturn->items_detail) as $invoiceItem)
                                                    <tr>
                                                        <td class="text-center">{{ $i }}</td>
                                                        <td class="text-center text-muted">
                                                            <select class="js-example-basic-single form-control"
                                                                aria-placeholder="Select Item" name="item_id[]"
                                                                onchange="calculateInvoice();" required>
                                                                @if (!empty($items))
                                                                    @foreach ($items as $item)
                                                                        <option value="{{ $item->id }}"
                                                                            {{ isset($saleReturn) ? ($item->id == $invoiceItem['item_id'] ? 'Selected' : '') : '' }}>
                                                                            {{ $item->name }} </option>
                                                                    @endforeach
                                                                @endif

                                                            </select>
                                                        </td>
                                                        <td class="text-center"><input name="item_price[]"
                                                                id="item_price" placeholder="Price"
                                                                value="{{ isset($saleReturn) ? $invoiceItem['item_price'] : '' }}"
                                                                type="text" class="form-control item_price"
                                                                onchange="calculateInvoiceSum();"></td>
                                                        <td class="text-center"><input name="item_pcs[]"
                                                                id="item_pcs" placeholder="PCS"
                                                                value="{{ isset($saleReturn) ? $invoiceItem['item_pcs'] : '' }}"
                                                                type="text" class="form-control item_pcs"
                                                                onchange="pcsSum();"></td>
                                                        <td class="text-center"><input name="item_qty[]"
                                                                id="item_qty" placeholder="Quantity"
                                                                value="{{ isset($saleReturn) ? $invoiceItem['item_qty'] : '' }}"
                                                                type="text" class="form-control item_qty item_qt"
                                                                onchange="calculateInvoiceSum(); qtySum();"></td>
                                                        <td class="text-center"><input name="amount[]" id="amount"
                                                                placeholder="Total Amount"
                                                                value="{{ isset($saleReturn) ? $invoiceItem['amount'] : '' }}"
                                                                type="text" class="form-control amount" readonly>
                                                        </td>

                                                    </tr>
                                                    @php
                                                        $i++;
                                                    @endphp
                                                @endforeach
                                            @endif
                                        @endif

                                        <tr class="btn-add-new">
                                            <td>
                                                <button class="btn btn-primary add_row" type="button"><i
                                                        class="fas fa-plus"></i></button>
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                    </tbody>
                                </table>





                            </div>
                        </div>


                        <div class="form-row">
                            <div class="col-md-8">
                                <label for="exampleEmail11" class="">Notes</label>

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{ isset($saleReturn) ? $saleReturn->note : '' }}</textarea>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Total</label>
                                    <input name="net_total" id="net_total" placeholder="" type="text"
                                        value="{{ isset($saleReturn) ? $saleReturn->net_total : '' }}"
                                        class="form-control" readonly>
                                </div>

                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net PCS</label>
                                    <input name="net_pcs" id="pcs" placeholder="" type="text"
                                        value="{{ isset($saleReturn) ? $saleReturn->net_pcs : '' }}"
                                        class="form-control" readonly>
                                </div>
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Qty</label>
                                    <input name="net_qty" id="qty_" placeholder="" type="text"
                                        value="{{ isset($saleReturn) ? $saleReturn->net_qty : '' }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($sale) ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
