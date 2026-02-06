const tpAutoTranslator = (function (window, $) {
  // get plugin configuration object.
  const configData = window.extradata;
  const { ajax_url: ajaxUrl, extra_class: rtlClass, nonce: nonce } = configData;
  var dict_id = new Array();
  var gettxt_id = new Array();
  var chromeAIStatus = false;
  var previousSelectedLang = null;
  onLoad();

  async function onLoad() {
    // Update localStorage with current language values
    updateLanguageStorage();

    // create strings modal
    createStringsModal("yandex");

    //create strings modal - check Chrome status with current language values
    chromeAIStatus = await checkChromeAILangStatus();
    createStringsModal('chrome-ai-translator');
  }

   async function checkChromeAILangStatus(){
    const status = await ChromeAiTranslator.languageSupportedStatus(localStorage.getItem("page_lang"),localStorage.getItem("language_code"),localStorage.getItem("target_language_name"),localStorage.getItem("source_language_name"));
    return status;
  }

  // Helper function to get the settings page URL
  function getChromeSettingsPageUrl() {
    // Try to get admin URL from various sources
    if (typeof ajaxurl !== 'undefined') {
      // Extract admin URL from ajaxurl (e.g., /wp-admin/admin-ajax.php)
      const adminBase = ajaxurl.replace('/admin-ajax.php', '');
      return adminBase + '/options-general.php?page=translatepress-tpap-dashboard&tab=settings';
    }
    // Fallback: construct from current location
    const currentPath = window.location.pathname;
    const adminMatch = currentPath.match(/^(.+\/wp-admin)/);
    if (adminMatch) {
      return adminMatch[1] + '/options-general.php?page=translatepress-tpap-dashboard&tab=settings';
    }
    // Last resort: relative path
    return '/wp-admin/options-general.php?page=translatepress-tpap-dashboard&tab=settings';
  }

  // Check if Chrome has browser/API/secure connection errors (not language errors)
  function hasChromeConfigurationError() {
    // Use centralized Chrome AI Translator utility methods
    if (typeof ChromeAiTranslator === 'undefined') {
      return true; // If ChromeAiTranslator not loaded, assume error
    }
    
    // Browser check
    const bypassBrowser = configData.chrome_ai_bypass_browser_check === '1';
    const bypassSecure = configData.chrome_ai_bypass_secure_check === '1';
    const bypassApi = configData.chrome_ai_bypass_api_check === '1';

    if (!ChromeAiTranslator.checkBrowserCompatibility() && !bypassBrowser) {
      return true;
    }
    
    const apiAvailable = ChromeAiTranslator.checkApiAvailability() || bypassApi;
    const secureConnection = ChromeAiTranslator.checkSecureConnection() || window.isSecureContext || bypassSecure;
    
    // Secure connection check
    if (!apiAvailable && !secureConnection) {
      return true;
    }
    
    // API availability check
    if (!apiAvailable) {
      return true;
    }
    
    return false;
  }

  // Check if language is unsupported (not in supported languages list)
  function isLanguageUnsupported(sourceLanguage, targetLanguage) {
    if (!sourceLanguage || !targetLanguage) {
      return false; // Can't determine if unsupported without language codes
    }
    
    // Use centralized Chrome AI Translator utility methods
    if (typeof ChromeAiTranslator === 'undefined') {
      return false; // If ChromeAiTranslator not loaded, can't check
    }
    
    return !ChromeAiTranslator.isLanguageSupported(sourceLanguage) || 
           !ChromeAiTranslator.isLanguageSupported(targetLanguage);
  }

  // Check if language pack is required (language is supported but pack not installed)
  async function isLanguagePackRequired(sourceLanguage, targetLanguage) {
    // First check if language is unsupported - if so, it's not a pack issue
    if (isLanguageUnsupported(sourceLanguage, targetLanguage)) {
      return false;
    }
    
    // Use centralized Chrome AI Translator utility methods
    if (typeof ChromeAiTranslator === 'undefined') {
      return false; // Can't check pack status if ChromeAiTranslator not loaded
    }
    
    // Check if browser API is available
    if (!ChromeAiTranslator.checkApiAvailability()) {
      return false; // Can't check pack status if API not available
    }
    
    // Check language pair availability using centralized method
    try {
      const status = await ChromeAiTranslator.languagePairAvality(sourceLanguage, targetLanguage);
      
      // If status indicates pack is required or downloading, return true
      if (status === "after-download" || status === "downloadable" || status === "unavailable" || status === "downloading") {
        return true;
      }
      
      // If status is not 'readily' or 'available', pack might be required
      if (status !== 'readily' && status !== 'available' && status !== false) {
        return true;
      }
    } catch (error) {
      // If check fails, assume it's not a pack issue
      console.log('Language pack check failed:', error);
      return false;
    }
    
    return false;
  }

  // Function to update Chrome AI translator button in modal when status changes
  async function updateChromeAIButton() {
    const icons = {
      error: extradata['error_preview']
    };
    const TPA_IMG = (key) => icons[key];
    
    // Find the Chrome AI translator row in the modal
    const $chromeRow = $('#tpa-dialog').find('tr').filter(function() {
      return $(this).text().indexOf('Chrome Built-in AI') !== -1;
    });
    
    if ($chromeRow.length > 0) {
      const $buttonCell = $chromeRow.find('td').eq(1); // Second column contains the button
      
      // Update button based on current chromeAIStatus
      if (chromeAIStatus === true) {
        $buttonCell.html('<button id="tpa_chrome_ai_translate_btn" class="tpa-provider-btn translate" data-translate-engen="chrome-ai-translator">Translate</button>');
        // Re-attach click handler for the new button
        $("#tpa_chrome_ai_translate_btn").off('click').on("click", function () {
          onChromeTranslateClick();
        });
      } else {
        const sourceLanguage = localStorage.getItem("page_lang");
        const targetLanguage = localStorage.getItem("language_code");
        const settingsUrl = getChromeSettingsPageUrl();
        
        if (hasChromeConfigurationError()) {
          // Browser/API/secure connection error
          $buttonCell.html(`
            <button class="tpa-chromeai-disabled-message tpa-provider-btn error" onclick="window.open('${settingsUrl}', '_blank'); return false;">
              <img src="${TPA_IMG('error')}" alt="error" style="height:16px; vertical-align:middle; margin-right:5px;">
              Configure
            </button>
          `);
        } else {
          // Check if it's a language pack issue (supported but not installed) or unsupported language
          const packRequired = await isLanguagePackRequired(sourceLanguage, targetLanguage);
          
          if (packRequired) {
            // Language pack required (supported but not installed) → "Configure"
            $buttonCell.html(`
              <button class="tpa-chromeai-disabled-message tpa-provider-btn error" onclick="window.open('${settingsUrl}', '_blank'); return false;">
                <img src="${TPA_IMG('error')}" alt="error" style="height:16px; vertical-align:middle; margin-right:5px;">
                Configure
              </button>
            `);
          } else {
            // Unsupported language - show disabled "Not Supported" button
            $buttonCell.html(`
              <button class="tpa-chromeai-disabled-message tpa-provider-btn error error-disabled" disabled>
                Not Supported
              </button>
            `);
          }
        }
      }
    }
  }

  // Helper function to update localStorage with current language values
  function updateLanguageStorage() {
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var getSelectedlang = $("#trp-language-select").val();
    var getTargetlangName = $("#trp-language-select").find("option:selected").text();
    var getDefaultlangName = $("#trp-language-select").find("option:first-child").text();
    
    if (getSelectedlang) {
      var pageLang = tpa_language_code(default_lang);
      var defaultLang = tpa_language_code(getSelectedlang);
      
      localStorage.setItem("page_lang", pageLang);
      localStorage.setItem("language_code", defaultLang);
      localStorage.setItem("lang", defaultLang);
      localStorage.setItem("language_name", getSelectedlang);
      localStorage.setItem("default_language", default_lang);
      localStorage.setItem("source_language_name", getDefaultlangName);
      localStorage.setItem("target_language_name", getTargetlangName);
    }
  }

  function initialize() {
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var getSelectedlang = $("#trp-language-select").val();

    // Embbed Auto Translate button inside Translatepress editor
    if ($("#tpa-auto-btn").length === 0) {
      addAutoTranslatepressBtn(default_lang, getSelectedlang);
    }

    //append auto translate settings model
    settingsModel();

    //on click on auto tranlsate button
    $("#tpa-auto-btn").on("click", function () {
      openSettingsModel();
    });

    //on click on yandex transllate button
    $("#tpa_yandex_translate_btn").on("click", function () {
      onYandexTranslateClick();
    });

    //on click on chrome ai translate button
    $("#tpa_chrome_ai_translate_btn").on("click", function () {
      onChromeTranslateClick();
    });

    // Handle click on "Configure" button (not disabled buttons)
    // Disabled "Not Supported" buttons won't trigger this handler
    $(document).on('click', '.tpa-chromeai-disabled-message:not(:disabled)', function(e) {
      // If button has onclick attribute (like "Configure" button), let it handle the click
      if ($(this).attr('onclick')) {
        return; // Let onclick handler execute
      }
      // Otherwise, prevent default
      e.preventDefault();
    });

    //on click on merge button
    $(".save_it").on("click", onSaveClick);
  }
  // open popup on autotranslate button click
  function openSettingsModel() {
    //Get Dictionary Ids
    $('#trp-string-categories optgroup option[data-group="String List"]').each(
      function (x, el) {
        var data_group = $(this).attr("data-group");
        var database_id = $(this).attr("data-database-id");
        var id = $(this).attr("value");
        var person = database_id;
        dict_id[x] = database_id;
      }
    );

    //Get Gettext Ids
    $(
      '#trp-string-categories optgroup option[data-group="Gettext Strings"]'
    ).each(function (x, el) {
      var data_group = $(this).attr("data-group");
      var database_id = $(this).attr("data-database-id");
      var id = $(this).attr("value");
      var person = database_id;
      gettxt_id[x] = database_id;
    });

    var getSelectedlang = $("#trp-language-select").val();
    var getTargetlangName = $("#trp-language-select").find("option:selected").text();
    var getDefaultlangName = $("#trp-language-select").find("option:first-child").text()
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var defaultLang = tpa_language_code(getSelectedlang);
    localStorage.setItem("language_code", defaultLang);
    localStorage.setItem("lang", defaultLang);
    
    // Force sync Yandex internal storage to be inactive but with the correct language
    localStorage.setItem("yt-widget", JSON.stringify({ active: false, lang: defaultLang }));

    localStorage.setItem("language_name", getSelectedlang);
    localStorage.setItem("default_language", default_lang);
    localStorage.setItem("source_language_name", getDefaultlangName);
    localStorage.setItem("target_language_name", getTargetlangName);
    localStorage.setItem("dictionary_id", dict_id);
    localStorage.setItem("gettxt_id", gettxt_id);

    createPopup();
  }

  // integrates auto translator button
  function addAutoTranslatepressBtn(default_lang, getSelectedlang) {
    $("#trp-language-switch").before(
      '<div><label class="tpa-steps">Step 1 - Select Language</label></div>'
    );
    $("#trp-next-previous").after(
      '<div><label class="tpa-steps">Step 2 - Click Auto Translate Button</label></div><button id="tpa-auto-btn">Auto Translate</button><div class="tpa-user-message"></div>'
    );

    // if (default_lang == getSelectedlang) {
    $("#tpa-auto-btn").removeClass("is-enable");
    $("#tpa-auto-btn").addClass("is-disable");
    $("#tpa-auto-btn").attr('disabled', true);
    $(".tpa-user-message").html('*To enable the button, change the default language in Step 1.');
    // } else {
    //   $("#tpa-auto-btn").addClass("is-enable");
    //   $("#tpa-auto-btn").removeClass("is-disable");
    // }
  }
  setInterval(enableAutotranslateButton, 200);
  async function enableAutotranslateButton() {
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var getSelectedlang = $("#trp-language-select").val();

    // Check if language has changed
    if (previousSelectedLang !== null && previousSelectedLang !== getSelectedlang) {
      // Update localStorage with new language values
      updateLanguageStorage();
      // Re-check Chrome AI translator status with new language
      chromeAIStatus = await checkChromeAILangStatus();
      // Update Chrome AI translator button in modal if modal exists
      updateChromeAIButton();
    }
    
    // Initialize previousSelectedLang on first run
    if (previousSelectedLang === null) {
      previousSelectedLang = getSelectedlang;
      // Update localStorage on first run
      updateLanguageStorage();
    } else {
      previousSelectedLang = getSelectedlang;
    }

    // Check if the loader is hidden
    var isLoaderHidden = $("#trp-preview-loader").css("display") === "none";
    var isAjaxLoaderHidden = $('#trp-string-saved-ajax-loader').css("display") == "none";
    var newButtonState = default_lang !== getSelectedlang && isLoaderHidden && isAjaxLoaderHidden;

    // Enable or disable the button based on the condition
    if (newButtonState) {
      $("#tpa-auto-btn").addClass("is-enable").removeClass("is-disable");
      $("#tpa-auto-btn").attr('disabled', false);
      $(".tpa-user-message").html('Translate all plain strings of current page.');
    } else {
      $("#tpa-auto-btn").addClass("is-disable").removeClass("is-enable");
      $("#tpa-auto-btn").attr('disabled', true);
      $(".tpa-user-message").html('*To enable the button, change the default language in Step 1.');
    }
  }

  // create auto translate popup
  function createPopup() {
    var style = $("#tpa-dialog")
      .dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        draggable: false,
        dialogClass: rtlClass,
        buttons: {
          Cancel: function () {
            $(this).dialog("close");
          },
        },
      })
      .css("background-color", "#E4D4D4");
  }

  //load strings in popup table
  function printStringsInPopup(jsonObj, type, group, idss) {
    $(".notice-container.notice.inline.notice-warning").remove();
    $(".string_container").show();
    $(".choose-lang").show();
    $(".save_it").show();
    var html = "";
    var totalTChars = 0;
    var index = 1;
    if (jsonObj) {
      for (const key in jsonObj) {
        if (jsonObj.hasOwnProperty(key)) {
          const groups = group[key];
          const element = jsonObj[key];
          if (element.source != "") {
            if (type == "yandex" || type == "chrome-ai-translator") {
              html += `<tr id="${key}"><td>${index}</td><td  class="notranslate source" data-group= "${group[key]}" data-db-id =" ${idss[key]}">${element}</td>`;
            } else {
              if (key > 2500) {
                break;
              }
              html += `<tr id="${key}"><td>${index}</td><td  class="notranslate source" data-group= "${group[key]}" data-db-id =" ${idss[key]}">${element}</td>`;
            }
            if (type == "yandex" || type == "chrome-ai-translator") {
              html += `<td translate ="yes" class = "target translate">${element}</td></tr>`;
            } else {
              html += `<td class ="target translate"></td></tr>`;
            }
            index++;
            totalTChars += element.length;
          }
        }
      }
      $(".tpa-stats").each(function () {
        $(this).find(".totalChars").html(totalTChars);
      });
    }

    $(`#${type}_tpap_string_tbl_body`).html(html);
  }

  // When the user clicks anywhere outside of the modal, close it
  $(window).on("click", function (event) {
    if ($(event.target).hasClass("tpa_custom_model")) {
        $(event.target).fadeOut("slow");
    }
  });

  $(document).on("click", ".tpa_custom_model .notice-dismiss", function () {
      $(".notice.inline.notice-info.is-dismissible").fadeOut("slow");
  });

  // Get the <span> element that closes the modal
  $(document).on("click", ".tpa_custom_model .close", function () {
      $(this).closest(".tpa_custom_model").fadeOut("slow");
      location.reload();
  });

  // When the user clicks Yandex button, open the modal

  function onYandexTranslateClick() {
    var tr_type = "yandex";
    $(".yandex-widget-container").find(".tpa-preloader-wrap").show();
    $(".yandex-widget-container").find(".translator-widget, .string_container, .notice-container, .notice-info, .is-dismissible").hide();
    
    //Add translate attribute with html tag
    $("html").attr("translate", "no");
    $(".save_it").prop("disabled", true);
    $(".tpa-stats").css("display", "none");
    var default_code = localStorage.getItem("language_code");
    var arr = [
      "ki","en","pl","af","jv","no","am","ar","az","ba","be","bg","bn","bs","ca","ceb","cs","cy","da","de","el","en","eo","es","et","eu","fa","fi","fr","ga","gd","gl","gu","he","hi","hr","ht","hu","hy","id","is","it","ja","jv","ka","kk","km","kn","ko","ky","la","lb","lo","lt","lv","mg","mhr","mi","mk","ml","mn","mr","mrj","ms","mt","my","ne","nl","no","pa","pap","pl","pt","ro","ru","si","sk","sl","sq","sr","su","sv","sw","ta","te","tg","th","tl","tr","tt","udm","uk","ur","uz","vi","xh","yi","zh",
    ];
    if (arr.includes(default_code)) {
      addStringsInModal(tr_type);
    } else {
      $(".yandex-widget-container").find(".tpa-preloader-wrap").hide();
      $(".yandex-widget-container").find(".notice-container")
        .addClass("notice inline notice-warning")
        .show()
        .html("Yandex Automatic Translator Does not support this language.");
    }

    //show yandex pop-up
    var style1 = {};
    $("#tpa_yandex_translate_btn").css(style1);
    $("#tpa-dialog").dialog("close");
    $(".yandex-widget-container").fadeIn("slow", function() {
        $(document).trigger('tpa_yandex_modal_open');
    });
  }
  
  function onChromeTranslateClick(){
    var tr_type = $('#tpa_chrome_ai_translate_btn').attr("data-translate-engen");
    $(".chrome-ai-translator-container").find(".tpa-preloader-wrap").show();
    $(".chrome-ai-translator-container").find(".translator-widget, .string_container, .notice-container, .notice-info, .is-dismissible").hide();

    $(".save_it").prop("disabled", true);
    $(".tpa-stats").css("display", "none");
    var default_code = localStorage.getItem("language_code");
    var arr = ['en', 'pl', 'af', 'jv', 'no', 'am', 'ar', 'az', 'ba', 'be', 'bg', 'bn', 'bs', 'ca', 'ceb', 'cs', 'cy', 'da', 'de', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'ga', 'gd', 'gl', 'gu', 'he', 'hi', 'hr', 'ht', 'hu', 'hy', 'id', 'is', 'it', 'ja', 'jv', 'ka', 'kk', 'km', 'kn', 'ko', 'ky', 'la', 'lb', 'lo', 'lt', 'lv', 'mg', 'mhr', 'mi', 'mk', 'ml', 'mn', 'mr', 'mrj', 'ms', 'mt', 'my', 'ne', 'nl', 'no', 'pa', 'pap', 'pl', 'pt', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr', 'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'tl', 'tr', 'tt', 'udm', 'uk', 'ur', 'uz', 'vi', 'xh', 'yi', 'zh'];
    if (arr.includes(default_code)) {
      addStringsInModal(tr_type, default_code);
    }else {
      $(".chrome-ai-translator-container").find(".tpa-preloader-wrap").hide();
      $(".chrome-ai-translator-container").find(".notice-container")
        .addClass("notice inline notice-warning")
        .show()
        .html("Chrome Translator Does not support this language.");
    }
    $("#tpa-dialog").dialog("close");
    $(".chrome-ai-translator-container").fadeIn("slow");
  }

  function addStringsInModal(tr_type, default_code){
    var language_code = localStorage.getItem("language_name");
    var default_lang = localStorage.getItem("default_language");
    var current_page_db_id = localStorage.getItem("dictionary_id");
    var gettxt_id = localStorage.getItem("gettxt_id");
    var request_data = {
      action: "tpa_get_strings",
      data: language_code,
      dictionary_id: current_page_db_id,
      gettxt_id: gettxt_id,
      default_lang: default_lang,
      _ajax_nonce: nonce,
    };
    $.ajax({
      type: "POST",
      url: ajaxUrl,
      dataType: "json",
      data: request_data,
      success: function (response) {
        var plainStrArr = response;
        var strings = new Array();
        var group = new Array();
        var idss = new Array();
        var i = 0;
        plainStrArr.forEach(function (entry) {
          strings[i] = entry.strings;
          group[i] = entry.data_group;
          idss[i] = entry.database_ids;
          i++;
        });
        if (plainStrArr.length > 0) {
          const $modal = $(`#tpa_${tr_type}_model`);
          $modal.find(".tpa-preloader-wrap").hide();
          $modal.find(".modal-body > *:not(.tpa-preloader-wrap):not(.my_translate_progress)").show();
          $modal.find(".notice-info").show();
          if (tr_type == "yandex") {
            $("html").attr("translate", "no");
          }
          printStringsInPopup(strings, tr_type, group, idss);
          if (tr_type == "yandex") {
            setTimeout(function() {
              $("#ytWidget .yt-button_type_left").trigger("click");
            }, 1000);
          }
          if(tr_type == "chrome-ai-translator"){
            tpaChromeAiInit();
            setTimeout(function() {
              $(".chrome_ai_translator_btn").trigger("click");
            }, 1000);
          }
        } else {
          const $modal = $(`#tpa_${tr_type}_model`);
          $modal.find(".tpa-preloader-wrap").hide();
          $modal.find('.modal-body .translator-widget, .notice-info, .is-dismissible').hide();
          $modal.find(".modal-body").show();
          if ($modal.find(".notice-container").length > 0) {
            $modal.find(".notice-container")
              .addClass("notice inline notice-warning")
              .show()
              .html("There is no plain string available for translations.");
          } else {
            $modal.find(".modal-content").append("<div class='notice-container'></div>");
            $modal.find(".notice-container")
              .addClass("notice inline notice-warning")
              .show()
              .html("There is no plain string available for translations.");
          }
          $modal.find(".string_container, .choose-lang, .save_it, .notice-info, .is-dismissible").hide();
        }
      },
    });
  }

  //Save strings translation
  function onSaveClick() {
    var translatedObj = [];
    var updatedataObj = [];
    let totalCharacterCount = 0;
    let totalWordCount = 0;
    let totalTranslationTime = localStorage.getItem("total_translation_time");
    $("#stringTemplate tbody tr").each(function (index) {
        var provider = $(this).closest('#stringTemplate').data("widget");
        // Get the source text from the current tr
        var sourceText = $(this).find("td.source").text().trim();

        // Calculate the word count
        var sourceWordCount = sourceText ? sourceText.trim().split(/\s+/).filter(word => /[^\p{L}\p{N}]/.test(word)).length : 0; // Split by whitespace

        // Calculate the character count
        var sourceCharacterCount = sourceText.length;
        totalCharacterCount += sourceCharacterCount;
        totalWordCount += sourceWordCount;
        var index = $(this).find("td.source").text();
        var source = $(this).find("td.source").text();
        var target = $(this).find("td.target").text();
        var type = $(this).find("td.source").data("group");
        var db_id = $(this).find("td.source").data("db-id");
        var language_code = localStorage.getItem("language_name");
        var default_lang = localStorage.getItem("default_language");
        var date = new Date().toISOString();
        post_id = extradata["post_id"];
        translatedObj.push({
          original: source,
          translated: target,
          data_group: type,
          language_code: language_code,
          id: db_id,
          status: "2",
          default_lang: default_lang,
        });
        updatedataObj = {
            'language_code': language_code,
            'default_lang': default_lang,
            'provider' : provider,
            'timeTaken' : totalTranslationTime,
            'totalWordCount' : totalWordCount,
            'totalCharacterCount' : totalCharacterCount,
            'date' : date,
            'post_id' : post_id
          };
    });
    var data = {
      action: "tpa_save_translations",
      data: JSON.stringify(translatedObj),
      _ajax_nonce: nonce,
    };
    // Close merge translation function
    jQuery.post(ajaxUrl, data, function (response) {
      var updateData = {
        'action': 'tpa_update_translate_data',
        'data': JSON.stringify(updatedataObj),
        '_ajax_nonce': nonce,
      };

      jQuery.post(ajaxUrl, updateData, function (updateResponse) {
        if (updateResponse.success) {
            console.log("Translation metadata saved successfully.");
        } else {
            
        }
      });
      $(".tpa_custom_model").fadeOut("slow");
      location.reload();
  });
  }

  function settingsModel() {
    const icons = {
        yandex: extradata['yt_preview'],
        google: extradata['gt_preview'],
        chrome: extradata['chrome_preview'],
        docs: extradata['document_preview'],
        error: extradata['error_preview']
    };

    const url = 'https://docs.coolplugins.net/docs/';
    const getGTProLink = "https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup_google";
    const getChromeProLink = "https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup_chrome";

    const TPA_IMG = (key) => icons[key];
    const DOC_ICON = `<img src="${TPA_IMG('docs')}" width="20" alt="Docs">`;

    // Get provider states from saved settings (extradata) - default to enabled if not set
    const isYandexEnabled = typeof extradata !== 'undefined' && extradata.provider_yandex_enabled ? extradata.provider_yandex_enabled === '1' : true;
    const isChromeEnabled = typeof extradata !== 'undefined' && extradata.provider_chrome_enabled ? extradata.provider_chrome_enabled === '1' : true;

    const allRows = [
        {
            name: 'Yandex Translate',
            icon: 'yandex',
            info: 'https://translate.yandex.com/',
            btn: `<button id="tpa_yandex_translate_btn" class="tpa-provider-btn translate">Translate</button>`,
            doc: `${url}automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-yandex/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_yandex`,
            enabled: isYandexEnabled
        },
        {
            name: 'Chrome Built-in AI',
            icon: 'chrome',
            info: 'https://developer.chrome.com/docs/ai/translator-api',
            btn: chromeAIStatus === true
                        ? `<button id="tpa_chrome_ai_translate_btn" class="tpa-provider-btn translate" data-translate-engen="chrome-ai-translator">Translate</button>`
                        : (hasChromeConfigurationError()
                            ? `
                                <button class="tpa-chromeai-disabled-message tpa-provider-btn error" onclick="window.open('${getChromeSettingsPageUrl()}', '_blank'); return false;">
                                    <img src="${TPA_IMG('error')}" alt="error" style="height:16px; vertical-align:middle; margin-right:5px;">
                                    Configure
                                </button>
                            `
                            : (isLanguageUnsupported(localStorage.getItem("page_lang"), localStorage.getItem("language_code"))
                                ? `
                                    <button class="tpa-chromeai-disabled-message tpa-provider-btn error error-disabled" disabled>
                                        Not Supported
                                    </button>
                                `
                                : `
                                    <button class="tpa-chromeai-disabled-message tpa-provider-btn error" id="tpa-chrome-btn-placeholder">
                                        <img src="${TPA_IMG('error')}" alt="error" style="height:16px; vertical-align:middle; margin-right:5px;">
                                        Loading...
                                    </button>
                                `)),
            doc: `${url}automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_chrome`,
            enabled: isChromeEnabled
        },
        {
            name: 'Google Translate',
            icon: 'google',
            info: 'https://translate.google.com/',
            btn: `<a href="${getGTProLink}" target="_blank">
                    <button id="tpa_google_translate_btn" class="tpa-provider-btn error">
                        <img src="${TPA_IMG('error')}" width="16" style="vertical-align:middle; margin-right:5px;" alt="Pro"> Buy Pro
                    </button>
                  </a>`,
            doc: `${url}automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-google/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_google`,
            enabled: true // Google Translate is always shown (Pro feature)
        }
    ];

    // Filter rows based on saved provider states - only show enabled providers
    const rows = allRows.filter(row => row.enabled);

    const rowHTML = rows.map(row => `
        <tr>
            <td class="tpa-provider-name">
                <a href="${row.info}" target="_blank">
                    <img src="${TPA_IMG(row.icon)}" class="tpa-provider-icon" alt="${row.name}">
                </a>
                ${row.name}
            </td>
            <td>${row.btn}</td>
            <td>
                <a href="${row.doc}" target="_blank" class="tpa-provider-docs-btn">${DOC_ICON}</a>
            </td>
        </tr>
    `).join('');

    const modelHTML = `
        <div class="tpa-provider-modal" id="tpa-dialog" title="Step 3 - Select Translation Provider" style="display:none;">
            <table class="tpa-provider-table">
                <thead>
                    <tr><th>Name</th><th>Translate</th><th>Docs</th></tr>
                </thead>
                <tbody>${rowHTML}</tbody>
            </table>
        </div>
    `;

    $("body").append(modelHTML);
    
    // After modal is created, check language pack status and update Chrome button if needed
    if (chromeAIStatus !== true && !hasChromeConfigurationError() && !isLanguageUnsupported(localStorage.getItem("page_lang"), localStorage.getItem("language_code"))) {
        (async function() {
            const sourceLanguage = localStorage.getItem("page_lang");
            const targetLanguage = localStorage.getItem("language_code");
            const packRequired = await isLanguagePackRequired(sourceLanguage, targetLanguage);
            const settingsUrl = getChromeSettingsPageUrl();
            const $chromeRow = $('#tpa-dialog').find('tr').filter(function() {
                return $(this).text().indexOf('Chrome Built-in AI') !== -1;
            });
            
            if ($chromeRow.length > 0) {
                const $buttonCell = $chromeRow.find('td').eq(1);
                const icons = {
                    error: extradata['error_preview']
                };
                const TPA_IMG = (key) => icons[key];
                
                if (packRequired) {
                    // Language pack required → "Configure"
                    $buttonCell.html(`
                        <button class="tpa-chromeai-disabled-message tpa-provider-btn error" onclick="window.open('${settingsUrl}', '_blank'); return false;">
                            <img src="${TPA_IMG('error')}" alt="error" style="height:16px; vertical-align:middle; margin-right:5px;">
                            Configure
                        </button>
                    `);
                } else {
                    // Unsupported language - show disabled "Not Supported" button
                    $buttonCell.html(`
                        <button class="tpa-chromeai-disabled-message tpa-provider-btn error error-disabled" disabled>
                            Not Supported
                        </button>
                    `);
                }
            }
        })();
    }
}

  // modal to show strings
  function createStringsModal(widgetType) {
    // Set wrapper, header, and body classes based on widgetType
    let { wrapperCls, headerCls, bodyCls, footerCls } =
      getWidgetClasses(widgetType);
    let modelHTML = `
        <div id="tpa_${widgetType}_model" class="modal tpa_custom_model ${wrapperCls} ${rtlClass}">
                <div class="modal-content">
                    <input type="hidden" id="project_id"> 
                    ${modelHeaderHTML(widgetType, headerCls)}   
                    ${modelBodyHTML(widgetType, bodyCls)}   
                    ${modelFooterHTML(widgetType, footerCls)}   
            </div></div>`;

    $("body").append(modelHTML);
  }

  // Get widget classes based on widgetType
  function getWidgetClasses(widgetType) {
    let wrapperCls = "";
    let headerCls = "";
    let bodyCls = "";
    let footerCls = "";
    switch (widgetType) {
      case "yandex":
        wrapperCls = "yandex-widget-container";
        headerCls = "yandex-widget-header";
        bodyCls = "yandex-widget-body";
        footerCls = "yandex-widget-footer";

        break;
      case "chrome-ai-translator":
        wrapperCls = "chrome-ai-translator chrome-ai-translator-container chrome-ai-translator-modal";
        headerCls = "chrome-ai-translator-header";
        bodyCls = "chrome-ai-translator-body";
        footerCls = "chrome-ai-translator-footer";
        break;
      default:
        // Default class if widgetType doesn't match any case
        wrapperCls = "yandex-widget-container";
        headerCls = "yandex-widget-header";
        bodyCls = "yandex-widget-body";
        footerCls = "yandex-widget-footer";
        break;
    }
    return { wrapperCls, headerCls, bodyCls, footerCls };
  }

  function modelHeaderHTML(widgetType, headerCls) {
    const HTML = `
        <div class="modal-header  ${headerCls}">
                        <span class="close">&times;</span>
                        <h2 class="notranslate">Step 4 - Start Automatic Translation Process</h2>
                        <div class="save_btn_cont">
                <button class="notranslate save_it button button-primary" disabled="true">Merge Translation</button>
                </div>
                <div style="display:none" class="tpa-stats hidden">
                Wahooo! You have saved your valauble time via auto translating 
                 <strong class="totalChars"> </strong> characters  using 
                  <strong> 
                  <a href="https://wordpress.org/support/plugin/automatic-translate-addon-for-translatepress/reviews/#new-post" target="_new">
                  AI Translation For TranslatePress</a>
                </strong>     
              </div>
                    </div>
                    <div class="notice inline notice-info is-dismissible">
                    <div class="tpa_notice_container">
                    Machine translations are not 100% correct. Please verify strings before using on production website.
                    <br/>Also Google Translate provides better machine translations. <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup" target="_blank">Pro version</a> provides unlimited translations via Google Page Translate Widget.
                    </div>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>`;
    return HTML;
  }

  function modelBodyHTML(widgetType, bodyCls) {
    const HTML = `
        <div class="modal-body  ${bodyCls}">
          <div class="tpa-preloader-wrap">
            <div class="ph-item">
                <div class="ph-col-12">
                    <div class="ph-row">
                        <div class="ph-col-6 big"></div>
                        <div class="ph-col-4 big"></div>
                        <div class="ph-col-2 big"></div>
                        <div class="ph-col-4"></div>
                        <div class="ph-col-8"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-12"></div>
                        <div class="ph-col-4"></div>
                        <div class="ph-col-8"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-12"></div>
                        <div class="ph-col-4"></div>
                        <div class="ph-col-8"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-6"></div>
                        <div class="ph-col-12"></div>
                        <div class="ph-col-6 big"></div>
                        <div class="ph-col-4 big"></div>
                        <div class="ph-col-2 big"></div>
                    </div>
                </div>
            </div>
          </div>
          <div class="my_translate_progress" style="display:none;">
            Automatic translation is in progress....<br/>
            It will take a few minutes, enjoy ☕ coffee in this time!<br/><br/>
            Please do not leave this window or browser tab while the translation is in progress...
              <div class="progress-wrapper">
                <div class="progress-container">
                  <div class="progress-bar" id="myProgressBar">
                    <span id="progressText">0%</span>
                </div>
              </div>
            </div>
          </div>
            ${translatorWidget(widgetType)}
            <div class="string_container">
                <table class="scrolldown" id="stringTemplate" data-widget = "${widgetType}">
                    <thead>
                        <th class="notranslate">S.No</th>
                        <th class="notranslate">Source Text</th>
                        <th class="notranslate">Translation</th>
                    </thead>
                    <tbody id="${widgetType}_tpap_string_tbl_body">
                    </tbody>
                </table>
            </div>
            <div class="notice-container"></div>
        </div>`;
    return HTML;
  }

  function modelFooterHTML(widgetType, footerCls) {
    const HTML = ` <div class="modal-footer ${footerCls}">
        <div class="save_btn_cont">
                <button class="notranslate save_it button button-primary" disabled="true">Merge Translation</button>
                </div>
                <div style="display:none" class="tpa-stats">
                Wahooo! You have saved your valauble time via auto translating 
                   <strong class="totalChars"></strong> characters  using 
                    <strong> 
                    <a href="https://wordpress.org/support/plugin/automatic-translate-addon-for-translatepress/reviews/#new-post" target="_new">
                    AI Translation For TranslatePress</a>
                  </strong>     
                </div>
    </div>`;
    return HTML;
  }

  function translatorWidget(widgetType) {
    if (widgetType === "yandex") {
      const widgetPlaceholder = '<div id="ytWidget">..Loading</div>';
      return `
            <div class="translator-widget">
            <h3 class="choose-lang">Choose language <span class="dashicons-before dashicons-translation"></span></h3>
                ${widgetPlaceholder}
            </div>`;
    } else if (widgetType === 'chrome-ai-translator'){
      return `
      <div class="translator-widget ${widgetType}">
        <h3 class="choose-lang">Translate Using Chrome AI Translator <span class="dashicons-before dashicons-translation"></span></h3>
        <div id="chrome_ai_translator_element"></div>
      </div>`
    } else {
      return ""; // Return an empty string for non-yandex widget types
    }
  }

  //This function is used to get language code
  function tpa_language_code(getSelectedlang) {
    var response = getSelectedlang.substring(0, 3);
    var default_code = "";
    var sbstr = getSelectedlang.substring(0, 3);
    if (sbstr == "nb_") {
      default_code = "no";
    } else if (sbstr == "azb") {
      default_code = "azb";
    } else if (sbstr == "arg") {
      default_code = "arg";
    } else {
      default_code = getSelectedlang.substring(0, 2);
    }
    return default_code;
  }



  function initializeClipboard() {
    if (typeof ChromeAiTranslator !== 'undefined' && ChromeAiTranslator.initializeClipboard) {
      ChromeAiTranslator.initializeClipboard();
    }
  }

  // oninit
  $(document).ready(function () {
    initialize();
  });
})(window, jQuery);
