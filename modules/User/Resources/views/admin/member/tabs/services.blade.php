<div class="services-management">
    <table class="table m-b-20">
        <thead>
            <tr>
                <th>Service ID</th>
                <th>Logo</th>
                <th>Service Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($services as $service)
                <tr data-service-id="{{ $service->id }}">
                    <td style="vertical-align: middle;">{{ $service->id }}</td>
                    <td style="vertical-align: middle;">
                        @if($service->logo && $service->logo->path)
                            <img src="{{ $service->logo->path }}" alt="{{ $service->name }}" style="width: 50px; height: 50px; object-fit: contain;">
                        @else
                            <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                <i class="las la-image" style="font-size: 24px; color: #999;"></i>
                            </div>
                        @endif
                    </td>
                    <td style="vertical-align: middle;">{{ $service->name }}</td>
                    <td style="vertical-align: middle;">
                        <span class="badge badge-{{ $service->is_enabled ? 'success' : 'danger' }} service-status">
                            {{ $service->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </td>
                    <td style="vertical-align: middle;">
                        <label class="switch">
                            <input type="checkbox" 
                                   class="service-toggle" 
                                   data-service-id="{{ $service->id }}"
                                   data-user-id="{{ $userId }}"
                                   {{ $service->is_enabled ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        No Services Found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
/* Hide save button on services tab */
#services.active + .form-group .btn-primary,
#services.active + .tab-pane.fade + .form-group .btn-primary {
    display: none;
}

/* Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #28a745;
}

input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 24px;
}

.slider.round:before {
    border-radius: 50%;
}

/* Disabled state */
input:disabled + .slider {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
$(document).ready(function() {
    // CSRF token setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle toggle switch change
    $('.service-toggle').on('change', function() {
        const $toggle = $(this);
        const serviceId = $toggle.data('service-id');
        const userId = $toggle.data('user-id');
        const isEnabled = $toggle.is(':checked');
        const $row = $toggle.closest('tr');
        const $statusBadge = $row.find('.service-status');

        if (isEnabled) {
            $statusBadge.removeClass('badge-danger').addClass('badge-success').text('Enabled');
        } else {
            $statusBadge.removeClass('badge-success').addClass('badge-danger').text('Disabled');
        }

        $toggle.prop('disabled', true);

        $.ajax({
            url: `/admin/member/${userId}/services/${serviceId}/toggle`,
            type: 'POST',
            data: {
                is_enabled: isEnabled
            },
            dataType: 'json',
            success: function(response) {
                $toggle.prop('disabled', false);
                
                if (response.success) {
                    if (response.is_enabled) {
                        $statusBadge.removeClass('badge-danger').addClass('badge-success').text('Enabled');
                    } else {
                        $statusBadge.removeClass('badge-success').addClass('badge-danger').text('Disabled');
                    }
                    console.log('Success:', response.message);
                    
                } else {
                    $toggle.prop('checked', !isEnabled);
                    if (!isEnabled) {
                        $statusBadge.removeClass('badge-danger').addClass('badge-success').text('Enabled');
                    } else {
                        $statusBadge.removeClass('badge-success').addClass('badge-danger').text('Disabled');
                    }
                    console.error('Error:', response.message || 'Failed to update service status');
                    alert(response.message || 'Failed to update service status');
                }
            },
            error: function(xhr, status, error) {
                $toggle.prop('disabled', false);
                $toggle.prop('checked', !isEnabled);
                if (!isEnabled) {
                    $statusBadge.removeClass('badge-danger').addClass('badge-success').text('Enabled');
                } else {
                    $statusBadge.removeClass('badge-success').addClass('badge-danger').text('Disabled');
                }
                
                let errorMessage = 'An error occurred while updating service status';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                console.error('Error:', error);
                alert(errorMessage);
            }
        });
    });
});
</script>
