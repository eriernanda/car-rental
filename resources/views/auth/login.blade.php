@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-center mb-5">Login Form</h1>
        <form id="form-login">
            <!-- Email input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="email">Email</label>
                <input name="email" type="email" id="email" class="form-control" />
            </div>

            <!-- Password input -->
            <div class="form-outline mb-4">
                <label class="form-label" for="password">Password</label>
                <input name="password" type="password" id="password" class="form-control" />
            </div>

            <!-- Submit button -->
            <button type="submit" class="float-right btn btn-primary btn-block mb-4">Sign in</button>
        </form>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function () {
        $(document).on({
            ajaxStart: function(){
                loaderForm($("#preloader"), "show")
            },
            ajaxStop: function(){
                loaderForm($("#preloader"), "hide")
            }
        })

        $("#form-login").validate({
            rules: {
                email: {
                    required: true,
                },
                password: {
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
                    url: "{{ url('login') }}",
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
