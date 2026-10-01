<div class="d-flex m-b-20" style="justify-content: end;">
    <button class="btn btn-primary assign-service-to-user" data-service-id="{{ $service->id }}">Assign Service to User</button>
</div>
<div class="users-list">
    <table class="table m-b-20">
        <thead>
            <tr>
                <th>User ID</th>
                <th>User Name</th>
                <th>User Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usersList as $user)
                @php
                    $userArray = $user->user->toArray();
                @endphp
                <tr data-user-id="{{ $user->user_id }}">
                    <td style="vertical-align: middle;"><a href="{{ route('admin.member.edit', $user->user_id) }}">{{ $user->user_id }}</a></td>
                    <td style="vertical-align: middle;">{{ $userArray['fullname'] }}</td>
                    <td style="vertical-align: middle;">{{ $user->user->email }}</td>
                    <td style="vertical-align: middle;">
                        <span class="badge badge-{{ $user->is_enabled ? 'success' : 'danger' }} user-status">
                            {{ $user->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </td>
                    <td style="vertical-align: middle;">
                        <label class="switch">
                            <input type="checkbox" 
                                   class="user-toggle" 
                                   data-service-id="{{ $user->service_id }}"
                                   data-user-id="{{ $user->user_id }}"
                                   {{ $user->is_enabled ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        No Users Found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- User Assignment Modal -->
<div class="modal fade" id="assignUserModal" tabindex="-1" role="dialog" aria-labelledby="assignUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignUserModalLabel" style="float: left;">Assign Service to User</h5>
                <button type="button" class="close" data-dismiss="modal" style="-webkit-text-stroke: unset;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="input-fields">
                    <label for="userSearch">Search User</label>
                    <input type="text" 
                           class="form-control" 
                           id="userSearch" 
                           placeholder="Search by name, email, or mobile number...">
                    <small class="form-text text-muted">Type at least 2 characters to search</small>
                </div>
                <div id="searchResults" class="search-results">
                    <!-- Search results will be displayed here -->
                </div>
                <div id="searchLoading" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

/* Modal Styles */
.search-results {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 15px;
}

.user-result-item {
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.user-result-item:hover {
    background-color: #f5f5f5;
    border-color: #007bff;
}

.user-result-item .user-name {
    font-weight: 600;
    color: #333;
}

.user-result-item .user-email {
    color: #666;
    font-size: 0.9em;
}

.user-result-item .user-phone {
    color: #888;
    font-size: 0.85em;
}

.no-results {
    text-align: center;
    padding: 20px;
    color: #999;
}
</style>
<script>
$(document).ready(function() {
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.user-toggle').on('change', function() {
        const $toggle = $(this);
        const serviceId = $toggle.data('service-id');
        const userId = $toggle.data('user-id');
        const isEnabled = $toggle.is(':checked');
        const $row = $toggle.closest('tr');
        const $statusBadge = $row.find('.user-status');

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

    /*  User Assignment Modal */
    let searchTimeout;
    const serviceId = $('.assign-service-to-user').data('service-id');

    /*  Open modal on button click */
    $('.assign-service-to-user').on('click', function(e) {
        e.preventDefault();
        $('#assignUserModal').modal('show');
        $('#userSearch').val('');
        $('#searchResults').html('');
    });

    /*  Search users with debouncing */
    $('#userSearch').on('input', function() {
        const query = $(this).val().trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            $('#searchResults').html('');
            return;
        }

        $('#searchLoading').show();
        $('#searchResults').html('');

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: `/admin/service-categories/${serviceId}/search-users`,
                type: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(response) {
                    $('#searchLoading').hide();
                    
                    if (response.success && response.users.length > 0) {
                        let resultsHtml = '';
                        response.users.forEach(function(user) {                            
                            resultsHtml += `
                                <div class="user-result-item" data-user-id="${user.id}">
                                    <div class="user-name">${user.fullname || 'N/A'}</div>
                                    <div class="user-email">${user.email || 'N/A'}</div>
                                    <div class="user-phone">${user.phone || 'N/A'}</div>
                                </div>
                            `;
                        });
                        $('#searchResults').html(resultsHtml);
                    } else {
                        $('#searchResults').html('<div class="no-results">No users found</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#searchLoading').hide();
                    $('#searchResults').html('<div class="no-results text-danger">Error searching users</div>');
                    console.error('Search error:', error);
                }
            });
        }, 500); // 500ms debounce
    });

    // Handle user selection
    $(document).on('click', '.user-result-item', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).find('.user-name').text();

        if (confirm(`Assign this service to ${userName}?`)) {
            $.ajax({
                url: `/admin/service-categories/${serviceId}/assign-user`,
                type: 'POST',
                data: {
                    user_id: userId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#assignUserModal').modal('hide');
                        // Reload the page to show the newly assigned user
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to assign user');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'An error occurred while assigning user';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                    console.error('Assignment error:', error);
                }
            });
        }
    });
});
</script>
