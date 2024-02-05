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
                        <div>Packages
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('quotationList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Quotation Invoice List">
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
                            <select class="select-drop-down form-control itemData" name="item_id[]"
                                onchange="fetchingItemData(this);" required>
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
                                onchange="calculateInvoiceSum();calculateNetProfit();"><input
                                name="item_purchase_price[]" id="item_purchase_price" placeholder="Purchase Price"
                                value="" type="text" class="form-control item_purchase_price" onchange=""
                                readonly></td>
                        <td class="text-center" style="min-width: 150px;"><input name="item_qty[]" id="item_qty"
                                placeholder="Quantity" value="" type="text"
                                class="form-control item_qty item_qt"
                                onchange="calculateInvoiceSum(); qtySum();calculatePurchaseAmountSum();calculateNetProfit();">
                        </td>
                        <td class="text-center" style="min-width: 150px;"><input name="amount[]" id="amount"
                                placeholder="Total Amount" value="" type="text" class="form-control amount"
                                readonly><input name="total_purchase_amount[]" id="total_purchase_amount"
                                placeholder="Total Purchase Amount" value="" type="text"
                                class="form-control total_purchase_amount" readonly></td>
                        <td><button class="btn btn-dark" type="button"
                                onclick="removeRow(this);calculateInvoiceSum();calculateNetProfit();"><i
                                    class="fas fa-times"></i></button>
                        </td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->

            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{ isset($quotation) ? route('updateQuotation') : route('savePackage') }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="id" id="id"
                        value="{{ isset($quotation) ? $quotation->id : '' }}">
                    <div class="card-body">
                        <h5 class="card-title">Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice #</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->invoice_number : Config::get('constants.PACKAGE_INVOICE_PREFIX') . $invoice_number }}"
                                        class="form-control">
                                    {{-- readonly --}}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Invoice Date</label>
                                    <input name="invoice_date" id="invoice_date" placeholder="" type="date"
                                        value="{{ isset($quotation) ? $quotation->invoice_date : date('Y-m-d') }}"
                                        class="form-control">
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
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($quotation))
                                            @if (!empty(unserialize($quotation->items_detail)))
                                                @php
                                                    $i = 1;
                                                @endphp
                                                @foreach (unserialize($quotation->items_detail) as $invoiceItem)
                                                    <tr>
                                                        <td class="text-center">{{ $i }}</td>
                                                        <td class="text-center text-muted">
                                                            <select
                                                                class="js-example-basic-single form-control itemData"
                                                                aria-placeholder="Select Item" name="item_id[]"
                                                                onchange="calculateInvoice();" required>
                                                                @if (!empty($items))
                                                                    @foreach ($items as $item)
                                                                        <option value="{{ $item->id }}"
                                                                            {{ isset($quotation) ? ($item->id == $invoiceItem['item_id'] ? 'Selected' : '') : '' }}>
                                                                            {{ $item->name }} </option>
                                                                    @endforeach
                                                                @endif

                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <input name="item_price[]" id="item_price"
                                                                placeholder="Price"
                                                                value="{{ isset($quotation) ? $invoiceItem['item_price'] : '' }}"
                                                                type="text" class="form-control item_price"
                                                                onchange="calculateInvoiceSum();calculateNetProfit();"><input
                                                                name="item_purchase_price[]" id="item_purchase_price"
                                                                placeholder="Purchase Price"
                                                                value="{{ isset($quotation) ? $invoiceItem['item_purchase_price'] : '' }}"
                                                                type="text"
                                                                class="form-control item_purchase_price"
                                                                onchange="" readonly>
                                                        </td>
                                                        <td class="text-center" style="min-width: 150px"><input
                                                                name="item_qty[]" id="item_qty"
                                                                placeholder="Quantity"
                                                                value="{{ isset($quotation) ? $invoiceItem['item_qty'] : '' }}"
                                                                type="text" class="form-control item_qty item_qt"
                                                                onchange="qtySum();calculateInvoiceSum();calculatePurchaseAmountSum();calculateNetProfit();">
                                                        </td>
                                                        <td class="text-center" style="min-width: 150px"><input
                                                                name="amount[]" id="amount"
                                                                placeholder="Total Amount"
                                                                value="{{ isset($quotation) ? $invoiceItem['amount'] : '' }}"
                                                                type="text" class="form-control amount"
                                                                readonly><input name="total_purchase_amount[]"
                                                                id="total_purchase_amount"
                                                                placeholder="Total Purchase Amount"
                                                                value="{{ isset($quotation) ? $invoiceItem['total_purchase_amount'] : '' }}"
                                                                type="text"
                                                                class="form-control total_purchase_amount" readonly>
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
                            <div class="col-md-6">
                                <label for="exampleEmail11" class="">Notes</label>
                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{ isset($quotation) ? $quotation->note : '' }}</textarea>
                            </div>
                            <div class="col-md-3">

                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Qty</label>
                                    <input name="net_qty" id="qty_" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->net_qty : '' }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="gross_amount" class="">Gross Amount</label>
                                    <input name="gross_amount" id="gross_amount" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->gross_amount : '' }}"
                                        class="form-control" readonly>
                                </div>

                            </div>

                        </div>
                        <div class="form-row">
                            <div class="col-md-9"></div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Gross Purchase Amount</label>
                                    <input name="gross_purchase_amount" id="gross_purchase_amount" placeholder=""
                                        type="text"
                                        value="{{ isset($quotation) ? $quotation->gross_purchase_amount : '' }}"
                                        class="form-control" onchange="calculateInvoiceSum();calculateNetProfit();"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-9"></div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Discount</label>
                                    <input name="discount_amount" id="discount_amount" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->discount_amount : '' }}"
                                        class="form-control" onchange="calculateInvoiceSum();calculateNetProfit();">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-9"></div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Total</label>
                                    <input name="net_total" id="net_total" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->net_total : '' }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-9"></div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Profit/Loss</label>
                                    <input name="net_profit" id="net_profit" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->net_profit : '' }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="d-block text-center card-footer">
                            <button type="submit"
                                class="mt-2 btn btn-primary">{{ isset($quotation) ? 'Update' : 'Save' }}</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
