<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Receipt</title>
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
</head>

<body>
    <div class="pt-5 mb-4">
        <table style="width: 100%;" collspacing="0" class="">
            <tr>
                <td style="width: 20%;" class="align-middle">
                    <div>
                        <img src="{{ $companyinfo->logo }}" style="object-fit: contain; width:100%;" alt=""
                            class="mb-4">
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
                <td class="align-middle" style="padding-left: 30%; padding-top:2rem;">
                    <p class="mb-1" style="font-family:Georgia, 'Times New Roman', Times, serif;">
                        <b>Verify Your Customer
                            Receipt</b>
                    </p>
                    <hr>
                    <img src="data:image/png;base64,{{ $qrCodeString }}">
                </td>
            </tr>
        </table>
    </div>
    <hr>
    <table style="width: 100%;" collspacing="0" class="">
        <tr>
            <td>
                <p class="mb-1"><b>Voucher #:</b> {{ $customer_receipt->voucher_number }}</p>
            </td>
            <td>
                <p class="mb-1"><b>Customer Name:</b> {{ $customer_receipt->name }}</p>
            </td>

            <td>
                <p class="mb-1"><b>Contact #:</b> {{ $customer_receipt->phone }}</p>

            </td>
        </tr>
        <tr>
            <td>
                <p class="mb-1"><b>Voucher Date:</b>
                    {{ \Carbon\Carbon::parse($customer_receipt->received_date)->format('d-m-Y') }}</p>

            </td>
            <td>
                <p class="mb-1"><b>Address:</b> {{ $customer_receipt->address }}</p>
            </td>
        </tr>
    </table>
    <h2 class="text-right"><b>Customer Receipt</b></h2>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th>#</th>
                <th>Name</th>
                <th>Voucher No.</th>
                <th>Check/Account Number</th>
                <th>Bank Name</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $customer_receipt->name }}</td>
                <td>{{ $customer_receipt->voucher_number }}</td>
                <td> {{ $customer_receipt->check_number }}</td>&nbsp;&nbsp;
                <td>{{ $customer_receipt->bank_name }}</td>&nbsp;&nbsp;
                <td>{{ $customer_receipt->amount }}</td>
            </tr>
            <tr>
                <td colspan="5">Net Total</td>
                <td>{{ !empty($customer_receipt->amount) ? $customer_receipt->amount . '.' . $currency_symbol : '' }}
                </td>
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

    {{-- <div class="mt-5">
        <div class="d-inline-block ">
            <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thank You for Your business!</p>
            <p><span style="font-size: 1.3rem; font-weight:900">Print Date:</span> @php echo date('d-m-Y'); @endphp</p>
        </div>
        <div class="d-inline-block float-right">
            <p style="border-top: 1px solid black;"><span class=""
                    style="font-size: 1.3rem; font-weight:900; ">Prepared by:</span>admin</p>
        </div>
    </div> --}}

    <div class="mt-5" style="position: fixed; bottom: 0; left: 0; width: 100%;">
        <div class="d-inline-block">
            {{-- <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thank You for Your business!</p> --}}
            <p><span style="font-size: 1.3rem; font-weight:900">Print Date:</span> @php echo date('d-m-Y'); @endphp</p>
        </div>
        <div class="d-inline-block float-right">
            <p class=""><b>System Generated, Doesn't Require Signature</b></p>
            <p style="border-top: 1px solid black;"><span class=""
                    style="font-size: 1.3rem; font-weight:900; ">Prepared by:</span> admin</p>
        </div>
    </div>
    <div
        style="position: fixed; bottom: -3rem; left: 0; width: 100%; align-items: center; justify-content: center; padding-left: 25%;">

        <p class="mb-0" style="font-size: 1.1rem; font-weight:900">Thank You for Your business!</p>

    </div>

</body>

</html>
