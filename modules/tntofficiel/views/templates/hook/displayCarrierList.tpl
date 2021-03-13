{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}

<!-- Tnt carriers -->
<div style="display: none">
    {* if TNT delivery option 'carrier_code' is selected *}
    {if $strCurrentCarrierCode}
    <div id="extra_address_data" class="box clearfix" data-validated="{$extraAddressDataValid|escape:'htmlall':'UTF-8'}" >
        <h1 class="page-subheading">{l s='TNT Additional Address' mod='tntofficiel'}</h1>
        <div id="tntofficielFallbackSelectDesc"></div>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="customer_email">{l s='Email' mod='tntofficiel'} <span class="required"></span></label>
                    {* Email *}
                    <input class="form-control is_required" type="text" id="customer_email" name="customer_email"
                           value="{$extraAddressDataValues.fields.customer_email|escape:'htmlall':'UTF-8'}" />
                    {if $extraAddressDataValues.fields.customer_email && array_key_exists('customer_email', $extraAddressDataValues.errors)}
                        <small class="form-text text-muted alert-danger error-customer_email">{$extraAddressDataValues.errors.customer_email|escape:'htmlall':'UTF-8'}.</small>
                    {/if}
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group">
                    <label for="customer_mobile">{l s='Cellphone' mod='tntofficiel'} <span class="required"></span></label>
                    {* Téléphone portable *}
                    <input class="form-control is_required" type="tel" id="customer_mobile" name="customer_mobile"
                           value="{$extraAddressDataValues.fields.customer_mobile|escape:'htmlall':'UTF-8'}" />
                    {if $extraAddressDataValues.fields.customer_mobile && array_key_exists('customer_mobile', $extraAddressDataValues.errors)}
                        <small class="form-text text-muted alert-danger error-customer_mobile">{$extraAddressDataValues.errors.customer_mobile|escape:'htmlall':'UTF-8'}.</small>
                    {/if}
                </div>
            </div>
        </div>
        {* B2C INDIVIDUAL *}
        {if preg_match('/^[ATMJ]Z_INDIVIDUAL$/ui', $strCurrentCarrierCode)}
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <label for="address_building">{l s='Building Number' mod='tntofficiel'}</label>
                    {* Numéro du bâtiment *}
                    <input class="form-control" type="text" id="address_building" name="address_building"
                           value="{$extraAddressDataValues.fields.address_building|escape:'htmlall':'UTF-8'}" maxlength="3" />
                    {if $extraAddressDataValues.fields.address_building && array_key_exists('address_building', $extraAddressDataValues.errors)}
                        <small class="form-text text-muted alert-danger error-address_building">{$extraAddressDataValues.errors.address_building|escape:'htmlall':'UTF-8'}.</small>
                    {else}
                        <small class="form-text text-muted info-address_building">3 caractères maximum</small>
                    {/if}
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <label for="address_accesscode">{l s='Intercom Code' mod='tntofficiel'}</label>
                    {* Code interphone *}
                    <input class="form-control" type="text" id="address_accesscode" name="address_accesscode"
                           value="{$extraAddressDataValues.fields.address_accesscode|escape:'htmlall':'UTF-8'}" maxlength="7" />
                    {if $extraAddressDataValues.fields.address_accesscode && array_key_exists('address_accesscode', $extraAddressDataValues.errors)}
                        <small class="form-text text-muted alert-danger error-address_accesscode">{$extraAddressDataValues.errors.address_accesscode|escape:'htmlall':'UTF-8'}.</small>
                    {else}
                        <small class="form-text text-muted info-address_accesscode">7 caractères maximum</small>
                    {/if}
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <label for="address_floor">{l s='Floor' mod='tntofficiel'} &nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </label>
                    {* Etage *}
                    <input class="form-control" type="text" id="address_floor" name="address_floor"
                           value="{$extraAddressDataValues.fields.address_floor|escape:'htmlall':'UTF-8'}" maxlength="2" />
                    {if $extraAddressDataValues.fields.address_floor && array_key_exists('address_floor', $extraAddressDataValues.errors)}
                        <small class="form-text text-muted alert-danger error-address_floor">{$extraAddressDataValues.errors.address_floor|escape:'htmlall':'UTF-8'}.</small>
                    {else}
                        <small class="form-text text-muted info-address_floor">2 caractères maximum</small>
                    {/if}
                </div>
            </div>
        </div>
        {/if}
        <p class="clearfix" style="min-height: 32px;margin: 3ex 0 1ex !important;clear: both;">
            <span class="required"></span> {l s='Required fields' mod='tntofficiel'}
            <a id="submitAddressExtraData" class="btn button button-tntofficiel-small pull-right" {if $extraAddressDataValues.length === 0} style="display: none;" {/if}>
                <span>Valider</span>
            </a>
        </p>
    </div>
    {/if}

    {foreach $carriers.products as $tnt_index => $product}
        {foreach $current_delivery_option as $idAddressSelected => $idCarrierOptionSelected}
            {assign var='arrCarrierOptionSelected' value=explode(',',$idCarrierOptionSelected)}
            {assign var='idCarrierOptionSelected' value=$arrCarrierOptionSelected[0]}
            <div id="{$product.id|escape:'htmlall':'UTF-8'}">
                <span class="tntofficiel-name"><strong>{TNTOfficiel_Carrier::$arrCarrierCodeInfos[$product.id]['label']|escape:'htmlall':'UTF-8'|replace:'&lt;':'<'|replace:"&gt;":">"}</strong></span><br />
                {TNTOfficiel_Carrier::$arrCarrierCodeInfos[$product.id]['description']|escape:'htmlall':'UTF-8'|replace:'&lt;':'<'|replace:"&gt;":">"}<br />
                {if !empty($product.due_date)}
                    <span class="tntofficiel-dpl">Date prévisionnelle de livraison :&nbsp;{date('d/m/Y', strtotime($product.due_date))|escape:'htmlall':'UTF-8'}</span><br />
                {/if}
                {if $strCurrentCarrierCode === $product.id and $idCarrierOptionSelected === "{$idcurrentTntCarrier}"}
                    {assign var='item_info' value=''}
                    {if {$product.type|lower} == 'dropoffpoint' and isset($deliveryPoint.xett)}
                        {assign var='current_method_id' value='relay_point'}
                        {assign var='current_method_name' value='relay-point'}
                        {assign var='item_info' value=$deliveryPoint}
                    {elseif {$product.type|lower} == 'depot' and isset($deliveryPoint.pex)}
                        {assign var='current_method_id' value='repository'}
                        {assign var='current_method_name' value='repository'}
                        {assign var='item_info' value=$deliveryPoint}
                    {/if}
                    {if isset($item_info) and $item_info != ''}
                        {include './displayCarrierList/deliveryPointSet.tpl' item=$item_info method_id=$current_method_id method_name=$current_method_name}
                        <div class="shipping-method-info-details">
                            <button type="button" class="btn button button-tntofficiel-small shipping-method-info-select">
                                <span>{l s='Change' mod='tntofficiel'}</span>
                            </button>
                        </div>
                    {elseif $product.id|strstr:"DROPOFFPOINT" or $product.id|strstr:"DEPOT" }
                        <div class="shipping-method-info-details">
                            <button type="button" class="btn button button-tntofficiel-small shipping-method-info-select">
                                <span>{l s='Select' mod='tntofficiel'}</span>
                            </button>
                        </div>
                    {/if}
                {/if}
            </div>
        {/foreach}
    {/foreach}
