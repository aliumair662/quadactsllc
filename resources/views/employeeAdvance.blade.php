<x-app-layout>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-users icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>Employee Advance
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{route('newAdvanceReturn')}}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Advance Return">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

         

        
            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Advance Return List
                            <div class="text-right btn-actions-pane-right ">
                                <div role="group" class="btn-group-sm btn-group">
                                    <span class="mt-2">Advance:</span> <input type="text" value="{{(isset($advance)) ? $advance->advance : ''}}" readonly class="form-control ml-2" style="width: 100px;">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Voucher No</th>
                                    <th class="text-center">Employee Name</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Voucher Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($lists))
                                @foreach($lists as $list)
                                <tr>
                                    <td class="text-center text-muted">{{$list->id}}</td>
                                    <td class="text-center">{{$list->voucher_number}}</td>
                                    <td class="text-center">{{$list->name}} </td>
                                    <td class="text-center">{{$list->amount}}</td>
                                    <td class="text-center">{{$list->voucher_date}}</td>
                                    <td class="text-center">
                                        <div class="mb-2 mr-2 btn-group">
                                            <button class="btn btn-outline-success">Edit</button>
                                            <button type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                <a href="{{route('editAdvanceReturn',$list->id)}}"><button type="button" tabindex="0" class="dropdown-item">Edit</button></a>
                                                <a href="{{route('deleteAdvanceReturn',$list->id)}}"><button type="button" tabindex="0" class="dropdown-item">Delete</button></a><a href="{{route('recordPdf',$list->id)}}" ><button type="button" tabindex="0" class="dropdown-item">PDF</button></a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mr-3 card-footer">
                            <!-- <div class="col-lg-12">
                                <nav class="float-right" aria-label="Page navigation example">
                                    <ul class="pagination">
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Previous"><span aria-hidden="true">«</span><span class="sr-only">Previous</span></a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">1</a></li>
                                        <li class="page-item active"><a href="javascript:void(0);" class="page-link">2</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">3</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">4</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link">5</a></li>
                                        <li class="page-item"><a href="javascript:void(0);" class="page-link" aria-label="Next"><span aria-hidden="true">»</span><span class="sr-only">Next</span></a></li>
                                    </ul>
                                </nav>
                            </div> -->
                            {{$lists->links()}}
                        </div>
                        <div class="text-right mx-2 mb-2">
                            Net Amount 
                           <span class="border border-light px-2 py-1" style="border-radius: 3px;">{{(isset($net_amount)) ? $net_amount : ''}}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
