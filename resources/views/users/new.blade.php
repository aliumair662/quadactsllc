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
                        <div>New User
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('userlist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Users List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data"
                    action="{{ isset($user) ? route('updateuser') : route('saveuser') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">User Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Email</label>
                                    <input name="email" id="email" placeholder="with a placeholder" type="email"
                                        value="{{ isset($user) ? $user->email : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Password</label>
                                    <input name="password" id="password" placeholder="password placeholder"
                                        type="password" value="" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Full Name</label>
                                    <input name="name" id="name" value="{{ isset($user) ? $user->name : '' }}"
                                        type="text" class="form-control">
                                    <input name="id" id="id" value="{{ isset($user) ? $user->id : '' }}"
                                        type="hidden" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Phone No</label>
                                    <input name="phone" id="phone"
                                        value="{{ isset($user) ? $user->phone : '' }}" type="text"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Branch
                                        <a href="" title="Branch List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control" name="branch" id="branch">
                                        <option value="1"
                                            {{ isset($user) ? ($user->branch == 0 ? 'selected' : '') : 'selected' }}>
                                            Default</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-8">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Address</label>
                                    <input name="address" id="address"
                                        value="{{ isset($user) ? $user->address : '' }}" type="text"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="avatar" class="">Profile Picture</label>
                                    <input name="avatar" id="avatar" type="file" class="form-control-file">
                                </div>
                            </div>
                            @if (isset($user))
                                <div class="col-md-6 mt-4">
                                    <label for="name" class="">Status</label>
                                    <label class="switch">
                                        <input type="checkbox"
                                            {{ isset($user) ? ($user->status == 1 ? 'checked' : '') : '' }}
                                            value="1" name="status">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            @endif
                            <div class="col-md-1">
                                @if (isset($user))
                                    <div class="widget-content-left">
                                        <img width="100" class="rounded-circle" src="{{ $user->avatar }}"
                                            alt="">
                                    </div>
                                @endif
                            </div>
                        </div>


                    </div>
                    <div class="card-header">User Permission
                    </div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Menu</th>
                                    <th class="text-center">Route</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!empty($menus))
                                    @foreach ($menus as $menu)
                                        <tr>
                                            <td class="text-center text-muted">{{ $menu->id }}</td>
                                            <td>
                                                <div class="widget-content p-0">
                                                    <div class="widget-content-wrapper">
                                                        <div class="widget-content-left mr-3">
                                                            <div class="widget-content-left">
                                                                <img width="40" class="rounded-circle"
                                                                    src="assets/images/avatars/4.jpg" alt="">
                                                            </div>
                                                        </div>
                                                        <div class="widget-content-left flex2">
                                                            <div class="widget-heading">{{ $menu->title }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $menu->route }}</td>

                                            <td class="text-center">
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        {{ isset($user) ? ($menu->active == 1 ? 'checked' : '') : '' }}
                                                        value="{{ serialize(['id' => $menu->id, 'title' => $menu->title, 'route' => $menu->route, 'module' => $menu->module]) }}"
                                                        name="userrights[]">
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                            </tbody>
                        </table>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($user) ? 'Update' : 'Register' }}</button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</x-app-layout>
