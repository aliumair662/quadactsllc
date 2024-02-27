<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice</title>
    <link href="{{ asset('css/thermal.css') }}" rel="stylesheet">
    {{-- <link href="{{ asset('css/pdf.css') }}" rel="stylesheet"> --}}
</head>
<style>
    html {
        font-size: 60%;
    }

    .thermal-pdf-table {
        text-align: center;

    }

    .thermal-pdf-heading-table__container {
        text-align: center;
        width: 100%;
        padding-left: 3rem;
    }

    .fbr-container {
        padding-left: 3rem;
    }
</style>

<body>
    <div class="thermal-pdf-heading-table__container">
        <table class="thermal-pdf-table" style="padding-left: 10%;" collspacing="0" class="" border="0">
            <tr>
                <td class="w-100">
                    <img src="{{ $companyinfo->logo }}" alt="" class="mb-4 company-logo">
                </td>
            </tr>
            <tr>
                <td class="w-100">
                    <div class="">
                        <h3>{{ $companyinfo->title }}</h3>
                        <p class="mb-1"><b>Address:</b>{{ $companyinfo->address }}</p>
                        <p class="mb-1"><b>Phone:</b>{{ $companyinfo->phone }}</p>
                        <p class="mb-1"><b>Email:</b>{{ $companyinfo->email }}</p>
                        <p class="mb-1"><b>URL:</b>{{ $companyinfo->web }}</p>
                        {{-- <p class="mb-1"><b>NTN #:</b>{{ $companyinfo->ntn_no }}</p>
                        <p class="mb-1"><b>Sale Tax #:</b>{{ $companyinfo->sales_tax_no }}</p> --}}
                        <hr>
                        <p class="mb-1"><b>Salesman Name:</b> {{ $sale->sale_user_name }}</p>
                        <p class="mb-1"><b>Invoice #:</b> {{ $sale->invoice_number }}</p>

                        <p class="mb-1"><b>Customer Name:</b> {{ $sale->name }}</p>

                        <p class="mb-1"><b>Contact #:</b> {{ $sale->phone }}</p>

                        <p class="mb-1"><b>Invoice Date:</b> {{ $sale->invoice_date }}</p>
                        <p class="mb-1"><b>Address:</b> {{ $sale->address }}</p>
                        {{-- <p class="mb-1"><b>Invoice Date :</b>{{ $sale->invoice_date }}</p>
                        @if (!empty($sale->customer_name))
                            <p class="mb-1"><b>Customer Name:</b> {{ $sale->customer_name }}</p>
                        @endif
                        @if (!empty($sale->cnic_no))
                            <p class="mb-1"><b>Cnic #:</b> {{ $sale->cnic_no }}</p>
                        @endif
                        @if (!empty($sale->ntn_no))
                            <p class="mb-1"><b>NTN #:</b> {{ $sale->ntn_no }}</p>
                        @endif
                        @if (!empty($sale->contact_no))
                            <p class="mb-1"><b>Contact #:</b> {{ $sale->contact_no }}</p>
                        @endif --}}
                    </div>
                </td>
            </tr>
        </table>
        <hr>
    </div>


    <p class="text-center" style="font-size: 1rem; padding-left: 15%;"><b>Sale Invoice</b></p>
    <div style="padding-left: 15%;">
        <table class="table table-bordered">
            <thead>
                <tr class="table-warning">
                    <th>#</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Price</th>
                    {{-- <th>Quantity</th> --}}
                    {{-- <th>Pcs</th> --}}
                    <th>Quantity</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($sale))
                    @if (!empty(unserialize($sale->items_detail)))
                        @php
                            $i = 1;
                        @endphp
                        @foreach (unserialize($sale->items_detail) as $invoiceItem)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    @if (!empty($items))
                                        @foreach ($items as $item)
                                            {{ isset($sale) ? ($item->id == $invoiceItem['item_id'] ? $item->code : '') : '' }}
                                        @endforeach
                                    @endif

                                </td>
                                <td>
                                    @if (!empty($items))
                                        @foreach ($items as $item)
                                            {{ isset($sale) ? ($item->id == $invoiceItem['item_id'] ? $item->name : '') : '' }}
                                        @endforeach
                                    @endif

                                </td>
                                &nbsp;&nbsp;
                                <td> {{ $invoiceItem['item_price'] }}</td>&nbsp;&nbsp;
                                {{-- <td>{{$invoiceItem['item_qty']}}</td>&nbsp;&nbsp; --}}
                                {{-- <td>{{$invoiceItem['item_pcs']}}</td>&nbsp;&nbsp; --}}
                                <td>{{ $invoiceItem['item_qty'] }}</td>&nbsp;&nbsp;
                                <td>{{ $invoiceItem['amount'] }}</td>
                            </tr>
                            @php
                                $i++;
                            @endphp
                        @endforeach
                    @endif
                @endif
                <tr>
                    <td colspan="5" style="text-align: right;padding-right:2rem;">Gross Amount</td>
                    <td>{{ !empty($sale->gross_amount) ? $sale->gross_amount . '.' . $currency_symbol : '' }}</td>

                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;padding-right:2rem;">Discount</td>
                    <td>{{ !empty($sale->discount_amount) ? $sale->discount_amount . '.' . $currency_symbol : '' }}
                    </td>

                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;padding-right:2rem;">Net Total</td>
                    <td>{{ !empty($sale->net_total) ? $sale->net_total . '.' . $currency_symbol : '' }}</td>

                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;padding-right:2rem;">Recieved/Advance Amount</td>
                    <td>{{ !empty($sale->recieved_amount) ? $sale->recieved_amount . '.' . $currency_symbol : '' }}
                    </td>

                </tr>
                <tr>
                    <td colspan="5" style="text-align: right;padding-right:2rem;">Balance</td>
                    <td>{{ !empty($sale->balance_amount) ? $sale->balance_amount . '.' . $currency_symbol : '' }}</td>

                </tr>
            </tbody>
        </table>
    </div>
    <div style="padding-left: 15%;">
        <p class="mb-1"><b>Description:</b></p>
        @php
            echo $sale->note_html;
        @endphp
    </div>
    <hr style="margin-left: 10%;">
    <div class="align-middle" style="padding-left: 40%;">
        <p class="mb-1" style="font-family:Georgia, 'Times New Roman', Times, serif;">
            <b>Verify Your
                Invoice</b>
        </p>
        <img src="data:image/png;base64,{{ $qrCodeString }}" class="qr-code">
    </div>
    <div class="fbr-container">

        <hr>
        <p>Powerd by Quadacts www.quadacts.com +923167652340</p>
    </div>


</body>

</html>
