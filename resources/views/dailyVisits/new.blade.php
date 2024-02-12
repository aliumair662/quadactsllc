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
                        <div>Daily Visits
                            <div class="page-title-subheading">
                                {{-- This is an example dashboard created using build-in
                                elements and components. --}}
                            </div>
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <a href="{{ route('dailyVisitList') }}">
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
                    action="{{ isset($dailyVisit) ? route('updatedailyVisit') : route('saveDailyVisit') }}"
                    method="post">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Add Visit Details</h5>
                        <div class="form-row">
                            <div class="col-md-2">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Visit ID</label>
                                    <input name="invoice_number" id="invoice_number" placeholder="" type="text"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->invoice_number : Config::get('constants.DAILY_VISITS_PREFIX') . $invoice_number }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Current Date & Time</label>
                                    <input style="text-align:center;" name="invoice_date" id="invoice_date"
                                        placeholder="" type="text"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->invoice_date : now()->format('d-m-Y h:i:s') }}"
                                        class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Status</label>
                                    <select class="js-example-basic-single form-control"
                                        placeholder="Select Visit Status" name="status_id" id="status_id">
                                        <option value="">Select Status</option>
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
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="exampleEmail11" class="">Business Name</label>
                                    <input name="name" id="text" placeholder="Enter Name" type="text"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->name : '' }}" class="form-control">
                                    <input name="id" id="id"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->id : '' }}" type="hidden"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="position-relative form-group">
                                    <label for="password" class="">Email</label>
                                    <input name="email" id="password" placeholder="Enter Email" type="email"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->email : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
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
                        </div>
                        {{-- d-none --}}
                        <div class="form-row">
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
                        </div>
                        <div class="form-row">
                            <div class="col-md-12">
                                <div class="position-relative form-group">
                                    <label for="location" class="">Location</label>
                                    <input name="location" id="location"
                                        value="{{ isset($dailyVisit) ? $dailyVisit->location : '' }}" type="text"
                                        class="form-control" placeholder="Location" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <label for="exampleEmail11" class="">Description</label>
                                <textarea name="description" id="description" placeholder="" type="text" value="" class="form-control">{{ isset($dailyVisit) ? $dailyVisit->description : '' }}</textarea>
                            </div>
                            <div class="col-md-3">
                                <div class="position-relative form-group">
                                    <label for="avatar" class="">Attachment</label>
                                    <input name="attachment" id="attachment" type="file"
                                        class="form-control-file" accept="image/*" capture="camera">
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if (isset($dailyVisit))
                                    <div class="widget-content-left">
                                        <img width="100" class=""
                                            src="{{ asset($dailyVisit->attachment) }}" alt="">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end px-4 pb-3">
                        <button type="submit" class="mt-2 btn btn-primary"
                            id='save_button'>{{ isset($dailyVisit) ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBWCT-3sgRjyld2IKVaHtG8EeVoF6G7JMY&libraries=places">
    </script>
    <script>
        // Function to get user's location
        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        // Callback function to handle successful position retrieval
        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            $("#longitute").val(longitude);
            $("#latitude").val(latitude);

            // Use reverse geocoding to get address
            getAddressFromCoordinates(latitude, longitude);
        }

        // Callback function to handle errors during geolocation
        function showError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }
        // Function to get address from coordinates using reverse geocoding
        function getAddressFromCoordinates(latitude, longitude) {
            console.log("in the address method");
            const geocoder = new google.maps.Geocoder();
            const latlng = new google.maps.LatLng(latitude, longitude);

            geocoder.geocode({
                'latLng': latlng
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        console.log("in the success condition");
                        const address = results[0].formatted_address;
                        $("#location").val(address);
                    } else {
                        alert("No address found");
                    }
                } else {
                    alert("Geocoder failed due to: " + status);
                }
            });
        }

        // Get user's location on page load
        window.addEventListener('load', getUserLocation);
    </script>
</x-app-layout>
