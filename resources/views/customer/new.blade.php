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
                        <div>New Customer
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Customers List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data"
                    action="{{ isset($customer) ? route('updatecustomer') : route('savecustomer') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Add Customer</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Name</label>
                                    <input name="name" id="text" placeholder="Enter Name" type="text"
                                        value="{{ isset($customer) ? $customer->name : '' }}" class="form-control">
                                    <input name="id" id="id"
                                        value="{{ isset($customer) ? $customer->id : '' }}" type="hidden"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Email</label>
                                    <input name="email" id="password" placeholder="Enter Email" type="email"
                                        value="{{ isset($customer) ? $customer->email : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Phone No</label>
                                    <input name="phone" id="phone"
                                        value="{{ isset($customer) ? $customer->phone : '' }}" type="text"
                                        class="form-control" placeholder="Enter Phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Address</label>
                                    <input name="address" id="address"
                                        value="{{ isset($customer) ? $customer->address : '' }}" type="text"
                                        class="form-control" placeholder="Address">
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

                    <div class="d-flex justify-content-end px-4 pb-3">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($customer) ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</x-app-layout>
