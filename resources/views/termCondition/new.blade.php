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
                        <div>Terms & Conditions
                            <div class="page-title-subheading">
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('termConditionList') }}">
                            <button type="button" data-toggle="tooltip" title="" data-placement="bottom"
                                class="btn-shadow mr-3 btn btn-dark" data-original-title="Daily Visits List">
                                <i class="fa fa-th-list"></i>
                            </button>
                        </a>

                    </div>
                </div>
            </div>
            <div class="main-card mb-3 card">
                <form class="Q-form" enctype="multipart/form-data"
                    action="{{ isset($term_condition) ? route('updateTermCondition') : route('saveTermCondition') }}"
                    method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Add Terms & Conditions Details</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">T&C ID</label>
                                    <input name="t_c_number" id="t_c_number" placeholder="" type="text"
                                        value="{{ isset($term_condition) ? $term_condition->t_c_number : Config::get('constants.TERM_CONDITION_PREFIX') . $t_c_number }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Name</label>
                                    <input name="name" id="text" placeholder="Enter Name" type="text"
                                        value="{{ isset($term_condition) ? $term_condition->name : '' }}"
                                        class="form-control">
                                    <input name="id" id="id"
                                        value="{{ isset($term_condition) ? $term_condition->id : '' }}" type="hidden"
                                        class="form-control">
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Current Date & Time</label>
                                    <input style="text-align:center;" name="invoice_date" id="invoice_date"
                                        placeholder="" type="text"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->invoice_date : now()->format('d-m-Y h:i:s') }}"
                                        class="form-control" readonly>
                                </div>
                            </div> --}}
                        </div>
                        <div class="form-row">
                            {{-- <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Email</label>
                                    <input name="email" id="password" placeholder="Enter Email" type="email"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->email : '' }}" class="form-control">
                                </div>
                            </div> --}}
                        </div>
                        {{-- <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="phone" class="">Phone No</label>
                                    <input name="phone" id="phone"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->phone : '' }}" type="text"
                                        class="form-control" placeholder="Enter Phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="address" class="">Address</label>
                                    <input name="address" id="address"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->address : '' }}" type="text"
                                        class="form-control" placeholder="Address">
                                </div>
                            </div>
                        </div> --}}
                        {{-- <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="latitude" class="">Latitude</label>
                                    <input name="latitude" id="latitude"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->latitude : '' }}" type="text"
                                        class="form-control" placeholder="Latitude" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="longitute" class="">Longitute</label>
                                    <input name="longitute" id="longitute"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->longitute : '' }}" type="text"
                                        class="form-control" placeholder="Longitute" readonly>
                                </div>
                            </div>
                        </div> --}}
                        {{-- <div class="form-row">
                            <div class="col-md-12">
                                <div class="position-relative form-group">
                                    <label for="location" class="">Location</label>
                                    <input name="location" id="location"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->location : '' }}" type="text"
                                        class="form-control" placeholder="Location" readonly>
                                </div>
                            </div>
                        </div> --}}
                        <div class="form-row">
                            <div class="col-md-12">
                                <div id="toolbar">
                                    <label for="exampleEmail11" class="">Details</label>
                                </div>
                                <div id="editor">
                                </div>
                                <textarea name="note" id="note" placeholder="" type="text" value="" class="form-control"
                                    data-custom-value="" hidden>{{ isset($term_condition) ? $term_condition->note : '' }}</textarea>
                            </div>
                            {{-- <div class="col-md-10">
                                <label for="exampleEmail11" class="">Description</label>
                                <textarea name="description" id="description" placeholder="" type="text" value="" class="form-control">{{ isset($term_condition) ? $term_condition->description : '' }}</textarea>
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="avatar" class="">Attachment</label>
                                    <input name="attachment" id="attachment" type="file" class="form-control-file"
                                        accept="image/*" capture="camera">
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if (isset($dailyVisit))
                                    <div class="widget-content-left">
                                        <img width="100" class="" src="{{ asset($dailyVisit->attachment) }}"
                                            alt="">
                                    </div>
                                @endif
                            </div> --}}
                        </div>
                        <div class="form-row">
                            <div class="col-md-9"></div>
                            <div class="col-md-3">
                                <input name="html_semantic" id="html_semantic" placeholder="" type="text"
                                    value="{{ isset($sale) ? $sale->note_html : '' }}" class="form-control" hidden>
                            </div>
                        </div>
                        {{-- <div class="form-row">
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Status</label>
                                    <select class="js-example-basic-single form-control"
                                        placeholder="Select Visit Status" name="status_id" id="status_id">
                                        @if (!empty($visit_status))
                                            @foreach ($visit_status as $data)
                                                <option value="{{ $data->id }}"
                                                    {{ isset($dailyVisit) && isset($dailyVisit->status_id) ? ($dailyVisit->status_id == $data->id ? 'Selected' : '') : '' }}>
                                                    {{ $data->name }} </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div> --}}
                        <div class="d-flex justify-content-center px-4 pb-3" style="margin-top: 10%;">
                            <button type="submit" class="mt-2 btn btn-primary"
                                id='save_button'>{{ isset($term_condition) ? 'Update' : 'Save' }}</button>
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
    });
    $(document).ready(function() {

        quill.setContents(JSON.parse($('#note').val()));

        $('.Q-form').submit(function(event) {
            $('#note').data('customValue', quill.getContents());
            var customValue = $('#note').data('customValue');
            $('#note').val(JSON.stringify(customValue));
        });
    });
</script>
