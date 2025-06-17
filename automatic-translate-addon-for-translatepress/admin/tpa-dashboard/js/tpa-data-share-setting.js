jQuery(function($) {
    const $termsLink = $('.tpa-see-terms');
    const $termsBox = $('#termsBox');

    $termsLink.on('click', function(e) {
        e.preventDefault();
        const isVisible = $termsBox.toggle().is(':visible');
        $(this).html(isVisible ? '[Hide terms]' : '[See terms]');
    });
});
