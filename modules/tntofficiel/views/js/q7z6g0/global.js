/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

(function() {
    window.TNTOfficiel = window.TNTOfficiel || {};
    try {
        var a = new Image, b = document.createElement("canvas").getContext("2d");
        a.onload = function() {
            b.drawImage(a, 0, 0);
            window.TNTOfficiel.APNG = 0 === b.getImageData(0, 0, 1, 1).data[3];
        };
        a.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACGFjVEwAAAABAAAAAcMq2TYAAAANSURBVAiZY2BgYPgPAAEEAQB9ssjfAAAAGmZjVEwAAAAAAAAAAQAAAAEAAAAAAAAAAAD6A+gBAbNU+2sAAAARZmRBVAAAAAEImWNgYGBgAAAABQAB6MzFdgAAAABJRU5ErkJggg==";
    } catch (c) {
    }
})();

function TNTOfficiel_CreatePageSpinner()
{
    // If no spinner created on page.
    if ($('#TNTOfficielLoading').length === 0) {
        // Create spinner to be shown during ajax request.
        $('body').append('\
<div id="TNTOfficielLoading" style="display: none">\
    <img id="loading-image" src="'+TNTOfficiel.link.image+'loader/loader-42'+(window.TNTOfficiel.APNG?'.png':'.gif')+'" alt="Loading..."/>\
</div>');
    }

    return $('#TNTOfficielLoading');
}

function TNTOfficiel_ShowPageSpinner()
{
    window.TNTOfficiel = window.TNTOfficiel || {};
    window.TNTOfficiel.AJAX = window.TNTOfficiel.AJAX > 0 ? ++window.TNTOfficiel.AJAX : 1;

    if (window.TNTOfficiel.AJAX_HTO) {
        window.clearTimeout(window.TNTOfficiel.AJAX_HTO);
        window.TNTOfficiel.AJAX_HTO = null;
    }
    if (window.TNTOfficiel.AJAX > 0) {
        TNTOfficiel_CreatePageSpinner();
        $('#TNTOfficielLoading').show();
        window.TNTOfficiel.AJAX_HTO = window.setTimeout(function(){
            TNTOfficiel_HidePageSpinner();
        }, 8 * 1000);

    }
}

function TNTOfficiel_HidePageSpinner()
{
    window.TNTOfficiel = window.TNTOfficiel || {};
    window.TNTOfficiel.AJAX = window.TNTOfficiel.AJAX > 0 ? --window.TNTOfficiel.AJAX : 0;

    if (window.TNTOfficiel.AJAX === 0) {
        if (window.TNTOfficiel.AJAX_HTO) {
            window.clearTimeout(window.TNTOfficiel.AJAX_HTO);
            window.TNTOfficiel.AJAX_HTO = null;
        }
        $('#TNTOfficielLoading').hide();
    }
}

function TNTOfficiel_AJAX($objArgAJAXParameters)
{
    // Global jQuery AJAX event, excepted for request with option "global":false.
    $objArgAJAXParameters["global"] = false;

    TNTOfficiel_ShowPageSpinner();

    var objJqXHR = $.ajax($objArgAJAXParameters);
    objJqXHR
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        // console.error(objJqXHR.status + ' ' + objJqXHR.statusText);
        alert($('<span>'+TNTOfficiel.translate.errorConnection+'</span>').text());
    })
    .always(function () {
        TNTOfficiel_HidePageSpinner();
    });

    return objJqXHR;
}


function TNTOfficiel_AdminAlert(arrArgAlert, strTitle)
{
    var objAlertType = {
        "success": 'success',
        "warning": 'warning',
        "error": 'danger'
    };

    if (strTitle == null) {
        strTitle = TNTOfficiel.module.title;
    }

    for (var strAlertType in objAlertType) {
        var strAlertClass = objAlertType[strAlertType];

        if (arrArgAlert
            && arrArgAlert[strAlertType]
            && arrArgAlert[strAlertType].length > 0
        ) {
            var $elmtAlert = $('\
                <div class="bootstrap">\
                    <div class="alert alert-' + strAlertClass + '" >\
                        <button type="button" class="close" data-dismiss="alert">×</button>\
                        <h4>' + strTitle + '</h4>\
                        <ul class="list-unstyled"></ul>\
                    </div>\
                </div>');

            $.each(arrArgAlert[strAlertType], function (index, value) {
                if (typeof value === 'string') {
                    // trim.
                    value = value.replace(/^\s+|\s+$/gi, '');
                    // If is a translation ID.
                    if (TNTOfficiel.translate[value]) {
                        value = $('<span>'+TNTOfficiel.translate[value]+'</span>').text();
                    }
                    // If is a translation ID (BO).
                    else if (TNTOfficiel.translate.back[value]) {
                        value = $('<span>'+TNTOfficiel.translate.back[value]+'</span>').text();
                    }
                    if (!/[.!?]$/gi.test(value)) {
                        value = value+'.';
                    }
                    $elmtAlert.find('ul').append($('<li></li>').append(document.createTextNode(value)));
                }
            });

            if ($elmtAlert.find('ul li').length > 0) {
                $('#content').prepend($elmtAlert);
                // Force to show alert on top of page
                // On load after redirection.
                $(window).on('load', function () {window.setTimeout(function(){$(window).scrollTop(0);}, 1);});
                // Or after a page was loaded.
                $(window).scrollTop(0);
            }
        }
    }
}


// On DOM Ready.
$(function () {
    /*
     Display error.
     */
    if (window.TNTOfficiel && window.TNTOfficiel.alert) {
        TNTOfficiel_AdminAlert(window.TNTOfficiel.alert);
    }

});

