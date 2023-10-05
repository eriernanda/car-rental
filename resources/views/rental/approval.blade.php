@extends('layouts.app')

@section('content')
    @include('layouts.menu-bar')

    <div class="container">
        <div class="text-center mt-2">
            <h1>User Approval Data</h1>
        </div>
        <div class="mt-5 mb-2">
            <table id="table-data" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%" scope="col">#</th>
                        <th style="width: 25%" scope="col">Car</th>
                        <th style="width: 25%" scope="col">Driver</th>
                        <th style="width: 20%" scope="col">Rent Date</th>
                        <th style="width: 25%" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
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

            $('#table-data').DataTable({
                serverSide: true,
                searching: false,
                ordering:  false,
                lengthChange: false,
                paging: true,
                info: false,
                pagingType: 'simple_numbers',
                ajax: {
                    "url": "{{ url('rent/approval-data') }}",
                    "type": "POST",
                    "headers": { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                },
                columns: [
                    {
                        data: 'num',
                        name: 'num'
                    },
                    {
                        data: 'car',
                        name: 'car'
                    },
                    {
                        data: 'driver',
                        name: 'driver'
                    },
                    {
                        data: 'rent_date',
                        name: 'rent_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function (data, type, full, meta) {
                            let id = full['id']

                            return `
                            <button data-id=${id} type="button" class="btn btn-success my-2 btn-mark-approve">Approve</button>
                            &nbsp;
                            <button data-id=${id} type="button" class="btn btn-danger my-2 btn-mark-reject">Reject</button>
                            `
                        }
                    },
                ],
                language: {
                    paginate: {
                        previous: "<i class='fa fa-angle-left' aria-hidden='true'></i>",
                        next: " <i class='fa fa-angle-right' aria-hidden='true'></i> ",
                    },
                },
                dom: '<"top"Bl<"clear">>rt<"bottom"ip<"clear">>',
            });

            $("#table-data").on("click", ".btn-mark-approve",  function () {
                let id = $(this).data("id")
                approve(id, 1)
            });

            $("#table-data").on("click", ".btn-mark-reject",  function () {
                let id = $(this).data("id")
                approve(id, 2)
            });
        });

        function approve(id, status) {
            let message = "approve"
            if (status == 2) {
                message = "reject"
            }

            Swal.fire({
                title: `Are you sure you want to ${message} this?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Yes, ${message} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('rent/approve') }}",
                        data: {
                            id: id,
                            status: status,
                        },
                        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                        success: function (response) {
                            if (response.meta.code == "200") {
                                SwalSuccess(response.message)

                                setTimeout(() => {
                                    window.location.href = "{{ url('/rent/approval') }}"
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
        }
    </script>
@endsection
