<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_send_email_enabled', trans('storefront::attributes.storefront_send_email_enabled_for_cron'), trans('storefront::storefront.tabs.send_mail_for_address'), $errors, $settings) }}
        
        <div class="hide" id="email-template-section">
            {{ Form::select('storefront_template_id', trans('storefront::attributes.select_template'), $errors, $emailTemplates, $settings) }}
            <div class="media-picker-divider"></div>
            <div class="form-group">
                <label class="col-md-3 control-label text-left">Send Email Manually</label>
                <div class="col-md-9">                    
                    <button class="btn btn-primary btn-sm" id="send-email-button" type="button">
                        <i class="fa fa-paper-plane"></i>
                        <span class="button-text">Send Email Now</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="media-picker-divider p-tb-15"></div>

        {{ Form::checkbox('send_notification_for_address', trans('storefront::attributes.storefront_send_notification_for_address'), trans('storefront::storefront.tabs.send_notification_for_address'), $errors, $settings) }}
        <div class="form-group">
            <label class="col-md-3 control-label text-left">Send Notification Manually</label>
            <div class="col-md-9">
                <button class="btn btn-primary btn-sm" id="send-notification-button" type="button">
                    <i class="fa fa-bell"></i>
                    <span class="button-text">Send Notification Now</span>
                </button>
            </div>
        </div>
        <div class="media-picker-divider p-tb-15"></div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#storefront_send_email_enabled').change(function() {
                if ($(this).is(':checked')) {
                    $('#email-template-section').removeClass('hide');
                } else {
                    $('#email-template-section').addClass('hide');
                }
            });

            $('#storefront_send_email_enabled').trigger('change');

            $('#send-email-button').click(function() {
                confirm('Are you sure you want to send the email?') && sendEmail();
            });
            function sendEmail() {
                var $button = $('#send-email-button');
                var $spinner = $button.find('.spinner-border');
                var $buttonText = $button.find('.button-text');
                var templateId = $('#storefront_template_id').val();                
                
                if (templateId) {

                    $button.prop('disabled', true);
                    $spinner.removeClass('d-none');
                    $buttonText.text('Sending...');

                    $.ajax({
                        url: '{{ route("send-email-manually") }}',
                        type: 'POST',
                        data: {
                            template_id: templateId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log(response);
                            alert('Email sent successfully!');
                        },
                        error: function(xhr) {
                            alert('Error sending email: ' + xhr.responseText);
                        },
                        complete: function() {
                            $button.prop('disabled', false);
                            $spinner.addClass('d-none');
                            $buttonText.text('Send Email Now');
                        }
                    });
                } else {
                    alert('Please select a template first.');
                }
            }

            $('#send-notification-button').click(function() {
                confirm('Are you sure you want to send the notification?') && sendNotification();
            });
            function sendNotification() {
                var $button = $('#send-notification-button');
                $button.prop('disabled', true);
                $.ajax({
                    url: '{{ route("send-notification-manually") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Notification sent successfully!');
                    },
                    error: function(xhr) {
                        alert('Error sending notification: ' + xhr.responseText);
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            }
        });
    </script>
@endpush