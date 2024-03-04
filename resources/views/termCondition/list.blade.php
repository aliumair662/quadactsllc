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
                        <div>Term & Conditions
                            <div class="page-title-subheading">
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newTermCondition') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Visit">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            {{-- <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{ route('searchDailyVisit') }}" method="post">
                        @csrf
                        <div class="row no-gutters">
                            <div class="form-group col-sm-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ isset($from_date) ? $from_date : (isset($_GET['queries']['from']) ? $_GET['queries']['from_date'] : '') }}">
                            </div>
                            <div class="form-group col-sm-2 ml-1">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ isset($to_date) ? $to_date : (isset($_GET['queries']['to']) ? $_GET['queries']['to_date'] : '') }}">
                            </div>
                            <div class="form-group col-sm-2 pl-1">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">Invoice</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ isset($invoice_number) ? $invoice_number : (isset($_GET['queries']['invoice_number']) ? $_GET['queries']['invoice_number'] : '') }}"
                                    placeholder="Invoice No.">
                            </div>
                            <div class="form-group col-sm-2 pl-1 pt-1">
                                <div class="form-group">
                                    <label for="branch" class="">
                                        Salesman
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Salesman"
                                        name="user_id" id="user_id">
                                        <option value="">Select Salesman</option>
                                        @if (!empty($users))
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ isset($user_id) ? ($user->id == $user_id ? 'Selected' : '') : '' }}>
                                                    {{ $user->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-sm-2 pl-0 pt-1">
                                <div class="form-group">
                                    <label for="branch" class="">
                                        Status
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Status"
                                        name="status_id" id="status_id">
                                        <option value="">Select Status</option>
                                        @if (!empty($visit_status))
                                            @foreach ($visit_status as $status)
                                                <option value="{{ $status->id }}"
                                                    {{ isset($status_id) ? ($status->id == $status_id ? 'Selected' : '') : '' }}>
                                                    {{ $status->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-2 align-self-end ml-2 pb-3" style="margin-bottom: 1.1rem;">
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
            </div> --}}

            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Term & Conditions List
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
                                        <th class="text-center">T&C ID</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Created At</th>
                                        <th class="text-center">Updated At</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($term_condition))
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($term_condition as $list)
                                            <tr>
                                                <td class="text-center text-muted">{{ $i }}</td>
                                                <td class="text-center">{{ $list->t_c_number }}</td>
                                                <td class="text-center">{{ $list->name }}</td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($list->created_at)->format('d-m-Y h:i:s') }}
                                                </td>
                                                @if (!empty($list->updated_at))
                                                    <td class="text-center">
                                                        {{ \Carbon\Carbon::parse($list->updated_at)->format('d-m-Y h:i:s') }}
                                                    </td>
                                                @else
                                                    <td class="text-center">
                                                    </td>
                                                @endif
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
                                                            <a href="{{ route('editTermCondition', $list->id) }}"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Edit</button></a>
                                                            <a href="#"
                                                                onclick="deleteRecord('{{ route('deleteTermCondition', $list->id) }}');"><button
                                                                    type="button" tabindex="0"
                                                                    class="dropdown-item">Delete</button></a>
                                                            {{-- <a href="{{ route('dailyVisitRecordPdf', $list->id) }}"
                                                                target="_blank"><button type="button" tabindex="0"
                                                                    class="dropdown-item">PDF</button></a> --}}
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
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            <div>
                                {{ $term_condition->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
