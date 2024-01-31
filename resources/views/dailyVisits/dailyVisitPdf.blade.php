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
        <h5 class="card-title">Add Visit Details</h5>
        <div class="form-row">
            <div class="col-md-2">
                <div class="position-relative form-group">
                    <label for="exampleEmail11" class="">Visit ID</label>
                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                        value="{{ isset($dailyVisit) ? $dailyVisit->invoice_number : Config::get('constants.PACKAGE_INVOICE_PREFIX') . $invoice_number }}"
                        class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="position-relative form-group">
                    <label for="exampleEmail11" class="">Current Date & Time</label>
                    <input style="text-align:center;" name="invoice_date" id="invoice_date" placeholder=""
                        type="text"
                        value="{{ isset($dailyVisit) ? $dailyVisit->invoice_date : now()->format('d-m-Y h:i:s') }}"
                        class="form-control" readonly>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="exampleEmail11" class="">Name</label>
                    <input name="name" id="text" placeholder="Enter Name" type="text"
                        value="{{ isset($dailyVisit) ? $dailyVisit->name : '' }}" class="form-control">
                    <input name="id" id="id" value="{{ isset($dailyVisit) ? $dailyVisit->id : '' }}"
                        type="hidden" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="password" class="">Email</label>
                    <input name="email" id="password" placeholder="Enter Email" type="email"
                        value="{{ isset($dailyVisit) ? $dailyVisit->email : '' }}" class="form-control">
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="phone" class="">Phone No</label>
                    <input name="phone" id="phone" value="{{ isset($dailyVisit) ? $dailyVisit->phone : '' }}"
                        type="text" class="form-control" placeholder="Enter Phone">
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="address" class="">Address</label>
                    <input name="address" id="address" value="{{ isset($dailyVisit) ? $dailyVisit->address : '' }}"
                        type="text" class="form-control" placeholder="Address">
                </div>
            </div>
        </div>
        {{-- d-none --}}
        <div class="form-row">
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="latitude" class="">Latitude</label>
                    <input name="latitude" id="latitude"
                        value="{{ isset($dailyVisit) ? $dailyVisit->latitude : '' }}" type="text"
                        class="form-control" placeholder="Latitude" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="position-relative form-group">
                    <label for="longitute" class="">Longitute</label>
                    <input name="longitute" id="longitute"
                        value="{{ isset($dailyVisit) ? $dailyVisit->longitute : '' }}" type="text"
                        class="form-control" placeholder="Longitute" readonly>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-12">
                <div class="position-relative form-group">
                    <label for="location" class="">Location</label>
                    <input name="location" id="location"
                        value="{{ isset($dailyVisit) ? $dailyVisit->location : '' }}" type="text"
                        class="form-control" placeholder="Location" readonly>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-6">
                <label for="exampleEmail11" class="">Description</label>
                <textarea name="description" id="description" placeholder="" type="text" value="" class="form-control">{{ isset($dailyVisit) ? $dailyVisit->description : '' }}</textarea>
            </div>
            <div class="col-md-3">
                <div class="position-relative form-group">
                    <label for="avatar" class="">Attachment</label>
                    <input name="attachment" id="attachment" type="file" class="form-control-file"
                        accept="image/*" capture="camera">
                </div>
            </div>
            <div class="col-md-3">
                @if (isset($dailyVisit))
                    <div class="widget-content-left">
                        <img width="100" class="" src="{{ asset($dailyVisit->attachment) }}"
                            alt="">
                    </div>
                @endif
            </div>
        </div>
    </div>



    <p class="mb-1"><b>Description:</b></p>
    <p class="mb-5">{{ $dailyVisit->description }}</p>
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
