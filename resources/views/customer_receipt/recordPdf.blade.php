<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
                        <p class="mb-1">Voucher #: {{ $customer_receipt->received_date }}</p>
                        <p class="mb-1">Voucher Date: {{ $customer_receipt->voucher_number }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th>#</th>
                <th>Name</th>
                <th>Voucher No.</th>
                <th>Check Number</th>
                <th>Bank Name</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $customer_receipt->name }}</td>
                <td>{{ \Carbon\Carbon::parse($customer_receipt->received_date)->format('d-m-Y') }}</td>
                <td> {{ $customer_receipt->check_number }}</td>&nbsp;&nbsp;
                <td>{{ $customer_receipt->bank_name }}</td>&nbsp;&nbsp;
                <td>{{ $customer_receipt->amount }}</td>
            </tr>
            <tr>
                <td colspan="5">Net Total</td>
                <td>{{ $customer_receipt->amount }}</td>
            </tr>
        </tbody>
    </table>
    <p><b>Payment Mode</b></p>
    @if ($customer_receipt->payment_mode == 1)
        <p class="mb-1"><b>Cash</b></p>
    @endif
    @if ($customer_receipt->payment_mode == 2)
        <p class="mb-1"><b>Online</b></p>
        <span>Check Number: &nbsp; {{ $customer_receipt->check_number }}</span> <br> <span>Bank Name: &nbsp;
            {{ $customer_receipt->bank_name }}</span>
    @endif
    @if ($customer_receipt->payment_mode == 3)
        <p class="mb-1"><b>Check</b></p>
        <span>Check Number: &nbsp; {{ $customer_receipt->check_number }}</span> <br> <span>Bank Name: &nbsp;
            {{ $customer_receipt->bank_name }}</span>
    @endif
    <p class="mb-1 mt-4"><b>Description:</b></p>
    <p>{{ $customer_receipt->note }}</p>

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
