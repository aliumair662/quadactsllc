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
                        <div>Balance Sheet
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                       {{-- <a href="{{ route('newPurchase') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Invoice">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>--}}

                    </div>
                </div>
            </div>
            <div class="text-right">
                <a href="{{route('balancePdf')}}" class="btn btn-outline-success mb-2">Download PDF</a>

            </div>
            <div class="rowcard mx-0 mb-2 pt-1">
                <div class="col-md-12 card">
                    <form action="{{route('balanceListSearch')}}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-2">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control" value="{{$fromdate}}">
                            </div>
                            <div class="form-group col-md-2 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control" value="{{$todate}}">
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
                <div class="col-md-6">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Assets
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left font-weight-bold">Account</th>
                                    <th class="text-left font-weight-bold">Balance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($allAssets as $account)
                                    @if($account->balance > 0)
                                <tr>
                                    <td class="text-left text-muted">{{$account->name}}</td>
                                    <td class="text-left text-muted">{{$account->balance}}</td>
                                </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td class="text-left text-muted">Total Assets</td>
                                    <td class="text-left text-muted font-weight-bold">{{$allAssetsTotal}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Liabilities
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left font-weight-bold">Account</th>
                                    <th class="text-left font-weight-bold">Balance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($allLiabilities as $account)
                                    @if($account->balance > 0)
                                    <tr>
                                        <td class="text-left text-muted">{{$account->name}}</td>
                                        <td class="text-left text-muted">{{$account->balance}}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td class="text-left text-muted">Total Liabilities</td>
                                    <td class="text-left text-muted font-weight-bold">{{$allLiabilitiesTotal}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                        </div>
                    </div>
                    <div class="main-card mb-3 card">
                        <div class="card-header">Equity
                            <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left font-weight-bold">Account</th>
                                    <th class="text-left font-weight-bold">Balance</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($allEquites as $account)
                                    @if($account->balance > 0)
                                        <tr>
                                            <td class="text-left text-muted">{{$account->name}}</td>
                                            <td class="text-left text-muted">{{$account->balance}}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr>
                                    <td class="text-left text-muted">Retained Earnings</td>
                                    <td class="text-left text-muted">{{$netProfileLoss}}</td>
                                </tr>
                                <tr>
                                    <td class="text-left text-muted">Total Equity</td>
                                    <td class="text-left text-muted font-weight-bold">{{$allEquityTotal + $netProfileLoss}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
