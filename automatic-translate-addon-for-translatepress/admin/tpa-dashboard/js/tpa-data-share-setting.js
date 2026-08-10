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
        const originalText = button.text().trim();
        
        // Determine action based on button text
        let action = 'install';
        if (originalText.toLowerCase() === 'activate' || originalText.toLowerCase().includes('activate')) {
            action = 'activate';
        }

        let nonce = action === 'activate'
            ? button.attr('data-nonce-activate')
            : button.attr('data-nonce-install');
    
        $wrapper.find('.tpa-install-message').empty();
    
        if (!slug || !nonce || typeof ajaxurl === 'undefined') {
            $wrapper.find('.tpa-install-message')
                .text(tpaDashboard.strings.missingData);
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
        })
        .done(function (response) {
            if (response && response.success) {
                const $container = button.closest('.tpa-dashboard-addon-l');
                if (response.data && response.data.activated === true) {
                    button.remove();
                    $container.find('.tpa-install-message').remove();
                    $container.append('<span class="installed">Activated</span>');
                } else {
                    // Not activated yet (e.g. Loco Translate missing)
                    let message = tpaDashboard.strings.installedSuccessfully;
                    if (response.data && response.data.message) {
                        message = String(response.data.message);
                    }
                    $container.find('.tpa-install-message').text(message);
                    button.text('Activate').prop('disabled', false);
                }
            } else {
                let errorMessage = 'Activation failed. Please try again.';
                if (response && response.data) {
                    if (typeof response.data === 'string') {
                        errorMessage = response.data;
                    } else if (response.data.message) {
                        errorMessage = String(response.data.message);
                    }
                }
                $wrapper.find('.tpa-install-message').text(errorMessage);
                button.text(originalText).prop('disabled', false);
            }

            $('.tpa-install-plugin').not(button).prop('disabled', false);
        })
        .fail(function () {
            const requestFailed = (typeof tpaDashboard !== 'undefined' && tpaDashboard.strings && tpaDashboard.strings.requestFailed)
                ? tpaDashboard.strings.requestFailed
                : 'Request failed. Please try again.';
            $wrapper.find('.tpa-install-message').text(requestFailed);
            button.text(originalText).prop('disabled', false);
            $('.tpa-install-plugin').not(button).prop('disabled', false);
        });
    });

    const BUILTIN_AI_PROVIDERS = {
        chrome: {
            toggleSelector: '.tpa-provider-toggle[data-provider="chrome-built-in-ai"]',
            cardName: 'Chrome Built-in AI',
            browserLabel: 'Chrome',
            checkBrowser: () => typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkBrowserCompatibility('chrome'),
        },
        edge: {
            toggleSelector: '.tpa-provider-toggle[data-provider="edge-built-in-ai"]',
            cardName: 'Edge Built-in AI',
            browserLabel: 'Edge',
            checkBrowser: () => typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkBrowserCompatibility('edge'),
        }
    };

    /* =========================
     * Provider Toggle Switches
     * Save provider states to database
     * ========================= */
    var tpaPendingToggleRequests = 0;
    var tpaLatestSettingsTabVisible = null;

    function tpaMaybeReloadForSettingsTab() {
        if (tpaPendingToggleRequests > 0 || tpaLatestSettingsTabVisible === null) {
            return;
        }

        var settingsTabPresent = $('.nav-tab-wrapper a[data-tab="settings"]').length > 0;

        if (tpaLatestSettingsTabVisible !== settingsTabPresent) {
            window.location.reload();
        }
    }

    $(document).on('change', '.tpa-provider-toggle', function() {
        const $toggle = $(this);
        
        // Skip if toggle is disabled (Pro providers)
        if ($toggle.prop('disabled')) {
            return;
        }

        const savedStates = (typeof tpaDashboard !== 'undefined' && tpaDashboard.provider_states)
            ? tpaDashboard.provider_states
            : {};

        const yandexToggle = $('.tpa-provider-toggle[data-provider="yandex-translate"]');
        const chromeToggle = $('.tpa-provider-toggle[data-provider="chrome-built-in-ai"]');
        const edgeToggle = $('.tpa-provider-toggle[data-provider="edge-built-in-ai"]');

        const yandexEnabled = yandexToggle.length
            ? (yandexToggle.is(':checked') ? '1' : '0')
            : (savedStates.yandex_enabled || '1');
        const chromeEnabled = chromeToggle.length
            ? (chromeToggle.is(':checked') ? '1' : '0')
            : (savedStates.chrome_enabled || '1');
        const edgeEnabled = edgeToggle.length
            ? (edgeToggle.is(':checked') ? '1' : '0')
            : (savedStates.edge_enabled || '1');
        
        if (typeof tpaDashboard !== 'undefined' && tpaDashboard.ajax_url && tpaDashboard.nonce) {
            tpaPendingToggleRequests++;

            $.post(tpaDashboard.ajax_url, {
                action: 'tpa_save_provider_states',
                yandex_enabled: yandexEnabled,
                chrome_enabled: chromeEnabled,
                edge_enabled: edgeEnabled,
                _wpnonce: tpaDashboard.nonce
            }).done(function(response) {
                if (!response || !response.success) {
                    console.error('Failed to save provider states:', response);
                    return;
                }
                if (response.data && response.data.message) {
                    console.info(response.data.message, response.data.changed || {});
                }
                tpaLatestSettingsTabVisible = !!(response.data && response.data.settings_tab_visible);
            }).fail(function() {
                console.error('Failed to save provider states.');
            }).always(function() {
                tpaPendingToggleRequests--;
                tpaMaybeReloadForSettingsTab();
            });
        }
        
        Object.keys(BUILTIN_AI_PROVIDERS).forEach(function(providerKey) {
            showBuiltinAIConfigureNotice(providerKey).catch(function(error) {
                console.log('Error checking ' + BUILTIN_AI_PROVIDERS[providerKey].browserLabel + ' notice:', error);
            });
        });
    });

    function checkBuiltinAIErrors(providerKey) {
        const config = BUILTIN_AI_PROVIDERS[providerKey];
        if (!config || typeof ChromeAiTranslator === 'undefined') {
            return { hasError: true, type: 'api' };
        }
        
        const bypassBrowser = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_browser_check === '1';
        const bypassSecure = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_secure_check === '1';
        const bypassApi = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_api_check === '1';

        const browserCompatible = config.checkBrowser() || bypassBrowser;
        const secureConnection = ChromeAiTranslator.checkSecureConnection() || window?.isSecureContext || bypassSecure;
        const apiAvailable = ChromeAiTranslator.checkApiAvailability() || bypassApi;
        
        if (!browserCompatible) {
            return { hasError: true, type: 'browser' };
        } else if (!apiAvailable && !secureConnection) {
            return { hasError: true, type: 'secure' };
        } else if (!apiAvailable) {
            return { hasError: true, type: 'api' };
        }
        
        return { hasError: false };
    }

    async function checkLanguagePackAvailability(providerKey) {
        if (typeof ChromeAiTranslator === 'undefined') {
            return { hasError: false };
        }
        
        async function checkLanguagePairAvailability(source, target) {
            if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.languagePairAvality) {
                return await ChromeAiTranslator.languagePairAvality(source, target);
            }
            return false;
        }
        
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
        
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages(providerKey);
        const sourceLang = sourceLanguage.toLowerCase();
        const targetLangs = [];
        
        if (allLanguages && allLanguages.length > 0) {
            allLanguages.forEach(function(lang) {
                if (!lang.is_default && supportedLanguages.includes(lang.code.toLowerCase())) {
                    targetLangs.push(lang.code.toLowerCase());
                }
            });
        } else if (supportedLanguages.includes(targetLanguage.toLowerCase())) {
            targetLangs.push(targetLanguage.toLowerCase());
        }
        
        if (targetLangs.length > 0 && supportedLanguages.includes(sourceLang)) {
            for (let i = 0; i < targetLangs.length; i++) {
                try {
                    const status = await checkLanguagePairAvailability(sourceLang, targetLangs[i]);
                    
                    if (status === "after-download" || status === "downloadable" || status === "unavailable" || status === "downloading") {
                        return { hasError: true, type: 'language-pack' };
                    }
                    
                    if (status !== 'readily' && status !== 'available' && status !== false) {
                        return { hasError: true, type: 'language-pack' };
                    }
                } catch (error) {
                    console.log('Language pack check failed for ' + sourceLang + '-' + targetLangs[i] + ':', error);
                }
            }
        }
        
        return { hasError: false };
    }

    function updateBuiltinAIConfigureButton(providerKey, showButton) {
        const config = BUILTIN_AI_PROVIDERS[providerKey];
        const $card = $('.tpa-dashboard-provider-card').filter(function() {
            return $(this).find('h4').text().includes(config.cardName);
        });

        if (!$card.length) {
            return;
        }

        const $configureButton = $card.find('.tpa-builtin-ai-configure-btn[data-provider="' + providerKey + '"]');
        if (showButton) {
            $configureButton.show();
        } else {
            $configureButton.hide();
        }
    }

    async function showBuiltinAIConfigureNotice(providerKey) {
        const config = BUILTIN_AI_PROVIDERS[providerKey];
        const $card = $('.tpa-dashboard-provider-card').filter(function() {
            return $(this).find('h4').text().includes(config.cardName);
        });

        if (!$card.length) {
            return;
        }

        const providerToggle = $(config.toggleSelector);
        const providerEnabled = providerToggle.length ? providerToggle.is(':checked') : false;

        if (!providerEnabled) {
            $card.find('.tpa-builtin-ai-configure-notice[data-provider="' + providerKey + '"]').remove();
            updateBuiltinAIConfigureButton(providerKey, false);
            return;
        }

        const errorCheck = checkBuiltinAIErrors(providerKey);
        let hasError = errorCheck.hasError;
        let errorType = errorCheck.type;

        if (!hasError) {
            const packCheck = await checkLanguagePackAvailability(providerKey);
            if (packCheck.hasError) {
                hasError = true;
                errorType = packCheck.type;
            }
        }

        updateBuiltinAIConfigureButton(providerKey, providerEnabled && hasError);
        $card.find('.tpa-builtin-ai-configure-notice[data-provider="' + providerKey + '"]').remove();

        if (hasError) {
            const $buttonsContainer = $card.find('.tpa-dashboard-provider-buttons');
            let noticeMessage = 'Please configure the ' + config.browserLabel + ' settings to use ' + config.browserLabel + ' AI Translator.';

            if (errorType === 'browser') {
                noticeMessage = config.browserLabel + ' browser is required. Please configure ' + config.browserLabel + ' settings.';
            } else if (errorType === 'secure') {
                noticeMessage = 'Secure connection (HTTPS) is required. Please configure ' + config.browserLabel + ' settings.';
            } else if (errorType === 'api') {
                noticeMessage = config.browserLabel + ' Translation API is not available. Please configure ' + config.browserLabel + ' settings.';
            } else if (errorType === 'language-pack') {
                noticeMessage = 'Language pack is required. Please configure ' + config.browserLabel + ' settings.';
            }

            const $notice = $('<div class="tpa-builtin-ai-configure-notice tpa-chrome-configure-notice" data-provider="' + providerKey + '" style="margin-top: 10px; font-size: 10px; color: #dc2626;">' + noticeMessage + '</div>');
            $buttonsContainer.after($notice);
        }
    }

    Object.keys(BUILTIN_AI_PROVIDERS).forEach(function(providerKey) {
        showBuiltinAIConfigureNotice(providerKey).catch(function(error) {
            console.log('Error checking ' + BUILTIN_AI_PROVIDERS[providerKey].browserLabel + ' notice:', error);
        });
    });
});
