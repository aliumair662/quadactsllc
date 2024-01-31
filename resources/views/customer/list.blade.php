<x-app-layout>

    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-users icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Customers
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newcustomer') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New User">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>


            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchCustomers') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">

                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">Search by Name</label>
                                <input type="text" name="customer_name" class="form-control"
                                    value="{{ isset($searchQuery) ? $searchQuery : (isset($_GET['queries']['name']) ? $_GET['queries']['name'] : '') }}">
                            </div>

                            <div class="col-2 align-self-end ml-2" style="margin-bottom: 1.1rem;">
                                <div class="page-title-actions">
                                    <a href="">
                                        <button type="submit" data-toggle="tooltip" title=""
                                            data-placement="bottom" class="btn-shadow btn btn-dark"
                                            data-original-title="Search">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Active Users
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-3">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="">#</th>
                                        @if (Auth::user()->is_admin === 1)
                                            <th class="text-center">User Name</th>
                                        @endif
                                        <th class="">Customer Name</th>
                                        <th class="">Email</th>
                                        <th class="">Phone</th>
                                        <th class="">Balance</th>
                                        <th class="">Address</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($customers))
                                        @foreach ($customers as $customer)
                                            <tr class="{{ $customer->status == 0 ? 'disabled' : '' }}">
                                                <td class="text-start text-muted">{{ $customer->id }}</td>
                                                @if (Auth::user()->is_admin === 1)
                                                    <td class="text-center text-muted"> {{ $customer->user_name }}</td>
                                                @endif
                                                <td>
                                                    <div class="widget-content p-0">
                                                        <div class="widget-content-wrapper">
                                                            <div class="widget-content-left">
                                                                <div class="widget-content-left">
                                                                    <img width="40" class="rounded-circle"
                                                                        src="" alt="">
                                                                </div>
                                                            </div>
                                                            <div class="widget-content-left flex2">
                                                                <div class="widget-heading">{{ $customer->name }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="">{{ $customer->email }}</td>
                                                <td class="">{{ $customer->phone }}</td>
                                                <td>{{ $customer->balance }}</td>
                                                <td class="" style="max-width: 150px;">{{ $customer->address }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($customer->status == 1)
                                                        <div class="mb-2 mr-2 btn-group">
                                                            <button class="btn btn-outline-success">Edit</button>
                                                            <button type="button" aria-haspopup="true"
                                                                aria-expanded="false" data-toggle="dropdown"
                                                                class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span
                                                                    class="sr-only">Toggle Dropdown</span>
                                                            </button>
                                                            <div tabindex="-1" role="menu" aria-hidden="true"
                                                                class="dropdown-menu" x-placement="bottom-start"
                                                                style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                                <a
                                                                    href="{{ route('ledger', $customer->general_ledger_account_id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Ledger</button></a>
                                                                <a href="{{ route('editcustomer', $customer->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Edit</button></a>
                                                                <a
                                                                    href="{{ route('deletecustomer', $customer->id) }}"><button
                                                                        type="button" tabindex="0"
                                                                        class="dropdown-item">Delete</button></a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($customer->status == 0)
                                                        <i class="fa fa-ban"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            <!-- <div class="col-lg-12">
                                <nav class="float-right" aria-label="Page navigation example">
                                    <ul class="pagination">
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Previous"><span aria-hidden="true">«</span><span class="sr-only">Previous</span></a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">1</a></li>
                                        <li class="page-item active"><a href="javascript:void(0);" class="page-link">2</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">3</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">4</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">5</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Next"><span aria-hidden="true">»</span><span class="sr-only">Next</span></a></li>
                                    </ul>
                                </nav>
                            </div> -->
                            {{ $customers->links() }}




                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
