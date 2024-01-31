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
                        <div>Add General Receipt
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('generalReceiptsList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="General Receipts List">
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


                                <select class="select-drop-down form-control" name="general_ledger_account_id[]" required>
                                    <option value="">Select Account</option>
                                    @if(!empty($accounts))
                                    @foreach($accounts as $account)
                                    <option value="{{$account->id}}"> {{$account->name}} </option>
                                    @endforeach
                                    @endif
                                </select>

                        </td>
                        <td class="text-center"><input name="description[]" id="description" placeholder="Description" value="" type="text" class="form-control description"></td>
                        <td class="text-center"><input name="amount[]" id="amount" placeholder="Amount" value="" type="number" class="form-control amountReceipt" onchange="calculateLedgerSum();"></td>
                        <td><button class="btn btn-dark" type="button" onclick="removeRow(this);calculateLedgerSum();"><i class="fas fa-times"></i></button></td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($generalReceipts)) ? route('updateGeneralReceipts') : route('saveLedgerReceipts')}}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{(isset($generalReceipts)) ? $generalReceipts->id : ''}}">
                    <div class="card-body">
                        <h5 class="card-title">Invoice Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher #</label>
                                    <input name="voucher_number" id="invoice_number" placeholder="" type="text" value="{{(isset($generalReceipts)) ? $generalReceipts->voucher_number : Config::get('constants.GENERAL_RECEIPT_PREFIX').$invoice_number}}" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher Date</label>
                                    <input name="voucher_date" id="invoice_date" placeholder="" type="date" value="{{(isset($generalReceipts)) ? $generalReceipts->voucher_date : date('Y-m-d')}}" class="form-control">
                                </div>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="table-responsive col-md-12">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Account</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Amount</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($generalReceipts))
                                        @if(!empty(unserialize($generalReceipts->voucher_detail)))
                                        @php
                                        $i=1;
                                        @endphp
                                        @foreach(unserialize($generalReceipts->voucher_detail) as $invoiceItem)
                                        <tr>
                                            <td class="text-center">{{$i}}</td>
                                            <td class="text-center text-muted">

                                                    <select class="js-example-basic-single form-control" name="general_ledger_account_id[]" aria-placeholder="Select Item" required>
                                                    @if(!empty($accounts))
                                                    @foreach($accounts as $account)
                                                    <option value="{{$account->id}}" {{(isset($generalReceipts)) ? ($invoiceItem['general_ledger_account_id'] == $account->id) ? 'Selected' : '' : ''}}> {{$account->name}} </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="text-center"><input name="description[]" id="description" placeholder="Description" value="{{(isset($generalReceipts)) ? $invoiceItem['description'] : ''}}" type="text" class="form-control item_price"></td>
                                            <td class="text-center"><input name="amount[]" id="amount" placeholder="Amount" value="{{(isset($generalReceipts)) ? $invoiceItem['amount'] : ''}}" type="number" class="form-control amountReceipt" onchange="calculateLedgerSum();"></td>
                                            <td><button class="btn btn-dark" type="button" onclick="removeRow(this);calculateLedgerSum();"><i class="fas fa-times"></i></button></td>
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
                                <label for="exampleEmail11" class="">Notes</label>

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{(isset($generalReceipts)) ? $generalReceipts->note : ''}}</textarea>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Net Total</label>
                                    <input name="net_total" id="net_total" placeholder="" type="text" value="{{(isset($generalReceipts)) ? $generalReceipts->net_total : ''}}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($sale)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
