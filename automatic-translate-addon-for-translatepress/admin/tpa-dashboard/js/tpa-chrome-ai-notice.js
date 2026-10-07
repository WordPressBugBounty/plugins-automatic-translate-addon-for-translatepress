jQuery(document).ready(function($) {
    // The new framework container is now output directly by PHP in settings.php

    // Add specific classes to TPA's dashboard cards so the framework can target them
    if ($('.tpa-provider-toggle').length) {
        $('.tpa-provider-toggle[data-provider="chrome-built-in-ai"]').closest('.tpa-dashboard-provider-card').addClass('tpa-card-chrome-built-in-ai');
        $('.tpa-provider-toggle[data-provider="edge-built-in-ai"]').closest('.tpa-dashboard-provider-card').addClass('tpa-card-edge-built-in-ai');
    }

    // Initialize the new framework
    if (typeof ChromeAINoticeFramework !== 'undefined' && typeof caisNoticeData !== 'undefined') {
        new ChromeAINoticeFramework({
            container: '#cais-chrome-setup-container',
            dataVar: 'caisNoticeData',
            providerTypes: ['chrome', 'edge'],
            cardSelectorPattern: '.tpa-card-{type}-built-in-ai',
            toggleSelectorPattern: '.tpa-card-{type}-built-in-ai .tpa-provider-toggle',
            configureBtnSelectorPattern: '.tpa-builtin-ai-configure-btn[data-provider="{type}"]',
            noticeClassPattern: 'tpa-{type}-configure-notice'
        });
    }

    // Inject CSS to hide the notice text (like autopoly) and hide the Configure button if the toggle is unchecked
    $('<style>')
        .text(`
            .tpa-chrome-configure-notice, .tpa-edge-configure-notice { display: none !important; }
            .tpa-dashboard-provider-card:has(.tpa-provider-toggle:not(:checked)) .tpa-builtin-ai-configure-btn { display: none !important; }
        `)
        .appendTo('head');
});
