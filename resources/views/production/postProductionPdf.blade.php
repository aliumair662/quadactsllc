<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRODUCTION INFORMATION</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        .table td {
            vertical-align: middle;
            padding: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="pt-5 mb-4">
        <table style="width: 100%;" collspacing="0" class="">
            <tr>
                <td style="width: 20%;" class="align-middle">
                    <div>
                        <img src="{{ $companyinfo->logo }}" style="object-fit: contain; width:100%; height: 100px;"
                            alt="" class="mb-4">
                    </div>
                </td>
                <td class="" style="width: 50%;">
                    <div class="ml-4">
                        <h3 style="font-family:Georgia, 'Times New Roman', Times, serif;">{{ $companyinfo->title }}</h3>
                        <p class="mb-1"><b>Address:</b>{{ $companyinfo->address }}</p>
                        <p class="mb-1"><b>Phone:</b>{{ $companyinfo->phone }}</p>
                        <p class="mb-1"><b>Email:</b>{{ $companyinfo->email }}</p>
                        <p class="mb-1"><b>URL:</b>{{ $companyinfo->web }}</p>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="ml-4">
                        <p class="mb-1">Employee Name : {{ $postProduction->employee_name }}</p>
                        <p class="mb-1">Voucher #: {{ $postProduction->voucher_number }}</p>
                        <p class="mb-1">Voucher Date:
                            {{ \Carbon\Carbon::parse($postProduction->voucher_date)->format('d-m-Y') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th>Sr#</th>
                <th>Item</th>
                <th>Rate</th>
                <th>Takan-Rate</th>
                <th>Quantity</th>
                <th>Amount</th>
            </tr>
        </thead>


        <tbody>
            @if (!empty($itemWisePorduction))
                @php
                    $i = 1;
                    $totalQty = 0;
                    $totalAmount = 0;
                @endphp
                @foreach ($itemWisePorduction as $production)
                    <tr>
                        <td> {{ $i }}</td>&nbsp;&nbsp;
                        <td> {{ $production['item_name'] }}</td>
                        &nbsp;&nbsp; <td> {{ $production['rate'] }}</td>&nbsp;&nbsp;
                        <td> {{ $production['additional_rate'] }}</td>
                        <td>{{ $production['item_qty'] }}</td>&nbsp;&nbsp;
                        <td>{{ $production['amount'] }}</td>
                    </tr>
                    @php
                        $totalQty += $production['item_qty'];
                        $totalAmount += $production['amount'];
                        $i++;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="4"> Total</td>&nbsp;&nbsp;
                    &nbsp;&nbsp; <td> {{ $totalQty }}</td>&nbsp;&nbsp;
                    <td> {{ $totalAmount }}</td>

                </tr>
                <tr>
                    <td colspan="5"> Final Production</td>&nbsp;&nbsp;
                    <td> {{ $postProduction->gross_total }}</td>

                </tr>
                <tr>
                    <td colspan="5">- Deduction</td>&nbsp;&nbsp;
                    <td> {{ $postProduction->deduction_amount }}</td>

                </tr>
                <tr>
                    <td colspan="5">+ Additional Amount</td>&nbsp;&nbsp;
                    <td> {{ $postProduction->additional_amount }}</td>

                </tr>
                <tr>
                    <td colspan="5"> Net Total</td>&nbsp;&nbsp;
                    <td> {{ $postProduction->net_total }}</td>

                </tr>
                <tr>
                    <td colspan="5"> Cash Paid</td>&nbsp;&nbsp;
                    <td> {{ $postProduction->cash_paid }}</td>

                </tr>
            @endif

        </tbody>
    </table>

    <div class="mt-5">
        <div class="d-inline-block ">
            <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thank You for Your business!</p>
            <p><span style="font-size: 1.3rem; font-weight:900">Print Date:</span> @php echo date('d-m-Y'); @endphp</p>
        </div>
        <div class="d-inline-block float-right">
            <p style="border-top: 1px solid black;"><span class=""
                    style="font-size: 1.3rem; font-weight:900; ">Prepared by:</span>admin</p>
        </div>
    </div>

</body>

</html>
