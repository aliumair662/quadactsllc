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
                        <div>{{(isset($ledgerAccount)) ? 'Edit Ledger Account' : 'New Ledger Account'}}
                            <div class="page-title-subheading">This is an example dashboard created using build-in elements and components.
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('ledgerAccountsList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark" data-original-title="Acounts List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data" action="{{(isset($ledgerAccount)) ? route('updateLedgerAccount') : route('saveLedgerAccount')}}" method="post">
                    @csrf
                <div class="card-body"><h5 class="card-title">{{(isset($ledgerAccount)) ? 'Edit Account' : 'Add Account'}}</h5>
                        <div class="form-row">
                            <input type="hidden" name="ledger_id" value="{{(isset($ledgerAccount)) ? $ledgerAccount->id : ''}}">
                            <div class="col-12">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Name</label>
                                    <input name="name" id="text" placeholder="with a placeholder" type="text" value="{{(isset($ledgerAccount)) ? $ledgerAccount->name  : ''}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                        <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Account Group
                                    </label>
                                    <select class="mb-2 form-control"  name="account_group" id="branch">
                                        <option disabled selected>Select Group</option>
                                        @if(isset($account_group))
                                        @foreach($account_group as $account_group)
                                        <option value="{{$account_group->id}}" {{isset($ledgerAccount) ? $ledgerAccount->chart_id == $account_group->id ? 'selected':'':''}}>{{$account_group->name}}</option>
                                        @endforeach
                                        @endif
                                    </select></div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Account Type
                                    </label>
                                    <select class="mb-2 form-control"  name="account_type" id="branch">
                                        @if(isset($account_type))
                                        @foreach($account_type as $account_type)
                                        <option value="{{$account_type->id}}" {{isset($ledgerAccount) ? $ledgerAccount->ledger_account_id == $account_type->id ? 'selected':'':''}} selected>{{$account_type->name}}</option>
                                        @endforeach
                                        @endif
                                    </select></div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Account Category
                                    </label>
                                    <select class="mb-2 form-control"  name="account_category" id="branch">
                                    <option disabled selected>Select Category</option>
                                        @if(isset($account_category))
                                        @foreach($account_category as $account_category)
                                        <option value="{{$account_category->id}}" {{isset($ledgerAccount) ? $ledgerAccount->accounts_category_id == $account_category->id ? 'selected':'':''}}>{{$account_category->name}}</option>
                                        @endforeach
                                        @endif
                                    </select></div>
                            </div>
                        </div>
                </div>
                <div class="d-block text-right card-footer">
                    <button type="submit" class="mt-2 mr-3 btn btn-primary">{{(isset($ledgerAccount)) ? 'Update' : 'Save'}}</button>
                </div>
                </form>
            </div>

        </div>

    </div>

</x-app-layout>

