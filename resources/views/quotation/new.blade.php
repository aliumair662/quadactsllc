<x-app-layout>
    <style>
        .pointer-none {
            pointer-events: none
        }
    </style>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-plus icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Quotations
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
                    <tr class="item_row">

                        <td class="text-center"><label class="sr_no">1</label></td>
                        <td class="text-center text-muted">
                            <select class="select-drop-down form-control itemData" name="item_id[]"
                                onchange="fetchingItemData(this);" required>
                                <option value="">Select Item</option>
                                @if (!empty($items))
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}"> {{ $item->code }} </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="text-center" style="min-width: 8rem;"><input name="item_name[]" id="item_name"
                                placeholder="Item Name" value="" type="text" class="form-control item_name"
                                readonly>
                        </td>
                        <td class="text-center" style="min-width: 8rem;"><input name="item_price[]" id="item_price"
                                placeholder="Price" value="" type="number" class="form-control item_price"
                                onchange="calculateInvoiceSum();calculateNetProfit();calculateProfitPercentage();"><input
                                name="item_purchase_price[]" id="item_purchase_price" placeholder="Purchase Price"
                                value="" type="number" class="form-control item_purchase_price" onchange=""
                                readonly></td>
                        <td class="text-center" style="min-width: 5rem;"><input name="item_qty[]" id="item_qty"
                                placeholder="Quantity" value="" type="number"
                                class="form-control item_qty item_qt"
                                onchange="calculateInvoiceSum(); qtySum();calculatePurchaseAmountSum();calculateNetProfit();calculateProfitPercentage();">
                        </td>
                        <td class="text-center" style="min-width: 8rem;"><input name="amount[]" id="amount"
                                placeholder="Total Amount" value="" type="number" class="form-control amount"
                                readonly><input name="total_purchase_amount[]" id="total_purchase_amount"
                                placeholder="Total Purchase Amount" value="" type="number"
                                class="form-control total_purchase_amount" readonly></td>
                        <td><button class="btn btn-dark" type="button"
                                onclick="removeRow(this);calculateInvoiceSum();calculatePurchaseAmountSum();calculateNetProfit();"><i
                                    class="fas fa-times"></i></button>
                        </td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->

            <div class="main-card mb-3 card">
                <form class="Q-form"
                    action="{{ isset($quotation) && isset($quotation->pkg_id) ? route('saveQuotation') : (isset($quotation) ? route('updateQuotation') : route('saveQuotation')) }}"
                    method="post">
                    @csrf
                    <input type="hidden" name="id" id="id"
                        value="{{ isset($quotation) ? $quotation->id : '' }}">
                    <div class="card-body">
                        <h5 class="card-title">Quotation Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Quotation Invoice #</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                                        value="{{ isset($quotation) ? $quotation->invoice_number : Config::get('constants.QUOTATION_INVOICE_PREFIX') . $invoice_number }}"
                                        class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Quotation Invoice Date</label>
                                    <input name="invoice_date" id="invoice_date" placeholder="" type="date"
                                        value="{{ isset($quotation) ? $quotation->invoice_date : date('Y-m-d') }}"
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
                                                    {{ isset($quotation) ? ($quotation->customer_id == $customer->id ? 'Selected' : '') : '' }}>
                                                    {{ $customer->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Policies
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Policy"
                                        name="policy_id" id="policy_id" onchange="fetchingTermConditionData(this);">
                                        <option value="">Select Policy</option>
                                        {{-- @if (!empty($term_conditions)) --}}
                                        @foreach ($term_conditions as $term_condition)
                                            <option value="{{ $term_condition->id }}">
                                                {{ $term_condition->name }} </option>
                                        @endforeach
                                        {{-- {{ isset($purchase) ? ($purchase->term_condition_id == $term_condition->t_c_number ? 'Selected' : '') : '' }} --}}
                                        {{-- @endif --}}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2" style="padding-top: 1.8rem;">
                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#exampleModal" data-whatever="@mdo">Add Customer</button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="position-relative form-group col-md-4">
                                    <label for="exampleEmail11" class="">Item Code</label>
                                    <input name="code" id="code"
                                        placeholder="Enter item's code to add in List" type="text" value=""
                                        class="form-control" autofocus>
                                </div>
                            </div>
                        </div>
                        {{-- && file_name == 'packages'  --}}
                        {{-- {{ Auth::user()->is_admin == 1 ? '' : 'pointer-none' }} --}}
                        <div class="">
                            <div class="form-row">
                                <div class="table-responsive col-md-12">
                                    <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th class="text-center">Item Code</th>
                                                <th class="text-center">Item Name</th>
                                                <th class="text-center">Price</th>
                                                {{-- <th class="text-center">PCS</th> --}}
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
                                                        <tr class="item_row">
                                                            <td class="text-center">{{ $i }}</td>
                                                            <td class="text-center text-muted"
                                                                style="min-width: 8rem;">
                                                                <select
                                                                    class="js-example-basic-single form-control itemData"
                                                                    aria-placeholder="Select Item" name="item_id[]"
                                                                    onchange="fetchingItemData(this);" required>
                                                                    @if (!empty($items))
                                                                        @foreach ($items as $item)
                                                                            <option value="{{ $item->id }}"
                                                                                {{ isset($quotation) ? ($item->id == $invoiceItem['item_id'] ? 'Selected' : '') : '' }}>
                                                                                {{ $item->code }} </option>
                                                                        @endforeach
                                                                    @endif

                                                                </select>
                                                            </td>
                                                            @foreach ($items as $item)
                                                                @if (!empty($items) && $item->id == $invoiceItem['item_id'])
                                                                    <td class="text-center" style="min-width: 8rem;">
                                                                        <input name="item_name[]" id="item_name"
                                                                            placeholder="Item Name"
                                                                            value="{{ isset($quotation) ? ($item->id == $invoiceItem['item_id'] ? $item->name : '') : '' }}"
                                                                            type="text"
                                                                            class="form-control item_name" readonly>
                                                                    </td>
                                                                @endif
                                                            @endforeach
                                                            <td class="text-center" style="min-width: 8rem;"><input
                                                                    name="item_price[]" id="item_price"
                                                                    placeholder="Price"
                                                                    value="{{ isset($quotation) ? $invoiceItem['item_price'] : '' }}"
                                                                    type="number" class="form-control item_price"
                                                                    onchange="calculateInvoiceSum();calculateNetProfit();calculateProfitPercentage();"><input
                                                                    name="item_purchase_price[]"
                                                                    id="item_purchase_price"
                                                                    placeholder="Purchase Price"
                                                                    value="{{ isset($quotation) ? $invoiceItem['item_purchase_price'] : '' }}"
                                                                    type="number"
                                                                    class="form-control item_purchase_price"
                                                                    onchange="" readonly></td>
                                                            <td class="text-center" style="min-width: 5rem;"><input
                                                                    name="item_qty[]" id="item_qty"
                                                                    placeholder="Quantity"
                                                                    value="{{ isset($quotation) ? $invoiceItem['item_qty'] : '' }}"
                                                                    type="number"
                                                                    class="form-control item_qty item_qt"
                                                                    onchange="qtySum();calculateInvoiceSum();calculatePurchaseAmountSum();calculateNetProfit();calculateProfitPercentage();">
                                                            </td>


                                                            <td class="text-center" style="min-width: 8rem;"><input
                                                                    name="amount[]" id="amount"
                                                                    placeholder="Total Amount"
                                                                    value="{{ isset($quotation) ? $invoiceItem['amount'] : '' }}"
                                                                    type="number" class="form-control amount"
                                                                    readonly>
                                                                <input name="total_purchase_amount[]"
                                                                    id="total_purchase_amount"
                                                                    placeholder="Total Purchase Amount"
                                                                    value="{{ isset($quotation) ? $invoiceItem['total_purchase_amount'] : '' }}"
                                                                    type="number"
                                                                    class="form-control total_purchase_amount"
                                                                    readonly>
                                                            </td>
                                                            <td><button class="btn btn-dark" type="button"
                                                                    onclick="removeRow(this);calculateInvoiceSum();calculatePurchaseAmountSum();calculateNetProfit();"><i
                                                                        class="fas fa-times"></i></button>
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
                                    <div id="toolbar">
                                        <label for="exampleEmail11" class="">Notes</label>
                                    </div>
                                    <div id="editor">
                                    </div>
                                    <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control"
                                        data-custom-value="" hidden>{{ isset($quotation) ? $quotation->note : '' }}</textarea>
                                </div>
                                <div class="col-md-2">

                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Net Qty</label>
                                        <input name="net_qty" id="qty_" placeholder="" type="number"
                                            value="{{ isset($quotation) ? $quotation->net_qty : '' }}"
                                            class="form-control" readonly>
                                    </div>
                                </div>
                                {{-- <div class="col-md-2">
                        <div class="position-relative form-group">
                            <label for="exampleEmail11" class="">Net PCS</label>
                            <input name="net_pcs" id="pcs" placeholder="" type="text" value="{{(isset($quotation)) ? $quotation->net_pcs : ''}}" class="form-control" readonly>
                        </div>
                    </div> --}}
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="gross_amount" class="">Total Amount</label>
                                        <input name="gross_amount" id="gross_amount" placeholder="" type="number"
                                            value="{{ isset($quotation) ? $quotation->gross_amount : '' }}"
                                            class="form-control" readonly>
                                    </div>

                                </div>

                            </div>
                            <div class="form-row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Total Purchase Amount</label>
                                        <input name="gross_purchase_amount" id="gross_purchase_amount" placeholder=""
                                            type="number"
                                            value="{{ isset($quotation) ? $quotation->gross_purchase_amount : '' }}"
                                            class="form-control"
                                            onchange="calculateInvoiceSum();calculateNetProfit();" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Discount</label>
                                        <input name="discount_amount" id="discount_amount" placeholder=""
                                            type="number"
                                            value="{{ isset($quotation) ? $quotation->discount_amount : '' }}"
                                            class="form-control"
                                            onchange="calculateInvoiceSum();calculateNetProfit();">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Total</label>
                                        <input name="net_total" id="net_total" placeholder="" type="number"
                                            value="{{ isset($quotation) ? $quotation->net_total : '' }}"
                                            class="form-control" readonly>
                                    </div>

                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Profit/Loss</label>
                                        <input name="net_profit" id="net_profit" placeholder="" type="number"
                                            value="{{ isset($quotation) ? $quotation->net_profit : '' }}"
                                            class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-10"></div>
                                <div class="col-md-2">
                                    <div class="position-relative form-group">
                                        <label for="exampleEmail11" class="">Profit %</label>
                                        <input name="profit_percent" id="profit_percent" placeholder=""
                                            type="number"
                                            value="{{ isset($quotation) ? $quotation->profit_percent : '' }}"
                                            class="form-control" readonly hidden>
                                        <p id="display_percent" class="form-control"
                                            style="color: white; width: 90%; text-align: center;">
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-9">
                                    <input name="html_semantic" id="html_semantic" placeholder="" type="text"
                                        value="{{ isset($purchase) ? $purchase->note_html : '' }}"
                                        class="form-control" hidden>
                                </div>
                            </div>
                        </div>
                        <div class="d-block text-center card-footer">
                            <button type="submit"
                                class="mt-2 btn btn-primary">{{ isset($quotation) && isset($quotation->pkg_id) ? 'Save' : (isset($quotation) ? 'Update' : 'Save') }}</button>
                        </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
<script>
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        ['blockquote', 'code-block'],
        // ['link', 'image', 'video', 'formula'],

        [{
            'header': 1
        }, {
            'header': 2
        }], // custom button values
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }, {
            'list': 'check'
        }],
        [{
            'script': 'sub'
        }, {
            'script': 'super'
        }], // superscript/subscript
        [{
            'indent': '-1'
        }, {
            'indent': '+1'
        }], // outdent/indent
        [{
            'direction': 'rtl'
        }], // text direction

        [{
            'size': ['small', false, 'large', 'huge']
        }], // custom dropdown
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],

        [{
            'color': []
        }, {
            'background': []
        }], // dropdown with defaults from theme
        [{
            'font': ['Times New Roman']
        }],
        [{
            'align': []
        }],

        ['clean'] // remove formatting button
    ];

    const quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });
    quill.on('text-change', (delta, oldDelta, source) => {
        if (source == 'user') {
            $('#note').val(JSON.stringify(quill.getContents()));
            const html = quill.getSemanticHTML();
            $('#html_semantic').val(html);
        }
        if (source == 'api') {
            $('#note').val(JSON.stringify(quill.getContents()));
            const html = quill.getSemanticHTML();
            $('#html_semantic').val(html);
        }
    });
    $(document).ready(function() {
        var profit_per_val = $('#profit_percent').val();
        if (profit_per_val != '' && profit_per_val >= 30) {
            $("#display_percent").css("background-color", "green");
            $("#display_percent").text(profit_per_val + " %");
        } else if (profit_per_val != '' && profit_per_val < 30) {
            $("#display_percent").css("background-color", "red");
            $("#display_percent").text(profit_per_val + " %");
        }

        quill.setContents(JSON.parse($('#note').val()));

        $('.Q-form').submit(function(event) {
            $('#note').data('customValue', quill.getContents());
            var customValue = $('#note').data('customValue');
            $('#note').val(JSON.stringify(customValue));
        });
    });
</script>
