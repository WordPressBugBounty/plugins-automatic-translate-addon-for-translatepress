class ChromeAiTranslator {
    /**
     * Detect the real browser brand via userAgentData brands.
     *
     * @returns {'Chrome'|'Edge'|'Other'}
     */
    static getBrowserType = () => {
        let type = 'Other';
        if (navigator && navigator.userAgentData && navigator.userAgentData.brands) {
            navigator.userAgentData.brands.forEach((data) => {
                if (data.brand === 'Google Chrome') {
                    type = 'Chrome';
                } else if (data.brand === 'Microsoft Edge') {
                    type = 'Edge';
                }
            });
        } else if (navigator.userAgent.includes('Edg')) {
            type = 'Edge';
        } else if (window.hasOwnProperty('chrome')) {
            type = 'Chrome';
        }
        return type;
    };

    static isEdge = () => ChromeAiTranslator.getBrowserType() === 'Edge';

    static isChrome = () => ChromeAiTranslator.getBrowserType() === 'Chrome';

    static MIN_EDGE_VERSION = 148;

    static getEdgeMajorVersion = () => {
        const match = (navigator.userAgent || '').match(/Edg(?:A|iOS)?\/(\d+)/);
        return match ? parseInt(match[1], 10) : null;
    };

    static checkEdgeVersion = () => {
        if (!ChromeAiTranslator.isEdge()) {
            return false;
        }
        const edgeVersion = ChromeAiTranslator.getEdgeMajorVersion();
        if (edgeVersion !== null && edgeVersion < ChromeAiTranslator.MIN_EDGE_VERSION) {
            return false;
        }
        return true;
    };

    static isOutdatedEdgeBrowser = () => {
        return ChromeAiTranslator.isEdge() && !ChromeAiTranslator.checkEdgeVersion();
    };

    static browserScheme = () => (ChromeAiTranslator.isEdge() ? 'edge' : 'chrome');

    static browserName = () => {
        const type = ChromeAiTranslator.getBrowserType();
        if (type === 'Edge') {
            return 'Edge';
        }
        if (type === 'Chrome') {
            return 'Chrome';
        }
        return 'Chrome';
    };

    static baseSupportedLanguages = [
        'en', 'es', 'ja', 'ar', 'de', 'bn', 'fr', 'hi', 'it', 'ko', 'nl', 'pl', 'pt', 'ru', 'th', 'tr', 'vi', 'zh',
        'zh-hant', 'bg', 'cs', 'da', 'el', 'fi', 'hr', 'hu', 'id', 'iw', 'lt', 'no', 'ro', 'sk', 'sl', 'sv', 'uk',
        'kn', 'ta', 'te', 'mr'
    ];

    static edgeOnlyLanguages = [
        'nb', 'af', 'is', 'fo', 'lb', 'pt-pt', 'ca', 'gl', 'oc', 'la', 'bs', 'dsb', 'hsb', 'sr-latn', 'be', 'mk',
        'sr-cyrl', 'kk', 'ky', 'tg', 'tt', 'mn-cyrl', 'ba', 'ce', 'cv', 'zh-hans', 'lzh', 'yue', 'gu', 'ml', 'ur',
        'as', 'or', 'pa', 'ne', 'si', 'my', 'dv', 'awa', 'bho', 'brx', 'doi', 'gom', 'hne', 'kha', 'lus', 'mag',
        'mai', 'mni', 'sat', 'ms', 'km', 'lo', 'jv', 'su', 'ceb', 'fil', 'mi', 'fj', 'haw', 'sm', 'to', 'ty', 'tet',
        'fa', 'he', 'ps', 'prs', 'ku', 'ks', 'sd', 'ug', 'ha', 'ig', 'yo', 'zu', 'xh', 'sw', 'sn', 'st', 'tn', 'nso',
        'run', 'rw', 'ln', 'mg', 'so', 'am', 'ti', 'nya', 'et', 'lv', 'mt', 'eu', 'cy', 'ga', 'sq', 'hy', 'ka', 'az',
        'uz', 'tk', 'bo', 'dzo', 'fr-ca', 'ht', 'ikt', 'iu-latn', 'iu', 'kmr', 'lug', 'luo', 'mn-mong', 'mww', 'otq',
        'sa', 'tlh-latn', 'yua'
    ];

    // Static method to create an instance of ChromeAiTranslator and return extra data
    static Object = (options) => {
        const selfObject = new this(options);
        return selfObject.extraData();
    };

    // Constructor to initialize the translator with options
    constructor(options) {
        this.btnSelector = options.btnSelector || false; // Selector for the button that triggers translation
        this.btnClass = options.btnClass || false; // Class for the button
        this.btnText = options.btnText || `Translate To ${options.targetLanguageLabel}`; // Text for the button
        this.stringSelector = options.stringSelector || false; // Selector for the elements containing strings to translate
        this.progressBarSelector = options.progressBarSelector || false; // Selector for the progress bar element
        this.onStartTranslationProcess = options.onStartTranslationProcess || (() => { }); // Callback for when translation starts
        this.onComplete = options.onComplete || (() => { }); // Callback for when translation completes
        this.onLanguageError = options.onLanguageError || (() => { }); // Callback for language errors
        this.onBeforeTranslate = options.onBeforeTranslate || (() => { }); // Callback for before translation
        this.onAfterTranslate = options.onAfterTranslate || (() => { }); // Callback for after translation
        this.sourceLanguage = options.sourceLanguage || "en"; // Default source language
        this.targetLanguage = options.targetLanguage || "hi"; // Default target language
        this.targetLanguageLabel = options.targetLanguageLabel || "Hindi"; // Label for the target language
        this.sourceLanguageLabel = options.sourceLanguageLabel || "English"; // Label for the source language
        this.provider = options.provider || 'chrome';
    }

    // Method to check language support and return relevant data
    extraData = async () => {
        // Check if the language is supported
        const langSupportedStatus = await ChromeAiTranslator.languageSupportedStatus(
            this.sourceLanguage,
            this.targetLanguage,
            this.targetLanguageLabel,
            this.sourceLanguageLabel,
            this.provider
        );

        if (!langSupportedStatus || langSupportedStatus.supported !== true) {
            if (typeof this.onLanguageError === 'function') {
                this.onLanguageError(langSupportedStatus || {
                    supported: false,
                    reason: 'language_not_supported',
                    message: 'This language is not supported by Chrome AI yet.',
                    supported_languages_url: ChromeAiTranslator.getSupportedLanguagesDocsUrl('chrome'),
                });
            }
            return {};
        }

        this.defaultLang = this.targetLanguage; // Set default language

        // Return methods for translation control
        return {
            continueTranslation: this.continueTranslation,
            stopTranslation: this.stopTranslation,
            startTranslation: this.startTranslation,
            reInit: this.reInit,
            init: this.init
        };
    }

    /**
     * Docs URL for the provider's supported-language list / Translator API reference.
     *
     * @param {'chrome'|'edge'} provider
     * @returns {string}
     */
    static getSupportedLanguagesDocsUrl = (provider = 'chrome') => {
        if (provider === 'edge') {
            return 'https://microsoftedge.github.io/Demos/built-in-ai/playgrounds/translator-api/';
        }
        return 'https://developer.chrome.com/docs/ai/translator-api';
    }

    /**
     * Build a structured language-support result for callers / UX.
     *
     * @param {boolean} supported
     * @param {'chrome'|'edge'} provider
     * @param {string} [reason]
     * @param {string} [message]
     * @returns {{supported: boolean, reason?: string, message?: string, supported_languages_url?: string}}
     */
    static buildLanguageSupportResult = (supported, provider = 'chrome', reason = '', message = '') => {
        if (supported) {
            return { supported: true };
        }

        const browserLabel = provider === 'edge' ? 'Edge' : 'Chrome';
        return {
            supported: false,
            reason: reason || 'language_not_supported',
            message: message || `This language is not supported by ${browserLabel} AI yet.`,
            supported_languages_url: ChromeAiTranslator.getSupportedLanguagesDocsUrl(provider),
        };
    }

    /**
     * Checks if the specified source and target languages are supported by Chrome AI Translator.
     * Note: Browser/API/secure connection checks are handled elsewhere and redirect to settings page.
     * This method only checks language-related issues.
     *
     * @param {string} sourceLanguage - The language code for the source language (e.g., "en" for English).
     * @param {string} targetLanguage - The language code for the target language (e.g., "hi" for Hindi).
     * @param {string} targetLanguageLabel - The label for the target language (e.g., "Hindi").
     * @param {string} sourceLanguageLabel - The label for the source language (e.g., "English").
     * @param {'chrome'|'edge'} [provider='chrome']
     * @returns {Promise<{supported: boolean, reason?: string, message?: string, supported_languages_url?: string}>}
     */
    static languageSupportedStatus = async (sourceLanguage, targetLanguage, targetLanguageLabel, sourceLanguageLabel, provider = 'chrome') => {
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages(provider);
        const browserLabel = provider === 'edge' ? 'Edge' : 'Chrome';
        const source = (sourceLanguage || '').toLowerCase();
        const target = (targetLanguage || '').toLowerCase();

        // Browser/API/secure connection checks are now handled in settings page
        // This method only checks language-related issues

        if (!supportedLanguages.includes(source) || !supportedLanguages.includes(target)) {
            return ChromeAiTranslator.buildLanguageSupportResult(
                false,
                provider,
                'language_not_supported',
                `This language is not supported by ${browserLabel} AI yet.`
            );
        }

        // Check if translation can be performed (language pack availability)
        const status = await ChromeAiTranslator.languagePairAvality(sourceLanguage, targetLanguage);

        if (status === 'readily' || status === 'available' || status === true) {
            return ChromeAiTranslator.buildLanguageSupportResult(true, provider);
        }

        return ChromeAiTranslator.buildLanguageSupportResult(
            false,
            provider,
            'language_pack_required',
            `Language packs for this pair are not ready in ${browserLabel} AI yet.`
        );
    }

    static languagePairAvality = async (source, target) => {
        let status = false;

        if (('translation' in self && 'createTranslator' in self.translation)) {
            status = await self.translation.canTranslate({
                sourceLanguage: source,
                targetLanguage: target,
            });
        } else if (('ai' in self && 'translator' in self.ai)) {
            const translatorCapabilities = await self.ai.translator.capabilities();
            status = await translatorCapabilities.languagePairAvailable(source, target);
        } else if ('Translator' in self && 'create' in self.Translator) {
            status = await self.Translator.availability({
                sourceLanguage: source,
                targetLanguage: target,
            });
        }

        return status;
    }

    /**
     * Get the list of supported languages for Chrome AI Translator
     * @returns {string[]} Array of supported language codes (lowercase)
     */
    static getSupportedLanguages = (provider = 'chrome') => {
        const forEdge = provider === 'edge';
        let supportedLanguages = [...ChromeAiTranslator.baseSupportedLanguages];
        if (forEdge) {
            supportedLanguages = [...supportedLanguages, ...ChromeAiTranslator.edgeOnlyLanguages];
        }
        return supportedLanguages.map((lang) => lang.toLowerCase());
    }

    /**
     * Check if the current browser matches the built-in AI provider.
     *
     * @param {'chrome'|'edge'} provider
     * @returns {boolean}
     */
    static checkBrowserCompatibility = (provider = 'chrome') => {
        if (provider === 'edge') {
            return ChromeAiTranslator.isEdge();
        }
        return ChromeAiTranslator.isChrome();
    }

    /**
     * Check if Chrome/Edge Translator API is available (presence check).
     *
     * @returns {boolean} True if API is available, false otherwise
     */
    static checkApiAvailability = () => {
        return ('translation' in self && 'createTranslator' in self.translation) ||
            ('ai' in self && 'translator' in self.ai) ||
            ('Translator' in self && 'create' in self.Translator);
    }

    /**
     * Check if connection is secure (HTTPS or secure context)
     * @returns {boolean} True if connection is secure, false otherwise
     */
    static checkSecureConnection = () => {
        return window.location.protocol === 'https:' || window.isSecureContext;
    }

    /**
     * Check if language is supported by Chrome AI Translator
     * @param {string} languageCode - Language code to check (e.g., 'en', 'hi')
     * @returns {boolean} True if language is supported, false otherwise
     */
    static isLanguageSupported = (languageCode, provider = 'chrome') => {
        if (!languageCode) return false;
        const supportedLanguages = ChromeAiTranslator.getSupportedLanguages(provider);
        const normalizedCode = languageCode.toLowerCase();
        return supportedLanguages.includes(normalizedCode) || supportedLanguages.includes(normalizedCode.split('-')[0]);
    }

    AITranslator=async (targetLanguage)=>{
        if(('translation' in self && 'createTranslator' in self.translation)){
            const translator=await self.translation.createTranslator({
                sourceLanguage: this.sourceLanguage,
                targetLanguage,
            });

            return translator;
        }else if(('ai' in self && 'translator' in self.ai )){
            const translator = await self.ai.translator.create({
                sourceLanguage: this.sourceLanguage,
                targetLanguage,
              });

            return translator;
        }else if("Translator" in self && "create" in self.Translator){
            const translator = await self.Translator.create({
                sourceLanguage: this.sourceLanguage,
                targetLanguage,
            });

            return translator;
        }

        return false;
    }

    /**
     * Initialize clipboard for elements with tapa-tooltip-element pattern
     * Using user provided implementation
     */
    static initializeClipboard = () => {
        // Support both old and new class names
        const clipboardElements = document.querySelectorAll('.chrome-ai-translator-flags, .chrome-url-link');
        
        const copyClipboard = async (text, startCopyStatus, endCopyStatus) => {
            if (!text || text === "") return;
            
            try {
                if (navigator?.clipboard?.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const div = document.createElement('div');
                    div.textContent = text;
                    document.body.appendChild(div);

                    if (window.getSelection && document.createRange) {
                        const range = document.createRange();
                        range.selectNodeContents(div);

                        const selection = window.getSelection();
                        selection.removeAllRanges(); // clear any existing selection
                        selection.addRange(range);   // select the range
                    }

                    if (document.execCommand) {
                        document.execCommand('copy');
                    }
                    document.body.removeChild(div);
                }
                
                startCopyStatus();
                setTimeout(endCopyStatus, 800);
            } catch (err) {
                console.error('Error copying text to clipboard:', err);
            }
        };

        clipboardElements.forEach(element => {
            // Skip if already initialized (has the class and data attribute)
            if (element.classList.contains('tapa-tooltip-element') && element.hasAttribute('data-clipboard-initialized')) {
                return;
            }
            
            element.classList.add('tapa-tooltip-element');
            element.setAttribute('data-clipboard-initialized', 'true');
            
            element.addEventListener('click', (e) => {
                e.preventDefault();
                
                const toolTipExists = element.querySelector('.tapa-tooltip');
                if (toolTipExists) {
                    return;
                }
                
                const toolTipElement = document.createElement('span');
                toolTipElement.textContent = "Text to be Copied!";
                toolTipElement.className = 'tapa-tooltip';
                element.appendChild(toolTipElement);
                
                copyClipboard(
                    element.getAttribute('data-clipboard-text'),
                    () => {
                        toolTipElement.classList.add('tapa-tooltip-active');
                    },
                    () => {
                        setTimeout(() => {
                            toolTipElement.remove();
                        }, 800);
                    }
                );
            });
        });
    }

    /**
     * Get SVG icons centrally
     * @param {string} iconName - Name of the icon
     * @returns {string} - SVG HTML string
     */
    static svgIcons = (iconName) => {
        const icons = {
            copy: '<svg style="width: 16px; height: 16px; vertical-align: middle; margin-left: 4px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>'
        };
        return icons[iconName] || '';
    }

    // Method to initialize the translation process
    init = async () => {
        this.appendBtn();
        this.translationStart = false; // Flag to indicate if translation has started
        this.completedTranslateIndex = 0; // Index of the last completed translation
        this.completedCharacterCount = 0; // Count of characters translated
        this.translateBtnEvents(); // Set up button events
        if (this.progressBarSelector) {
            // Use shared modal progress bar when it already exists (Google/Yandex/Chrome)
            this.useSharedProgressBar = jQuery(this.progressBarSelector).find(".progress-wrapper").length > 0;
            if (!this.useSharedProgressBar) {
                this.addProgressBar(); // Add progress bar only when no shared one exists
            }
        }
    };

    /**
     * Appends a translation button to the specified button selector.
     * The button is styled with primary button classes and includes
     * any additional classes specified in `this.btnClass`.
     */
    appendBtn = () => {
        this.translateBtn = jQuery(`<button class="button button-primary${this.btnClass ? ' ' + this.btnClass : ''}">${this.btnText}</button>`);
        jQuery(this.btnSelector).append(this.translateBtn);
    }

    /**
     * Formats a number by converting it to a string and removing any non-numeric characters.
     * 
     * @param {number} number - The number to format.
     * @returns returns formatted number
     */
    formatCharacterCount = (number) => {
        if (number >= 1000000) {
            return (number / 1000000).toFixed(1) + 'M';
        } else if (number >= 1000) {
            return (number / 1000).toFixed(1) + 'K';
        }
        return number;
    }

    // Method to set up button events for translation
    translateBtnEvents = (e) => {
        if (!this.btnSelector || jQuery(this.btnSelector).length === 0) return this.onLanguageError("The button selector is missing. Please provide a valid selector for the button.");
        if (!this.stringSelector || jQuery(this.stringSelector).length === 0) return this.onLanguageError("The string selector is missing. Please provide a valid selector for the strings to be translated.");

        this.translateStatus = true; // Set translation status to true
        this.translateBtn.off("click"); // Clear previous click handlers
        this.translateBtn.prop("disabled", false); // Enable the button

        // Set up click event for starting translation
        if (!this.translationStart) {
            this.translateBtn.on("click", this.startTranslationProcess);
        } else if (this.translateStringEle.length > (this.completedTranslateIndex + 1)) {
            this.translateBtn.on("click", () => {
                this.onStartTranslationProcess(); // Call the start translation callback
                this.stringTranslation(this.completedTranslateIndex + 1); // Start translating the next string
            });
        } else {
            this.onComplete({ translatedStringsCount: this.completedCharacterCount }); // Call the complete callback
            this.translateBtn.prop("disabled", true); // Disable the button
        }
    };

    // Method to start the translation process
    startTranslationProcess = async () => {
        this.onStartTranslationProcess(); // Call the start translation callback
        const langCode = this.defaultLang; // Get the default language code

        this.translationStart = true; // Set translation start flag
        this.translateStringEle = jQuery(this.stringSelector); // Get the elements to translate

        // Calculate total character count for progress tracking
        this.totalStringCount = Array.from(this.translateStringEle).map(ele => ele.innerText.length).reduce((a, b) => a + b, 0);

        // Create a translator instance
        this.translator = await this.AITranslator(langCode);

        // Start translating if there are strings to translate
        if (this.translateStringEle.length > 0) {
            await this.stringTranslation(this.completedTranslateIndex);
        }
    };

    // Method to translate a specific string at the given index
    stringTranslation = async (index) => {
        if (!this.translateStatus) return; // Exit if translation is stopped
        const ele = this.translateStringEle[index]; // Get the element to translate
        this.onBeforeTranslate(ele); // Call the before translation callback
        const orignalText = ele.innerText;
        let originalString = [];

        if (ele.childNodes.length > 0 && !ele.querySelector('.notranslate')) {
            ele.childNodes.forEach(child => {
                if (child.nodeType === 3 && child.nodeValue.trim() !== '') {
                    originalString.push(child);
                }
            });
        } else if (ele.querySelector('.notranslate')) {
            ele.childNodes.forEach(child => {
                if (child.nodeType === 3 && child.nodeValue.trim() !== '') {
                    originalString.push(child);
                }
            });
        }

        if (originalString.length > 0) {
            await this.stringTranslationBatch(originalString, 0);
        }

        this.completedCharacterCount += orignalText.length; // Update character count
        this.completedTranslateIndex = index; // Update completed index
        if (this.progressBarSelector) {
            this.updateProgressBar(); // Update the progress bar
        }
        this.onAfterTranslate(ele); // Call the after translation callback

        // Continue translating the next string if available
        if (this.translateStringEle.length > index + 1) {
            await this.stringTranslation(this.completedTranslateIndex + 1);
        }

        // If all strings are translated, complete the process
        if (index === this.translateStringEle.length - 1) {
            this.translateBtn.prop("disabled", true); // Disable the button
            this.onComplete({ characterCount: this.completedCharacterCount }); // Call the complete callback
            if (!this.useSharedProgressBar) {
                jQuery(this.progressBarSelector).find(".chrome-ai-translator-strings-count").show().find(".totalChars").text(this.formatCharacterCount(this.completedCharacterCount));
            }
        }
    };

    stringTranslationBatch = async (originalString, index) => {
        const translatedString = await this.translator.translate(originalString[index].nodeValue); // Translate the string

        if (translatedString && '' !== translatedString) {
            originalString[index].nodeValue = translatedString; // Set the translated string
        }

        if (index < originalString.length - 1) {
            await this.stringTranslationBatch(originalString, index + 1);
        }

        return true;
    }

    // Method to add a progress bar to the UI
    addProgressBar = () => {
        if (!document.querySelector("#chrome-ai-translator-modal .chrome-ai-translator_progress_bar")) {
            const progressBar = jQuery(`
                <div class="chrome-ai-translator_progress_bar" style="background-color: #f3f3f3;border-radius: 10px;overflow: hidden;margin: 1.5rem auto; width: 50%;">
                <div class="chrome-ai-translator_progress" style="overflow: hidden;transition: width .5s ease-in-out; border-radius: 10px;text-align: center;width: 0%;height: 20px;box-sizing: border-box;background-color: #4caf50; color: #fff; font-weight: 600;"></div>
                </div>
                <div style="display:none; color: white;" class="chrome-ai-translator-strings-count hidden">
                    Wahooo! You have saved your valuable time via auto translating 
                    <strong class="totalChars">0</strong> characters using 
                    <strong>
                        ${ChromeAiTranslator.browserName()} AI Translator
                    </strong>
                </div>
            `);
            jQuery(this.progressBarSelector).append(progressBar); // Append the progress bar to the specified selector
        }
    };

    // Method to update the progress bar based on translation progress
    updateProgressBar = () => {
        const progress = ((this.completedCharacterCount / this.totalStringCount) * 1000) / 10; // Calculate progress percentage
        let decimalValue = progress.toString().split('.')[1] || ''; // Get decimal part of the progress
        decimalValue = decimalValue.length > 0 && decimalValue[0] !== '0' ? decimalValue[0] : ''; // Format decimal value
        const formattedProgress = parseInt(progress) + `${decimalValue !== '' ? '.' + decimalValue : ''}`; // Format progress for display
        if (this.useSharedProgressBar) {
            const progressBar = jQuery(this.progressBarSelector).find(".progress-wrapper .progress-bar");
            progressBar.css("width", `${formattedProgress}%`).find("#progressText").text(`${formattedProgress}%`);
        } else {
            jQuery(".chrome-ai-translator_progress").css({ "width": `${formattedProgress}%` }).text(`${formattedProgress}%`); // Update progress bar width and text
        }
    };

    // Method to stop the translation process
    stopTranslation = () => {
        this.translateStatus = false; // Set translation status to false
    }

    // Method to reinitialize button events
    reInit = () => {
        this.translateBtnEvents(); // Re-setup button events
    }

    // Method to start translation from the current index
    startTranslation = () => {
        this.translateStatus = true; // Set translation status to true
        this.startTranslationProcess(this.completedTranslateIndex + 1); // Start translation process
    }

    
}

