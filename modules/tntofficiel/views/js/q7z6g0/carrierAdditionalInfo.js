/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
$(function () {

    // If not an order with TNT carrier.
    if (window.TNTOfficiel.link.back && !TNTOfficiel.order.isTNT) {
        return;
    }

    // Clear on each display.
    window.clearInterval(window.TNTOfficiel_hdlInterval);
    window.TNTOfficiel_hdlInterval = window.setInterval(function(){
        // Get state.
        var boolTextChanged = false;
        $('#customer_email, #customer_mobile, #address_building, #address_accesscode, #address_floor')
        .each(function (intIndex, element) {
            if (element.getAttribute('value') !== $(element).val()) {
                boolTextChanged = true;
            }
        });

        // If form validated or modified.
        if ((!window.TNTOfficiel.link.back&&!TNTOfficiel_isExtraDataValidated()) || boolTextChanged) {
            // Display Validate button.
            $('#submitAddressExtraData').fadeIn(125);
        } else {
            // Hide Validate button.
            $('#submitAddressExtraData').fadeOut(65);
        }
    }, 125);

    // Perform an AJAX request when the extra address data form is submitted.
    // Submit the address extra data form in AJAX.
    $(document).on('click.'+TNTOfficiel.module.name, '#submitAddressExtraData', function () {

        var objLink = window.TNTOfficiel.link.front;
        var objData = {
            'customer_email': $('#customer_email').val(),
            'customer_mobile': $('#customer_mobile').val(),
            'address_building': $('#address_building').val(),
            'address_accesscode': $('#address_accesscode').val(),
            'address_floor': $('#address_floor').val()
        };

        if (window.TNTOfficiel.link.back) {
            objLink = window.TNTOfficiel.link.back;
            objData['id_order'] = TNTOfficiel.order.intOrderID;
        }

        var objJqXHR = TNTOfficiel_AJAX({
            "url": objLink.module.storeDeliveryInfo,
            "method": 'POST',
            "data": objData,
            "dataType": 'json',
            "cache": false
        });

        objJqXHR
        .done(function (objJSONResponse, strTextStatus, objJqXHR) {

            // Update HTML data for AJAX for modified state.
            $('#customer_email, #customer_mobile, #address_building, #address_accesscode, #address_floor')
            .each(function (intIndex, element) {
                element.setAttribute('value', $(element).val());
            });

            $('#extra_address_data .alert-danger').remove();
            $.each(objJSONResponse.fields, function (strFieldName, strFieldValue) {

                // If modification during request send/receive.
                if (objData[strFieldName] !== $('#extra_address_data #'+strFieldName).val()) {
                    return;
                }
                // If returned value diff from original value.
                if (objData[strFieldName] !== strFieldValue) {
                    // Update the field.
                    $('#extra_address_data #'+strFieldName).val(strFieldValue);
                }
                var strErrorMessage = objJSONResponse.errors[strFieldName];
                if (strErrorMessage) {
                    $('#extra_address_data .alert-danger.error-'+strFieldName).remove();
                    $('#extra_address_data .info-'+strFieldName).hide();
                    $('#extra_address_data #'+strFieldName).after(
                        $('<small class="form-text text-muted alert-danger error-'+strFieldName+'"></div>')
                            .html(strErrorMessage+(window.TNTOfficiel.link.back?'':'.'))
                    );
                    $('#extra_address_data #'+strFieldName).parent('.form-group').removeClass('form-ok').addClass('form-error');
                } else {
                    $('#extra_address_data .info-'+strFieldName).show();
                    $('#extra_address_data #'+strFieldName).parent('.form-group').removeClass('form-error').addClass('form-ok');
                }
            });

            // Flag validated. Default.
            var boolExtraDataValidated = false;

            // If there is no error.
            if (objJSONResponse.length === 0) {
                // If data not stored.
                if (!objJSONResponse.stored) {
                    alert($('<span>'+TNTOfficiel.translate.errorTechnical+'</span>').text());
                    return false;
                }

                // Flag validated. Allow payment usage.
                boolExtraDataValidated = true;

                if (window.TNTOfficiel.link.back) {
                    TNTOfficiel_ShowPageSpinner();
                    window.location.reload();
                }
            }

            if (!window.TNTOfficiel.link.back) {
                TNTOfficiel_setExtraDataValidated(boolExtraDataValidated);
            }
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            window.location.reload();
        });

    });

    if (!window.TNTOfficiel.link.back) {
        // unset form field class.
        $(document).on('change.'+TNTOfficiel.module.name, '#extra_address_data #customer_email, #extra_address_data #customer_mobile, #extra_address_data #address_building, #extra_address_data #address_accesscode, #extra_address_data #address_floor', function () {
            //$('#extra_address_data .alert-danger.error-'+$(this).attr('id')).remove();
            $(this).parent('.form-group').removeClass('form-error').removeClass('form-ok');
        });
    }

});