jQuery(document).ready(function($){
    $('.cpt-dashboard-tab').click(function(){
        var tab = $(this).data('tab');
        $('.cpt-dashboard-table').hide();
        $('#cpt-'+tab+'-table').show();

        $('.cpt-dashboard-tab').removeClass('cpt-active');
        $(this).addClass('cpt-active');

        $('.cpt-dashboard-tables').find('table').hide();
        $('#cpt-'+tab+'-table').show();
    });

    $('.tpa-review-notice-dismiss button').click(function(){
        var $button = $(this);
        var prefix = $button.closest('.tpa-review-notice-dismiss').data('prefix');
        var nonce = $button.closest('.tpa-review-notice-dismiss').data('nonce');
        var $notice = $button.closest('.cpt-review-notice');

        $.post(ajaxurl, {
            action: 'tpa_hide_review_notice',
            prefix: prefix,
            nonce: nonce
        }, function(response) {
            if (response && response.success) {
                $notice.slideUp();
            } else {
                console.error('Failed to hide review notice:', response);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Failed to hide review notice:', textStatus, errorThrown);
        });
    });
});