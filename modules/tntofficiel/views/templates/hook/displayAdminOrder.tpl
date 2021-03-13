{*
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 * 
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *}

<div class="row"><div class="col-lg-12">
<div id="TNTOfficelAdminOrdersViewOrder" class="panel">

    <div class="panel-heading">
        <i class="icon-AdminTNTOfficiel"></i>
        {l s='TNT Express France' mod='tntofficiel'}
    </div>

    <div id="TNTOfficielOrderWellButton" class="well hidden-print">
        {if $objTNTOrderModel}
            {if $objTNTOrderModel->bt_filename}
                <a class="btn btn-default" href="{$link->getAdminLink('AdminTNTOfficiel')|escape:'html':'UTF-8'}&amp;action=getBT&amp;id_order={$order->id|intval}">
                    <i class="icon-AdminTNTOfficiel"></i>
                    {l s='TNT Transport Ticket' mod='tntofficiel'}
                </a>
            {else}
                <span class="span label label-inactive">
                <i class="icon-remove"></i>
                    {l s='TNT Transport Ticket' mod='tntofficiel'}
                </span>
            {/if}
            &nbsp;
            <a class="btn btn-default" href="{$link->getAdminLink('AdminTNTOfficiel')|escape:'html':'UTF-8'}&amp;action=getManifest&amp;id_order={$order->id|intval}">
                <i class="icon-AdminTNTOfficiel"></i>
                {l s='TNT Manifesto' mod='tntofficiel'}
            </a>
            &nbsp;
            {if $objTNTOrderModel->bt_filename}
                <a class="btn btn-default" href="javascript:void(0);" onclick="window.open('{$link->getAdminLink('AdminTNTOfficiel')|escape:'html':'UTF-8'}&amp;action=tracking&amp;ajax=true&amp;orderId={$order->id|intval}', 'Tracking', 'menubar=no, scrollbars=yes, top=100, left=100, width=900, height=600');">
                    <i class="icon-AdminTNTOfficiel"></i>
                    {l s='TNT Tracking' mod='tntofficiel'}
                </a>
            {else}
                <span class="span label label-inactive">
                    <i class="icon-remove"></i>
                    {l s='TNT Tracking' mod='tntofficiel'}
                </span>
            {/if}
            &nbsp;
        {/if}
    </div>

    <div class="">
        <div class="row">
            <div id="TNTOfficielSection2" class="col-lg-7">
                {if $strDeliveryPointType !== null}
                    <div class="well">
                        <div class="row">
                            <div class="col-sm-12">
                                <p class="clearfix">
                                    <span class="TNTOfficiel_DPLogo TNTOfficiel_DPLogo{if $strDeliveryPointType === 'xett'}RelaisColis{else}Entreprise{/if}">
                                        {if $strDeliveryPointCode !== null}
                                            {l s='Code' mod='tntofficiel'}: <b>{$strDeliveryPointCode|escape:'htmlall':'UTF-8'}</b>
                                        {/if}
                                    </span>
                                    <span>
                                        {if !$isShipped}
                                            <button type="button" class="btn button button-tntofficiel-small pull-right shipping-method-info-select"><span><i class="icon-pencil"></i> &nbsp;{if $strDeliveryPointCode !== null}{l s='Change' mod='tntofficiel'}{else}{l s='Select' mod='tntofficiel'}{/if}</span></button>
                                        {/if}
                                        {if $strDeliveryPointCode !== null && $arrDeliveryPoint}
                                            <b>{$arrDeliveryPoint['name']|escape:'htmlall':'UTF-8'}</b><br />
                                            {if $strDeliveryPointType === 'xett'}
                                                {$arrDeliveryPoint['address']|escape:'htmlall':'UTF-8'}
                                            {else}
                                                {$arrDeliveryPoint['address1']|escape:'htmlall':'UTF-8'}<br />
                                                {$arrDeliveryPoint['address2']|escape:'htmlall':'UTF-8'}
                                            {/if}
                                            <br />
                                            {$arrDeliveryPoint['postcode']|escape:'htmlall':'UTF-8'} {$arrDeliveryPoint['city']|escape:'htmlall':'UTF-8'}<br />
                                            {l s='France' mod='tntofficiel'}
                                        {else}
                                            {* Smarty registered Prestashop methode AddressFormat::generateAddressSmarty() *}
                                            {displayAddressDetail address=$objAddress newLine='<br />'}
                                        {/if}
                                    </span>
                                </p>
                            </div>
                        </div>
                            <hr />
                            <div class="row">
                                <div class="col-sm-6">
                                    {if $strDeliveryPointCode !== null && $arrDeliveryPoint}
                                    <b>{l s='Schedules' mod='tntofficiel'} :</b><br />
                                    {foreach from=$arrSchedules key=day item=schedule}
                                        <span class="weekday">{l s=$day mod='tntofficiel'}:</span>
                                        {if !empty($schedule)}
                                            {assign var='i' value=0}
                                            {foreach from=$schedule item=part}
                                                <span>{' - '|implode:$part|escape:'htmlall':'UTF-8'}</span>
                                                {if ($schedule|@count) > 1 and $i < (($schedule|@count) -1)}
                                                    <span>{l s='and' mod='tntofficiel'}</span>
                                                {/if}
                                                {assign var='i' value=$i+1}
                                            {/foreach}
                                            <br />
                                        {else}
                                            <span>{l s='Closed' mod='tntofficiel'}</span>
                                            <br />
                                        {/if}
                                    {/foreach}
                                    {/if}
                                </div>
                                <div class="col-sm-6 hidden-print">
                                    <div id="map-delivery-point-canvas"></div>
                                </div>
                            </div>
                    </div>
                {/if}
            </div>
            <div id="TNTOfficielSection3" class="col-lg-5">
                <div id="extra_address_data" class="panel">
                    <div class="panel-heading">
                        <i class="icon-AdminTNTOfficiel"></i> {l s='TNT Additional Address' mod='tntofficiel'}
                    </div>
                    <div class="clearfix" data-validated="true">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="customer_email">{l s='Email' mod='tntofficiel'}</label>
                                {* Email *}
                                {if $extraAddressDataValues.fields.customer_email && array_key_exists('customer_email', $extraAddressDataValues.errors)}
                                    <div class="alert-danger error-customer_email">{$extraAddressDataValues.errors.customer_email|escape:'htmlall':'UTF-8'}<span class="tiles"></span></div>
                                {/if}
                                <input class="form-control is_required" type="text" id="customer_email" name="customer_email" value="{$extraAddressDataValues.fields.customer_email|escape:'htmlall':'UTF-8'}" {if $isShipped}disabled="disabled"{/if} />
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="customer_mobile">{l s='Cellphone' mod='tntofficiel'}</label>
                                {* Téléphone portable *}
                                {if $extraAddressDataValues.fields.customer_mobile && array_key_exists('customer_mobile', $extraAddressDataValues.errors)}
                                    <div class="alert-danger error-customer_mobile">{$extraAddressDataValues.errors.customer_mobile|escape:'htmlall':'UTF-8'}<span class="tiles"></span></div>
                                {/if}
                                <input class="form-control is_required" type="tel" id="customer_mobile" name="customer_mobile" value="{$extraAddressDataValues.fields.customer_mobile|escape:'htmlall':'UTF-8'}" {if $isShipped}disabled="disabled"{/if} />
                            </div>
                        </div>
                        {if !$isShipped}
                            <a id="submitAddressExtraData" class="btn button button-tntofficiel-small pull-right">
                                <span>{l s='Validate' mod='tntofficiel'}</span>
                            </a>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="">
        <div class="row">
            <div class="col-lg-7">

                <div id="formAdminParcelsPanel" class="panel">
                    <div class="panel-heading">
                        <i class="icon-AdminTNTOfficiel"></i>
                        {l s='parcels' mod='tntofficiel'} <span class="badge">{$arrTNTParcelList|@count}</span> {if $strPickUpNumber}<span class="badge">{l s='Pickup number: ' mod='tntofficiel'} {$strPickUpNumber|escape:'htmlall':'UTF-8'}</span>{/if}
                        <span class="badge">{l s='Total weight: ' mod='tntofficiel'} <span id="total-weight">0</span> {l s='Kg' mod='tntofficiel'}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="parcelsTable">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='N°' mod='tntofficiel'}</span></th>
                                <th><span class="title_box">{l s='weight' mod='tntofficiel'}</span></th>
                                <th><span class="title_box">{l s='tracking number' mod='tntofficiel'}</span></th>
                                <th><span class="title_box ">{l s='PDL' mod='tntofficiel'}</span></th>
                                <th><span class="title_box "></span></th>
                            </tr>
                            </thead>
                            <tbody id="parcelsTbody">
                            {foreach from=$arrTNTParcelList item=arrTNTParcel key=intTNTParcelIndex}
                                <tr class="current-edit hidden-print" id="row-parcel-{$arrTNTParcel.id_parcel|intval}">
                                    <td>
                                        <div class="input-group">
                                            {$intTNTParcelIndex + 1|intval}
                                        </div>
                                    </td>
                                    <td>
                                        <input id="parcelWeight-{$arrTNTParcel.id_parcel|intval}" value="{$arrTNTParcel.weight|escape:'htmlall':'UTF-8'}" class="form-control fixed-width-sm" {if $isShipped}disabled="disabled"{/if} />
                                        <div class="alert alert-danger alert-danger-small" id="parcelWeightError-{$arrTNTParcel.id_parcel|intval}" style="display: none">
                                            <p id="updateParcelErrorMessage-{$arrTNTParcel.id_parcel|intval}"></p>
                                        </div>
                                        <div class="alert alert-success alert-danger-small" id="parcelWeightSuccess-{$arrTNTParcel.id_parcel|intval}" style="display: none">
                                            <p id="updateParcelSuccessMessage-{$arrTNTParcel.id_parcel|intval}">{l s='Update successful' mod='tntofficiel'}</p>
                                        </div>
                                    </td>
                                    <td>
                                        {if $arrTNTParcel.tracking_url != ''}
                                            <a href="{$arrTNTParcel.tracking_url|escape:'html':'UTF-8'}" target="_blank">
                                                {$arrTNTParcel.parcel_number|escape:'htmlall':'UTF-8'}
                                                <i class="icon-external-link"></i>
                                            </a>
                                        {else}
                                            -
                                        {/if}
                                    </td>
                                    <td>
                                        {if $arrTNTParcel['pdl'] != ''}
                                            <a href="{$arrTNTParcel['pdl']|escape:'html':'UTF-8'}" target="_blank">
                                                <button class="btn btn-primary" >
                                                    <span>{l s='see' mod='tntofficiel'}</span>
                                                </button>
                                            </a>
                                        {else}
                                            -
                                        {/if}
                                    </td>
                                    <td class="actions">
                                        {if !$isShipped}
                                            <button class="btn btn-primary updateParcel" value="{$arrTNTParcel.id_parcel|intval}">
                                                <span>{l s='Update' mod='tntofficiel'}</span>
                                            </button>&nbsp;
                                            <button class="btn btn-primary removeParcel" value="{$arrTNTParcel.id_parcel|intval}">
                                                <span>{l s='Delete' mod='tntofficiel'}</span>
                                            </button>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="row row-margin-bottom row-margin-top">
                        <div class="col-lg-7">
                        </div>
                        <div class="col-lg-5">
                            {if !$isShipped}
                                <a href="#addParcelFancyBox" id="fancyBoxAddParcelLink">
                                    <button class="btn btn-default pull-right" id="addParcel">
                                        <i class="icon-plus-sign"></i>
                                        {l s='add' mod='tntofficiel'}
                                    </button>
                                </a>
                            {/if}
                        </div>
                    </div>
                </div>

                <div style="display:none">
                    <div class="bootstrap" id="addParcelFancyBox">
                        <h1 class="page-subheading">{l s='add parcel' mod='tntofficiel'}</h1>
                        <div class="alert alert-danger alert-danger-small" id="addParcelError" style="display: none">
                            <p id="addParcelErrorMessage"></p>
                        </div>
                        <div class="form-group">
                            <label for="weight">{l s='parcel weight' mod='tntofficiel'}</label>
                            <input class="form-control validate" type="text" id="addParcelWeight">
                        </div>

                        <p class="text-right">
                            <button type="submit" name="submitAddParcel" id="submitAddParcel" class="btn btn-default">
                    <span>
                        {l s='save' mod='tntofficiel'}
                        <i class="icon-chevron-right right"></i>
                    </span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">

                <div id="formAdminShippingDatePanel" class="panel">
                    <div class="panel-heading">
                        <i class="icon-calendar"></i>
                        {l s='Shipping date' mod='tntofficiel'}
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="parcelsTable">
                            <thead>
                            <tr>
                                <th><span class="title_box ">{l s='Shipping date' mod='tntofficiel'}</span></th>
                                <th><span class="title_box ">{l s='Due date' mod='tntofficiel'}</span></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div class="input-group fixed-width-xl" style="float:left;margin-right:3px;">
                                        <input type="text" name="shipping_date" id="shipping_date" class="datepicker" value="" >
                                        <div class="input-group-addon">
                                            <i class="icon-calendar-o"></i>
                                        </div>
                                    </div>
                                    <div id="delivery-date-error" class="alert alert-danger alert-danger-small" style="display: none">
                                        <p>{l s='La date n\'est pas valide' mod='tntofficiel'}</p>
                                    </div>
                                    <div id="delivery-date-success" class="alert alert-success alert-danger-small" style="display: none">
                                        <p>{l s='La date est valide' mod='tntofficiel'}</p>
                                    </div>
                                </td>
                                <td id="due-date">
                                    {$dueDate|escape:'htmlall':'UTF-8'}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <span class="waitimage" style="display: none;"></span>
                </div>

            </div>
        </div>
    </div>

