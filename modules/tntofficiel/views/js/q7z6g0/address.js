/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
window.document.addEventListener('DOMContentLoaded', function () {

    /* Direct */

    // If page is for this controller.
    switch (window.TNTOfficiel.link.controller) {
        case 'ordercontroller':
            // OrderController - Step 1 : 03. Address.
            // On click submit button for next step.
            jQuery(':submit[name="processAddress"]')
                .on('click', TNTOfficiel_AddressDeliveryCityDirectValidate);
        case 'orderopccontroller':
            // If carrier list is displayed.
            if (window.TNTOfficiel.cart.isCarrierListDisplay) {
                // Check current delivery address and reload page at validation.
                TNTOfficiel_AddressDeliveryCityDirectValidate();
            }
            break;
        case 'adminorderscontroller':
            if (TNTOfficiel.order.isDirectAddressCheck) {
                TNTOfficiel_AddressDeliveryCityDirectValidate();
            }
            break;
        default:
            break;
    }

    /* Form */

    // If form field postcode, city and id_country exist on page.
    if (jQuery(window.strTNTOfficieljQSelectorInputPostCode).length === 1
        && jQuery(window.strTNTOfficieljQSelectorInputCity).length === 1
        && jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).length === 1
    ) {
        // If page is for this controller.
        switch (window.TNTOfficiel.link.controller) {
            case 'addresscontroller':
            case 'authcontroller':
            case 'orderopccontroller':

                // On address form click on submit button.
                jQuery(window.strTNTOfficieljQSelectorInputCity).parents('form').find(':submit')
                    .not('#opc_guestCheckout, #opc_createAccount')
                    .filter('#submitAccount, #submitGuestAccount').on('click', TNTOfficiel_onAddressSubmitValidate);
                // On address form submit.
                jQuery(window.strTNTOfficieljQSelectorInputCity).parents('form').on('submit', TNTOfficiel_onAddressSubmitValidate);

                // Ensure the 'data-validate' attribute is set for validation on some fields using validate_field().
                jQuery(window.strTNTOfficieljQSelectorInputPostCode).attr('data-validate', 'isPostCode').addClass('is_required validate');
                jQuery(window.strTNTOfficieljQSelectorInputCity).attr('data-validate', 'isCityName').addClass('is_required validate');
                break;
            case 'adminaddressescontroller':
                jQuery(window.document)
                    .on('change.'+window.TNTOfficiel.module.name, window.strTNTOfficieljQSelectorInputPostCode, function() {
                        validate_isPostCode(this.value);
                    })
                    .on('change.'+window.TNTOfficiel.module.name, window.strTNTOfficieljQSelectorInputCity, function() {
                        validate_isCityName(this.value);
                    });
            default:
                break;
        }
    }

});

/**
 * If required, display fancybox to set the city and prevent address form submit.
 */
function TNTOfficiel_onAddressSubmitValidate(objEvent)
{
    // if country is France.
    var strInputCity = jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).val();

    if (strInputCity
        && window.TNTOfficiel.country.list[strInputCity]
        && window.TNTOfficiel.country.list[strInputCity].iso_code === 'FR'
    ) {
        var boolIsPostCodeValid = validate_isTNTOfficielPostCode(true);
        var boolIsCityFromPostCodeValid = validate_isTNTOfficielCityName(false);

        if (!boolIsPostCodeValid) {
            jQuery(window.strTNTOfficieljQSelectorInputPostCode).focus();
            jQuery(window.strTNTOfficieljQSelectorInputPostCode).parent().addClass('form-error').removeClass('form-ok');
        }
        // if city is not correct from postcode.
        if (!boolIsCityFromPostCodeValid) {
            // Prevent form submit and further action.
            objEvent.stopImmediatePropagation();
            objEvent.preventDefault();
            return false;
        }
    }

    return true;
}

/**
 * If required, display directly fancybox to set the city (not using address form).
 */
