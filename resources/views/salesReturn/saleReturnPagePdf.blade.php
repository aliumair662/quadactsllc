<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- <link href="{{ asset('css/custom.css') }}" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>

    <div class="pt-4 d-table" style="width: 100%; vertical-align: middle;">

        <table style="width: 100%;">
            <tr>
                <td style="width: 25%;">
                    <div class="d-inline-block w-25">

                        <img src="{{ $companyinfo->logo }}" style="object-fit: contain;" height="100" alt=""
                            class="mb-4">
                    </div>
                </td>
                <td style="width: 70%; padding-left:20px;">
                    <div class="d-inline-block" style="font-size: .9rem; width: 70%;">
                        <h2 style="font-family:Georgia, 'Times New Roman', Times, serif;">{{ $companyinfo->title }}</h2>
                        <p class="mb-1"><b>Address:</b>{{ $companyinfo->address }}</p>
                        <p class="mb-1"><b>Phone:</b>{{ $companyinfo->phone }}</p>
                        <p class="mb-1"><b>Email:</b>{{ $companyinfo->email }}</p>
                        <p class="mb-1"><b>URL:</b>{{ $companyinfo->web }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <p class="text-right"><b>Sale Return Invoice List</b></p>
    <div class="">

        <table class="table table-bordered" cellpadding="10px" cellspacing="10px">
            <thead class="">
                <tr class="table-warning">
                    <th>#</th>
                    <th>Voucher No.</th>
                    <th>Customer Name</th>
                    <th>Net Total</th>
                    <th>Net Pcs</th>
                    <th>Net Qty</th>
                    <th>Voucher Date</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($saleReturnList))
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($saleReturnList as $list)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $list->invoice_number }}</td>
                            <td>{{ $list->customer_name }}</td>
                            <td>{{ $list->net_total }} </td>
                            <td>{{ $list->net_pcs }} </td>
                            <td>{{ $list->net_qty }} </td>
                            <td>{{ \Carbon\Carbon::parse($list->invoice_date)->format('d-m-Y') }}</td>

                        </tr>
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endif
                <tr>
                    <td colspan="4">Net Total</td>
                    <td>{{ $net }}</td>

                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <div class="d-inline-block ">
            <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thanks You for Your business!</p>
            <p><span style="font-size: 1.3rem; font-weight:900">Print Date:</span> @php echo date('d-m-Y'); @endphp</p>
        </div>
        <div class="d-inline-block float-right">
            <p style="border-top: 1px solid black;"><span class=""
                    style="font-size: 1.3rem; font-weight:900; ">Prepared by:</span>admin</p>
        </div>
    </div>

</body>

</html>
