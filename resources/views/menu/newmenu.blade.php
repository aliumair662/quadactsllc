<x-app-layout>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-plus icon-gradient bg-mean-fruit">
                            </i>
                        </div>
                        <div>New Menue
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('menulist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Menue List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
                
            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($menus)) ? route('updatemenu') : route('savemenu')}}" method="post">
                    @csrf
                <div class="card-body"><h5 class="card-title">Menue Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Title</label>
                                    <input name="title" id="title" placeholder="Menue Title" type="text" value="{{(isset($menus)) ? $menus->title : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Route" class="">Route</label>
                                    <input name="route" id="route" placeholder="Route" type="text" value="{{(isset($menus)) ? $menus->route : ''}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Module</label>
                                    <input name="module" id="module" placeholder="Module" value="{{(isset($menus)) ? $menus->module : ''}}" type="text" class="form-control">
                                    <input name="id" id="id" value="{{(isset($menus)) ? $menus->id : ''}}" type="hidden" class="form-control">
                                </div>
                            </div>
                            @if(isset($menus))
                            <div class="col-md-6 mt-4">
                                <label for="name" class="">Status</label>
                            <label class="switch">
                                            <input type="checkbox" {{(isset($menus)) ? ($menus->status==1) ? 'checked' : '' : ''}} value="1" name="status">
                                            <span class="slider round"></span>
                                        </label>
                            </div>
                            @endif
                        </div>
                </div>
                
                <div class="d-block text-center card-footer">
                    <button type="submit" class="mt-2 btn btn-primary">{{(isset($menus)) ? 'Update' : 'Register'}}</button>
                </div>
                </form>
            </div>
         

        </div>

    </div>

</x-app-layout>

