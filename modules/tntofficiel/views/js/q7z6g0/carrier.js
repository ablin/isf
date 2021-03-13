/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// Default is required.
var strTNTOfficieljQSelectorInputRadioTNT = strTNTOfficieljQSelectorInputRadioTNT || '';

/**
 * Update carrier list.
 * Used after a delivery point selection on FO.
 */
function TNTOfficiel_updateCarrier()
{
    //
    var $elmtTNTOfficielInputRadioTNTSelected = $(strTNTOfficieljQSelectorInputRadioTNT).filter(':checked'),
        strCarrierList = null,
        intAddressID = null;

    if ($elmtTNTOfficielInputRadioTNTSelected.length === 1) {
        strCarrierList = $elmtTNTOfficielInputRadioTNTSelected.data('key');
        // Fallback.
        if (!strCarrierList) {
            strCarrierList = $elmtTNTOfficielInputRadioTNTSelected.val();
        }

        intAddressID = $elmtTNTOfficielInputRadioTNTSelected.data('id_address');
        // Fallback.
        if (!intAddressID) {
            var strName = $elmtTNTOfficielInputRadioTNTSelected.attr('name');
            if ($.type(strName) === 'string') {
                intAddressID = strName.replace(/^[^[]+\[([0-9]+)\]$/gi, '$1');
            }
        }
        intAddressID = parseInt(intAddressID);
    }

    // !window.TNTOfficiel.cart.isOPC
    if (window.orderProcess != null
        && orderProcess == 'order'
        && strCarrierList && intAddressID
    ) {
        // 5 Step.
        updateExtraCarrier(strCarrierList, intAddressID);
    } else if (window.orderProcess != null
        && orderProcess == 'order-opc'
        && typeof updateCarrierSelectionAndGift !== 'undefined'
    ) {
        // OPC.
        updateCarrierSelectionAndGift();
    } else {
        // Fallback.
        window.location.reload();
    }
}


function TNTOfficiel_isExtraDataValidated()
{
    return ($('#extra_address_data').length == 0 || (!! $('#extra_address_data').data('validated')));
}

function TNTOfficiel_setExtraDataValidated(boolArgAllow)
{
    // Flag validated.
    $('#extra_address_data').data(
        'validated',
        boolArgAllow == null ? TNTOfficiel_isExtraDataValidated() : !!boolArgAllow
    );

    // Implies update.
    TNTOfficiel_updatePaymentDisplay();
}


function TNTOfficiel_isDeliveryPointValidated()
{
    var $elmtTNTOfficielInputRadioTNTSelected = $(strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
    var strTNTCheckedCarrierID, strTNTCheckedCarrierCode;

    if ($elmtTNTOfficielInputRadioTNTSelected.length) {
        strTNTCheckedCarrierID = $elmtTNTOfficielInputRadioTNTSelected.val().split(',')[0];
        strTNTCheckedCarrierCode = window.TNTOfficiel.carrier[strTNTCheckedCarrierID];
    }

    var boolHasRepoAddressSelected = $elmtTNTOfficielInputRadioTNTSelected.closest('table').find('.shipping-method-info').length > 0;
    var boolIsRepoTypeSelected = (
        strpos(strTNTCheckedCarrierCode, 'DROPOFFPOINT')
        || strpos(strTNTCheckedCarrierCode, 'DEPOT')
    );

    // If the selected TNT is a delivery point with a selected address.
    // or not a delivery point and no address is selected.
    return (
        (boolIsRepoTypeSelected && boolHasRepoAddressSelected)
        || (!boolIsRepoTypeSelected && !boolHasRepoAddressSelected)
    );
}

/**
 * Get current payment ready state.
 * @returns {boolean}
 * @constructor
 */
function TNTOfficiel_isPaymentReady()
{
    var arrError = [];

    // Result from async AJAX request.
    var objResult = null;
    var $elmtTNTOfficielInputRadioTNTSelected = $(strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
    var strTNTCheckedCarrierID, strTNTCheckedCarrierCode;

    if ($elmtTNTOfficielInputRadioTNTSelected.length) {
        strTNTCheckedCarrierID = $elmtTNTOfficielInputRadioTNTSelected.val().split(',')[0];
        strTNTCheckedCarrierCode = window.TNTOfficiel.carrier[strTNTCheckedCarrierID];
    }

    var objJqXHR = TNTOfficiel_AJAX({
        "url": window.TNTOfficiel.link.front.module.checkPaymentReady,
        "method": 'POST',
        "dataType": 'json',
        "data": {
            productCode: strTNTCheckedCarrierCode // unused
        },
        "async": false
    });

    objJqXHR
    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
        objResult = objResponseJSON;
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        //console.error(objJqXHR.status + ' ' + objJqXHR.statusText);
    });

    // If no result or has error.
    if (!objResult || objResult.error != null) {
        // Display alert message.
        if (objResult && objResult.error != null) {
            arrError.push(objResult.error);
        } else {
            arrError.push('errorTechnical');
        }

        return arrError;
    }

    // If the selected carrier (core) is not TNT, we don't handle it.
    if (objResult['carrier'] !== window.TNTOfficiel.module.name) {
        return arrError;
    }
/*
    if (!TNTOfficiel_isDeliveryPointValidated()) {
        arrError.push('errorNoDeliveryPointSelected');
    }
*/
    // If extra data form was not filled and validated.
    if (!TNTOfficiel_isExtraDataValidated()) {
        arrError.push('validateAdditionalCarrierInfo');
    }

    return arrError;
}

/**
 * Allow payment by showing or hiding payments options.
 */
function TNTOfficiel_updatePaymentDisplay()
{
    // OPC / 5 Step.
    var $elmtInsertBefore = window.TNTOfficiel.cart.isOPC ? $('#HOOK_TOP_PAYMENT') : $('.cart_navigation');

    $(':submit[name="processCarrier"]').removeClass('disabled');
    $('#TNTOfficielHidePayment').remove();

    // if extra data form to fill exist and is validated.
    var arrPaymentReadyError = TNTOfficiel_isPaymentReady();
    if (arrPaymentReadyError.length > 0) {
        var strError = (TNTOfficiel.translate[arrPaymentReadyError[0]] || arrPaymentReadyError[0]);

        $(':submit[name="processCarrier"]').addClass('disabled');
        $elmtInsertBefore.before('\
<div id="TNTOfficielHidePayment">\
<!--div class="alert alert-danger">'+TNTOfficiel.module.title+': '+strError+'</div-->\
<p class="required-warning">'+TNTOfficiel.module.title+': '+strError+'</p>\
<style type="text/css">\
\
    #HOOK_PAYMENT, #HOOK_PAYMENT * {\
        display: none !important;\
    }\
\
</style>\
</div>');
    }
}


// On DOM Ready.
$(function () {

    // Click on address displayed in delivery option from DROPOFFPOINT or DEPOT.
    $(document).on('click', [
        'tr td .shipping-method-info',
        'tr td .shipping-method-info-select',
        '#tntofficielFallbackSelectDesc .shipping-method-info',
        '#tntofficielFallbackSelectDesc .shipping-method-info-select'
    ].join(','),
    function (objEvent) {
        //var $elmtInputRadioVirtTNTMatch = $(this).parents('tr').find('input:radio.delivery_option_radio');
        var $elmtInputRadioVirtTNTMatch = $(strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');
        var strTNTClickedCarrierID = $elmtInputRadioVirtTNTMatch.val().split(',')[0];
        var strTNTClickedCarrierCode = window.TNTOfficiel.carrier[strTNTClickedCarrierID];

        TNTOfficiel_XHRBoxDeliveryPoints(strTNTClickedCarrierCode);

        objEvent.stopImmediatePropagation();
        objEvent.preventDefault();
        return false;
    });


    /*
     * Payment Choice.
     */

    if (window.TNTOfficiel.cart.isOPC) {
        // On payment mode choice with OPC.
        // contextmenu mouseup mousedown may be used to prevent middle click, but can break link redirection using FF+ext.
        $(document).on('click', '#opc_payment_methods a', TNTOfficiel_XHRcheckPaymentReady);
        // Free cart button.
        var confirmFreeOrderFunction = window.confirmFreeOrder;
        window.confirmFreeOrder = function () {
            if (TNTOfficiel_XHRcheckPaymentReady() !== false) {
                confirmFreeOrderFunction();
            }
        }
    } else {
        // On submit before payment choice.
        $('#form').on('submit', TNTOfficiel_XHRcheckPaymentReady);
    }

});


/*
 * AJAX after a click on payment link (OPC) or form submit button (5 step).
 * Do check to prevent payment action.
 */
function TNTOfficiel_XHRcheckPaymentReady(objEvent)
{
    // If payment is ready (JS check).
    var arrPaymentReadyError = TNTOfficiel_isPaymentReady();
    if (arrPaymentReadyError.length > 0) {
        var strError = (TNTOfficiel.translate[arrPaymentReadyError[0]] || arrPaymentReadyError[0]);
        alert($('<span>'+strError+'</span>').text());
        // Force to stay on current page.
        window.location.reload();
        // Stop form submit.
        objEvent.preventDefault();

        return false;
    }
}
