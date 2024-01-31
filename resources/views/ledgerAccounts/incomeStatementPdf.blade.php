<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    @if(!empty($allSalesAccounts))

    <h3>Income Statement</h3>
    <div class="mb-2">From Date : {{$from_date}} To Date: {{$to_date}}
    </div>

    <div class="">
        <table class="table table-bordered">
            <thead>
                <tr class="table-warning">
                    <th class="">Account</th>
                    <th class="">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="" colspan="2">Sales</th>
                </tr>
                @foreach($allSalesAccounts as $account)
                <tr>
                    <td class="">{{$account['name']}}</td>
                    <td class="d">{{$account['balance']}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="">Total Sale</td>
                    <td class="">{{$totalSale}}</td>
                </tr>
                <tr>
                    <th class="" colspan="2">Cost of Goods Sold</th>
                </tr>
                <tr>
                    <th class="" colspan="2">Opening Stock</th>
                </tr>
                @foreach($OpeningStockAccounts as $account)
                <tr>
                    <td class="">{{$account['name']}}</td>
                    <td class="">{{$account['balance']}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="">Total Opening Stock</td>
                    <td class="">{{$totalOpeningStock}}</td>
                </tr>
                <tr>
                    <th class="" colspan="2">Purchases</th>
                </tr>
                @foreach($PurchasedStockAccounts as $account)
                <tr>
                    <td class="">{{$account['name']}}</td>
                    <td class="">{{$account['balance']}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="">Total Purchases</td>
                    <td class="">{{$totalPurchasedStock}}</td>
                </tr>
                <tr>
                    <th class="" colspan="2">Closing Stock</th>
                </tr>
                @foreach($closingStockAccounts as $account)
                <tr>
                    <td class="">{{$account['name']}}</td>
                    <td class="">{{$account['balance']}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="">Total Closing Stock</td>
                    <td class="">{{$totalClosingStock}}</td>
                </tr>


                <tr>
                    <td class="">Cost of Goods Sold Total</td>
                    <td class="">{{$CostOfGoodsSold}}</td>
                </tr>
                <tr>
                    <th class="" colspan="2">Expenses</th>
                </tr>
                @foreach($allExpensesAccounts as $account)
                @if($account['balance'] > 0)
                <tr>
                    <td class="">{{$account['name']}}</td>
                    <td class="">{{$account['balance']}}</td>
                </tr>
                @endif
                @endforeach
                <tr>
                    <td class="">Total Expenses</td>
                    <td class="">{{$totalExpense}}</td>
                </tr>
                <tr>
                    <td class="">Net Profit/Loss</td>
                    <td class="">{{$netProfileLoss}}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
</body>

</html>