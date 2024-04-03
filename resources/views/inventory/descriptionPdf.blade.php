<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Description</title>
    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
</head>
<style>
    .table th {
        background-color: rgb(133, 226, 41);
    }
</style>

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
            </tr>
        </table>
    </div>
    <hr>
    <div>
        <h4 style="text-align: right;"><b>Customer Name:</b> {{ $qutotation->customer_name }}</h4>
    </div>
    @foreach ($item_list as $item)
        <div style="text-align: center;">
            <h3 style="margin-bottom: 10px;">{{ $item->name }}</h3>
        </div>
        <div style="width: 30%; margin: 0 auto; display: flex; justify-content: center;">
            <img src="{{ asset($item->pic) }}" style="object-fit: contain; width:100%;" alt="" class="mb-4">
        </div>
        @php
            echo $item->note_html;
        @endphp
        <hr>
    @endforeach
</body>

</html>
