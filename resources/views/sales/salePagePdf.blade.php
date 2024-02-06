<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice List</title>
    <!-- <link href="{{ asset('css/main.css') }}" rel="stylesheet"> -->
    <!-- <link href="{{ asset('css/custom.css') }}" rel="stylesheet"> -->
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
    <style>

    </style>
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
                <td style="width: 70%;padding-left:20px;">
                    <div class="d-inline-block" style="font-size: .9rem; width: 70%;">
                        <h2 style="font-family:Georgia, 'Times New Roman', Times, serif;">{{ $companyinfo->title }}</h2>
                        <p class="mb-1"><b>Address:</b>{{ $companyinfo->address }} </p>
                        <p class="mb-1"><b>Phone:</b>{{ $companyinfo->phone }}</p>
                        <p class="mb-1"><b>Email:</b>{{ $companyinfo->email }}</p>
                        <p class="mb-1"><b>URL:</b>{{ $companyinfo->web }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <p class="text-right"><b>Sale Invoice List</b></p>
    <div class="">

        <table class="table table-bordered" cellpadding="10px" cellspacing="10px">
            <thead class="">
                <tr class="table-warning">
                    <th>#</th>
                    <th>Voucher No.</th>
                    <th>Item Details</th>
                    <th>Customer Name</th>
                    <th>Net Total</th>
                    {{-- <th>Net Pcs</th> --}}
                    <th>Net Qty</th>
                    <th>Voucher Date</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($salelist))
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($salelist as $list)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $list->invoice_number }}</td>
                            <td>
                                @foreach ($list->items_detail as $item)
                                    {{ $item['item_name'] }} : {{ $item['item_qty'] }},
                                @endforeach
                            </td>
                            <td>{{ $list->customer_name }}</td>
                            <td>{{ $list->net_total }} </td>
                            {{-- <td>{{$list->net_pcs}} </td> --}}
                            <td>{{ $list->net_qty }} </td>
                            <td>{{ \Carbon\Carbon::parse($list->invoice_date)->format('d-m-Y') }}</td>

                        </tr>
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endif
                <tr>
                    <td colspan="6">Net Total</td>
                    <td>{{ !empty($net) ? $net . '.' . $currency_symbol : '' }}</td>

                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-5">
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
