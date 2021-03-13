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
    if (!TNTOfficiel.order.isTNT) {
        return;
    }


    var $elmtOrderPanel = $('#tabOrder').parents('.panel').first();
    var $elmtCustomerPanel = $('#tabAddresses').parents('.panel').first();
    var $elmtTNTOfficielPanel = $('#TNTOfficelAdminOrdersViewOrder');

    var $elmtTNTOfficielOrderWellButton = $elmtTNTOfficielPanel.find('#TNTOfficielOrderWellButton');
    var $elmtTNTOfficielCustomerAdressShippingTabPane = $elmtCustomerPanel.find('#addressShipping');


    /**
     * Button (BT,Tracking)
     */

    var $elmtOrderPanelFirstWell = $elmtOrderPanel.children('.well');
    // Move them to upper.
    if ($elmtOrderPanelFirstWell.length === 1
        && $elmtTNTOfficielOrderWellButton.length === 1
    ) {
        $elmtTNTOfficielOrderWellButton.removeClass().css('margin', '8px 0 0');
        $elmtOrderPanelFirstWell.append($elmtTNTOfficielOrderWellButton);
    }



    // Disable delivery address Modification for DROPOFFPOINT or DEPOT.
    if (TNTOfficiel.order.isCarrierDeliveryPoint) {
        $('#addressShipping form :input').attr('disabled', true);
        $('#addressShipping a').css('cursor', 'not-allowed');
        $('#addressShipping a').on('click', function (objEvent) {
            objEvent.preventDefault();
        });

        $('#addressShipping [name="submitAddressShipping"]').parents('form').first().hide();
        $('#addressShipping .well').first().hide();
        $('#map-delivery-point-canvas').replaceWith($('#map-delivery-canvas'));
    }



    /**
     * Delivery Point.
     */

    var $elmtTNTOfficielS2 = $elmtTNTOfficielPanel.find('#TNTOfficielSection2');
    if ($elmtTNTOfficielCustomerAdressShippingTabPane.length === 1
        && $elmtTNTOfficielS2.length === 1
    ) {
        $elmtTNTOfficielCustomerAdressShippingTabPane.append($elmtTNTOfficielS2.html());
        $elmtTNTOfficielS2.remove();
    }



    // Click on DROPOFFPOINT or DEPOT address displayed in delivery option.
    $(document).on('click', '.shipping-method-info-select', function (objEvent) {

        TNTOfficiel_XHRBoxDeliveryPoints(TNTOfficiel.order.strCarrierCode);

        objEvent.stopImmediatePropagation();
        objEvent.preventDefault();
        return false;
    });



    /**
     * Carrier Additional Information.
     */

    var $elmtTNTOfficielTAI = $elmtTNTOfficielPanel.find('#TNTOfficielSection3');
    if ($elmtTNTOfficielCustomerAdressShippingTabPane.length === 1
        && $elmtTNTOfficielTAI.length === 1
    ) {
        $elmtTNTOfficielCustomerAdressShippingTabPane.append($elmtTNTOfficielTAI.html());
        $elmtTNTOfficielTAI.remove();
    }



    /**
     * Parcel / Pickup
     */

    $("input[id*='parcelWeight-']").on('change', function () {
        var nbrParcelWeight = parseFloat($(this).val());
        if (nbrParcelWeight.toFixed(1) == 0.0) {
            nbrParcelWeight = 0.1;
        }
        $(this).val(nbrParcelWeight.toFixed(1));
    });

    $('#formAdminParcelsPanel')
    .on('click', '.removeParcel:submit', function (objEvent) {
        removeParcel($(this).val());
    })
    .on('click', '.updateParcel:submit', function (objEvent) {
        updateParcel($(this).val());
    });


    $('a#fancyBoxAddParcelLink').fancybox({
        "afterClose": function () {
            $("#addParcelFancyBox #addParcelError").hide();
            $("#addParcelWeight").val("");
        },
        "transitionIn": 'elastic',
        "transitionOut": 'elastic',
        "type": 'inline',
        "speedIn": 600,
        "speedOut": 200,
        "overlayShow": false,
        "autoDimensions": true,
        "autoCenter": false,
        "helpers": {
            overlay: {
                closeClick: false,
                locked: false
            }
        }
    });

    // Click on FancyBox submit to add a parcel.
    $(document).on('click', '.fancybox-inner #addParcelFancyBox #submitAddParcel:submit', function (objEvent) {
        $("#addParcelFancyBox #addParcelError").hide();

        var fltArgWeight = $("#addParcelWeight").val();

        //check if the weight value is valid
        if (isNaN(fltArgWeight) || (fltArgWeight <= 0)) {
            $('#addParcelFancyBox #addParcelErrorMessage').html('Le poids n\'est pas valide');
            $('#addParcelFancyBox #addParcelError').show();
        } else {
            addParcel(fltArgWeight);
        }
    });


    $('#shipping_date').datepicker({
        "minDate": startDateAdminOrder,
        "dateFormat": 'dd/mm/yy',
        "onSelect": function () {
            $('#formAdminShippingDatePanel .alert').hide();
            $('.waitimage').show();
            var objData = {};
            objData['orderId'] = window.id_order;
            objData['shippingDate'] = $('#shipping_date').val();

            var objJqXHR = TNTOfficiel_AJAX({
                "url": TNTOfficiel.link.back.module.checkShippingDateValidUrl,
                "method": 'POST',
                "dataType": 'json',
                "data": objData,
                "async": true
            });

            objJqXHR
            .done(function (objResponseJSON, strTextStatus, objJqXHR) {
                if (objResponseJSON.error) {
                    if (objResponseJSON.error.length) {
                        $("#delivery-date-error").html(objResponseJSON.error);
                    } else {
                        $("#delivery-date-error").html('La date n\'est pas valide.');
                    }
                    $("#delivery-date-error").show();
                    return;
                }

                if (objResponseJSON.dueDate) {
                    $("#due-date").html(objResponseJSON.dueDate);
                }

                if (objResponseJSON.mdwMessage) {
                    $("#delivery-date-success").html(objResponseJSON.mdwMessage);
                } else {
                    $("#delivery-date-success").html('La date est valide.');
                }
                $("#delivery-date-success").show();
            })
            .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
                $("#delivery-date-error").html('Une erreur s\'est produite, merci de réessayer dans quelques minutes.');
                $("#delivery-date-error").show();
            })
            .always(function () {
                $('.waitimage').hide();
            });
        }
    });

    if (typeof shippingDateAdminOrder != 'undefined') {
        $("#shipping_date").datepicker("setDate", shippingDateAdminOrder);
    }
    if (TNTOfficiel.order.isShipped) {
        $("#shipping_date").datepicker("option", "disabled", true);
    }

    updateTotalWeight();

});





