const tpAutoTranslator = (function (window, $) {
  // get plugin configuration object.
  const configData = window.extradata;
  const { ajax_url: ajaxUrl, extra_class: rtlClass, nonce: nonce } = configData;
  var dict_id = new Array();
  var gettxt_id = new Array();
  onLoad();

  function onLoad() {
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var pageLang = tpa_language_code(default_lang);
    localStorage.setItem("page_lang", pageLang);

    // create strings modal
    const widgetType = "yandex";
    createStringsModal(widgetType);
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
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var defaultLang = tpa_language_code(getSelectedlang);
    localStorage.setItem("language_code", defaultLang);
    localStorage.setItem("language_name", getSelectedlang);
    localStorage.setItem("default_language", default_lang);
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
  function enableAutotranslateButton() {
    var default_lang = $("#trp-language-select")
      .find("option:first-child")
      .val();
    var getSelectedlang = $("#trp-language-select").val();

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
    $("#ytWidget").show();
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
            if (type == "yandex") {
              html += `<tr id="${key}"><td>${index}</td><td  class="notranslate source" data-group= "${group[key]}" data-db-id =" ${idss[key]}">${element}</td>`;
            } else {
              if (key > 2500) {
                break;
              }
              html += `<tr id="${key}"><td>${index}</td><td  class="notranslate source" data-group= "${group[key]}" data-db-id =" ${idss[key]}">${element}</td>`;
            }
            if (type == "yandex") {
              html += `<td translate ="yes" class = "target translate">${element}</td></tr>`;
            } else {
              html += `<td class ="target translate"></td></tr>`;
            }
            index++;
            totalTChars += element.length;
          }
        }
      }
      $(".ytstats").each(function () {
        $(this).find(".totalChars").html(totalTChars);
      });
    }

    if (type == "yandex") {
      $("#yandex_string_tbl").html(html);
    }
  }

  // Get the modal id
  var gModal = document.getElementById("#tpa_strings_model");

  // When the user clicks anywhere outside of the modal, close it
  $(window).click(function (event) {
    if (event.target == gModal) {
      gModal.style.display = "none";
    }
  });

  $("#tpa_strings_model")
    .find(".notice-dismiss")
    .on("click", function () {
      $(".notice.inline.notice-info.is-dismissible").fadeOut("slow");
    });
  // Get the <span> element that closes the modal
  $("#tpa_strings_model")
    .find(".close")
    .on("click", function () {
      $("#tpa_strings_model").fadeOut("slow");
      location.reload(true);
    });

  // When the user clicks Yandex button, open the modal

  function onYandexTranslateClick() {
    //Add translate attribute with html tag
    $("html").attr("translate", "no");
    $(".save_it").prop("disabled", true);
    $(".ytstats").css("display", "none");
    var default_code = localStorage.getItem("language_code");
    var arr = [
      "en","pl","af","jv","no","am","ar","az","ba","be","bg","bn","bs","ca","ceb","cs","cy","da","de","el","en","eo","es","et","eu","fa","fi","fr","ga","gd","gl","gu","he","hi","hr","ht","hu","hy","id","is","it","ja","jv","ka","kk","km","kn","ko","ky","la","lb","lo","lt","lv","mg","mhr","mi","mk","ml","mn","mr","mrj","ms","mt","my","ne","nl","no","pa","pap","pl","pt","ro","ru","si","sk","sl","sq","sr","su","sv","sw","ta","te","tg","th","tl","tr","tt","udm","uk","ur","uz","vi","xh","yi","zh",
    ];
    if (arr.includes(default_code)) {
      $("#yandex_string_tbl").html(`<tr>                               
                            <td>
                            <div class="ph-item">
                            <div class="ph-col-12">
                               <div class="ph-row">                                   
                                   <div class="ph-col-6 big"></div>
                                   <div class="ph-col-4  big"></div>
                                   <div class="ph-col-2 big"></div>
                                   <div class="ph-col-4"></div>
                                   <div class="ph-col-8 "></div>
                                   <div class="ph-col-6"></div>
                                   <div class="ph-col-6 "></div>
                                   <div class="ph-col-12"></div>                                 
                               </div>
                            </div>
                            </div>
                            </td>
                            <td>
                            <div class="ph-item">
                            <div class="ph-col-12">
                               <div class="ph-row">
                                   <div class="ph-col-6 big"></div>
                                   <div class="ph-col-4  big"></div>
                                   <div class="ph-col-2 big"></div>
                                   <div class="ph-col-4"></div>
                                   <div class="ph-col-8 "></div>
                                   <div class="ph-col-6"></div>
                                   <div class="ph-col-6 "></div>
                                   <div class="ph-col-12"></div>
                               </div>
                            </div>
                            </div>
                            </td>
                            <td>
                            <div class="ph-item">
                            <div class="ph-col-12">
                               <div class="ph-row">
                                   <div class="ph-col-6 big"></div>
                                   <div class="ph-col-4  big"></div>
                                   <div class="ph-col-2 big"></div>
                                   <div class="ph-col-4"></div>
                                   <div class="ph-col-8 "></div>
                                   <div class="ph-col-6"></div>
                                   <div class="ph-col-6 "></div>
                                   <div class="ph-col-12"></div>
                               </div>
                            </div>
                            </div>
                            </td>
                            </tr>`);
                            addStringsInModal();
      } else {
      $(".notice-container")
        .addClass("notice inline notice-warning")
        .html("Yandex Automatic Translator Does not support this language.");
      $(".string_container, .choose-lang, .save_it, .notice-info, .is-dismissible").hide();
      $("#ytWidget").hide();
    }

    //show yandex pop-up
    var style1 = {};
    $("#tpa_yandex_translate_btn").css(style1);
    $("#tpa-dialog").dialog("close");
    $("#tpa_strings_model").addClass("tpa_custom_model").fadeIn("slow");
  }
  
  function addStringsInModal(){
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
          printStringsInPopup(strings, (type = "yandex"), group, idss);
        } else {
          $("#ytWidget").hide();
          if ($("#tpa_strings_model .notice-container").length > 0) {
            $(".notice-container")
              .addClass("notice inline notice-warning")
              .html("There is no plain string available for translations.");
          } else {
            $(".modal-content").append("<div class='notice-container'></div>");
            $(".notice-container")
              .addClass("notice inline notice-warning")
              .html("There is no plain string available for translations.");
          }
          $(".string_container, .choose-lang, .save_it, .notice-info, .is-dismissible").hide();
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
            'provider' : 'yandex',
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
      $("#tpa_strings_model").fadeOut("slow");
      location.reload();
  });
  }

  function settingsModel() {
    let ytPreviewImg = extradata["yt_preview"];
    let gtPreviewImg = extradata["gt_preview"];
    let chromePreviewImg = extradata["chrome_preview"];
    const documentPreviewImg = extradata['document_preview'];
    const informationPreviewImg = extradata['information_preview'];
    const getGTProLink =
      "https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup_google";
    const getChromeProLink =
      "https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup_chrome";
          
      const modelHTML = `
          <div id="tpa-dialog" title="Step 3 - Select Translation Provider" style="display:none;">
              <div class="tpa-settings" style="opacity:1;">
                  <div class="tpa-translator-row">
                      <div class="tpa-translator tpa-yandex-translator">
                          <div class="tpa-translator-icon">
                              <a href="https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-yandex/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_yandex" target="_blank"><img src="${documentPreviewImg}" alt="Documentation"></a>
                              <a href="https://translate.yandex.com/" target="_blank"><img src="${informationPreviewImg}" alt="Information"></a>
                          </div>
                          <strong class="tpa-heading">Translate Using Yandex Page Translate Widget</strong>
                          <div class="inputGroup">
                               <a href="https://translate.yandex.com/" target="_blank" title="View More"><img class="pro-features-img" src="${ytPreviewImg}" alt="powered by Yandex Translate Widget" title="View More"></a><br/>
                              <button id="tpa_yandex_translate_btn" class="notranslate button button-primary">Yandex Translate</button>
                          </div>
                      </div>   

                      <div class="tpa-translator tpa-pro-translator"> 
                          <div class="tpa-translator-icon">
                              <a href="https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-google/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_google" target="_blank"><img src="${documentPreviewImg}" alt="Documentation"></a>
                              <a href="https://translate.google.com/" target="_blank"><img src="${informationPreviewImg}" alt="Information"></a>
                          </div>
                          <strong class="tpa-heading">Translate Using Google Page Translate Widget</strong>
                          <div class="inputGroup">
                               <a href="https://translate.google.com/" target="_blank"><img class="pro-features-img" src="${gtPreviewImg}" alt="powered by Google Translate Widget" title="View More"></a><br/>
                              <button id="tpa_gtranslate_btn" class="notranslate button button-primary" disabled="disabled">Google Translate</button><span class="proonly-button"><a href="${getGTProLink}" target="_blank" title="Buy Pro">💎 Buy Pro</a></span>
                          </div>
                      </div>                         
                      
                      <div class="tpa-translator tpa-pro-translator">
                          <div class="tpa-translator-icon">
                              <a href="https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=popup_chrome" target="_blank"><img src="${documentPreviewImg}" alt="Documentation"></a>
                              <a href="https://developer.chrome.com/docs/ai/translator-api" target="_blank"><img src="${informationPreviewImg}" alt="Information"></a>
                          </div>
                          <strong class="tpa-heading">Translate Using Chrome Built-in AI</strong>
                          <div class="inputGroup">
                              <a href="https://developer.chrome.com/docs/ai/translator-api" target="_blank" title="View More"><img class="pro-features-img" src="${chromePreviewImg}" width="100" alt="powered by Chrome built-in API"></a><br/>
                              <button id="tpa_chrometranslate_btn" class="button button-primary" disabled="disabled">Chrome AI Translator</button><span class="proonly-button"><a href="${getChromeProLink}" target="_blank" title="Buy Pro">💎 Buy Pro</a></span>
                          </div>
                      </div>
                  </div>
              </div> 
          </div>
      `;
    $("body").append(modelHTML);
  }

  // modal to show strings
  function createStringsModal(widgetType) {
    // Set wrapper, header, and body classes based on widgetType
    let { wrapperCls, headerCls, bodyCls, footerCls } =
      getWidgetClasses("yandex");
    let modelHTML = `
        <div id="tpa_strings_model" class="modal tpa_custom_model ${wrapperCls} ${rtlClass}">
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
                <div style="display:none" class="ytstats hidden">
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
                    <br/>Also Google Translate provides better machine translations than Yandex. <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=popup" target="_blank">Pro version</a> provides unlimited translations via Google Page Translate Widget.
                    </div>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>`;
    return HTML;
  }

  function modelBodyHTML(widgetType, bodyCls) {
    const HTML = `
        <div class="modal-body  ${bodyCls}">
          <div class="my_translate_progress">
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
                <table class="scrolldown" id="stringTemplate">
                    <thead>
                        <th class="notranslate">S.No</th>
                        <th class="notranslate">Source Text</th>
                        <th class="notranslate">Translation</th>
                    </thead>
                    <tbody id="yandex_string_tbl">
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
                <div style="display:none" class="ytstats">
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

  // oninit
  $(document).ready(function () {
    initialize();
  });
})(window, jQuery);
