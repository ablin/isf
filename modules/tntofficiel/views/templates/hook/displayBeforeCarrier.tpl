{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}

{if !$boolCityPostCodeIsValid}
<div class="tntofficiel-box-panel clearfix" id="noTNTCarrierWarning">
    <a href="{$link->getPageLink('address', true)|escape:'html':'UTF-8'}?id_address={$id_address_delivery|intval}&amp;back=order{if $opc}-opc{/if}.php%3Fstep%3D1"
       class="btn button button-tntofficiel-small pull-right"><span>{l s='Validate my address' mod='tntofficiel'} <i class="icon-chevron-right right"></i> </span></a>
    <span style="font-size: 1.1em;line-height: 2em;">{l s='To view all delivery options, please verify the postal code and city of your delivery address.' mod='tntofficiel'}</span>
</div>
<br />
{/if}

<script type="text/javascript">
{literal}

    var arrTNTOfficieljQSelectorInputRadioCoreTNT = $.map(window.TNTOfficiel.carrier, function (value, index) {
        return 'input:radio[value^="' + index + ',"].delivery_option_radio';
    });

    var strTNTOfficieljQSelectorInputRadioTNT = arrTNTOfficieljQSelectorInputRadioCoreTNT.join(', ');

    // Flag.
    $.extend(true, TNTOfficiel, {
        "cart": {
            "isCarrierListDisplay": true
        }
    });

{/literal}
</script>