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
                        <div>Income Statement
                            <div class="page-title-subheading">This is an example dashboard created using build-in
                                elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        {{-- <a href="{{ route('newPurchase') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Invoice">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a> --}}

                    </div>
                </div>
            </div>
            <div class="text-right">
                <a href="{{ route('incomeStatePdf') }}" class="btn btn-outline-success mb-2">Download PDF</a>

            </div>
            <div class="row justify-content-center mx-0 mb-2 pt-1">
                <div class="col-md-8 card">
                    <form action="{{ route('incomeStatementSearch') }}" method="post">
                        @csrf
                        <!-- <p style="font-size: 1.2rem;" class="mb-1">Search</p> -->
                        <div class="row no-gutters">
                            <div class="form-group col-4">
                                <label for="from_date" class="form-label" style="font-size: 1rem;">From</label>
                                <input type="date" name="from_date" class="form-control" value="{{ $from_date }}">
                            </div>
                            <div class="form-group col-md-4 mx-2">
                                <label for="to_date" class="form-label" style="font-size: 1rem;">To</label>
                                <input type="date" name="to_date" class="form-control" value="{{ $to_date }}">
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
            @if (!empty($allSalesAccounts))
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="main-card mb-3 card">
                            <div class="card-header">From Date : {{ $from_date }} To Date: {{ $to_date }}
                                <div class="btn-actions-pane-right">
                                    <div role="group" class="btn-group-sm btn-group">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-left">Account</th>
                                            <th class="text-left">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th class="text-left text-muted" colspan="2">Sales</th>
                                        </tr>
                                        @foreach ($allSalesAccounts as $account)
                                            <tr>
                                                <td class="text-left text-muted">{{ $account['name'] }}</td>
                                                <td class="text-left text-muted">{{ $account['balance'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="text-left text-muted">Total Sale</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $totalSale }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-left text-muted" colspan="2">Cost of Goods Sold</th>
                                        </tr>
                                        <tr>
                                            <td class="text-left text-muted font-weight-bold" colspan="2">Opening
                                                Stock</td>
                                        </tr>
                                        @foreach ($OpeningStockAccounts as $account)
                                            <tr>
                                                <td class="text-left text-muted">{{ $account['name'] }}</td>
                                                <td class="text-left text-muted">{{ $account['balance'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="text-left text-muted">Total Opening Stock</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $totalOpeningStock }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left text-muted font-weight-bold" colspan="2">Purchases
                                            </td>
                                        </tr>
                                        @foreach ($PurchasedStockAccounts as $account)
                                            <tr>
                                                <td class="text-left text-muted">{{ $account['name'] }}</td>
                                                <td class="text-left text-muted">{{ $account['balance'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="text-left text-muted">Total Purchases</td>
                                            <td class="text-left text-muted font-weight-bold">
                                                {{ $totalPurchasedStock }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left text-muted font-weight-bold" colspan="2">Closing
                                                Stock</td>
                                        </tr>
                                        @foreach ($closingStockAccounts as $account)
                                            <tr>
                                                <td class="text-left text-muted">{{ $account['name'] }}</td>
                                                <td class="text-left text-muted">{{ $account['balance'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="text-left text-muted">Total Closing Stock</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $totalClosingStock }}
                                            </td>
                                        </tr>


                                        <tr>
                                            <td class="text-left text-muted">Cost of Goods Sold Total</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $CostOfGoodsSold }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left text-muted font-weight-bold" colspan="2">Expenses
                                            </td>
                                        </tr>
                                        @foreach ($allExpensesAccounts as $account)
                                            @if ($account['balance'] > 0)
                                                <tr>
                                                    <td class="text-left text-muted">{{ $account['name'] }}</td>
                                                    <td class="text-left text-muted">{{ $account['balance'] }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        <tr>
                                            <td class="text-left text-muted">Total Expenses</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $totalExpense }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-left text-muted font-weight-bold">Net Profit/Loss</td>
                                            <td class="text-left text-muted font-weight-bold">{{ $netProfileLoss }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mr-3 card-footer">

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

</x-app-layout>
