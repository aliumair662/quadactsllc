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
                        <div>Transaction Log
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('transationLogSearch') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-sm-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : (isset($_GET['queries']['from']) ? $_GET['queries']['from'] : '') }}">
                            </div>
                            <div class="form-group col-sm-2 ml-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : (isset($_GET['queries']['to']) ? $_GET['queries']['to'] : '') }}">
                            </div>

                            <div class="form-group col-sm-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Username</label>
                                <input type="text" name="username" class="form-control"
                                    value="{{ isset($customer_name) ? $customer_name : (isset($_GET['queries']['name']) ? $_GET['queries']['name'] : '') }}"
                                    placeholder="name">
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
                        <div class="card-header">Sale Invoice List
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Voucher #</th>
                                        <th class="text-center">User Name</th>
                                        <th class="text-center">Transaction Action</th>
                                        <th class="text-center">Transaction Type</th>
                                        <th class="text-center">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($lists))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($lists as $list)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center text-muted">{{ $list->voucher_number }}</td>
                                                <td class="text-center">{{ $list->name }}</td>
                                                <td class="text-center">{{ $list->transaction_action }} </td>
                                                <td class="text-center">{{ $list->transaction_type }}</td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->created_at)->format('d-m-Y') }}
                                                </td>

                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            {{ $lists->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