/*
 * Example Usage of the ChromeAiTranslator.init method.
 * This method initializes the Chrome AI Translator with a comprehensive set of configuration options to facilitate the translation process.
 * 
 * Configuration Options:
 * 
 * - mainWrapperSelector: A CSS selector for the main wrapper element that encapsulates all translation-related elements.
 * - btnSelector: A CSS selector for the button that initiates the translation process.
 * - btnClass: A custom class for styling the translation button.
 * - btnText: The text displayed on the translation button.
 * - stringSelector: A CSS selector for the elements that contain the strings intended for translation.
 * - progressBarSelector: A CSS selector for the progress bar element that visually represents the translation progress.
 * - sourceLanguage: The language code representing the source language (e.g., "es" for Spanish).
 * - targetLanguage: The language code representing the target language (e.g., "fr" for French).
 * - onStartTranslationProcess: A callback function that is executed when the translation process begins.
 * - onBeforeTranslate: A callback function that is executed prior to each individual translation.
 * - onAfterTranslate: A callback function that is executed following each translation.
 * - onComplete: A callback function that is executed upon the completion of the translation process.
 * - onLanguageError: A callback function that is executed when a language-related error occurs.
 */

// Example for checking language support status
// ChromeAiTranslator.languageSupportedStatus("en", "fr", "French");