function TNTOfficiel_AddressDeliveryCityDirectValidate(objEvent)
{
    var objLink = window.TNTOfficiel.link.front;
    var objData = {};
    if (window.TNTOfficiel.link.back) {
        objLink = window.TNTOfficiel.link.back;
        objData['id_order'] = window.TNTOfficiel.order.intOrderID;
    }

    var objJqXHR = TNTOfficiel_AJAX({
        "url": objLink.module.getAddressCities,
        "method": 'POST',
        "dataType": 'json',
        "data": objData,
        "async": false //!objEvent
    });

    objJqXHR
    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
        // If city is valid, nothing to do (including non FR countries).
        if (objResponseJSON.valid === true) {
            return;
        }

        // If event exist.
        if (objEvent) {
            // Prevent form submit and further action.
            objEvent.stopImmediatePropagation();
            objEvent.preventDefault();
        }

        // Right postcode, there is cities.
        if (jQuery.isArray(objResponseJSON.cities) && objResponseJSON.cities.length > 0) {
            TNTOfficiel_displayFancyBoxSelectCity(
                objResponseJSON.postcode,
                objResponseJSON.cities,
                function (strArgCitySelected) {
                    objData['city'] = strArgCitySelected;
                    // Save.
                    var objJqXHR = TNTOfficiel_AJAX({
                        "url": objLink.module.updateAddressDelivery,
                        "method": 'POST',
                        "dataType": 'json',
                        "data": objData,
                        "async": false
                    });

                    objJqXHR
                    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                        if (objResponseJSON.result == true){
                            // Success.
                        }
                    })
                    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
                        window.location.reload();
                    })
                    .always(function () {
                        // Close the FancyBox.
                        jQuery.fancybox.close();

                        // If not an event to prevent, then reload page.
                        if (!objEvent) {
                            window.location.reload();
                        }
                    });
                }
            );
        }
        // Wrong postcode, there is no city.
        else
        {
            if (!!jQuery.prototype.fancybox) {
                jQuery.fancybox.open([
                    {
                        "type": 'inline',
                        //"autoScale": true,
                        "autoDimensions": true,
                        //"centerOnScroll": true,
                        //"maxWidth": 1280,
                        //"maxHeight": 768,
                        //"fitToView": false,
                        //"width": '100%',
                        //"height": '100%',
                        //"autoSize": false,
                        //"closeClick": false,
                        //"openEffect": 'none',
                        //"closeEffect": 'none',

                        "transitionIn": 'elastic',
                        "transitionOut": 'elastic',
                        "speedIn": 600,
                        "speedOut": 200,
                        "overlayShow": false,

                        "content": '\
<div id="city_postcode_error">\
<h1 class="page-subheading">'+window.TNTOfficiel.translate.validateDeliveryAddress+'</h1>\
<p class="alert-danger">'+window.TNTOfficiel.translate.unknownPostalCode+'.</p>\
<p>'+window.TNTOfficiel.translate.validatePostalCodeDeliveryAddress+'</p>\
</div>\
',
                        "afterShow": function () {},
                        "helpers": {
                            "overlay": {
                                "locked": true,
                                // prevents closing when clicking OUTSIDE fancybox.
                                "closeClick": false
                            }
                        }
                    }
                ], {
                    "padding": 20
                });
            }
        }

    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        window.location.reload();
    });
}



/**
 * Form validate function using /js/validate.js
 */
window.strTNTOfficieljQSelectorInputPostCode = 'input#postcode';
window.strTNTOfficieljQSelectorInputCity = 'input#city';
window.strTNTOfficieljQSelectorSelectCountrySelected = '#id_country option:selected';

var
    // Save function as a new reference.
    validate_isPostCodeRef = validate_isPostCode || function(){},
    validate_isCityNameRef = validate_isCityName || function(){},
    // Redefine original function using itself.
    validate_isPostCode = function validate_isPostCode(s, pattern) {
        var strInputCity = jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).val();

        // if country is France and form field postcode, city and id_country exist on page.
        if (
            strInputCity
            && window.TNTOfficiel.country.list[strInputCity]
            && window.TNTOfficiel.country.list[strInputCity].iso_code === 'FR'
            && jQuery(window.strTNTOfficieljQSelectorInputPostCode).length === 1
            && jQuery(window.strTNTOfficieljQSelectorInputPostCode).val() === s
            && jQuery(window.strTNTOfficieljQSelectorInputCity).length === 1
            && jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).length === 1
        ) {
            // Format postcode name field.
            s = s.replace(/\s*-\s*/gi, ' ').replace(/^\s+|\s+$/gi, '').toUpperCase();
            jQuery(window.strTNTOfficieljQSelectorInputPostCode).val(s);

            return validate_isTNTOfficielPostCode(false);
        }

        return validate_isPostCodeRef(s, pattern);
    },
    // Redefine original function using itself.
    validate_isCityName = function validate_isCityName(s) {
        var strInputCity = jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).val();

        // if country is France form field postcode, city and id_country exist on page.
        if (
            strInputCity
            && window.TNTOfficiel.country.list[strInputCity]
            && window.TNTOfficiel.country.list[strInputCity].iso_code === 'FR'
            && jQuery(window.strTNTOfficieljQSelectorInputPostCode).length === 1
            && jQuery(window.strTNTOfficieljQSelectorInputCity).length === 1
            && jQuery(window.strTNTOfficieljQSelectorInputCity).val() === s
            && jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).length === 1
        ) {
            // Format city name field.
            s = s.replace(/\s*-\s*/gi, ' ').replace(/^\s+|\s+$/gi, '').toUpperCase();
            jQuery(window.strTNTOfficieljQSelectorInputCity).val(s);

            return validate_isTNTOfficielCityName(false);
        }

        return validate_isCityNameRef(s);
    };

