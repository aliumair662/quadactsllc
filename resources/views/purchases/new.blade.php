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
                        <div>Purchases
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('purchaseList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Purchase invoice list">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <!--HTML USED FOR CREATE NEW ROW -->
            <table style="display: none;">
                <tbody class="new_row">
                    <tr class="item_row">

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
                                placeholder="Price" value="" type="number" class="form-control item_price"
                                onchange="calculateInvoiceSum();"></td>
                        <td class="text-center d-none"><input name="item_pcs[]" id="item_qty" placeholder="Pcs"
                                value="" type="number" class="form-control item_pcs" onchange="pcsSum();"></td>
                        <td class="text-center" style="min-width: 150px;"><input name="item_qty[]" id="item_qty"
                                placeholder="Quantity" value="" type="number"
                                class="form-control item_qty item_qt" onchange="calculateInvoiceSum();qtySum();"></td>
                        <td class="text-center" style="min-width: 150px;"><input name="amount[]" id="amount"
                                placeholder="Total Amount" value="" type="number" class="form-control amount"
                                readonly></td>
                        <td><button class="btn btn-dark" type="button"
                                onclick="removeRow(this);calculateInvoiceSum();"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{ isset($purchase) ? route('updatePurchase') : route('savePurchase') }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="id" id="id"
                        value="{{ isset($purchase) ? $purchase->id : '' }}">
                    <div class="card-body">
                        <h5 class="card-title">Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice #</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                                        value="{{ isset($purchase) ? $purchase->invoice_number : Config::get('constants.PURCHASE_INVOICE_PREFIX') . $invoice_number }}"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice Date</label>
                                    <input name="invoice_date" id="invoice_date" placeholder="" type="date"
                                        value="{{ isset($purchase) ? $purchase->invoice_date : date('Y-m-d') }}"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Vendor
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Customer"
                                        name="vendor_id" id="customer_id" required>
                                        <option value="">Select vendor</option>
                                        @if (!empty($vendors))
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}"
                                                    {{ isset($purchase) ? ($purchase->vendor_id == $vendor->id ? 'Selected' : '') : '' }}>
                                                    {{ $vendor->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="position-relative form-group col-md-4">
                                    <label for="exampleEmail11" class="">Item Code</label>
                                    <input name="code" id="code" placeholder="" type="text"
                                        value="" class="form-control" autofocus>
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
                                            {{-- <th class="text-center">Pcs</th> --}}
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($purchase))
                                            @if (!empty(unserialize($purchase->items_detail)))
                                                @php
                                                    $i = 1;
                                                @endphp
                                                @foreach (unserialize($purchase->items_detail) as $invoiceItem)
                                                    <tr class="item_row">
                                                        <td class="text-center">{{ $i }}</td>
                                                        <td class="text-center text-muted">
                                                            <select class="js-example-basic-single form-control"
                                                                aria-placeholder="Select Item" name="item_id[]"
                                                                onchange="calculateInvoice();" required>
                                                                @if (!empty($items))
                                                                    @foreach ($items as $item)
                                                                        <option value="{{ $item->id }}"
                                                                            {{ isset($purchase) ? ($item->id == $invoiceItem['item_id'] ? 'Selected' : '') : '' }}>
                                                                            {{ $item->name }} </option>
                                                                    @endforeach
                                                                @endif

                                                            </select>
                                                        </td>
                                                        <td class="text-center"><input name="item_price[]"
                                                                id="item_price" placeholder="Price"
                                                                value="{{ isset($purchase) ? $invoiceItem['item_price'] : '' }}"
                                                                type="number" class="form-control item_price"
                                                                onchange="calculateInvoiceSum();"></td>
                                                        <td class="text-center d-none"><input name="item_pcs[]"
                                                                id="item_qty" placeholder="Pcs"
                                                                value="{{ isset($purchase) ? $invoiceItem['item_pcs'] : '' }}"
                                                                type="number" class="form-control item_pcs"
                                                                onchange="pcsSum();"></td>
                                                        <td class="text-center"><input name="item_qty[]"
                                                                id="item_qty" placeholder="Quantity"
                                                                value="{{ isset($purchase) ? $invoiceItem['item_qty'] : '' }}"
                                                                type="number" class="form-control item_qty item_qt"
                                                                onchange="calculateInvoiceSum();qtySum();"></td>
                                                        <td class="text-center"><input name="amount[]" id="amount"
                                                                placeholder="Total Amount"
                                                                value="{{ isset($purchase) ? $invoiceItem['amount'] : '' }}"
                                                                type="number" class="form-control amount" readonly>
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

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{ isset($purchase) ? $purchase->note : '' }}</textarea>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Qty</label>
                                    <input name="net_qty" id="qty_" placeholder="" type="number"
                                        value="{{ isset($purchase) ? $purchase->net_qty : '' }}" class="form-control"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-md-2 d-none">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net PCS</label>
                                    <input name="net_pcs" id="pcs" placeholder="" type="number"
                                        value="{{ isset($purchase) ? $purchase->net_pcs : '' }}" class="form-control"
                                        readonly>
                                </div>

                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="gross_amount" class="">Gross Amount</label>
                                    <input name="gross_amount" id="gross_amount" placeholder="" type="number"
                                        value="{{ isset($purchase) ? $purchase->gross_amount : '' }}"
                                        class="form-control" readonly>
                                </div>

                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-10"></div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Discount</label>
                                    <input name="discount_amount" id="discount_amount" placeholder="" type="number"
                                        value="{{ isset($purchase) ? $purchase->discount_amount : '' }}"
                                        class="form-control" onchange="calculateInvoiceSum();">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-10"></div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Total</label>
                                    <input name="net_total" id="net_total" placeholder="" type="number"
                                        value="{{ isset($purchase) ? $purchase->net_total : '' }}"
                                        class="form-control" readonly>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($purchase) ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
