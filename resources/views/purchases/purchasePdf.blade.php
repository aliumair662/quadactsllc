<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice</title>
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
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
                    <div class="ml-2">
                        <h3 style="font-family:Georgia, 'Times New Roman', Times, serif;">Purchase Invoice</h3>
                        <p class="mb-1"><b>Invoice #:</b>{{ $purchase->invoice_number }}</p>
                        <p class="mb-1"><b>Invoice Date:
                            </b>{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d-m-Y') }}</p>
                        <p class="mb-1"><b>Vendor Name:</b>{{ $purchase->name }}</p>
                        <p class="mb-1"><b>Address:</b>{{ $purchase->address }}</p>
                        <p class="mb-1"><b>Contact #:</b>{{ $purchase->phone }}</p>
                    </div>
                </td>
                {{-- <td class="align-middle">
                    <div class="ml-4">
                        <p class="mb-1">Invoice #: {{$purchase->invoice_number}}</p>
                        <p class="mb-1">Invoice Date: {{$purchase->invoice_date}}</p>
                    </div>
                </td> --}}
            </tr>
        </table>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th>#</th>
                <th>Items</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Pcs</th>
                <th>Quantity</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($purchase))
                @if (!empty(unserialize($purchase->items_detail)))
                    @php
                        $i = 1;
                    @endphp
                    @foreach (unserialize($purchase->items_detail) as $invoiceItem)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>
                                @if (!empty($items))
                                    @foreach ($items as $item)
                                        {{ isset($purchase) ? ($item->id == $invoiceItem['item_id'] ? $item->name : '') : '' }}
                                    @endforeach
                                @endif

                            </td>
                            &nbsp;&nbsp;
                            <td> {{ $invoiceItem['item_price'] }}</td>&nbsp;&nbsp;
                            <td>{{ $invoiceItem['item_qty'] }}</td>&nbsp;&nbsp;
                            <td>{{ $invoiceItem['item_pcs'] }}</td>&nbsp;&nbsp;
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
                <td colspan="6" style="text-align: right;padding-right:2rem;">Gross Amount</td>
                <td>{{ $purchase->gross_amount }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right;padding-right:2rem;">Discount</td>
                <td>{{ $purchase->discount_amount }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right;padding-right:2rem;">Net Total</td>
                <td>{{ $purchase->net_total }}</td>
            </tr>
        </tbody>
    </table>

    <p class="mb-1"><b>Description:</b></p>
    <p>{{ $purchase->note }}</p>

    <div class="mt-5" style="position: fixed; bottom: 10%; left: 0; width: 100%;">
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
        style="position: fixed; bottom: 0; left: 0; width: 100%; align-items: center; justify-content: center; padding-left: 25%;">

        <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thank You for Your business!</p>

    </div>
</body>

</html>