/**
 * remove a parcel
 * @param rowNumber
 */
function removeParcel(parcelId)
{
    var objData = {
        "parcelId": parcelId
    };
    var parcelCount = getParcelRowCount();

    if (parcelCount <= 1) {
        $("#updateParcelErrorMessage-" + parcelId).html(TNTOfficiel.translate.back.atLeastOneParcelStr);
        $("#parcelWeightError-" + parcelId).show();
    } else {
        var objJqXHR = TNTOfficiel_AJAX({
            "url": TNTOfficiel.link.back.module.removeParcelUrl,
            "method": 'POST',
            "dataType": 'json',
            "data": objData,
            "async": true
        });

        objJqXHR
        .done(function (objResponseJSON, strTextStatus, objJqXHR) {
            $('#row-parcel-' + parcelId).remove();
            updateTotalWeight();
        });
    }
}

/**
 * Update a parcel
 * @param parcelId
 */
function updateParcel(parcelId)
{
    $('#parcelWeightError-' + parcelId).hide();
    $('#parcelWeightSuccess-' + parcelId).hide();

    var objData = {};
    objData['parcelId'] = parcelId;
    objData['weight'] = $('#parcelWeight-' + parcelId).val();
    objData['orderId'] = window.id_order;

    if (isNaN($('#parcelWeight-' + parcelId).val())
        || $('#parcelWeight-' + parcelId).val() <= 0
    ) {
        $('#updateParcelErrorMessage-' + parcelId).html('Le poids n\'est pas valide');
        $('#parcelWeightError-' + parcelId).show();
    } else {
        var objJqXHR = TNTOfficiel_AJAX({
            "url": TNTOfficiel.link.back.module.updateParcelUrl,
            "method": 'POST',
            "dataType": 'json',
            "data": objData,
            "async": true
        });

        objJqXHR
        .done(function (objResponseJSON, strTextStatus, objJqXHR) {
            if (!objResponseJSON.result) {
                $('#updateParcelErrorMessage-' + parcelId).html(objResponseJSON.error);
                $('#parcelWeightError-' + parcelId).show();
            } else {
                $('#parcelWeight-' + parcelId).val(objResponseJSON.weight);
                $('#parcelWeightSuccess-' + parcelId).show();
                updateTotalWeight();
            }
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            //window.location.reload();
        });
    }
}

