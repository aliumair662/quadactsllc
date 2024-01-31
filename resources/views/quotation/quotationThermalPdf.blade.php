<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Invoice</title>
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
                        <hr>
                        <p class="mb-1"><b>Invoice #:</b> {{ $quotation->invoice_number }}</p>

                        <p class="mb-1"><b>Customer Name:</b> {{ $quotation->name }}</p>

                        <p class="mb-1"><b>Contact #:</b> {{ $quotation->phone }}</p>

                        <p class="mb-1"><b>Invoice Date:</b> {{ $quotation->invoice_date }}</p>
                        <p class="mb-1"><b>Address:</b> {{ $quotation->address }}</p>
                    </div>
                </td>
            </tr>
        </table>
        <hr>
    </div>


    <p class="text-center" style="font-size: 1rem; padding-left: 15%;"><b>quotation Invoice</b></p>
    <div style="padding-left: 15%;">
        <table class="table table-bordered">
            <thead>
                <tr class="table-warning">
                    <th>#</th>
                    <th>Items</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($quotation))
                    @if (!empty(unserialize($quotation->items_detail)))
                        @php
                            $i = 1;
                        @endphp
                        @foreach (unserialize($quotation->items_detail) as $invoiceItem)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    @if (!empty($items))
                                        @foreach ($items as $item)
                                            {{ isset($quotation) ? ($item->id == $invoiceItem['item_id'] ? $item->name : '') : '' }}
                                        @endforeach
                                    @endif

                                </td>
                                &nbsp;&nbsp;
                                <td> {{ $invoiceItem['item_price'] }}</td>&nbsp;&nbsp;
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
                    <td colspan="4" style="text-align: right;padding-right:2rem;">Gross Amount</td>
                    <td>{{ $quotation->gross_amount }}</td>

                </tr>
                <tr>
                    <td colspan="4" style="text-align: right;padding-right:2rem;">Discount</td>
                    <td>{{ $quotation->discount_amount }}</td>

                </tr>
                <tr>
                    <td colspan="4" style="text-align: right;padding-right:2rem;">Net Total</td>
                    <td>{{ $quotation->net_total }}</td>

                </tr>
                {{-- <tr>
                    <td colspan="4" style="text-align: right;padding-right:2rem;">Recieved/Advance Amount</td>
                    <td>{{ $quotation->recieved_amount }}</td>

                </tr> --}}
                {{-- <tr>
                    <td colspan="4" style="text-align: right;padding-right:2rem;">Balance</td>
                    <td>{{ $quotation->balance_amount }}</td>

                </tr> --}}
            </tbody>
        </table>
    </div>
    <div style="padding-left: 15%;">
        <p class="mb-1"><b>Description:</b></p>
        <p>
            {{ $quotation->note }}
        </p>
    </div>
    <hr style="margin-left: 10%;">
    <div class="align-middle" style="padding-left: 30%; ">
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
