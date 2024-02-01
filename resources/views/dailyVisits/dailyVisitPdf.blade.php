<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Visit</title>
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
                {{-- <td class="align-middle" style="padding-left: 30%; padding-top:2rem;">
                    <p class="mb-1" style="font-family:Georgia, 'Times New Roman', Times, serif;">
                        <b>Verify Your
                            Invoice</b>
                    </p>
                    <hr>
                    <img src="data:image/png;base64,{{ $qrCodeString }}">
                </td> --}}
                {{-- <td class="align-middle">
                    <div class="ml-2">
                        <h3 style="font-family:Georgia, 'Times New Roman', Times, serif;">Sale Invoice</h3>
                        <p class="mb-1"><b>Invoice #:</b> {{ $sale->invoice_number }}</p>
                        <p class="mb-1"><b>Invoice Date:</b> {{ $sale->invoice_date }}</p>
                        <p class="mb-1"><b>Customer Name:</b> {{ $sale->name }}</p>
                        <p class="mb-1"><b>Address:</b> {{ $sale->address }}</p>
                        <p class="mb-1"><b>Contact #:</b> {{ $sale->phone }}</p>
                    </div>
                </td> --}}
            </tr>
        </table>
    </div>
    <hr>
    {{-- <table style="width: 100%;" collspacing="0" class="">
        <tr>
            <td>
                <p class="mb-1"><b>Invoice #:</b> {{ $sale->invoice_number }}</p>
            </td>
            <td>
                <p class="mb-1"><b>Customer Name:</b> {{ $sale->name }}</p>
            </td>

            <td>
                <p class="mb-1"><b>Contact #:</b> {{ $sale->phone }}</p>

            </td>
        </tr>
        <tr>
            <td>
                <p class="mb-1"><b>Invoice Date:</b>
                    {{ \Carbon\Carbon::parse($sale->invoice_date)->format('d-m-Y') }}</p>

            </td>
            <td>
                <p class="mb-1"><b>Address:</b> {{ $sale->address }}</p>
            </td>
        </tr>
    </table> --}}
    {{-- <p class="text-right"><b>Sale Invoice</b></p> --}}

    <div class="card-body">
        <h4 class="card-title" style="text-align: center">Visit Details</h4>
        <table>
            <tr>
                <td>
                    <p for="exampleEmail11"><b>Visit ID: </b>{{ $dailyVisit->invoice_number }}
                    </p>
                </td>
                <td>
                    <p for="exampleEmail11"><b>Current Date &
                            Time:</b>{{ \Carbon\Carbon::parse($dailyVisit->invoice_date)->format('d-m-Y') }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p for="exampleEmail11"><b>Customer Name: </b>{{ $dailyVisit->name }}</p>
                </td>
                <td>
                    <p for="password"><b>Email: </b>{{ $dailyVisit->email }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p for="phone"><b>Phone No: </b>{{ $dailyVisit->phone }}</p>
                </td>
                <td>
                    <p for="address"><b>Address: </b>{{ $dailyVisit->address }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p for="latitude"><b>Latitude: </b>{{ $dailyVisit->latitude }}</p>
                </td>
                <td>
                    <p for="longitute"><b>Longitute: </b>{{ $dailyVisit->longitute }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p for="location" class=""><b>Location:</b>{{ $dailyVisit->location }}</p>
                </td>
            </tr>
        </table>
    </div>


    <p class="mb-1"><b>Image Link:</b>{{ asset($dailyVisit->attachment) }} </p>
    <p class="mb-1"><b>Description:</b></p>
    <p class="mb-5">{{ $dailyVisit->description }}</p>
    <hr>
    <div class="mt-2" style="text-align: center;">
        <img src="{{ public_path($dailyVisit->attachment) }}" alt="Image" width="350px">
    </div>
    {{-- {{ public_path($dailyVisit->attachment) }} --}}
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

        <p class="mb-0" style="font-size: 1.3rem; font-weight:900">Thank You for Your business! :)</p>
    </div>

</body>

</html>
