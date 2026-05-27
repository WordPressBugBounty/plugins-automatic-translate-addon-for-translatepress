(function($){

    $(document).ready(function(){

         $('.tpa_dismiss_notice').on('click', function (event) {
                var thisE = $(this);
                var wrapper=thisE.parents('.tpa-feedback-notice-wrapper');
                var ajaxURL=wrapper.data('ajax-url');
                var ajaxCallback=wrapper.data('ajax-callback');
                var nonce=wrapper.data('nonce');
                $.post(ajaxURL, { 'action':ajaxCallback, 'nonce':nonce }, function( data ) {
                    if(data.success) {
                        wrapper.slideUp('fast');
                    }
                  }, 'json');
            });
            
        let plugin_slug = 'automatic-translate-addon-for-translatepress';
		let plugin_prefix = 'TPA';
        const $feedbackDialog = $("#cool-plugins-deactivate-feedback-dialog-wrapper[data-slug='" + plugin_slug + "']");

        function showFeedbackValidationError(message) {
            let $error = $feedbackDialog.find('.cool-plugins-feedback-error');
            if (!$error.length) {
                $error = $('<p class="cool-plugins-feedback-error" role="alert"></p>');
                $feedbackDialog.find('.cool-plugin-popup-button-wrapper').before($error);
            }
            $error.text(message).show();
        }

        function clearFeedbackValidationError() {
            $feedbackDialog.find('.cool-plugins-feedback-error').hide().text('');
            $feedbackDialog.find('.cool-plugins-feedback-text').removeClass('cool-plugins-feedback-input-error');
        }

        $target = $('#the-list').find('[data-slug="'+plugin_slug+'"] span.deactivate a');

        var plugin_deactivate_link = $target.attr('href');

        $($target).on('click', function(event) {
            event.preventDefault();
            $('#wpwrap').css('opacity', '0.4');
        
            $("#cool-plugins-deactivate-feedback-dialog-wrapper[data-slug='" + plugin_slug + "']").animate({
                opacity: 1
            }, 200, function() {
                $("#cool-plugins-deactivate-feedback-dialog-wrapper[data-slug='" + plugin_slug + "']").removeClass('hide-feedback-popup');
                $("#cool-plugins-deactivate-feedback-dialog-wrapper[data-slug='" + plugin_slug + "']").find('#cool-plugin-submitNdeactivate').addClass(plugin_slug);
                $("#cool-plugins-deactivate-feedback-dialog-wrapper[data-slug='" + plugin_slug + "']").find('#cool-plugin-skipNdeactivate').addClass(plugin_slug);
            });
        });

        $('.cool-plugins-deactivate-feedback-dialog-input').on('click',function(){
            clearFeedbackValidationError();
            if($(`#cool-plugins-GDPR-data-notice-${plugin_prefix}`).is(":checked") === true && $('.cool-plugins-deactivate-feedback-dialog-input').is(':checked') === true){
                $('#cool-plugin-submitNdeactivate').removeClass('button-deactivate');
            }
            else{
                $('#cool-plugin-submitNdeactivate').addClass('button-deactivate');
            }

        });

        $feedbackDialog.on('input', '.cool-plugins-feedback-text', clearFeedbackValidationError);

        $(`#cool-plugins-GDPR-data-notice-${plugin_prefix}`).on('click', function(){
            clearFeedbackValidationError();
            if($(`#cool-plugins-GDPR-data-notice-${plugin_prefix}`).is(":checked") === true && $('.cool-plugins-deactivate-feedback-dialog-input').is(':checked') === true){ 
                $('#cool-plugin-submitNdeactivate').removeClass('button-deactivate');
            }
            else{
                $('#cool-plugin-submitNdeactivate').addClass('button-deactivate');
            }
        })

        $('#wpwrap').on('click', function(ev){
            if( $("#cool-plugins-deactivate-feedback-dialog-wrapper.hide-feedback-popup").length==0 ){
                ev.preventDefault();
                $("#cool-plugins-deactivate-feedback-dialog-wrapper").animate({
                    opacity:0
                },200,function(){
                    $("#cool-plugins-deactivate-feedback-dialog-wrapper").addClass("hide-feedback-popup");
                    $("#cool-plugins-deactivate-feedback-dialog-wrapper").find('#cool-plugin-submitNdeactivate').removeClass(plugin_prefix);
                    clearFeedbackValidationError();
                    $('#wpwrap').css('opacity','1');
                })

            }
        })

        function submitDeactivationFeedback() {
            const $submit = $('#cool-plugin-submitNdeactivate');
            if ($submit.hasClass('button-deactivate') || !$submit.hasClass(plugin_slug)) {
                return;
            }

            const nonce = $('#_wpnonce').val();
            const reason = $('.cool-plugins-deactivate-feedback-dialog-input:checked').val();
            let message = '';

            if ($('textarea[name="reason_' + reason + '"]').length > 0) {
                const $reasonTextarea = $('textarea[name="reason_' + reason + '"]');
                if ($reasonTextarea.val().trim() === '') {
                    showFeedbackValidationError('Please provide some extra information!');
                    $reasonTextarea.addClass('cool-plugins-feedback-input-error').focus();
                    return;
                }
                clearFeedbackValidationError();
                message = $reasonTextarea.val();
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: plugin_prefix + '_submit_deactivation_response',
                    _wpnonce: nonce,
                    reason: reason,
                    message: message,
                },
                beforeSend: function () {
                    $submit.text('Deactivating...');
                    $submit.attr('id', 'deactivating-plugin');
                    $('#cool-plugins-loader-wrapper').show();
                    $('#cool-plugin-skipNdeactivate').remove();
                },
                success: function () {
                    $('#cool-plugins-loader-wrapper').hide();
                    window.location = plugin_deactivate_link;
                    $('#deactivating-plugin').text('Deactivated');
                },
            });
        }

        $(document).on('submit', '#cool-plugins-deactivate-feedback-dialog-form', function (event) {
            event.preventDefault();
            submitDeactivationFeedback();
        });

        $(document).on('click', '#cool-plugin-skipNdeactivate.'+plugin_slug+':not(".button-deactivate")', function(){
            $('#cool-plugin-submitNdeactivate').remove();
            $('#cool-plugin-skipNdeactivate').addClass('button-deactivate');
            $('#cool-plugin-skipNdeactivate').attr('id','deactivating-plugin');
            window.location = plugin_deactivate_link;
        });

    });
})(jQuery);