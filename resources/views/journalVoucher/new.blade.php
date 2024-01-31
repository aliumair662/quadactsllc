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
                        <div>Add Journal Voucher
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('journalVoucherList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Journal Voucher List">
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

                            <div class="form-group mb-0">
                                <select class="select-drop-down form-control" name="general_ledger_account_id[]" required>

                                    <option value="">Select Account</option>
                                    @if(!empty($accounts))
                                    @foreach($accounts as $account)
                                    <option value="{{$account->id}}"> {{$account->name}} </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </td>
                        <td class="text-center"><input name="description[]" id="item_price" placeholder="Description" value="" type="text" class="form-control item_price"></td>
                        <td class="text-center"><input name="amount[]" id="item_qty" placeholder="Credit" value="" type="number" class="form-control amountReceipt" onchange="calculateLedgerSum();"></td>
                        <td><input type="text" class="form-control "></td>
                    </tr>
                <tbody>
            </table>

            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($journalVoucher)) ? route('updateJournalVoucher') : route('saveJournalPayment')}}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{(isset($journalVoucher)) ? $journalVoucher->id : ''}}">
                    <div class="card-body">
                        <h5 class="card-title">Voucher Information</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher #</label>
                                    <input name="voucher_number" id="invoice_number" placeholder="" type="text" value="{{(isset($journalVoucher)) ? $journalVoucher->voucher_number : Config::get('constants.JOURNAL_VOUCHER').$invoice_number}}" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher Date</label>
                                    <input name="voucher_date" id="invoice_date" placeholder="" type="date" value="{{(isset($journalVoucher)) ? $journalVoucher->voucher_date : date('Y-m-d')}}" class="form-control">
                                </div>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="table-responsive col-md-12">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>

                                            <th class="text-center">Account</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Debit/Credit</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($journalVoucher))
                                        @if(!empty(unserialize($journalVoucher->voucher_detail)))
                                        @php
                                        $i=1;
                                        $invoiceItem=unserialize($journalVoucher->voucher_detail);
                                        $debit=array();
                                        $credit=array();
                                        if($invoiceItem[0]['debit'] > 0){
                                            $debit=$invoiceItem[0];
                                            $credit=$invoiceItem[1];
                                        }
                                        @endphp

                                            <tr>
                                                <td class="text-center text-muted">

                                                    <div class="form-group mb-0">
                                                        <select class="js-example-basic-single form-control" name="debit_general_ledger_account_id" required>

                                                            <option value="">Select Account</option>
                                                            @if(!empty($accounts))
                                                                @foreach($accounts as $account)
                                                                    <option value="{{$account->id}}" {{(isset($journalVoucher)) ? ($account->id == $debit['general_ledger_account_id']) ? 'Selected' : '' : ''}}> {{$account->name}} </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input name="debit_description" id="debit_description" placeholder="Description" value="{{$debit['note']}}" type="text" class="form-control debit_description"></td>
                                                <td><input type="number" name="debit" class="form-control debit" placeholder="Debit" value="{{$debit['debit']}}" ></td>
                                            </tr>
                                            <tr>


                                                <td class="text-center text-muted">

                                                    <div class="form-group mb-0">
                                                        <select class="js-example-basic-single form-control" name="credit_general_ledger_account_id" required>

                                                            <option value="">Select Account</option>
                                                            @if(!empty($accounts))
                                                                @foreach($accounts as $account)
                                                                    <option value="{{$account->id}}" {{(isset($journalVoucher)) ? ($account->id == $credit['general_ledger_account_id']) ? 'Selected' : '' : ''}}> {{$account->name}} </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input name="credit_description" id="credit_description" placeholder="Description" value="{{$credit['note']}}" type="text" class="form-control credit_description"></td>
                                                <td><input type="number" name="credit" class="form-control credit" placeholder="Credit" value="{{$credit['credit']}}"></td>
                                            </tr>





                                        @endif

                                        @else
                                        <tr>


                                            <td class="text-center text-muted">

                                                <div class="form-group mb-0">
                                                    <select class="js-example-basic-single form-control" name="debit_general_ledger_account_id" required>

                                                        <option value="">Select Account</option>
                                                        @if(!empty($accounts))
                                                        @foreach($accounts as $account)
                                                        <option value="{{$account->id}}"> {{$account->name}} </option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-center"><input name="debit_description" id="debit_description" placeholder="Description" value="" type="text" class="form-control debit_description"></td>
                                            <td><input type="number" name="debit" class="form-control debit" placeholder="Debit"  ></td>
                                        </tr>
                                        <tr>


                                            <td class="text-center text-muted">

                                                <div class="form-group mb-0">
                                                    <select class="js-example-basic-single form-control" name="credit_general_ledger_account_id" required>

                                                        <option value="">Select Account</option>
                                                        @if(!empty($accounts))
                                                            @foreach($accounts as $account)
                                                                <option value="{{$account->id}}"> {{$account->name}} </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-center"><input name="credit_description" id="credit_description" placeholder="Description" value="" type="text" class="form-control credit_description"></td>
                                            <td><input type="number" name="credit" class="form-control credit" placeholder="Credit" ></td>
                                        </tr>
                                        @endif



                                    </tbody>
                                </table>





                            </div>
                        </div>



                    </div>
                    <div class="form-row px-3 pb-3">
                            <div class="col-md-10">
                                <label for="exampleEmail11" class="">Notes</label>

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{(isset($journalVoucher)) ? $journalVoucher->note : ''}}</textarea>
                            </div>

                        </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($journalVoucher)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
