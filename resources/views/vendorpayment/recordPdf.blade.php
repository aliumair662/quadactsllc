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
                        <p class="mb-1">Voucher #:
                            {{ \Carbon\Carbon::parse($vendorpayment->received_date)->format('d-m-Y') }}</p>
                        <p class="mb-1">Voucher Date: {{ $vendorpayment->voucher_number }}</p>
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
                <td>{{ $vendorpayment->name }}</td>
                <td>{{ $vendorpayment->received_date }}</td>
                <td> {{ $vendorpayment->check_number }}</td>&nbsp;&nbsp;
                <td>{{ $vendorpayment->about_bank }}</td>&nbsp;&nbsp;
                <td>{{ $vendorpayment->amount }}</td>
            </tr>
            <tr>
                <td colspan="5">Net Total</td>
                <td>{{ $vendorpayment->amount }}</td>
            </tr>
        </tbody>
    </table>
    <p><b>Payment Mode</b></p>
    @if ($vendorpayment->payment_mode == 1)
        <p class="mb-1"><b>Cash</b></p>
    @endif
    @if ($vendorpayment->payment_mode == 2)
        <p class="mb-1"><b>Online</b></p>
        <span>Check Number: &nbsp; {{ $vendorpayment->check_number }}</span> <br> <span>Bank Name: &nbsp;
            {{ $vendorpayment->about_bank }}</span>
    @endif
    @if ($vendorpayment->payment_mode == 3)
        <p class="mb-1"><b>Check</b></p>
        <span>Check Number: &nbsp; {{ $vendorpayment->check_number }}</span> <br> <span>Bank Name: &nbsp;
            {{ $vendorpayment->bank_name }}</span>
    @endif
    <p class="mb-1 mt-4"><b>Description:</b></p>
    <p>{{ $vendorpayment->note }}</p>

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