/**
 * Add a parcel
 */
function addParcel(fltArgWeight)
{
    var objJqXHR = TNTOfficiel_AJAX({
        "url": TNTOfficiel.link.back.module.addParcelUrl,
        "method": 'POST',
        "dataType": 'json',
        "data": {
            "orderId": window.id_order,
            "weight": fltArgWeight
        },
        "async": true
    });

    objJqXHR
    .done(function (objResponseJSON, strTextStatus, objJqXHR) {
        if (objResponseJSON.result) {
            $.fancybox.close();
            addRowParcel(objResponseJSON);
            updateTotalWeight();
        } else {
            $('#addParcelFancyBox #addParcelErrorMessage').html(objResponseJSON.error);
            $('#addParcelFancyBox #addParcelError').show();
        }
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        //window.location.reload();
    });
}

/**
 * add a row in the parcels table
 */
function addRowParcel(objResponseJSON) {
    var nextRowNumber = getNexttParcelNumber();

    $('#parcelsTbody').append('\
<tr class="current-edit hidden-print" id="row-parcel-' + objResponseJSON['parcel'][0]['id_parcel'] + '">\
    <td>\
        <div class="input-group">' + nextRowNumber + '</div>\
    </td>\
    <td>\
        <input id="parcelWeight-' + objResponseJSON['parcel'][0]['id_parcel'] + '" value="' + objResponseJSON['parcel'][0]['weight'] + '" class="form-control fixed-width-sm" /> \
        <div class="alert alert-danger alert-danger-small" \
             id="parcelWeightError-' + objResponseJSON['parcel'][0]['id_parcel'] + '" \
             style="display: none" \
        ><p id="updateParcelErrorMessage-' + objResponseJSON['parcel'][0]['id_parcel'] + '"></p>\
        </div>\
        <div class="alert alert-success alert-danger-small" \
             id="parcelWeightSuccess-' + objResponseJSON['parcel'][0]['id_parcel'] + '" \
             style="display: none" \
        ><p id="updateParcelSuccessMessage-' + objResponseJSON['parcel'][0]['id_parcel'] + '">' + TNTOfficiel.translate.back.updateSuccessfulStr + '</p>\
        </div>\
    </td>\
    <td>-</td>\
    <td>-</td>\
    <td class="actions">\
        <button class="btn btn-primary updateParcel" value="' + objResponseJSON['parcel'][0]['id_parcel'] + '">' + TNTOfficiel.translate.back.updateStr + '</button>&nbsp;\
        <button class="btn btn-primary removeParcel" value="' + objResponseJSON['parcel'][0]['id_parcel'] + '">' + TNTOfficiel.translate.back.deleteStr + '</button>\
    </td>\
</tr>');

}

/*
 * Add or update total weight
 */
function updateTotalWeight() {
    var sum = 0;
    $('[id*="parcelWeight-"]').each(function () {
        var value = $(this).val();
        // add only if the value is number
        if (!isNaN(value) && value.length != 0) {
            sum += parseFloat(value);
        }
    });
    $('#total-weight').html(sum.toFixed(1));
}

function getParcelRowCount() {
    return $('#parcelsTable > #parcelsTbody tr').length++;
}

function getNexttParcelNumber() {
    return parseInt($('#parcelsTable > #parcelsTbody tr:last-child td:first-child div.input-group').html()) + 1
}