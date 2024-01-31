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
                        <div>Vendor Payment
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{route('vendorpaymentlist')}}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Payment List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>



            <!--END OF HTML USED FOR CREATE NEW ROW -->



            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{isset($vendorpayment) ? route('updatevendorpayment'): route('savevendorpayment')}}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">{{isset($vendorpayment) ? 'Edit Vendor Payment' : 'Add Vendor Payment'}}</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Voucher No#</label>
                                    <input name="voucher_number" id="invoice_number" placeholder="" type="text" value="{{(isset($vendorpayment)) ? $vendorpayment->voucher_number : Config::get('constants.VENDOR_PAYMENT_PREFIX').$voucher_no}}" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Date</label>
                                    <input name="voucher_date" id="invoice_date" placeholder="" type="date" value="{{isset($vendorpayment) ? $vendorpayment->received_date : date('Y-m-d')}}" class="form-control">
                                </div>
                                <input type="hidden" name="id" value="{{isset($vendorpayment) ? $vendorpayment->id : ''}}">
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Vendor
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Customer" name="vendor_id" id="customer_id">
                                        <option disabled selected>Select</option>
                                        @if(!empty($vendors))
                                        @foreach($vendors as $vendor)
                                        <option value="{{$vendor->id}}" {{isset($vendorpayment) ? $vendorpayment->vendor == $vendor->id ? 'selected':'':''}}> {{$vendor->name}} </option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <p style="font-size: 1rem; font-weight:500;" class="mt-2">Payment Mode</p>
                        <div class="form-row">
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="d-flex align-items-baseline col-md-2">
                                        <label for="cash" class="mr-2 pb-3" style="font-weight: 500; display:inline-block;">Cash</label>
                                        <input type="radio" class="cash" name="payment_mode"style="position: relative; top:2px;" value="1" {{isset($vendorpayment) ? $vendorpayment->payment_mode == 1 ? 'checked' : '' : 'checked'}}>
                                    </div>
                                    <div class="d-flex align-items-baseline col-md-2">
                                        <label for="cash" class="mr-2 pb-3" style="font-weight: 500; display:inline-block;">Online</label>
                                        <input type="radio" name="payment_mode" class="bank_check_toggle" style="position: relative; top:2px;" value="2" {{isset($vendorpayment) ? $vendorpayment->payment_mode == 2 ? 'checked': '': ''}}>
                                    </div>
                                    <div class="d-flex align-items-baseline col-md-2">
                                        <label for="cash" class="mr-2 pb-3" style="font-weight: 500; display:inline-block;">Check</label>
                                        <input type="radio" name="payment_mode" class="bank_check_toggle" style="position: relative; top:2px;" value="3" {{isset($vendorpayment) ? $vendorpayment->payment_mode == 3 ? 'checked': '': ''}}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs -->
                        <div class="form-row"  id="show_hide_inps" style="display:{{isset($vendorpayment) ? $vendorpayment->payment_mode !== 1 ? 'flex': 'none' : 'none'}}">
                            <div class="form-group col-md-5">
                                <label for="check_number" class="form-label">Check Number</label>
                                <input type="text" class="form-control check_number" name="check_number" value="{{isset($vendorpayment) ? $vendorpayment->check_number : ''}}">
                            </div>
                            <div class="form-group col-md-5">
                                <label for="bank_name" class="form-label">About Bank</label>
                                <input type="text" class="form-control bank_name" name="bank_name" value="{{isset($vendorpayment) ? $vendorpayment->about_bank : ''}}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-5">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Amount</label>
                                    <input name="amount" id="net_total" placeholder="" type="text" value="{{isset($vendorpayment) ? $vendorpayment->amount : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="exampleEmail11" class="">Notes</label>

                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control">{{isset($vendorpayment) ? $vendorpayment->note : ''}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($vendorpayment)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
