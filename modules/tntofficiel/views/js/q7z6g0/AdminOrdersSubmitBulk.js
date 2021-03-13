/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

// On DOM Ready.
$(function () {
    // Correcting action URL for bulk processing in orders list.
    // ex: Selecting order list and click bulk TB, then click bulk manifest but act like bulk BT.
    $("#form-order button").on('click', function () {
        var $elmtForm = $("#form-order");
        var strAttrAction = $elmtForm.attr('action');
        if (strAttrAction) {
            strAttrAction = strAttrAction.replace('&submitBulkgetManifestorder', '').replace('&submitBulkgetBTorder', '');
            $elmtForm.attr('action', strAttrAction);
        }
    });
});
