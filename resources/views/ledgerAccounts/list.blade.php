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
                        <div>General Ledger Accounts
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newLedgerAccount') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add Account">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            <div class="row card mx-0 mb-2 pt-1">
                <div class="col-md-12">
                    <form action="{{route('searchGeneralAccounts')}}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">

                            <div class="form-group col-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">Search by Name</label>
                                <input type="text" name="ledger_name" class="form-control" value="{{(isset($searchQuery)) ? $searchQuery : ''}}">
                            </div>
                            <div class="form-group col-2 pl-1 pt-1 mb-0">
                                <div class="form-group">
                                    <label for="branch" class="">
                                        Search by Account Group
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Account Group" name="account_group" id="account_group">
                                        <option value="">Select Account Group</option>
                                        @if(!empty($chart_of_accounts))
                                        @foreach($chart_of_accounts as $chart_of_account)
                                        <option value="{{$chart_of_account->id}}"                          {{(isset($searchByAccountGroup)) ? ($chart_of_account->id==$searchByAccountGroup) ? 'Selected' : '' : ''}}> {{$chart_of_account->name}} </option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-2 align-self-end ml-2" style="margin-bottom: 1.1rem;">
                                <div class="page-title-actions">
                                    <a href="">
                                        <button type="submit" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow btn btn-dark" data-original-title="Search">
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
                        <div class="card-header">Ledger Accounts
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
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Account Group</th>
                                    <th class="text-center">Account Type</th>
                                    <th class="text-center">Acount Category</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($ledgerAccountsList))
                                @foreach($ledgerAccountsList as $list)
                                <tr class="{{($list->status == 0) ? 'disabled' : ''}}" >
                                    <td class="text-center text-muted">{{$list->id}}</td>
                                    <td class="text-center">{{$list->name}}</td>
                                    <td class="text-center">{{$list->chart_name}}</td>
                                    <td class="text-center">{{$list->ledger_account_name}}</td>
                                    <td class="text-center">{{$list->accounts_category_name}}</td>
                                    <td class="text-center">


                                        <div class="mb-2 mr-2 btn-group">
                                            <button class="btn btn-outline-success">Edit</button>
                                            <button type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                <a href="{{route('ledger',$list->id)}}"><button type="button" tabindex="0" class="dropdown-item">Ledger</button></a>
                                                @if($list->default_account == 0 || $list->status=1)
                                                <a href="{{route('editLedgerAccount',$list->id)}}" class="{{($list->default_account == 1) ? 'd-none' : ''}}"><button type="button" tabindex="0" class="dropdown-item">Edit</button></a>
                                                <a href="{{route('deleteLedgerAccount',$list->id)}}" class="{{($list->default_account == 1) ? 'd-none' : ''}}"><button type="button" tabindex="0" class="dropdown-item">Delete</button></a>
                                                @endif
                                            </div>
                                        </div>

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
                            {{ $ledgerAccountsList->links() }}
                        </div>
                    </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>

