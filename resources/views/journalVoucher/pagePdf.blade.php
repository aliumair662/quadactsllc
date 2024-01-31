<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
<div class="pt-5 mb-4">
        <table style="width: 100%;" collspacing="0" class="">
            <tr>
                <td style="width: 20%;" class="align-middle">
                    <div>
                        <img src="{{$companyinfo->logo}}" style="object-fit: contain; width:100%; height: 100px;" alt="" class="mb-4">
                    </div>
                </td>
                <td class="" style="width: 50%;">
                    <div class="ml-4">
                        <h3 style="font-family:Georgia, 'Times New Roman', Times, serif;">{{$companyinfo->title}}</h3>
                        <p class="mb-1"><b>Address:</b>{{$companyinfo->address}}</p>
                        <p class="mb-1"><b>Phone:</b>{{$companyinfo->phone}}</p>
                        <p class="mb-1"><b>Email:</b>{{$companyinfo->email}}</p>
                        <p class="mb-1"><b>URL:</b>{{$companyinfo->web}}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="text-right">
        <p><b>Voucher list</b></p>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr class="table-warning">
                <th class="">#</th>
                <th class="">Voucher Number</th>
                <th class="">Voucher Date</th>
                <th class="">Notes</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($lists))
            @php
            $i=1;
            @endphp
            @foreach($lists as $list)
            <tr>
                <td class="">{{$i}}</td>
                <td class="">{{$list->voucher_number}}</td>
                <td class="">{{$list->voucher_date}}</td>
                <td class="align-middle" style="max-width: 100px;">{{substr($list->note,0,50)}} {{ strlen($list->note) > 50 ?  "...." : "" }} </td>
            </tr>
            @php
            $i++;
            @endphp
            @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>