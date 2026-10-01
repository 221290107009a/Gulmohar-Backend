@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('emailtemplate::emailtemplates.emailtemplates'))

    <li class="active">{{ trans('emailtemplate::emailtemplates.emailtemplates') }}</li> 
@endcomponent

@section('content')
<div class="row">
    <div class="btn-group pull-right">
        <a href="{{ route('admin.emailtemplates.create') }}" class="btn btn-primary btn-actions btn-create">
            Create Email Template
        </a>
    </div>
</div>

<div class="box box-primary">
    <div class="box-body index-table" id="email_templates-table">
    <button type="button" class="btn-delete cust-delete" style="">Delete</button>
        @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')
                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('emailtemplate::emailtemplates.table.name') }}</th>                 
                <th>{{ trans('admin::admin.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                <th>{{ trans('emailtemplate::emailtemplates.table.actions') }}</th>
            </tr>
        @endslot
        @endcomponent
    </div>
</div>
@endsection

@push('scripts')
    <script type="module">
        DataTable.setRoutes('#email_templates-table .table', {
            table: '{{ "admin.emailtemplates.table" }}',
            show: '{{ "admin.emailtemplates.edit" }}',
        });

        new DataTable('#email_templates-table .table', {            
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name' },                
                { data: 'status', searchable: false },
                { data: 'created', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'clone-main' },
            ],
        });
        
        $('#DataTables_Table_0').on('click', '.btn-clone', function (event) {            
            event.stopPropagation();            
            var id = $(this).data('id');
            
            var url = route('admin.emailtemplates.duplicate', id);
            window.location.href = url;            
        });

        $(document).ready(function(){            
            $("#-select-all").on('change', function(){
                $(".clickable-row .select-row").prop('checked', $(this).prop('checked'));
            });

            $('#email_templates-table').on('click', '.cust-delete', function (e){
                e.preventDefault();
                
                var deleteIds = [];
                $('input:checkbox.select-row:checked').each(function() {
                    deleteIds.push($(this).val());
                });

                if (deleteIds.length === 0) {
                    alert('Please select at least one record to delete');
                    return;
                }

                var confirmBox = $("#confirmation-modal");
                confirmBox.modal('show');

                confirmBox.find('.delete').one('click', function(e) {
                    confirmBox.modal('hide');
                    
                    $.ajax({
                        url: "{{ route('admin.emailtemplates.destroy') }}",
                        type: 'DELETE',
                        data: { id: deleteIds },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response){
                            if(response == '1'){
                                alert("Approved records cannot be deleted");
                            } else {
                                window.location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert("Error occurred while deleting records");
                            console.error(xhr.responseText);
                        }
                    });
                });
            });
        });
    </script>
@endpush