// const chromeAiTranslatorObject = ChromeAiTranslator.Object(
//     {
//         mainWrapperSelector: ".main-wrapper", // CSS selector for the main wrapper element
//         btnSelector: ".translator-container .translator-button", // CSS selector for the translation button
//         btnClass: "Btn_custom_class", // Custom class for button styling
//         btnText: "Translate To French", // Text displayed on the translation button
//         stringSelector: ".translator-body .translation-item", // CSS selector for translation string elements
//         progressBarSelector: ".translator-progress-bar", // CSS selector for the progress bar
//         sourceLanguage: "es", // Language code for the source language
//         targetLanguage: "fr", // Language code for the target language
//         onStartTranslationProcess: () => { console.log("Translation process started."); }, // Callback for translation start
//         onBeforeTranslate: () => { console.log("Before translation."); }, // Callback before each translation
//         onAfterTranslate: () => { console.log("After translation."); }, // Callback after each translation
//         onComplete: () => { console.log("Translation completed."); }, // Callback for completion
//         onLanguageError: () => { console.error("Language error occurred."); } // Callback for language errors
//     }
// );
// chromeAiTranslatorObject.init();

var tpaChromeAiInit=async ()=>{
    let transalationInitialize = false;
        let translatedLanguageName = trp_editor_data.language_names[localStorage.language_name]

        
    const startTransaltion = () => {
        localStorage.setItem("translationStartTime", new Date().getTime());
        const stringContainer = jQuery(".chrome-ai-translator-modal .modal-content .string_container");
        if (stringContainer[0] && stringContainer[0].scrollHeight > 100) {
            jQuery(".chrome-ai-translator-modal .progress-wrapper").show();
            jQuery(".chrome-ai-translator-modal .progress-wrapper .progress-bar").css("width", "0%").find("#progressText").text("0%");
            jQuery(".chrome-ai-translator-modal .my_translate_progress").fadeIn("slow");
        }
    }
    
    const beforeTranslate = (ele) => {
        const stringContainer = jQuery(".chrome-ai-translator-modal .modal-content .string_container");
        const scrollStringContainer = (position) => {
            stringContainer.scrollTop(position);
        };
    
        const stringContainerPosition = stringContainer[0].getBoundingClientRect();
    
        const eleTopPosition = ele.closest("tr").offsetTop;
        const containerHeight = stringContainer.height();
    
        if (eleTopPosition > (containerHeight + stringContainerPosition.y)) {
            scrollStringContainer(eleTopPosition - containerHeight + ele.offsetHeight);
        }
    }
    
    const completeTranslation = () => {
        const endTime = new Date().getTime();
        const startTime = parseInt(localStorage.getItem("translationStartTime"));
        const totalTime = ((endTime - startTime) / 1000).toFixed(2);
        localStorage.setItem("total_translation_time", totalTime);
        setTimeout(() => {
            jQuery(".chrome-ai-translator-modal .save_it").prop("disabled", false);
            jQuery(".chrome-ai-translator-modal .my_translate_progress").fadeOut("slow");
            jQuery(".chrome-ai-translator-modal .tpa-stats").fadeIn("slow");
        }, 3000);
    }
    
    const languageError = (status) => {
        // Disable the button if language is not supported
        jQuery("#chrome-ai-translator_settings_btn").attr("disabled", true);

        if (status && status.message) {
            const docsLink = status.supported_languages_url
                ? ` <a href="${String(status.supported_languages_url).replace(/"/g, '&quot;')}" target="_blank" rel="noopener noreferrer">View supported languages</a>`
                : '';
            const safeMessage = String(status.message)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            jQuery(".chrome-ai-translator-container")
                .find(".notice-container")
                .addClass("notice inline notice-warning")
                .show()
                .html(safeMessage + docsLink);
            jQuery(".chrome-ai-translator-container").find(".tpa-preloader-wrap").hide();
        }
    }

        const TranslatorObject = await ChromeAiTranslator.Object(
            {
                mainWrapperSelector: ".chrome-ai-translator",
                btnSelector: ".chrome-ai-translator #chrome_ai_translator_element",
                btnClass: "chrome_ai_translator_btn", 
                btnText: (translatedLanguageName !== undefined) ? `Translate To ${translatedLanguageName}` : `Translate`,
                stringSelector: ".chrome-ai-translator-body table tbody tr td.target.translate",
                progressBarSelector: ".chrome-ai-translator-modal .my_translate_progress",
                sourceLanguage: localStorage.page_lang,
                targetLanguage: localStorage.language_code,
                onStartTranslationProcess: startTransaltion,
                onComplete: completeTranslation,
                onLanguageError: languageError,
                onBeforeTranslate: beforeTranslate,
                targetLanguageLabel: translatedLanguageName,
                provider: (ChromeAiTranslator.getBrowserType() === 'Edge') ? 'edge' : 'chrome'
            }
        );

        jQuery(document).on("click", "#chrome-ai-translator_settings_btn", function () {
            if(typeof TranslatorObject.reInit === 'function') {
                TranslatorObject.reInit();
            }
        });

        if (!transalationInitialize && typeof TranslatorObject.init === 'function') {
            transalationInitialize = true;
            TranslatorObject.init();
        }

        jQuery(window).on("click", (event) => {
            if (jQuery(".chrome-ai-translator-modal").length > 0 && !event.target.closest(".modal-content") && !event.target.closest("#latlt-dialog") && jQuery(".chrome-ai-translator-modal").css("display") !== "none") {
                TranslatorObject.stopTranslation();
            }
        });

        jQuery(document).on("click", ".chrome-ai-translator-header .close", () => {
            TranslatorObject.stopTranslation();
        });
}


