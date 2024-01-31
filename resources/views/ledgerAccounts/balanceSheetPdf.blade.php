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
    <div class="w-100">

    <h3>Balance Sheet</h3>

        <div class="">
            <div class="mb-2"><b>Assets</b>
                <div class="">
                    <div role="group" class="">
                    </div>
                </div>
            </div>
            <div class="">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-warning">
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


        <div class="">


            <div class="mb-2"><b>Liabilities</b>
                <div class="">
                    <div role="group" class="">
                    </div>
                </div>
            </div>
            <div class="">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-warning">
                            <th class="">Account</th>
                            <th class="">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allLiabilities as $account)
                        @if($account->balance > 0)
                        <tr>
                            <td class="">{{$account->name}}</td>
                            <td class="">{{$account->balance}}</td>
                        </tr>
                        @endif
                        @endforeach
                        <tr>
                            <td class="">Total Liabilities</td>
                            <td class="">{{$allLiabilitiesTotal}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <div class="mb-2"><b>Equity</b>
        <div class="">
            <div role="group" class="">
            </div>
        </div>
    </div>
    <div class="">
        <table class="table table-bordered">
            <thead>
                <tr class="table-warning">
                    <th class="">Account</th>
                    <th class="">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allEquites as $account)
                @if($account->balance > 0)
                <tr>
                    <td class="">{{$account->name}}</td>
                    <td class="">{{$account->balance}}</td>
                </tr>
                @endif
                @endforeach
                <tr>
                    <td class="">Retained Earnings</td>
                    <td class="">{{$netProfileLoss}}</td>
                </tr>
                <tr>
                    <td class="">Total Equity</td>
                    <td class="">{{$allEquityTotal + $netProfileLoss}}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>

</html>