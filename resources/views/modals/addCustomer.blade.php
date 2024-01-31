<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    {{-- modal-dialog --}}
    <div class="modal-dialog" role="document">
        {{-- modal-content --}}
        <div class="modal-content">
            <div class="modal-body">
                {{-- <div class="main-card mb-3 card"> --}}
                <form class="Q-form" enctype="multipart/form-data" action="{{ route('savecustomer') }}" method="post">
                    @csrf
                    <div class="card-body">
                        {{-- <h5 class="card-title">Add Customer</h5> --}}
                        <div class="modal-header" style="background-color: white !important">
                            <h5 class="modal-title" id="exampleModalLabel">New Customer</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-row" style="padding-top: 1rem;">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Name</label>
                                    <input name="name" id="text" placeholder="Enter Name" type="text"
                                        value="" class="form-control">
                                    <input name="id" id="id" value="" type="hidden"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Email</label>
                                    <input name="email" id="password" placeholder="Enter Email" type="email"
                                        value="" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Phone No</label>
                                    <input name="phone" id="phone" value="" type="text"
                                        class="form-control" placeholder="Enter Phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Address</label>
                                    <input name="address" id="address" value="" type="text"
                                        class="form-control" placeholder="Address">
                                </div>
                            </div>
                            <div class="col-md-6" hidden>
                                <div class="position-relative form-group">
                                    <input name="customer_model" id="customer_model" value="1" type="text"
                                        class="form-control">
                                </div>
                            </div>
                        </div>


                        @if (isset($customer))
                            <div class="col-md-6 mt-4">
                                <label for="name" class="">Status</label>
                                <label class="switch">
                                    <input type="checkbox"
                                        {{ isset($customer) ? ($customer->status == 1 ? 'checked' : '') : '' }}
                                        value="1" name="status">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end px-4 pb-3 modal-footer"
                        style="background-color: white !important">
                        <button type="submit" class="mt-2 btn btn-primary">Save</button>
                    </div>
                </form>
                {{-- </div> --}}
            </div>
        </div>
    </div>
</div>
