@extends('layouts.app')

@section('content')
    @include('layouts.menu-bar')

    <div class="container">
        <div class="text-center mt-2">
            <h1>Rent Data</h1>
        </div>
        <div class="row">
            <div class="col col-3 form-group">
                <label for="start_date" class="mb-1">Start Date</label>
                <input type="text" name="start_date" id="start_date" class="form-control data-date" readonly>
                <label class="text-danger form-text"></label>
            </div>
            <div class="col col-3 form-group">
                <label for="end_date" class="mb-1">End Date</label>
                <input type="text" name="end_date" id="end_date" class="form-control data-date" readonly>
                <label class="text-danger form-text"></label>
            </div>
            <div class="col col-3 form-group">
                <button type="button" class="btn btn-primary" style="margin-top: 1.7rem"
                    id="btn-search-date">Search</button>
                <button type="button" class="btn btn-secondary" style="margin-top: 1.7rem"
                    id="btn-reset-date">Reset</button>
            </div>
        </div>
        <canvas id="rentChart" class="h500">
        </canvas>
        <div class="mt-5 mb-2">
            <div style="float: right">
                <a href="{{ url('rent/insert-rent') }}">
                    <button type="button" class="btn btn-primary my-2">Rent Car</button>
                </a>
                <button type="button" class="btn btn-success my-2" id="btn-export-excel">
                    <i class="fa-solid fa-file-excel"></i>
                </button>
            </div>
            <table id="table-data" class="table table-bordered table-hover mb-2">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Car</th>
                        <th scope="col">Driver</th>
                        <th scope="col">Rent Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
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
        $(document).ready(function() {
            $(document).on({
                ajaxStart: function() {
                    loaderForm($("#preloader"), "show")
                },
                ajaxStop: function() {
                    loaderForm($("#preloader"), "hide")
                }
            })

            $(".data-date").datepicker({
                format: "yyyy-mm-dd",
                todayHighlight: true,
                autoclose: true,
            })

            const tableData = $('#table-data').DataTable({
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                paging: true,
                info: false,
                pagingType: 'simple_numbers',
                ajax: {
                    "url": "{{ url('rent/data') }}",
                    "type": "POST",
                    "headers": {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    "data": function(d) {
                        if ($("#start_date").val() != '') {
                            d.start_date = $("#start_date").val()
                        }

                        if ($("#end_date").val() != '') {
                            d.end_date = $("#end_date").val()
                        }
                    }
                },
                columns: [{
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
                        data: 'status',
                        name: 'status',
                        render: function(data, type, full, meta) {
                            let status = full['status']

                            switch (status) {
                                case 1:
                                    return '<span class="badge bg-primary">Active</span>'
                                    break;

                                case 2:
                                    return '<span class="badge bg-success">Returned</span>'
                                    break;

                                case 3:
                                    return '<span class="badge bg-warning text-dark">Approval</span>'
                                    break;

                                case 4:
                                    return '<span class="badge bg-danger">Rejected</span>'
                                    break;

                                default:
                                    return '<span class="badge bg-secondary">-</span>'
                                    break;
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function(data, type, full, meta) {
                            let id = full['id']
                            let status = full['status']

                            switch (status) {
                                case 1:
                                    return `<button data-id=${id} type="button" class="btn btn-primary my-2 btn-mark-return">Mark Return</button>`
                                    break;

                                default:
                                    return `<button data-id=${id} type="button" class="btn btn-secondary my-2 btn-mark-return" disabled>No Action</button>`
                                    break;
                            }
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

            $("#table-data").on("click", ".btn-mark-return", function() {
                let id = $(this).data("id")
                Swal.fire({
                    title: `Are you sure you want to mark it as "returned" this?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: `Yes, mark it as "returned"!`
                }).then((result) => {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('rent/return') }}",
                        data: {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.meta.code == "200") {
                                SwalSuccess(response.message)

                                setTimeout(() => {
                                    window.location.href =
                                        "{{ url('/rent/all') }}"
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
                })
            });

            $("#btn-export-excel").click(function(e) {
                e.preventDefault();

                let url = "{{ url('/rent/export-excel') }}"

                if ($("#start_date").val() != '') {
                    url = url + "?start_date=" + $("#start_date").val()
                }

                if ($("#end_date").val() != '') {
                    if ($("#start_date").val() != '') {
                        url = url + "&end_date=" + $("#end_date").val()
                    } else {
                        url = url + "?end_date=" + $("#end_date").val()
                    }
                }

                window.open(url)
            });

            $("#btn-search-date").click(function(e) {
                e.preventDefault();
                let startDate = $("#start_date").val();
                let endDate = $("#end_date").val();

                let error = 0
                if (startDate != '' && endDate != '') {
                    if (new Date(startDate) > new Date(endDate)) {
                        error += 1
                        $("#start_date").closest('.form-group').find('.form-text').html(
                            "Bigger than end date!");
                    } else {
                        $("#start_date").closest('.form-group').find('.form-text').html("");
                    }
                }

                if (error == 0) {
                    tableData.ajax.reload();
                    rentChartJs.destroy()
                    rentChart()
                }
            });

            $("#btn-reset-date").click(function(e) {
                e.preventDefault();
                $("#start_date").val('');
                $("#end_date").val('');

                tableData.ajax.reload();
                rentChartJs.destroy()
                rentChart()
            });

            rentChart()
        });

        function rentChart() {
            let data = {}

            let url = "{{ url('rent/chart') }}"

            if ($("#start_date").val() != '') {
                url = url + "?start_date=" + $("#start_date").val()
            }

            if ($("#end_date").val() != '') {
                if ($("#start_date").val() != '') {
                    url = url + "&end_date=" + $("#end_date").val()
                } else {
                    url = url + "?end_date=" + $("#end_date").val()
                }
            }

            $.ajax({
                type: "GET",
                url: url,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.meta.code == "200") {
                        data = response.data

                        let cData = []
                        let cLabel = []

                        $.each(data, function(key, value) {
                            cLabel.push(value.car)
                            cData.push(value.total)
                        });

                        let rent = $("#rentChart")

                        rentChartJs = new Chart(rent, {
                            type: 'bar',
                            data: {
                                labels: cLabel,
                                datasets: [{
                                    axis: 'y',
                                    label: 'Total of Rent',
                                    data: cData,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)',
                                        'rgba(255, 159, 64, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                        })
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
    </script>
@endsection
