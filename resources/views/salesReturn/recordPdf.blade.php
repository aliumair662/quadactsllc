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
                        <p class="mb-1">Invoice #: {{ $saleReturn->invoice_number }}</p>
                        <p class="mb-1">Invoice Date:
                            {{ \Carbon\Carbon::parse($saleReturn->invoice_date)->format('d-m-Y') }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th>#</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Pcs</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($saleReturn))
                @if (!empty(unserialize($saleReturn->items_detail)))
                    @php
                        $i = 1;
                    @endphp
                    @foreach (unserialize($saleReturn->items_detail) as $invoiceItem)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>
                                @if (!empty($items))
                                    @foreach ($items as $item)
                                        {{ isset($saleReturn) ? ($item->id == $invoiceItem['item_id'] ? $item->code : '') : '' }}
                                    @endforeach
                                @endif

                            </td>
                            <td>
                                @if (!empty($items))
                                    @foreach ($items as $item)
                                        {{ isset($saleReturn) ? ($item->id == $invoiceItem['item_id'] ? $item->name : '') : '' }}
                                    @endforeach
                                @endif

                            </td>
                            &nbsp;&nbsp;
                            <td> {{ $invoiceItem['item_price'] }}</td>&nbsp;&nbsp;
                            <td>{{ $invoiceItem['item_qty'] }}</td>&nbsp;&nbsp;
                            <td>{{ $invoiceItem['item_pcs'] }}</td>&nbsp;&nbsp;
                            <td>{{ $invoiceItem['amount'] }}</td>
                        </tr>
                        @php
                            $i++;
                        @endphp
                    @endforeach
                @endif
            @endif
            <tr>
                <td colspan="6">Net Total</td>
                <td>{{ !empty($saleReturn->net_total) ? $saleReturn->net_total . '.' . $currency_symbol : '' }}
                </td>

            </tr>
        </tbody>
    </table>

    <p class="mb-1"><b>Description:</b></p>
    <p>{{ $saleReturn->note }}</p>

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
