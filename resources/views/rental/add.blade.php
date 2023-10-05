@extends('layouts.app')

@section('content')
    @include('layouts.menu-bar')

    <div class="container">
        <h1 class="text-center mt-3 mb-5">Add Data</h1>
        <form id="form-add">
            <h2>Rent Form</h2>

            <div class="row m-3">
                <div class="col col-6 form-group">
                    <label for="rn_car" class="mb-1">Car</label>
                    <select class="form-select" name="rn_car" id="rn_car">
                            <option value="" hidden>Choose Car</option>
                        @foreach ($data['car'] as $item)
                            <option value="{{ $item->id }}"><?= $item->name . " (" . $item->police_number . ")" ?></option>
                        @endforeach
                    </select>
                    <label class="text-danger form-text"></label>
                </div>
                <div class="col col-6 form-group">
                    <label for="rn_driver" class="mb-1">Driver</label>
                    <select class="form-select" name="rn_driver" id="rn_driver">
                            <option value="" hidden>Choose Driver</option>
                        @foreach ($data['driver'] as $item)
                            <option value="{{ $item->id }}"><?= $item->name . " (" . $item->gender . ")" ?></option>
                        @endforeach
                    </select>
                    <label class="text-danger form-text"></label>
                </div>
            </div>
            <div class="row m-3">
                <div class="col col-6 form-group">
                    <label for="rn_date" class="mb-1">Rent Date</label>
                    <input type="text" name="rn_date" id="rn_date" class="form-control" readonly>
                    <label class="text-danger form-text"></label>
                </div>
                <div class="col col-6 form-group">
                    <label for="rn_approval" class="mb-1">Approval</label>
                    <select class="form-control form-select" name="rn_approval[]" id="rn_approval" multiple="multiple">
                            <option value="" hidden>Choose Approval</option>
                        @foreach ($data['user'] as $item)
                            <option value="{{ $item->id }}"><?= $item->name ?></option>
                        @endforeach
                    </select>
                    <label class="text-danger form-text"></label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-2 w-25 h-25" style="float: right">Submit</button>
        </form>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $("select").select2()

            $("#rn_date").datepicker({
                format: "yyyy-mm-dd",
                todayHighlight: true,
                autoclose: true,
            })

            $("#form-add").validate({
                rules: {
                    "rn_car": {
                        required: true,
                    },
                    "rn_date": {
                        required: true,
                    },
                    "rn_driver": {
                        required: true,
                    },
                    "rn_approval[]": {
                        required: true,
                    },
                },
            errorPlacement: function (error, element) {
                $(element).closest('.form-group').find('.form-text').html(error.html());
            },
            success: function (label, element) {
                $(element).closest('.form-group').find('.form-text').html('');
            },
            submitHandler: function (form, event) {
                let formData = $(form).serialize()

                $.ajax({
                    type: "POST",
                    url: "{{ url('/rent/add-rent') }}",
                    data: formData,
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.meta.code == "200") {
                            SwalSuccess(response.message)

                            setTimeout(() => {
                                window.location.href = "{{ url('/rent/all') }}"
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, err) {
                        data = xhr.responseJSON
                        if (data.meta.code == 500) {
                            SwalError("Error server. Try again in a while")
                        } else {
                            SwalError(data.meta.message)
                        }
                    }
                });
            }
        })
        });
    </script>
@endsection
