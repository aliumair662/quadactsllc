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
                        <div>Update Company Info
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('companylist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Company List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data"
                    action="{{ isset($company) ? route('updatecompany') : route('savecompany') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Company Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Company Title</label>
                                    <input name="title" id="title" placeholder="company title" type="text"
                                        value="{{ isset($company) ? $company->title : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Phone No</label>
                                    <input name="phone" id="phone" placeholder="Phone No" type="text"
                                        value="{{ isset($company) ? $company->phone : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Company Address</label>
                                    <input name="address" id="adress"
                                        value="{{ isset($company) ? $company->address : '' }}" type="text"
                                        class="form-control">
                                    <input name="id" id="id"
                                        value="{{ isset($company) ? $company->id : '' }}" type="hidden"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Email</label>
                                    <input name="email" id="email" placeholder="with a placeholder" type="email"
                                        value="{{ isset($company) ? $company->email : '' }}" class="form-control">
                                </div>
                            </div>

                        </div>
                        {{-- <div class="form-row py-2 align-items-baseline">
                                    <div class="col-md-2">
                                    <label for="exampleEmail11" class=""><b>Stock Calculation:</b></label>
                                    </div>

                                        <div class="col-md-1 d-flex align-items-center">
                                            <span>Auto</span> <input value="0" name="status" type="radio" class="ml-2"{{(isset($company)) ? (($company->stock_calculation == 0) ? 'checked' : '') : '' }}>
                                        </div>
                                        <div class="col-md-1  d-flex align-items-center">
                                            <span>Manual</span> <input name="status" value="1" type="radio" class="ml-2" {{(isset($company)) ? (($company->stock_calculation == 1) ? 'checked' : '') : '' }}>
                                        </div>

                        </div> --}}
                        <div class="col-md-6">
                            <div class="form-row py-2 align-items-baseline">
                                <div class="col-md-5">
                                    <label for="exampleEmail11" class=""><b>Stock Calculation:</b></label>
                                </div>

                                <div class="col-md-3 d-flex align-items-center">
                                    <span>Auto</span> <input value="0" name="status" type="radio" class="ml-2"
                                        {{ isset($company) ? ($company->stock_calculation == 0 ? 'checked' : '') : '' }}>
                                </div>
                                <div class="col-md-3  d-flex align-items-center">
                                    <span>Manual</span> <input name="status" value="1" type="radio"
                                        class="ml-2"
                                        {{ isset($company) ? ($company->stock_calculation == 1 ? 'checked' : '') : '' }}>
                                </div>

                            </div>
                            <div class="form-row py-2 align-items-baseline">
                                <div class="col-md-5">
                                    <label for="exampleEmail11" class=""><b>Auto Print Invoice :</b></label>
                                </div>

                                <div class="col-md-3 d-flex align-items-center">
                                    <span>A4</span> <input value="0" name="auto_print_invoice" type="radio"
                                        class="ml-2"
                                        {{ isset($company) ? ($company->auto_print_invoice == 0 ? 'checked' : '') : '' }}>
                                </div>
                                <div class="col-md-3  d-flex align-items-center">
                                    <span>Thermal</span> <input name="auto_print_invoice" value="1" type="radio"
                                        class="ml-2"
                                        {{ isset($company) ? ($company->auto_print_invoice == 1 ? 'checked' : '') : '' }}>
                                </div>

                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Web</label>
                                    <input name="web" id="web" placeholder="with a placeholder"
                                        type="text" value="{{ isset($company) ? $company->web : '' }}"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="avatar" class="">Company Logo</label>
                                    <input name="logo" id="logo" type="file" class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if (isset($company))
                                    <div class="widget-content-left">
                                        <img width="100" class="rounded-circle" src="{{ $company->logo }}"
                                            alt="">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>


            </div>

            <div class="d-block text-center card-footer">
                <button type="submit"
                    class="mt-2 btn btn-primary">{{ isset($company) ? 'Update' : 'Register' }}</button>
            </div>
            </form>
        </div>

    </div>

    </div>

</x-app-layout>