/**
 * AJAX request +cache to get cities using postcode and country.
 */
function TNTOfficiel_checkAddressPostcodeCity(intCountryID, strInputPostCode, strInputCity)
{
    var objCheckPostcodeCityResponse = null;

    var objLink = window.TNTOfficiel.link.front;
    if (window.TNTOfficiel.link.back) {
        objLink = window.TNTOfficiel.link.back;
    }

    // if country is France.
    if (intCountryID
        && window.TNTOfficiel.country.list[intCountryID]
        && window.TNTOfficiel.country.list[intCountryID].iso_code === 'FR'
    ) {
        // Do not perform a check if the postcode or the city is not entered.
        if (strInputPostCode.length === 5) {
            var strCacheKey = intCountryID + '\n' + strInputPostCode;

            TNTOfficiel_checkAddressPostcodeCity.cache = TNTOfficiel_checkAddressPostcodeCity.cache || {};

            // Retreive from cache if available (deep copy).
            if (TNTOfficiel_checkAddressPostcodeCity.cache[strCacheKey] != null) {
                objCheckPostcodeCityResponse =
                    jQuery.extend(true, {}, TNTOfficiel_checkAddressPostcodeCity.cache[strCacheKey]);
            }

            if (objCheckPostcodeCityResponse == null) {
                // Get the cities list matching the postcode.
                var objJqXHR = TNTOfficiel_AJAX({
                    "url": objLink.module.checkAddressPostcodeCity,
                    "method": 'POST',
                    "dataType": 'json',
                    "data": {
                        "countryId": intCountryID,
                        "postcode": strInputPostCode,
                        "city": ''
                    },
                    "async": false
                });

                objJqXHR
                .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                    // handle the response from the ajax request.
                    objCheckPostcodeCityResponse = objResponseJSON;
                })
                .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
                    window.location.reload();
                });

                // Store to cache if response available (deep copy).
                if (objCheckPostcodeCityResponse != null) {
                    TNTOfficiel_checkAddressPostcodeCity.cache[strCacheKey] =
                        jQuery.extend(true, {}, objCheckPostcodeCityResponse);
                }
            }

            // If selected city match one from list.
            if (jQuery.isArray(objCheckPostcodeCityResponse.cities)
                && jQuery.inArray(strInputCity, objCheckPostcodeCityResponse.cities) >= 0
            ) {
                // Flag matched.
                objCheckPostcodeCityResponse.cities = true;
            }
        }
    }

    return objCheckPostcodeCityResponse;
}

/**
 * Postcode form field validator.
 */
function validate_isTNTOfficielPostCode(boolArgNoSelect)
{
    var intCountryID = jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).val(),
        strInputPostCode = jQuery(window.strTNTOfficieljQSelectorInputPostCode).val(),
        strInputCity = jQuery(window.strTNTOfficieljQSelectorInputCity).val()
        ;

    var objCheckPostcodeCityResponse = TNTOfficiel_checkAddressPostcodeCity(
        intCountryID,
        strInputPostCode,
        strInputCity
    );

    // If PostCode valid, but choice for cities.
    if (boolArgNoSelect !== true
        && objCheckPostcodeCityResponse != null
        && objCheckPostcodeCityResponse.postcode === true
        && jQuery.isArray(objCheckPostcodeCityResponse.cities)
        && objCheckPostcodeCityResponse.cities.length > 0
    ) {
        // Display the FancyBox to select a city.
        TNTOfficiel_displayFancyBoxSelectCity(
            strInputPostCode,
            objCheckPostcodeCityResponse.cities,
            function (strArgCitySelected)
            {
                // Actions to perform when a city is selected in the fancybox.
                // put the value in the city field
                jQuery(window.strTNTOfficieljQSelectorInputCity).val(strArgCitySelected).focus();
                // Close the fancybox.
                jQuery.fancybox.close();

                // enable the save button.
                // jQuery("#submitAddress").removeClass("disabled");
            }
        );
    }

    // Postcode is valid if no response Error
    return objCheckPostcodeCityResponse != null
        // and if PostCode/City check not required.
        && (objCheckPostcodeCityResponse.required !== true
            // or if PostCode is valid.
            || objCheckPostcodeCityResponse.postcode === true
        );
}

/**
 * City form field validator.
 */
