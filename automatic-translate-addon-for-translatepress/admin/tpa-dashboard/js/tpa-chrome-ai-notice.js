jQuery(function($) {
    /* =========================
     * Chrome Local AI Notice
     * Initialize Chrome AI translator notice on settings page
     * ========================= */
    async function initChromeLocalAINotice() {
        // Check if notice element exists
        const $notice = $('#tpa-chrome-local-ai-notice');
        if (!$notice.length) {
            return; // Notice element doesn't exist, exit early
        }
        // Use centralized Chrome AI Translator utility methods
        const bypassBrowser = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_browser_check === '1';
        const bypassSecure = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_secure_check === '1';
        const bypassApi = typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages.chrome_ai_bypass_api_check === '1';

        const safeBrowser = (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkSecureConnection) 
            ? ChromeAiTranslator.checkSecureConnection() 
            : (window?.location?.protocol === "https:" || window?.isSecureContext);
        const browserContentSecure = window?.isSecureContext;
        
        // Secure connection + API availability check (use centralized method)
        const apiAvailable = (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkApiAvailability) 
            ? ChromeAiTranslator.checkApiAvailability() 
            : false;

        const effectiveApiAvailable = apiAvailable || bypassApi;
        const effectiveSecure = safeBrowser || browserContentSecure || bypassSecure;

        const $heading = $('#tpa-chrome-notice-heading');
        const $message = $('#tpa-chrome-notice-message');
        
        let showBrowserNotice = false;
        let showSecureNotice = false;
        let showApiNotice = false;
        let showLanguageNotice = false;
        let languageNoticeData = null;
        
        // Browser check (must be Chrome, not Edge or others) - use centralized method
        const isBrowserCompatible = (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkBrowserCompatibility) 
            ? ChromeAiTranslator.checkBrowserCompatibility() 
            : (window?.hasOwnProperty("chrome") && navigator?.userAgent?.includes("Chrome") && !navigator?.userAgent?.includes("Edg"));
        
        const effectiveBrowserCompatible = isBrowserCompatible || bypassBrowser;

        if (!effectiveBrowserCompatible) {
            showBrowserNotice = true;
        } else if (!effectiveApiAvailable && !effectiveSecure) {
            showSecureNotice = true;
        } else if (!effectiveApiAvailable) {
            showApiNotice = true;
        } else {
            // Only check language issues if browser/API/secure checks pass
            languageNoticeData = await checkLanguageIssues();
            if (languageNoticeData) {
                showLanguageNotice = true;
            }
        }
        
        if (!showBrowserNotice && !showSecureNotice && !showApiNotice && !showLanguageNotice) {
            // Hide only notice content; keep container visible for test translation section
            $notice.find('.tpa-chrome-local-ai-notice-content').hide();
            $notice.show();
            return; // no notice needed
        }
        
        // Notice messages
        const notices = {
            browserHeading: '⚠️ Important Notice: Browser Compatibility',
            browserMessage: '<ul><li>' +
                'The <strong>Translator API</strong>, which uses Chrome Local AI Models, is designed exclusively for use with the <strong>Chrome browser</strong>.' +
                '</li><li>' +
                'If you are using a different browser (such as Edge, Firefox, or Safari), the API may not function correctly.' +
                '</li><li>' +
                'Learn more in the <a href="https://developer.chrome.com/docs/ai/translator-api" target="_blank" rel="noreferrer">official documentation</a>.' +
                '</li></ul>',
            secureHeading: '⚠️ Important Notice: Secure Connection Required',
            secureMessage: '<ul><li>' +
                'The <strong>Translator API</strong> requires a secure (HTTPS) connection to function properly.' +
                '</li><li>' +
                'If you are on an insecure connection (HTTP), the API will not work.' +
                '</li></ul>' +
                '<p><strong>👉 How to Fix This:</strong></p>' +
                '<ol>' +
                '<li>Switch to a secure connection by using <strong><code>https://</code></strong>.</li>' +
                '<li>' +
                'Alternatively, add this URL to Chrome\'s list of insecure origins treated as secure: ' + createCopyableLink('chrome://flags/#unsafely-treat-insecure-origin-as-secure') + 
                '<br />Copy the URL and then open a new window and paste this URL to access the settings.' +
                '</li></ol>',
            apiHeading: '⚠️ Important Notice: API Availability',
            apiMessage: '<ol>' +
                '<li>Open this URL in a new Chrome tab: ' + createCopyableLink('chrome://flags/#translation-api') + '. Copy this URL and then open a new window and paste this URL to access the settings.</li>' +
                '<li>Ensure that the <strong>Experimental translation API</strong> option is set to <strong>Enabled</strong>.</li>' +
                '<li>After change the setting, Click on the <strong>Relaunch</strong> button to apply the changes.</li>' +
                '<li>The Translator AI modal should now be enabled and ready for use.</li>' +
                '</ol>' +
                '<p>For more information, please refer to the <a href="https://developer.chrome.com/docs/ai/translator-api" target="_blank">documentation</a>.</p>' +
                '<p>If the issue persists, please ensure that your browser is up to date and restart your browser.</p>' +
                '<p>If you continue to experience issues after following the above steps, please <a href="https://my.coolplugins.net/account/support-tickets/" target="_blank" rel="noopener">open a support ticket</a> with our team. We are here to help you resolve any problems and ensure a smooth translation experience.</p>'
        };
        
        let heading = '';
        let message = '';
        
        if (showBrowserNotice) {
            heading = notices.browserHeading;
            message = notices.browserMessage;
        } else if (showSecureNotice) {
            heading = notices.secureHeading;
            message = notices.secureMessage;
        } else if (showApiNotice) {
            heading = notices.apiHeading;
            message = notices.apiMessage;
        } else if (showLanguageNotice && languageNoticeData) {
            heading = languageNoticeData.heading;
            message = languageNoticeData.message;
        }
        
        $heading.html(heading);
        $message.html(message);
        
        // Check if this is a combined notice (has both unsupported and language pack issues)
        // Combined notice toggles a class (styling handled in CSS)
        const isCombinedNotice = showLanguageNotice && languageNoticeData && 
                                 languageNoticeData.isCombined === true;
        
        if (isCombinedNotice) {
            $notice.addClass('tpa-chrome-notice-combined');
        } else {
            $notice.removeClass('tpa-chrome-notice-combined');
        }

        $notice.find('.tpa-chrome-local-ai-notice-content').show();
        $notice.show();
        
        // Initialize clipboards for any new copyable links
        if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.initializeClipboard) {
            ChromeAiTranslator.initializeClipboard();
        }
    }
    
    function escapeHtmlAttr(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escapeHtmlText(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    /**
     * Copy-only chrome:// URL control (not a navigable link).
     */
    function createCopyableLink(url) {
        const copyIcon = typeof ChromeAiTranslator !== 'undefined' ? ChromeAiTranslator.svgIcons('copy') : '';
        return '<span class="chrome-url-link chrome-ai-translator-flags tapa-tooltip-element" role="button" tabindex="0" title="Click to copy" data-clipboard-text="' + escapeHtmlAttr(url) + '">' +
            '<span class="chrome-url-text">' + escapeHtmlText(url) + '</span> ' + copyIcon +
            '</span>';
    }

    /* =========================
     * Check Language Issues
     * Check for language support and language pack issues for ALL languages
     * ========================= */
    async function checkLanguageIssues() {
        // Get supported languages list from centralized Chrome AI Translator
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages();
        
        // Get languages from TRP settings (passed via wp_localize_script)
        let sourceLanguage = 'en';
        let targetLanguage = 'hi';
        let sourceLanguageLabel = 'English';
        let targetLanguageLabel = 'Hindi';
        let allLanguages = [];
        
        if (typeof tpaTrpLanguages !== 'undefined' && tpaTrpLanguages) {
            // Use TRP settings from PHP
            sourceLanguage = tpaTrpLanguages.source_language || 'en';
            targetLanguage = tpaTrpLanguages.target_language || 'hi';
            sourceLanguageLabel = tpaTrpLanguages.source_language_label || 'English';
            targetLanguageLabel = tpaTrpLanguages.target_language_label || 'Hindi';
            allLanguages = tpaTrpLanguages.all_languages || [];
        }
        
        // Check all languages for unsupported ones
        const unsupportedLanguages = [];
        const supportedLanguagePairs = []; // Store supported language pairs for pack checking
        
        if (allLanguages && allLanguages.length > 0) {
            // Check all languages from TRP settings
            allLanguages.forEach(function(lang) {
                if (!supportedLanguages.includes(lang.code.toLowerCase())) {
                    unsupportedLanguages.push({
                        code: lang.code.toUpperCase(),
                        label: lang.label,
                        isDefault: lang.is_default || false
                    });
                } else {
                    // Store supported languages for pack checking
                    supportedLanguagePairs.push({
                        code: lang.code.toLowerCase(),
                        fullCode: lang.code,
                        label: lang.label,
                        isDefault: lang.is_default || false
                    });
                }
            });
        } else {
            // Fallback: check source and target languages
            if (!supportedLanguages.includes(sourceLanguage.toLowerCase())) {
                unsupportedLanguages.push({
                    code: sourceLanguage.toUpperCase(),
                    label: sourceLanguageLabel,
                    isDefault: true
                });
            } else {
                supportedLanguagePairs.push({
                    code: sourceLanguage.toLowerCase(),
                    fullCode: sourceLanguage,
                    label: sourceLanguageLabel,
                    isDefault: true
                });
            }
            if (!supportedLanguages.includes(targetLanguage.toLowerCase())) {
                unsupportedLanguages.push({
                    code: targetLanguage.toUpperCase(),
                    label: targetLanguageLabel,
                    isDefault: false
                });
            } else {
                supportedLanguagePairs.push({
                    code: targetLanguage.toLowerCase(),
                    fullCode: targetLanguage,
                    label: targetLanguageLabel,
                    isDefault: false
                });
            }
        }
        
        // Build unsupported languages notice (Pro-style markup)
        let unsupportedNotice = null;
        if (unsupportedLanguages.length > 0) {
            let unsupportedList = '';
            unsupportedLanguages.forEach(function(lang) {
                const safeLabel = $('<div>').text(lang.label).html(); 
                const safeCode = $('<div>').text(lang.code).html();
                unsupportedList += '<strong>' + safeLabel + ' (' + safeCode + ')</strong>, ';
            });
            // Remove trailing comma
            unsupportedList = unsupportedList.replace(/, $/, '');

            unsupportedNotice = {
                heading: '<span class="tpa-chrome-unsupported-heading"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" id="error"><g><rect fill="none"/></g><g><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"></path></g></svg>Unsupported Languages</span> ',
                message: '<div class="tpa-chrome-unsupported-box">' +
                    '<p>The following languages are not supported by the current AI engine: </br><span class="tpa-unsupported-list">' + unsupportedList + '</span></p>' +
                    '<p>To see the full list of supported translation languages, visit: '+ createCopyableLink('chrome://on-device-translation-internals') + '</p>' +
                    '</div>',
                isCombined: true
            };
        }
        
        // Check language pack availability for ALL supported language pairs if API is available
        const languagePackIssues = [];
        
        // Helper function to check language pair availability (use centralized method)
        async function checkLanguagePairAvailability(source, target) {
            if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.languagePairAvality) {
                return await ChromeAiTranslator.languagePairAvality(source, target);
            }
            return false;
        }
        
        // Check if browser API is available (use centralized method)
        const apiAvailable = (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkApiAvailability) 
            ? ChromeAiTranslator.checkApiAvailability() 
            : false;
        
        if (apiAvailable && supportedLanguagePairs.length > 0) {
            try {
                // Get source language
                const sourceLang = supportedLanguagePairs.find(l => l.isDefault) || supportedLanguagePairs[0];
                const targetLangs = supportedLanguagePairs.filter(l => !l.isDefault);
                
                // Check each target language against source for language pack issues
                for (let i = 0; i < targetLangs.length; i++) {
                    const targetLang = targetLangs[i];
                    try {
                        const status = await checkLanguagePairAvailability(sourceLang.code, targetLang.code);
                        
                        // Collect all language pack issues (don't break on first issue)
                        if (status === "after-download" || status === "downloadable" || status === "unavailable") {
                            languagePackIssues.push({
                                sourceLang: sourceLang,
                                targetLang: targetLang,
                                status: 'required',
                                message: 'Language pack required for ' + targetLang.label + ' (' + targetLang.code.toUpperCase() + ')'
                            });
                        } else if (status === "downloading") {
                            languagePackIssues.push({
                                sourceLang: sourceLang,
                                targetLang: targetLang,
                                status: 'downloading',
                                message: 'Language pack downloading for ' + targetLang.label + ' (' + targetLang.code.toUpperCase() + ')'
                            });
                        } else if (status !== 'readily' && status !== 'available' && status !== false) {
                            languagePackIssues.push({
                                sourceLang: sourceLang,
                                targetLang: targetLang,
                                status: 'required',
                                message: 'Language pack required for ' + targetLang.label + ' (' + targetLang.code.toUpperCase() + ')'
                            });
                        }
                    } catch (error) {
                        // Continue checking other language pairs if one fails
                        console.log('Language pair check failed for ' + sourceLang.code + '-' + targetLang.code + ':', error);
                    }
                }
            } catch (error) {
                // If language pair check fails, log it but continue
                console.log('Language pair check failed:', error);
            }
        }
        
        // Build language pack notice from all collected issues (Pro-style markup)
        let languagePackNotice = null;
        if (languagePackIssues.length > 0) {
            // Group issues by status type
            const requiredIssues = languagePackIssues.filter(issue => issue.status === 'required');
            const downloadingIssues = languagePackIssues.filter(issue => issue.status === 'downloading');
            
            // If there are downloading issues, show downloading notice first
            if (downloadingIssues.length > 0) {
                let downloadingList = '';
                downloadingIssues.forEach(function(issue) {
                    downloadingList += '<strong>' + issue.targetLang.label + ' (' + issue.targetLang.code.toUpperCase() + ')</strong>, ';
                });
                // Remove trailing comma
                downloadingList = downloadingList.replace(/, $/, '');
                
                languagePackNotice = {
                    heading: '⏳ Language Packs Downloading',
                    message: '<div class="tpa-chrome-language-pack-box">' +
                        '<p>Language packs are being downloaded: ' + downloadingList + '</p>' +
                        '<p>Please wait for the download to complete. Translation will be available automatically once finished.</p>' +
                        '<p>Check download progress: ' + createCopyableLink('chrome://on-device-translation-internals') + '</p>' +
                        '</div>',
                    isCombined: true
                };
            } else if (requiredIssues.length > 0) {
                // Show required language packs notice
                let requiredList = '';
                const uniqueTargetLangs = [];
                requiredIssues.forEach(function(issue) {
                    // Avoid duplicates
                    if (!uniqueTargetLangs.find(l => l.code === issue.targetLang.code)) {
                        uniqueTargetLangs.push(issue.targetLang);
                        requiredList += '<strong>' + issue.targetLang.label + ' (' + issue.targetLang.code.toUpperCase() + ')</strong>, ';
                    }
                });
                // Remove trailing comma
                requiredList = requiredList.replace(/, $/, '');
                
                const sourceLang = requiredIssues[0].sourceLang;
                languagePackNotice = {
                    heading: '<span class="tpa-chrome-language-pack-heading"><svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="15" viewBox="0 0 24 24" width="15"><g><rect fill="none"/></g><g><path d="M20,2H4C3,2,2,2.9,2,4v3.01C2,7.73,2.43,8.35,3,8.7V20c0,1.1,1.1,2,2,2h14c0.9,0,2-0.9,2-2V8.7c0.57-0.35,1-0.97,1-1.69V4 C22,2.9,21,2,20,2z M15,14H9v-2h6V14z M20,7H4V4h16V7z"/></g></svg>Language Pack Required</span> ',
                    message: '<div class="tpa-chrome-language-pack-box">' +
                        '<p>Chrome needs language packs installed for translation to work. This is a one-time setup.</p>' +
                        '<div class="tpa-chrome-language-pack-inner">' +
                        '<p><strong class="tpa-required-label">Required Languages:</strong><br>' + sourceLang.label + ' (Source), <span class="tpa-required-lang">' + requiredList + '</span></p>' +
                        '<p><strong>Quick Setup:</strong></p>' +
                        '<ol class="tpa-chrome-steps-list">' +
                        '<li><span class="tpa-chrome-step-number">1</span>Open <strong>Chrome Settings → Languages</strong>: ' + createCopyableLink('chrome://settings/languages') + '</li>' +
                        '<li><span class="tpa-chrome-step-number">2</span>Click <strong class="tpa-required-lang">Add languages</strong> and add the languages listed above</li>' +
                        '<li><span class="tpa-chrome-step-number">3</span>Reload this page to verify configuration.</li>' +
                        '</ol>' +
                        '<p>Verify language packs: '+ createCopyableLink('chrome://on-device-translation-internals') + '</p>' +
                        '</div>' +
                        '</div>',
                    isCombined: true
                };
            }
        }
        
        // Combine notices if both exist (Pro-style layout)
        if (unsupportedNotice && languagePackNotice) {
            return {
                heading: '⚠️ Language Configuration Issues',
                message: '<div class="tpa-chrome-combined-section">' + 
                    '<h4 class="tpa-chrome-combined-heading">' + unsupportedNotice.heading + '</h4>' +
                    unsupportedNotice.message +
                    '</div>' +
                    '<div class="tpa-chrome-combined-section">' +
                    '<h4 class="tpa-chrome-combined-heading">' + languagePackNotice.heading + '</h4>' +
                    languagePackNotice.message +
                    '</div>',
                isCombined: true
            };
        }
        
        // Return individual notices if only one exists
        if (unsupportedNotice) {
            return unsupportedNotice;
        }
        
        if (languagePackNotice) {
            return languagePackNotice;
        }
        
        return null; // No language issues detected
    }
    
    /* =========================
     * Test Translation Feature
     * Allow users to test Chrome AI translation
     * ========================= */
    async function initTestTranslation() {
        const $testSection = $('#tpa-chrome-test-translation');
        if (!$testSection.length) {
            return; // Test section doesn't exist
        }
        
        // Hide test section initially - it will be shown after notice is displayed
        $testSection.hide();
        
        // Check for critical Chrome AI configuration errors (browser, API, secure connection)
        // Hide test section if any critical errors exist
        // Use centralized Chrome AI Translator utility methods
        if (typeof ChromeAiTranslator === 'undefined') {
            return; // ChromeAiTranslator not loaded
        }
        
        const safeBrowser = ChromeAiTranslator.checkSecureConnection();
        const browserContentSecure = window?.isSecureContext;
        const apiAvailable = ChromeAiTranslator.checkApiAvailability();
        
        // Browser check (must be Chrome, not Edge or others)
        const hasBrowserError = !ChromeAiTranslator.checkBrowserCompatibility();
        
        // Secure connection check
        const hasSecureError = !apiAvailable && !safeBrowser && !browserContentSecure;
        
        // API availability check
        const hasApiError = !apiAvailable;
        
        // Hide test translation section if any critical errors exist
        if (hasBrowserError || hasSecureError || hasApiError) {
            return; // Don't initialize test translation if critical errors exist
        }
        
        const $sourceSelect = $('#tpa-test-translation-source');
        const $targetSelect = $('#tpa-test-translation-target');
        const $testBtn = $('#tpa-test-translation-btn');
        const $resultDiv = $('#tpa-test-translation-result');
        const $errorDiv = $('#tpa-test-translation-error');

        // Supported languages list (use centralized method)
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages();

        // Helper function to check language pair availability (use centralized method)
        async function checkLanguagePairAvailability(source, target) {
            if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.languagePairAvality) {
                return await ChromeAiTranslator.languagePairAvality(source, target);
            }
            return false;
        }

        // Filter languages: only supported AND with language packs installed
        async function getAvailableLanguages() {
            const availableLanguages = [];

            if (typeof tpaTrpLanguages === 'undefined' || !tpaTrpLanguages.all_languages || tpaTrpLanguages.all_languages.length === 0) {
                return availableLanguages;
            }

            const languages = tpaTrpLanguages.all_languages;
            const defaultSourceLanguage = tpaTrpLanguages.source_language || 'en';

            // Filter to only supported languages
            const supportedLangs = languages.filter(function(lang) {
                return supportedLanguages.includes(lang.code.toLowerCase());
            });

            // Check if browser API is available (use centralized method)
            const apiAvailable = (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.checkApiAvailability) 
                ? ChromeAiTranslator.checkApiAvailability() 
                : false;

            if (!apiAvailable || supportedLangs.length === 0) {
                // If API not available, return only supported languages (user can still try)
                return supportedLangs;
            }

            // Check language pack availability for each language
            // We'll check each language against the default source to see if packs are available
            for (let i = 0; i < supportedLangs.length; i++) {
                const lang = supportedLangs[i];
                const langCode = lang.code.toLowerCase();
                const defaultSourceCode = defaultSourceLanguage.toLowerCase();
                let isAvailable = false;

                if (lang.code === defaultSourceLanguage) {
                    // This is the default source language - check if it can translate to at least one supported target
                    for (let j = 0; j < supportedLangs.length; j++) {
                        const targetLang = supportedLangs[j];
                        if (targetLang.code !== defaultSourceLanguage) {
                            try {
                                const status = await checkLanguagePairAvailability(langCode, targetLang.code.toLowerCase());
                                if (status === 'readily' || status === 'available' || status === true) {
                                    isAvailable = true;
                                    break;
                                }
                            } catch (error) {
                                // Continue checking other pairs
                            }
                        }
                    }
                } else {
                    // This is a target language (or alternative source) - check if default source can translate to it
                    try {
                        const status = await checkLanguagePairAvailability(defaultSourceCode, langCode);
                        if (status === 'readily' || status === 'available' || status === true) {
                            isAvailable = true;
                        }
                    } catch (error) {
                        // Language pack not available for this pair
                    }
                }

                if (isAvailable) {
                    availableLanguages.push(lang);
                }
            }

            return availableLanguages;
        }

        // Get available languages and populate dropdowns
        const availableLanguages = await getAvailableLanguages();

        if (availableLanguages.length === 0) {
            $testSection.hide();
            return;
        }

        // Show test section now that we've found available languages
        $testSection.show();
        
        // Get source language
        const sourceLanguage = tpaTrpLanguages.source_language || 'en';
        
        // Populate source language dropdown (only source language)
        const sourceLangObj = availableLanguages.find(function(lang) {
            return lang.code === sourceLanguage;
        });
        if (sourceLangObj) {
            const option = $('<option></option>')
                .attr('value', sourceLangObj.code)
                .text(sourceLangObj.label);
            $sourceSelect.append(option);
        }
        
        // Populate target language dropdown (exclude source language, only available languages)
        availableLanguages.forEach(function(lang) {
            if (lang.code !== sourceLanguage) {
                const option = $('<option></option>')
                    .attr('value', lang.code)
                    .text(lang.label);
                $targetSelect.append(option);
            }
        });
        
        // Set default source language
        if (sourceLanguage && $sourceSelect.find('option[value="' + sourceLanguage + '"]').length) {
            $sourceSelect.val(sourceLanguage);
        } else if ($sourceSelect.find('option').length > 0) {
            $sourceSelect.val($sourceSelect.find('option').first().val());
        }
        
        // Set default target language (first available target language)
        if ($targetSelect.find('option').length > 0) {
            $targetSelect.val($targetSelect.find('option').first().val());
        }
        
        // Static test text
        const staticTestText = 'Hello, this is a test translation.';
        
        // Handle test translation button click
        $testBtn.on('click', async function() {
            const textToTranslate = staticTestText;
            const sourceLang = $sourceSelect.val();
            const targetLang = $targetSelect.val();
            
            // Hide previous results
            $resultDiv.hide();
            $errorDiv.hide();
            
            // Validate language selection
            if (!sourceLang || !targetLang) {
                $errorDiv.html('Please select both source and target languages.').show();
                return;
            }
            
            if (sourceLang === targetLang) {
                $errorDiv.html('Source and target languages must be different.').show();
                return;
            }
            
            // Disable button and show loading state
            $testBtn.prop('disabled', true).text('Translating...');
            
            try {
                // Create translator instance
                let translator = null;
                
                if ('translation' in self && 'createTranslator' in self.translation) {
                    translator = await self.translation.createTranslator({
                        sourceLanguage: sourceLang.toLowerCase(),
                        targetLanguage: targetLang.toLowerCase()
                    });
                } else if ('ai' in self && 'translator' in self.ai) {
                    translator = await self.ai.translator.create({
                        sourceLanguage: sourceLang.toLowerCase(),
                        targetLanguage: targetLang.toLowerCase()
                    });
                } else if ('Translator' in self && 'create' in self.Translator) {
                    translator = await self.Translator.create({
                        sourceLanguage: sourceLang.toLowerCase(),
                        targetLanguage: targetLang.toLowerCase()
                    });
                }
                
                if (!translator) {
                    throw new Error('Chrome AI Translator API is not available. Please check your browser configuration.');
                }
                
                // Perform translation
                const translatedText = await translator.translate(textToTranslate);
                
                // Display result
                $resultDiv.html(
                    '<strong>Original:</strong> ' + $('<div>').text(textToTranslate).html() + '<br><br>' +
                    '<strong>Translated:</strong> ' + $('<div>').text(translatedText).html()
                ).css({
                    'background': '#f0f9ff',
                    'border': '1px solid #bae6fd',
                    'color': '#0c4a6e'
                }).show();
                
            } catch (error) {
                // Display error
                let errorMessage = 'Translation failed. ';
                if (error.message) {
                    errorMessage += error.message;
                } else {
                    errorMessage += 'Please check your Chrome AI Translator configuration.';
                }
                $errorDiv.text(errorMessage).show();
            } finally {
                // Re-enable button
                $testBtn.prop('disabled', false).text('Test Translation');
            }
        });
    }
    
    // Initialize Chrome AI notice first, then show test translation section after notice is displayed
    if ($('#tpa-chrome-local-ai-notice').length) {
        // Initialize notice and wait for it to complete
        initChromeLocalAINotice().then(function() {
            // After notice is shown (or hidden if no notice needed), initialize test translation
            if ($('#tpa-chrome-test-translation').length) {
                initTestTranslation().catch(function(error) {
                    console.error('Failed to initialize test translation:', error);
                    const $errorDiv = $('#tpa-test-translation-error');
                    if ($errorDiv.length) {
                        $errorDiv.html('Failed to load available languages. Please refresh the page.').show();
                    }
                });
            }
        }).catch(function(error) {
            console.error('Failed to initialize Chrome AI notice:', error);
            // Even if notice fails, try to initialize test translation
            if ($('#tpa-chrome-test-translation').length) {
                initTestTranslation().catch(function(testError) {
                    console.error('Failed to initialize test translation:', testError);
                });
            }
        });
    } else {
        // If no notice element exists, initialize test translation directly
        if ($('#tpa-chrome-test-translation').length) {
            initTestTranslation().catch(function(error) {
                console.error('Failed to initialize test translation:', error);
                const $errorDiv = $('#tpa-test-translation-error');
                if ($errorDiv.length) {
                    $errorDiv.html('Failed to load available languages. Please refresh the page.').show();
                }
            });
        }
    }

});