</div>



<script type="text/javascript">

    // On DOM Ready.
    $(function () {

        $(strTNTOfficieljQSelectorInputRadioTNT).each(function (intIndex, element) {

            $(this).parents('tr').addClass('tntofficiel');
            var strTNTClickedCarrierID = $(this).val().split(',')[0];
            var strTNTClickedCarrierCode = window.TNTOfficiel.carrier[strTNTClickedCarrierID];
            var $elmtTdDescription = $(this).parents('tr').children('td:nth-child(4)').prev('td');

            // If Description found.
            if ($elmtTdDescription.length === 1) {
                $elmtTdDescription.html($('#'+strTNTClickedCarrierCode).html());
            } else if ($(this).filter(':checked').length === 1) {
                $('#tntofficielFallbackSelectDesc').html(
                    '<div style="background-color: #FFFFFF; padding: 6px; margin: 12px 0px; border-radius: 3px;">'
                    +$('#'+strTNTClickedCarrierCode).html()
                    +'</div>'
                );
            }

            $('#'+strTNTClickedCarrierCode).remove();
        });

        // Click on a virtual TNT carrier.
        $(strTNTOfficieljQSelectorInputRadioTNT).off('.'+TNTOfficiel.module.name).on('click.'+TNTOfficiel.module.name, function (objEvent) {
            var $elmtInputRadioVirtTNTClick = $(this);
            var strTNTClickedCarrierID = $elmtInputRadioVirtTNTClick.val().split(',')[0];
            var strTNTClickedCarrierCode = window.TNTOfficiel.carrier[strTNTClickedCarrierID];

            // Display pop-in to select delivery point. only for DROPOFFPOINT or DEPOT.
            TNTOfficiel_XHRBoxDeliveryPoints(strTNTClickedCarrierCode);
        });

        // Remove pasted form.
        $('input:radio.delivery_option_radio').off('.all'+TNTOfficiel.module.name).on('change.all'+TNTOfficiel.module.name, function (objEvent) {
            var $elmtInputRadioVirtTNTClick = $(this);
            var strTNTClickedCarrierID = $elmtInputRadioVirtTNTClick.val().split(',')[0];
            var strTNTClickedCarrierCode = window.TNTOfficiel.carrier[strTNTClickedCarrierID];
            // If selection is TNT.
            if (strTNTClickedCarrierCode) {
                TNTOfficiel_ShowPageSpinner();
            }
            // Hide extra data form.
            $('#extra_address_data').remove();
            /*$('#extra_address_data').slideUp(125, function(){
                $('#extra_address_data').remove();
            });*/
        });

    });

    // On DOM Ready.
    $(function () {
        // Update on display.
        TNTOfficiel_updatePaymentDisplay();

        {if $strCurrentCarrierCode}
        var $elmtFormExtraAddressData = $('#extra_address_data');
        var $elmtInputRadioVirtTNTMatch = $(strTNTOfficieljQSelectorInputRadioTNT).filter(':checked');

        var $elmtTdDescription = $elmtInputRadioVirtTNTMatch.parents('tr').children('td:nth-child(4)').prev('td');
        var $elmtDeliveryOptionBlock = $elmtInputRadioVirtTNTMatch.parents('.delivery_option');

        if (false && $elmtTdDescription.length === 1 && $elmtDeliveryOptionBlock.length === 1) {
            $elmtDeliveryOptionBlock.after($elmtFormExtraAddressData);
        } else if (!$elmtFormExtraAddressData.is(':visible')) {
            $elmtFormExtraAddressData.parents(':visible').first().append($elmtFormExtraAddressData);
        }

        // If selection is TNT.
        if ($('#TNTOfficielLoading:visible').length) {
            TNTOfficiel_HidePageSpinner();
        }
        // Then show.
        $elmtFormExtraAddressData.show(0);
        {/if}
    });

</script>
