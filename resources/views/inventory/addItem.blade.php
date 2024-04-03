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
                        <div>New Item
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in elements and components. --}}
                            </div>

                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('itemlist') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Items List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>

            <table style="display: none;">
                <tbody class="new_row">
                    <tr class="tr-content">

                        <td class="text-center"><label class="sr_no">1</label></td>
                        <td class="text-center text-muted">
                            <select class="select-drop-down form-control bank_name select" name="item_id[]" required>
                                <option value="">Select Item</option>
                                @if (!empty($itemss))
                                    @foreach ($itemss as $singleItem)
                                        <option value="{{ $singleItem->id }}"> {{ $singleItem->name }} </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td class="text-center"><input name="item_qty[]" id="item_qty" placeholder="Quantity"
                                value="" type="number" class="form-control item_qty check_number"
                                onchange="calculateInvoiceSum();"></td>
                        <td><button class="btn btn-dark" type="button" onclick="removeRow(this)"><i
                                    class="fas fa-times"></i></button></td>

                    </tr>
                <tbody>
            </table>

            <div class="main-card mb-3 card">
                <form class="Q-form" action="{{ isset($item) ? route('updateitem') : route('saveitem') }}"
                    method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Item Information</h5>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Item Code</label>
                                    <input name="code" placeholder="Item Code" type="text"
                                        value="{{ isset($item) ? $item->code : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="Route" class="">Item Name</label>
                                    <input name="name" id="name" placeholder="Item Name" type="text"
                                        value="{{ isset($item) ? $item->name : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Purchase Price</label>
                                    <input name="purchase_price" id="purchase_price" placeholder="Purchase Price"
                                        value="{{ isset($item) ? $item->purchase_price : '' }}" type="text"
                                        class="form-control">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Sale Price</label>
                                    <input name="sele_price" id="sele_price" placeholder="sale Price"
                                        value="{{ isset($item) ? $item->sele_price : '' }}" type="text"
                                        class="form-control">
                                    <input name="id" id="id" value="{{ isset($item) ? $item->id : '' }}"
                                        type="hidden" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class=""><b>Item Type</b></label>
                                    <div>
                                        Single Sale Item <input name="item_type" value="0" type="radio"
                                            class="mr-4 cash"
                                            {{ isset($item) ? ($item->item_type == 0 ? 'checked' : '') : 'checked' }}>
                                        Group Sale Item <input name="item_type" value="1" type="radio"
                                            class="bank_check_toggle"
                                            {{ isset($item) ? ($item->item_type == 1 ? 'checked' : '') : '' }}>
                                    </div>


                                    <div>
                                        <table
                                            class="align-middle mb-0 table table-borderless table-striped table-hover"
                                            id="show_hide_inps">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th class="text-center">Items</th>
                                                    <th class="text-center">Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($item))
                                                    @if (count(unserialize($item->linked_items)) > 0)
                                                        @php
                                                            $i = 1;
                                                        @endphp
                                                        @foreach (unserialize($item->linked_items) as $invoiceItem)
                                                            <tr>
                                                                <td class="text-center">{{ $i }}</td>
                                                                <td class="text-center text-muted">
                                                                    <select
                                                                        class="js-example-basic-single form-control check_number"
                                                                        aria-placeholder="Select Item"
                                                                        name="item_id[]"
                                                                        onchange="calculateInvoice();" required>
                                                                        @if (!empty($itemss))
                                                                            @foreach ($itemss as $singleItem)
                                                                                <option value="{{ $singleItem->id }}"
                                                                                    {{ isset($item) ? ($singleItem->id == $invoiceItem['item_id'] ? 'Selected' : '') : '' }}>
                                                                                    {{ $singleItem->name }} </option>
                                                                            @endforeach
                                                                        @endif

                                                                    </select>
                                                                </td>
                                                                <td class="text-center"><input name="item_qty[]"
                                                                        id="item_qty" placeholder="Quantity"
                                                                        value="{{ isset($item) ? $invoiceItem['item_qty'] : '' }}"
                                                                        type="number" class="form-control item_qty"
                                                                        onchange="calculateInvoiceSum();"></td>
                                                                <td><button class="btn btn-dark" type="button"
                                                                        onclick="removeRow(this)"><i
                                                                            class="fas fa-times"></i></button></td>
                                                            </tr>
                                                            @php
                                                                $i++;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                @endif

                                                <tr class="btn-add-new">
                                                    <td>
                                                        <button class="btn btn-primary add_row" type="button"><i
                                                                class="fas fa-plus"></i></button>
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>

                                            </tbody>
                                        </table>



                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">

                            <!--<div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="name" class="">Stock</label>
                                    <input name="stock" id="stock" placeholder="stock" value="{{ isset($item) ? $item->stock : '' }}" type="text" class="form-control">
                                </div>
                            </div>-->

                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Category
                                        <a href="" title="category List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control" name="category" id="category">
                                        @if (!empty($category))
                                            @foreach ($category as $cat)
                                                <option
                                                    value="{{ $cat->id }}"{{ isset($item) ? ($item->category == $cat->id ? 'selected' : '') : 'selected' }}>
                                                    {{ $cat->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="pic" class="">Item Picture</label>
                                    <input name="pic" id="pic" type="file" class="form-control-file">
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if (isset($item))
                                    <div class="widget-content-left">
                                        <img width="100" class="rounded-circle" src="{{ $item->pic }}"
                                            alt="">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12">
                                <div id="toolbar">
                                    <label for="exampleEmail11" class="">Notes</label>
                                </div>
                                <div id="editor">
                                </div>
                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control"
                                    data-custom-value="" hidden>{{ isset($item) ? $item->note : '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            {{-- <div class="col-md-9"></div> --}}
                            <div class="col-md-9" style="margin-top: 7%">
                                <input name="html_semantic" id="html_semantic" placeholder="" type="text"
                                    value="{{ isset($item) ? $item->note_html : '' }}" class="form-control" hidden>
                            </div>
                        </div>
                        <div class="form-row">
                            <!--<div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="branch" class="">
                                        Branch
                                        <a href="" title="Branch List"><i class="fa fa-list"></i></a>
                                    </label>
                                    <select class="mb-2 form-control"  name="branch" id="branch">
                                        <option value="1"  {{ isset($item) ? ($item->branch == 1 ? 'selected' : '') : 'selected' }}>Default</option>
                                    </select></div>
                            </div>-->



                        </div>
                    </div>
                    <div class="d-block text-center card-footer">
                        <button type="submit"
                            class="mt-2 btn btn-primary">{{ isset($item) ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>


        </div>

    </div>

</x-app-layout>
<script>
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        ['blockquote', 'code-block'],
        // ['link', 'image', 'video', 'formula'],

        [{
            'header': 1
        }, {
            'header': 2
        }], // custom button values
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }, {
            'list': 'check'
        }],
        [{
            'script': 'sub'
        }, {
            'script': 'super'
        }], // superscript/subscript
        [{
            'indent': '-1'
        }, {
            'indent': '+1'
        }], // outdent/indent
        [{
            'direction': 'rtl'
        }], // text direction

        [{
            'size': ['small', false, 'large', 'huge']
        }], // custom dropdown
        [{
            'header': [1, 2, 3, 4, 5, 6, false]
        }],

        [{
            'color': []
        }, {
            'background': []
        }], // dropdown with defaults from theme
        [{
            'font': ['Times New Roman']
        }],
        [{
            'align': []
        }],

        ['clean'] // remove formatting button
    ];

    const quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        theme: 'snow'
    });
    quill.on('text-change', (delta, oldDelta, source) => {
        if (source == 'user') {
            $('#note').val(JSON.stringify(quill.getContents()));
            const html = quill.getSemanticHTML();
            $('#html_semantic').val(html);
        }
        if (source == 'api') {
            $('#note').val(JSON.stringify(quill.getContents()));
            const html = quill.getSemanticHTML();
            $('#html_semantic').val(html);
        }
    });
    $(document).ready(function() {
        var profit_per_val = $('#profit_percent').val();
        if (profit_per_val != '' && profit_per_val >= 30) {
            $("#display_percent").css("background-color", "green");
            $("#display_percent").text(profit_per_val + " %");
        } else if (profit_per_val != '' && profit_per_val < 30) {
            $("#display_percent").css("background-color", "red");
            $("#display_percent").text(profit_per_val + " %");
        }

        quill.setContents(JSON.parse($('#note').val()));

        $('.Q-form').submit(function(event) {
            $('#note').data('customValue', quill.getContents());
            var customValue = $('#note').data('customValue');
            $('#note').val(JSON.stringify(customValue));
        });
    });
</script>