</div>
</div></div>

<script type="text/javascript">
    {literal}

    $.extend(true, TNTOfficiel, {"order": {
        "isTNT": true,
        "isDirectAddressCheck": {/literal}{if $boolDirectAddressCheck}true{else}false{/if}{literal},
        "isShipped" : {/literal}{if $isShipped}true{else}false{/if}{literal},
        "intOrderID" : {/literal}{$objTNTOrderModel->id_order|escape:'javascript':'UTF-8'}{literal},
        "strCarrierCode" : {/literal}'{$objTNTOrderModel->getCarrierCode()|escape:'javascript':'UTF-8'}'{literal},
        "isCarrierDeliveryPoint" : {/literal}{if $strDeliveryPointType !== null}true{else}false{/if}{literal}
    }});

    {/literal}

    var startDateAdminOrder = 0;
    var tempDate;
{if $firstAvailableDate}
    tempDate = "{$firstAvailableDate|escape:'javascript':'UTF-8'}".split('/');
    var startDateAdminOrder = new Date(tempDate[2], tempDate[1] - 1, tempDate[0]);
{/if}
{if !empty($shippingDate)}
    tempDate = "{$shippingDate|escape:'javascript':'UTF-8'}".split('-');
    var shippingDateAdminOrder = new Date(tempDate[0], tempDate[1] - 1, tempDate[2]);
{/if}

</script>