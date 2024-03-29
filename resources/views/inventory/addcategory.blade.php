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
                        <div>New Category
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('categorylist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Category List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{ isset($category) ? route('updatecategory') : route('savecategory') }}"
                    method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Category Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Name</label>
                                    <input name="name" id="name" placeholder="category Title" type="text"
                                        value="{{ isset($category) ? $category->name : '' }}" class="form-control">
                                    <input name="id" id="id" type="hidden"
                                        value="{{ isset($category) ? $category->id : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Branch
                                        <a href="" title="Branch List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control" name="branch" id="branch">
                                        <option value="1"
                                            {{ isset($category) ? ($category->branch == 1 ? 'selected' : '') : 'selected' }}>
                                            Default</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="d-block text-center card-footer">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($category) ? 'Update' : 'Register' }}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
