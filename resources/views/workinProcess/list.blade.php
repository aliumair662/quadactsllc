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
                        <div>Work In Process
                            <div class="page-title-subheading">Current Work In Process Inventory List .
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newWorkingProcess') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add Work in process">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchWorkingProcess') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : '' }}">
                            </div>
                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : '' }}">
                            </div>
                            <div class="form-group col-2 ml-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Voucher</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : '' }}"
                                    placeholder="Voucher No.">
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
                        <div class="card-header">Work in process list
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
                                        <th class="text-center">Voucher Date</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Action</th>
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
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->voucher_date)->format('d-m-Y') }}
                                                </td>
                                                <td class="text-center">{{ $list->net_total }} </td>
                                                <td class="text-center">
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
                                                            <a href="{{ route('editWorkingProcess', $list->id) }}"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Edit</button></a>
                                                            <a href="#"
                                                                onclick="deleteRecord('{{ route('deleteWorkingProcess', $list->id) }}');"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Delete</button></a>
                                                        </div>
                                                    </div>
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
                        <div class="row justify-content-end no-gutters pr-4 align-items-baseline mt-3">
                            <div class="col-md-2 col-3 text-right pr-3">
                                <label for="" class="" style="font-weight: bold;">Total Amount:</label>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6">
                                <span
                                    style="display: inline-block; width: 100px; font-weight:600; border: 1px solid rgb(199, 197, 197); border-radius: 4px;"
                                    class="py-1 px-2">{{ isset($total) ? $total : '' }}</span>
                            </div>
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
                            {{ $lists->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
