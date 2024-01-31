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
                        <div>To Do
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('toDoList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="To do List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>


            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{(isset($record)) ? route('updateToDo') : route('saveTodo')}}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="id" value="{{(isset($record)) ? $record->id : ''}}">
                    <div class="card-body">
                        <h5 class="card-title">To Do Information</h5>
                        <div class="form-row">

                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Title</label>
                                    <input name="title" id="invoice_date" placeholder="" type="text" value="{{(isset($record)) ? $record->title : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Priority
                                    </label>
                                    <select class="js-example-basic-single form-control" placeholder="Select Customer" name="priority" id="customer_id" required>
                                        <option >Select</option>
                                        <option value="1" {{(isset($record->priority)) ? ($record->priority == 1 ? 'selected': '') : ''}}>To Do</option>
                                        <option value="2" {{(isset($record->priority)) ? ($record->priority == 2 ? 'selected': '') : ''}}>In Progress</option>
                                        <option value="3" {{(isset($record->priority)) ? ($record->priority == 3 ? 'selected': '') : ''}}>Ready</option>
                                        <option value="4" {{(isset($record->priority)) ? ($record->priority == 4 ? 'selected': '') : ''}}>Complete</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    

                        <div class="form-row">
                            <div class="col-md-10">
                                <label for="exampleEmail11" class="">Description</label>

                                <textarea name="description" id="note" placeholder="" type="text" value="" class="form-control">{{(isset($record)) ? $record->description : ''}}</textarea>
                            </div>
                        </div>


                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit" class="mt-2 btn btn-primary">{{(isset($record)) ? 'Update' : 'Save'}}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
