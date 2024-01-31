<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice</title>
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
                        <br>
                        @if ($data['customer'] != '')
                            <p class="mb-1"><b>Customer Name:</b> {{ $data['customer']->name }}</p>
                            <p class="mb-1"><b>Customer Address:</b> {{ $data['customer']->address }}</p>
                            <p class="mb-1"><b>Customer Contact #:</b> {{ $data['customer']->phone }}</p>
                        @endif
                        @if ($data['vendor'] != '')
                            <p class="mb-1"><b>Vendor Name:</b> {{ $data['vendor']->name }}</p>
                            <p class="mb-1"><b>Vendor Address:</b> {{ $data['vendor']->address }}</p>
                            <p class="mb-1"><b>Vendor Contact #:</b> {{ $data['vendor']->phone }}</p>
                        @endif
                    </div>
                </td>

            </tr>
        </table>
    </div>
    @if ($data['vendor'] != '')
        <h4 class="text-right"><b>{{ $data['vendor']->name }} - Vendor Ledger</b></h4>
    @endif
    @if ($data['customer'] != '')
        <h4 class="text-right"><b>{{ $data['customer']->name }} - Customer Ledger</b></h4>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Voucher Date</th>
                <th class="text-center">Voucher Number</th>
                <th class="text-center">Note</th>
                <th class="text-center">debit</th>
                <th class="text-center">credit</th>
                <th class="text-center">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right text-muted" colspan="6">Beginning Balance</td>
                <td class="text-center">{{ $data['beginningBalance'] }} {{ $data['journal_entry_rule'] }}</td>
            </tr>
            @if (!empty($data['transactions']))
                @php
                    $i = 1;
                @endphp
                @foreach ($data['transactions'] as $transaction)
                    <tr>
                        <td class="text-center text-muted">{{ $i }}</td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($transaction->voucher_date)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $transaction->voucher_number }}</td>
                        <td class="text-center">{{ $transaction->note }}</td>
                        <td class="text-center">{{ $transaction->debit }}</td>
                        <td class="text-center">{{ $transaction->credit }}</td>
                        <td class="text-center">{{ $transaction->closingBalance }}</td>

                    </tr>
                    @php
                        $i++;
                    @endphp
                @endforeach
            @endif
            <tr>
                <td class="text-right text-muted" colspan="6">Ending Balance</td>
                <td class="text-center">{{ $data['endingBalance'] }} {{ $data['journal_entry_rule'] }}</td>
            </tr>
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
