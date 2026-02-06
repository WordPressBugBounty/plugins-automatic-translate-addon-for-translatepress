jQuery(function($) {
    /* =========================
     * Terms show / hide
     * ========================= */
    const $termsLink = $('.tpa-see-terms');
    const $termsBox = $('#termsBox');

    $termsLink.on('click', function(e) {
        e.preventDefault();
        const isVisible = $termsBox.toggle().is(':visible');
        $(this).html(isVisible ? '[Hide terms]' : '[See terms]');
    });

    /* =========================
     * Plugin install button
     * ========================= */
     $(document).on('click', '.tpa-install-plugin', function (e) {

        e.preventDefault();
    
        let button   = $(this);
        let $wrapper = button.closest('.tpa-dashboard-addon-l');
        let slug     = button.data('slug');
        let nonce    = button.data('nonce');
        const originalText = button.text().trim();
        
        // Determine action based on button text
        let action = 'install';
        if (originalText.toLowerCase() === 'activate' || originalText.toLowerCase().includes('activate')) {
            action = 'activate';
        }
    
        $wrapper.find('.tpa-install-message').empty();
    
        if (!slug || !nonce || typeof ajaxurl === 'undefined') {
            $wrapper.find('.tpa-install-message')
                .text('Missing required data. Please reload the page.');
            return;
        }
    
        // Show appropriate loading text based on action
        button.text(action === 'activate' ? 'Activating...' : 'Installing...');
        $('.tpa-install-plugin').prop('disabled', true);
    
        $.post(ajaxurl, {
            action: 'tpa_install_plugin',
            slug: slug,
            plugin_action: action,
            _wpnonce: nonce
        }, function (response) {
            if (response && response.success) {
    
                const $container = button.closest('.tpa-dashboard-addon-l');
                if (response.data && response.data.activated === true) {
                    button.remove();
                    $container.find('.tpa-install-message').remove();
        
                    $container.append(`
                        <span class="installed">Activated</span>
                    `);
                } else {
                    // Not activated yet (e.g. Loco Translate missing)
                    let message = 'Installed successfully.';
                    if (response.data && response.data.message) {
                        message = response.data.message;
                    }
                    $container.find('.tpa-install-message').text(message);
                    button.text('Activate').prop('disabled', false);
                }
    
            }else {
                let errorMessage = 'Activation failed. Please try again.';
                    // Normal case: try to get message from response
                    if (response && response.data) {
                        if (typeof response.data === 'string') {
                            errorMessage = response.data;
                        } else if (response.data.message) {
                            errorMessage = response.data.message;
                        }
                    }
                // Show the notice and re-enable the button
                $wrapper.find('.tpa-install-message').text(errorMessage);
                button.text(originalText).prop('disabled', false);
            }
                        
    
            $('.tpa-install-plugin').not(button).prop('disabled', false);
        });
    });

    /* =========================
     * Provider Toggle Switches
     * Save provider states to database
     * ========================= */
    $(document).on('change', '.tpa-provider-toggle', function() {
        const $toggle = $(this);
        const provider = $toggle.data('provider');
        
        // Skip if toggle is disabled (Pro providers)
        if ($toggle.prop('disabled')) {
            return;
        }

        // Get current states of all provider toggles
        const yandexToggle = $('.tpa-provider-toggle[data-provider="yandex-translate"]');
        const chromeToggle = $('.tpa-provider-toggle[data-provider="chrome-built-in-ai"]');
        
        const yandexEnabled = yandexToggle.length ? (yandexToggle.is(':checked') ? '1' : '0') : '1';
        const chromeEnabled = chromeToggle.length ? (chromeToggle.is(':checked') ? '1' : '0') : '1';
        
        // Update Configure button visibility immediately (before AJAX)
        updateChromeConfigureButton(chromeEnabled === '1');
        
        // Save states via AJAX
        if (typeof tpaDashboard !== 'undefined' && tpaDashboard.ajax_url && tpaDashboard.nonce) {
            $.post(tpaDashboard.ajax_url, {
                action: 'tpa_save_provider_states',
                yandex_enabled: yandexEnabled,
                chrome_enabled: chromeEnabled,
                _wpnonce: tpaDashboard.nonce
            }, function(response) {
                if (response && response.success) {
                    // Button visibility already updated above
                } else {
                    console.error('Failed to save provider states:', response);
                }
            });
        }
        
        // Re-check Chrome notice after toggle change
        showChromeConfigureNotice().catch(function(error) {
            console.log('Error checking Chrome notice:', error);
        });
    });

    /* =========================
     * Chrome AI Error Check
     * Check for Chrome AI errors and show notice below Configure button
     * ========================= */
    function checkChromeAIErrors() {
        // Use centralized Chrome AI Translator utility methods
        if (typeof ChromeAiTranslator === 'undefined') {
            return { hasError: true, type: 'api' }; // If ChromeAiTranslator not loaded, assume API error
        }
        
        const bypassBrowser = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_browser_check === '1';
        const bypassSecure = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_secure_check === '1';
        const bypassApi = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_api_check === '1';

        const browserCompatible = ChromeAiTranslator.checkBrowserCompatibility() || bypassBrowser;
        const secureConnection = ChromeAiTranslator.checkSecureConnection() || window?.isSecureContext || bypassSecure;
        const apiAvailable = ChromeAiTranslator.checkApiAvailability() || bypassApi;
        
        // Browser check (must be Chrome, not Edge or others)
        if (!browserCompatible) {
            return { hasError: true, type: 'browser' };
        } else if (!apiAvailable && !secureConnection) {
            return { hasError: true, type: 'secure' };
        } else if (!apiAvailable) {
            return { hasError: true, type: 'api' };
        }
        
        return { hasError: false };
    }

    /* =========================
     * Check Language Pack Availability
     * Check if language packs are installed for supported languages
     * ========================= */
    async function checkLanguagePackAvailability() {
        // Use centralized Chrome AI Translator utility methods
        if (typeof ChromeAiTranslator === 'undefined') {
            return { hasError: false }; // Can't check if ChromeAiTranslator not loaded
        }
        
        // Helper function to check language pair availability (use centralized method)
        async function checkLanguagePairAvailability(source, target) {
            if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.languagePairAvality) {
                return await ChromeAiTranslator.languagePairAvality(source, target);
            }
            return false;
        }
        
        // Get languages from TRP settings
        let sourceLanguage = 'en';
        let targetLanguage = 'hi';
        let allLanguages = [];
        
        if (typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages) {
            sourceLanguage = tpaTrpLanguages.source_language || 'en';
            targetLanguage = tpaTrpLanguages.target_language || 'hi';
            allLanguages = tpaTrpLanguages.all_languages || [];
        } else if (typeof localStorage !== 'undefined') {
            sourceLanguage = localStorage.getItem('page_lang') || 'en';
            targetLanguage = localStorage.getItem('language_code') || 'hi';
        }
        
        // Check supported languages list (use centralized method)
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages();
        
        // Get source language
        const sourceLang = sourceLanguage.toLowerCase();
        const targetLangs = [];
        
        if (allLanguages && allLanguages.length > 0) {
            // Get all supported target languages
            allLanguages.forEach(function(lang) {
                if (!lang.is_default && supportedLanguages.includes(lang.code.toLowerCase())) {
                    targetLangs.push(lang.code.toLowerCase());
                }
            });
        } else if (supportedLanguages.includes(targetLanguage.toLowerCase())) {
            targetLangs.push(targetLanguage.toLowerCase());
        }
        
        // Check language pack status for each target language
        if (targetLangs.length > 0 && supportedLanguages.includes(sourceLang)) {
            for (let i = 0; i < targetLangs.length; i++) {
                try {
                    const status = await checkLanguagePairAvailability(sourceLang, targetLangs[i]);
                    
                    // If status indicates pack is required or downloading, return error
                    if (status === "after-download" || status === "downloadable" || status === "unavailable" || status === "downloading") {
                        return { hasError: true, type: 'language-pack' };
                    }
                    
                    // If status is not 'readily' or 'available', pack might be required
                    if (status !== 'readily' && status !== 'available' && status !== false) {
                        return { hasError: true, type: 'language-pack' };
                    }
                } catch (error) {
                    // Continue checking other language pairs if one fails
                    console.log('Language pack check failed for ' + sourceLang + '-' + targetLangs[i] + ':', error);
                }
            }
        }
        
        return { hasError: false };
    }

    /* =========================
     * Update Chrome Configure Button Visibility
     * Show/hide Configure button based on Chrome toggle state
     * ========================= */
    function updateChromeConfigureButton(isEnabled) {
        const $chromeCard = $('.tpa-dashboard-provider-card').filter(function() {
            return $(this).find('h4').text().includes('Chrome Built-in AI');
        });

        if (!$chromeCard.length) {
            return; // Chrome card not found
        }

        const $configureButton = $chromeCard.find('.tpa-chrome-configure-btn');
        
        if (isEnabled) {
            // Show button if Chrome is enabled
            $configureButton.show();
        } else {
            // Hide button if Chrome is disabled
            $configureButton.hide();
        }
    }

    async function showChromeConfigureNotice() {
        // Find Chrome Built-in AI provider card
        const $chromeCard = $('.tpa-dashboard-provider-card').filter(function() {
            return $(this).find('h4').text().includes('Chrome Built-in AI');
        });

        if (!$chromeCard.length) {
            return; // Chrome card not found
        }

        // Check if Chrome is enabled
        const chromeToggle = $('.tpa-provider-toggle[data-provider="chrome-built-in-ai"]');
        const chromeEnabled = chromeToggle.length ? chromeToggle.is(':checked') : false;

        // Update Configure button visibility
        updateChromeConfigureButton(chromeEnabled);

        // Only show notice if Chrome is enabled
        if (!chromeEnabled) {
            // Remove notice if Chrome is disabled
            $chromeCard.find('.tpa-chrome-configure-notice').remove();
            return;
        }

        const errorCheck = checkChromeAIErrors();
        let hasError = errorCheck.hasError;
        let errorType = errorCheck.type;
        
        // If browser/API/secure checks pass, check language pack availability
        if (!hasError) {
            const packCheck = await checkLanguagePackAvailability();
            if (packCheck.hasError) {
                hasError = true;
                errorType = 'language-pack';
            }
        }
        
        if (hasError) {
            // Remove existing notice if any
            $chromeCard.find('.tpa-chrome-configure-notice').remove();
            
            // Find the Configure button container
            const $buttonsContainer = $chromeCard.find('.tpa-dashboard-provider-buttons');
            
            // Create notice with specific message based on error type
            let noticeMessage = 'Please configure the Chrome settings to use Chrome AI Translator.';
            
            if (errorType === 'browser') {
                noticeMessage = 'Chrome browser is required. Please configure Chrome settings.';
            } else if (errorType === 'secure') {
                noticeMessage = 'Secure connection (HTTPS) is required. Please configure Chrome settings.';
            } else if (errorType === 'api') {
                noticeMessage = 'Chrome Translation API is not available. Please configure Chrome settings.';
            } else if (errorType === 'language-pack') {
                noticeMessage = 'Language pack is required. Please configure Chrome settings.';
            }
            
            const $notice = $('<div class="tpa-chrome-configure-notice" style="margin-top: 10px; font-size: 10px; color: #dc2626;">' + noticeMessage + '</div>');
            
            $buttonsContainer.after($notice);
        } else {
            // Remove notice if no errors
            $chromeCard.find('.tpa-chrome-configure-notice').remove();
        }
    }

    // Check and show notice on page load
    showChromeConfigureNotice().catch(function(error) {
        console.log('Error checking Chrome notice:', error);
    });
});