function validate_isTNTOfficielCityName(boolArgNoSelect)
{
    var intCountryID = jQuery(window.strTNTOfficieljQSelectorSelectCountrySelected).val(),
        strInputPostCode = jQuery(window.strTNTOfficieljQSelectorInputPostCode).val(),
        strInputCity = jQuery(window.strTNTOfficieljQSelectorInputCity).val()
        ;

    var objCheckPostcodeCityResponse = TNTOfficiel_checkAddressPostcodeCity(
        intCountryID,
        strInputPostCode,
        strInputCity
    );

    // If PostCode valid, but choice for cities.
    if (boolArgNoSelect !== true
        && objCheckPostcodeCityResponse != null
        && objCheckPostcodeCityResponse.postcode === true
        && jQuery.isArray(objCheckPostcodeCityResponse.cities)
        && objCheckPostcodeCityResponse.cities.length > 0
    ) {
        // Display the FancyBox to select a city.
        TNTOfficiel_displayFancyBoxSelectCity(
            strInputPostCode,
            objCheckPostcodeCityResponse.cities,
            function (strArgCitySelected)
            {
                // Actions to perform when a city is selected in the fancybox.
                // put the value in the city field
                jQuery(window.strTNTOfficieljQSelectorInputCity).val(strArgCitySelected).focus();
                // Close the fancybox.
                jQuery.fancybox.close();

                // enable the save button.
                // jQuery("#submitAddress").removeClass("disabled");
            }
        );
    }

    // City is valid if no response Error
    return objCheckPostcodeCityResponse != null
        // and if PostCode/City check not required.
        && (objCheckPostcodeCityResponse.required !== true
            // or if City is valid.
            || objCheckPostcodeCityResponse.cities === true
        );
}

/**
 * Display the fancybox to choose a city from the postcode.
 */
function TNTOfficiel_displayFancyBoxSelectCity(strArgPostCode, arrArgCities, onSubmitCity)
{
    // Right postcode, there is cities to select.
    if (jQuery.isArray(arrArgCities) && arrArgCities.length > 0) {
        // Generate the options to be put in the city select field.
        var strHTMLOptions = '';
        jQuery.each(arrArgCities, function (index, city) {
            strHTMLOptions += "<option value='" + city + "'>" + city + "</option>";
        });

        if (!!jQuery.prototype.fancybox) {

            //jQuery(window.strTNTOfficieljQSelectorInputPostCode).prop('disabled', true);
            //jQuery(window.strTNTOfficieljQSelectorInputCity).prop('disabled', true);

            jQuery.fancybox.open([
                {
                    "type": 'inline',
                    //"autoScale": true,
                    "autoDimensions": true,
                    //"centerOnScroll": true,
                    //"maxWidth": 256,
                    //"maxHeight": 768,
                    //"fitToView": false,
                    //"width": '100%',
                    //"height": '100%',
                    //"autoSize": false,
                    //"closeClick": false,
                    //"openEffect": 'none',
                    //"closeEffect": 'none',

                    "transitionIn": 'elastic',
                    "transitionOut": 'elastic',
                    "speedIn": 600,
                    "speedOut": 200,
                    "overlayShow": false,

                    "content": '\
<div id="city_helper" class="bootstrap">\
    <h1 class="page-subheading">'+window.TNTOfficiel.translate.validateDeliveryAddress+'</h1>\
    <p>'/*+window.TNTOfficiel.translate.unrecognizedCity+'.<br />'*/+window.TNTOfficiel.translate.selectCityDeliveryAddress+'</p>\
    <div class="form-group">\
        <label for="postcode">'+window.TNTOfficiel.translate.postalCode+'</label>\
        <input class="form-control" type="text" id="helper_postcode" name="helper_postcode" value="' + strArgPostCode + '" disabled="disabled" />\
    </div>\
    <div class="form-group">\
        <label for="helper_city">'+window.TNTOfficiel.translate.city+'</label>\
        <select id="helper_city" name="helper_city" class="form-control">' + strHTMLOptions + '</select>\
    </div>\
    <br />\
    <p class="text-right">\
        <button id="validateCity" class="btn button button-tntofficiel-small">\
            <span>'+window.TNTOfficiel.translate.validate+' <i class="icon-chevron-right right"></i></span>\
        </button>\
    </p>\
</div>\
',
                    "afterShow": function () {
                        // Focus on city select.
                        jQuery("#city_helper #helper_city").focus();

                        //jQuery(window.strTNTOfficieljQSelectorInputPostCode).removeProp('disabled');
                        //jQuery(window.strTNTOfficieljQSelectorInputCity).removeProp('disabled');

                        // When a city is selected from the FancyBox pop-in.
                        jQuery("#city_helper #validateCity").on('click', function (objEvent) {
                            var strCitySelected = jQuery("#city_helper #helper_city").val();

                            if (jQuery.isFunction(onSubmitCity)) {
                                onSubmitCity(strCitySelected);
                            }
                        });
                    },
                    "helpers": {
                        "overlay": {
                            "locked": true,
                            "closeClick": false // prevents closing when clicking OUTSIDE fancybox.
                        }
                    }
                }
            ], {
                "padding": 20
            });
        }
    }

    return;
}
