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
                        <div>Category
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('newcategory') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Add New Category">
                                <i class="fa fa-plus"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="main-card mb-3 card">
                        <div class="card-header">Category List
                            <!-- <div class="btn-actions-pane-right">
                                <div role="group" class="btn-group-sm btn-group">
                                <a href="{{ route('activemenu') }}"><button class="active btn btn-focus">Active</button></a>
                                <a href="{{ route('inactivemenu') }}"><button class="btn btn-focus">InActive</button></a>
                                </div>
                            </div> -->
                        </div>
                        <div class="table-responsive">
                            <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Name</th>
                                    <!-- <th class="text-center">phone</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Web</th> -->

                                    <!-- <th class="text-center">Address</th>
                                    <th class="text-center">Action</th> -->
                                </tr>
                                </thead>
                                <tbody>
                                @if(!empty($category))
                                @foreach($category as $cat)
                                <tr>
                                    <td class="text-center text-muted">{{$cat->id}}</td>
                                    <td>
                                        <div class="widget-content p-0">
                                            <div class="widget-content-wrapper">
                                                <div class="widget-content-left mr-3">
                                                
                                                </div>
                                                <div class="widget-content-left flex2">
                                                    <div class="widget-heading">{{$cat->name}}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </td> 
                                    <td class="text-center">
                                        <div class="mb-2 mr-2 btn-group">
                                           <button class="btn btn-outline-success">Edit</button>
                                           <button type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="dropdown-toggle-split dropdown-toggle btn btn-outline-success"><span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(68px, 33px, 0px);">
                                                <a href="{{route('editcategory',$cat->id)}}"><button type="button" tabindex="0" class="dropdown-item">Edit</button></a>
                                                <a href="{{route('deletecategory',$cat->id)}}"><button type="button" tabindex="0" class="dropdown-item">Delete</button></a>
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
                            {{ $category->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>

