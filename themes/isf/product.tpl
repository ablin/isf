{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{include file="$tpl_dir./errors.tpl"}
{if $errors|@count == 0}
    {if !isset($priceDisplayPrecision)}
        {assign var='priceDisplayPrecision' value=2}
    {/if}
    {if !$priceDisplay || $priceDisplay == 2}
        {assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, 6)}
        {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
    {elseif $priceDisplay == 1}
        {assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, 6)}
        {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
    {/if}
<div itemscope itemtype="https://schema.org/Product">
    <meta itemprop="url" content="{$link->getProductLink($product)}">
    <div class="primary_block row">
        {if !$content_only}
            <div class="container">
                <div class="top-hr"></div>
            </div>
        {/if}
        {if isset($adminActionDisplay) && $adminActionDisplay}
            <div id="admin-action" class="container">
                <p class="alert alert-info">{l s='This product is not visible to your customers.'}
                    <input type="hidden" id="admin-action-product-id" value="{$product->id}" />
                    <a id="publish_button" class="btn btn-default button button-small" href="#">
                        <span>{l s='Publish'}</span>
                    </a>
                    <a id="lnk_view" class="btn btn-default button button-small" href="#">
                        <span>{l s='Back'}</span>
                    </a>
                </p>
                <p id="admin-action-result"></p>
            </div>
        {/if}
        {if isset($confirmation) && $confirmation}
            <p class="confirmation">
                {$confirmation}
            </p>
        {/if}
        <!-- left infos-->
        <div class="pb-left-column col-xs-12 col-sm-4 col-md-3">
            <!-- product img-->
            <div id="image-block" class="clearfix">
                {if $product->new}
                    <span class="new-box">
                        <span class="new-label">{l s='New'}</span>
                    </span>
                {/if}
                {if $product->on_sale}
                    <span class="sale-box no-print">
                        <span class="sale-label">{l s='Sale!'}</span>
                    </span>
                {elseif $product->specificPrice && $product->specificPrice.reduction && $productPriceWithoutReduction > $productPrice}
                    <span class="discount">{l s='Reduced price!'}</span>
                {/if}
                <span id="view_full_size">
                    {if $jqZoomEnabled && $have_image && !$content_only}
                        <a class="jqzoom" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" rel="gal1" href="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'thickbox_default')|escape:'html':'UTF-8'}">
                            {if preg_match("/-default/", $cover.id_image) && !preg_match("/-default/", {$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default', $product->id)|escape:'html':'UTF-8'})}
                                <div class="filigrane">
									<span>
										{l s='Non contractual photo'}
									</span>
								</div>
                            {/if}
                            <img itemprop="image" src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default')|escape:'html':'UTF-8'}" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" alt="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" width="{$largeSize.width}" height="{$largeSize.height}"/>
                        </a>
                    {else}
                        {if preg_match("/-default/", $cover.id_image) && !preg_match("/-default/", {$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default', $product->id)|escape:'html':'UTF-8'})}
                            <div class="filigrane">
                                <span>
                                    {l s='Non contractual photo'}
                                </span>
                            </div>
                        {/if}
                        <img id="bigpic" itemprop="image" src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default', $product->id)|escape:'html':'UTF-8'}" title="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" alt="{if !empty($cover.legend)}{$cover.legend|escape:'html':'UTF-8'}{else}{$product->name|escape:'html':'UTF-8'}{/if}" width="{$largeSize.width}" height="{$largeSize.height}"/>
                        {if !$content_only}
                            <span class="span_link no-print">{l s='View larger'}</span>
                        {/if}
                    {/if}
                </span>
            </div> <!-- end image-block -->
            {if isset($images) && count($images) > 0}
                <!-- thumbnails -->
                <div id="views_block" class="clearfix {if isset($images) && count($images) < 2}hidden{/if}">
                    {if isset($images) && count($images) > 2}
                        <span class="view_scroll_spacer">
                            <a id="view_scroll_left" class="" title="{l s='Other views'}" href="javascript:{ldelim}{rdelim}">
                                {l s='Previous'}
                            </a>
                        </span>
                    {/if}
                    <div id="thumbs_list">
                        <ul id="thumbs_list_frame">
                        {if isset($images)}
                            {foreach from=$images item=image name=thumbnails}
                                {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                                {if !empty($image.legend)}
                                    {assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
                                {else}
                                    {assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
                                {/if}
                                <li id="thumbnail_{$image.id_image}"{if $smarty.foreach.thumbnails.last} class="last"{/if}>
                                    <a{if $jqZoomEnabled && $have_image && !$content_only} href="javascript:void(0);" rel="{literal}{{/literal}gallery: 'gal1', smallimage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'large_default')|escape:'html':'UTF-8'}',largeimage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}'{literal}}{/literal}"{else} href="{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}" data-fancybox-group="other-views" class="fancybox{if $image.id_image == $cover.id_image} shown{/if}"{/if} title="{$imageTitle}">
                                        <img class="img-responsive" id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'cart_default')|escape:'html':'UTF-8'}" alt="{$imageTitle}" title="{$imageTitle}"{if isset($cartSize)} height="{$cartSize.height}" width="{$cartSize.width}"{/if} itemprop="image" />
                                    </a>
                                </li>
                            {/foreach}
                        {/if}
                        </ul>
                    </div> <!-- end thumbs_list -->
                    {if isset($images) && count($images) > 2}
                        <a id="view_scroll_right" title="{l s='Other views'}" href="javascript:{ldelim}{rdelim}">
                            {l s='Next'}
                        </a>
                    {/if}
                </div> <!-- end views-block -->
                <!-- end thumbnails -->
            {/if}
            {if isset($images) && count($images) > 1}
                <p class="resetimg clear no-print">
                    <span id="wrapResetImages" style="display: none;">
                        <a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}" data-id="resetImages">
                            <i class="icon-repeat"></i>
                            {l s='Display all pictures'}
                        </a>
                    </span>
                </p>
            {/if}
        </div> <!-- end pb-left-column -->
        <!-- end left infos-->
        <!-- center infos -->
        <div class="pb-center-column col-xs-12 col-sm-5">
            {if $product->online_only}
                <p class="online_only">{l s='Online only'}</p>
            {/if}
            <h1 itemprop="name">{$product->name|escape:'html':'UTF-8'}</h1>
            {if $product->description_short || $packItems|@count > 0}
                <div id="short_description_block">
                    {if $product->description_short}
                        <div id="short_description_content" class="rte align_justify" itemprop="description">{$product->description_short|stripslashes}</div>
                    {/if}

                    {if $product->description}
                        <p class="buttons_bottom_block">
                            <a href="javascript:{ldelim}{rdelim}" class="button">
                                {l s='More details'}
                            </a>
                        </p>
                    {/if}
                </div> <!-- end short_description_block -->
            {/if}
            <p id="product_reference"{if empty($product->reference) || !$product->reference} style="display: none;"{/if}>
                <label>{l s='Reference:'} </label>
                <span class="editable" itemprop="sku"{if !empty($product->reference) && $product->reference} content="{$product->reference}"{/if}>{if !isset($groups)}{$product->reference|escape:'html':'UTF-8'}{/if}</span>
            </p>
            {if ($display_qties == 1 && !$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && $product->available_for_order)}
                <!-- number of item in stock -->
                <p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
                    <span id="quantityAvailable">{$product->quantity|intval}</span>
                    <span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='Item'}</span>
                    <span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='Items'}</span>
                </p>
            {/if}
            {if $nb_tarif == 0}
                <p id="price_on_demand">
                    <span class="label label-warning">{l s='Price on demand'}</span>
                </p>
                {if $stock != '' or $dispo != '' or $jauge != ''}
                    <p id="product_stock">
                        <strong>{l s='Stock:'}</strong>
                        {if $stock != -1}
                            <span class="availability">
                                {if $stock > 0}
                                    <span id="availability_value" class="label label-success">
                                        {$stock}
                                    </span>
                                {else}
                                    <span id="availability_value" class="label label-danger">
                                        0
                                    </span>
                                {/if}
                            </span>
                        {/if}
                        {if $dispo != -1}
                            <span class="availability">
                                {if $dispo > 0}
                                    <span id="availability_value" class="label-success">
                                        {l s='Available'}
                                    </span>
                                {else}
                                    <span id="availability_value" class="label-danger">
                                        {l s='Unavailable'}
                                    </span>
                                {/if}
                            </span>
                        {/if}
                        {if $jauge != -1}
                            <span class="availability">
                                <span class="availability-gauge{if $jauge == 1} orange{elseif $jauge > 0} green{/if}"></span>
                                <span class="availability-gauge{if $jauge > 1} green{/if}"></span>
                                <span class="availability-gauge{if $jauge > 2} green{/if}"></span>
                            </span>
                        {/if}
                    </p>
                {/if}
            {/if}
            {if $PS_STOCK_MANAGEMENT}
                {if !$product->is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
                <p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties || $product->quantity <= 0) || $allow_oosp || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none"{/if} >{l s='Warning: Last items in stock!'}</p>
            {/if}
            <p id="availability_date"{if ($product->quantity > 0) || !$product->available_for_order || $PS_CATALOG_MODE || !isset($product->available_date) || $product->available_date < $smarty.now|date_format:'%Y-%m-%d'} style="display: none;"{/if}>
                <span id="availability_date_label">{l s='Availability date:'}</span>
                <span id="availability_date_value">{if Validate::isDate($product->available_date)}{dateFormat date=$product->available_date full=false}{/if}</span>
            </p>
            <!-- Out of stock hook -->
            <div id="oosHook"{if $product->quantity > 0} style="display: none;"{/if}>
                {$HOOK_PRODUCT_OOS}
            </div>
            {if isset($HOOK_EXTRA_RIGHT) && $HOOK_EXTRA_RIGHT}{$HOOK_EXTRA_RIGHT}{/if}
            {if !$content_only}
                <!-- usefull links-->
                <ul id="usefull_link_block" class="clearfix no-print">
                    {if $HOOK_EXTRA_LEFT}{$HOOK_EXTRA_LEFT}{/if}
                    <li class="print">
                        <a href="javascript:print();">
                            {l s='Print'}
                        </a>
                    </li>
                </ul>
            {/if}
        </div>
        <!-- end center infos-->
        <!-- pb-right-column-->
        <div class="pb-right-column col-xs-12 col-sm-4 col-md-3">
            {if ($product->show_price && !isset($restricted_country_mode)) || isset($groups) || $product->reference || (isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS)}
            <!-- add to cart form-->
            <form id="buy_block"{if $PS_CATALOG_MODE && !isset($groups) && $product->quantity > 0} class="hidden"{/if} action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
                <!-- hidden datas -->
                <p class="hidden">
                    <input type="hidden" name="token" value="{$static_token}" />
                    <input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
                    <input type="hidden" name="add" value="1" />
                    <input type="hidden" name="id_product_attribute" id="idCombination" value="" />
                </p>
                <div class="box-info-product">
                    <div class="product_attributes clearfix">
                        <!-- quantity wanted -->
                        {if !$PS_CATALOG_MODE}
                            <p id="quantity_wanted_p"{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                                <label for="quantity_wanted">{l s='Quantity'}</label>
                                <input type="number" min="1" name="qty" id="quantity_wanted" class="text" value="{if isset($quantityBackup)}{$quantityBackup|intval}{else}{if $product->minimal_quantity > 1}{$product->minimal_quantity}{else}1{/if}{/if}" />
                                <a href="#" data-field-qty="qty" class="btn btn-default button-minus product_quantity_down">
                                    <span><i class="icon-minus"></i></span>
                                </a>
                                <a href="#" data-field-qty="qty" class="btn btn-default button-plus product_quantity_up">
                                    <span><i class="icon-plus"></i></span>
                                </a>
                                <span class="clearfix"></span>
                            </p>
                        {/if}
                        <!-- minimal quantity wanted -->
                        <p id="minimal_quantity_wanted_p"{if $product->minimal_quantity <= 1 || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                            {l s='The minimum purchase order quantity for the product is'} <b id="minimal_quantity_label">{$product->minimal_quantity}</b>
                        </p>
                        {if isset($groups)}
                            <!-- attributes -->
                            <div id="attributes">
                                <div class="clearfix"></div>
                                {foreach from=$groups key=id_attribute_group item=group}
                                    {if $group.attributes|@count}
                                        <fieldset class="attribute_fieldset">
                                            <label class="attribute_label" {if $group.group_type != 'color' && $group.group_type != 'radio'}for="group_{$id_attribute_group|intval}"{/if}>{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
                                            {assign var="groupName" value="group_$id_attribute_group"}
                                            <div class="attribute_list">
                                                {if ($group.group_type == 'select')}
                                                    <select name="{$groupName}" id="group_{$id_attribute_group|intval}" class="form-control attribute_select no-print">
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}</option>
                                                        {/foreach}
                                                    </select>
                                                {elseif ($group.group_type == 'color')}
                                                    <ul id="color_to_pick_list" class="clearfix">
                                                        {assign var="default_colorpicker" value=""}
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            {assign var='img_color_exists' value=file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                                                            <li{if $group.default == $id_attribute} class="selected"{/if}>
                                                                <a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}" id="color_{$id_attribute|intval}" name="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick{if ($group.default == $id_attribute)} selected{/if}"{if !$img_color_exists && isset($colors.$id_attribute.value) && $colors.$id_attribute.value} style="background:{$colors.$id_attribute.value|escape:'html':'UTF-8'};"{/if} title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}">
                                                                    {if $img_color_exists}
                                                                        <img src="{$img_col_dir}{$id_attribute|intval}.jpg" alt="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" width="20" height="20" />
                                                                    {/if}
                                                                </a>
                                                            </li>
                                                            {if ($group.default == $id_attribute)}
                                                                {$default_colorpicker = $id_attribute}
                                                            {/if}
                                                        {/foreach}
                                                    </ul>
                                                    <input type="hidden" class="color_pick_hidden" name="{$groupName|escape:'html':'UTF-8'}" value="{$default_colorpicker|intval}" />
                                                {elseif ($group.group_type == 'radio')}
                                                    <ul>
                                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                            <li>
                                                                <input type="radio" class="attribute_radio" name="{$groupName|escape:'html':'UTF-8'}" value="{$id_attribute}" {if ($group.default == $id_attribute)} checked="checked"{/if} />
                                                                <span>{$group_attribute|escape:'html':'UTF-8'}</span>
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                {/if}
                                            </div> <!-- end attribute_list -->
                                        </fieldset>
                                    {/if}
                                {/foreach}
                            </div> <!-- end attributes -->
                        {/if}
                    </div> <!-- end product_attributes -->
                    <div class="box-cart-bottom">
                        <div>
                            <p id="add_to_cart" class="buttons_bottom_block no-print">
                                <button type="submit" name="Submit" class="exclusive" {if $PS_CATALOG_MODE} disabled{/if}>
                                    <span>{if $content_only && (isset($product->customization_required) && $product->customization_required)}{l s='Customize'}{else}{l s='Add to cart'}{/if}</span>
                                </button>
                            </p>
                        </div>
                        {if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}
                    </div> <!-- end box-cart-bottom -->
                </div> <!-- end box-info-product -->
            </form>
            {/if}
        </div> <!-- end pb-right-column-->
        {if $nb_tarif > 0 && $sousRefs|@count > 0}
            <div id="productTarifs">
                {if $alerte}
                    <span class="alert">{$alerte}</span>
                {/if}
                <table>
                    <tr style="background: grey; color: #fff;">
                        <th></th>
                        <th>{l s='Minimal quantity'}</th>
                        <th>{l s='Price by'}</th>
                        <th>{l s='Unit price'}</th>
                        <th>{l s='Discount'}</th>
                        <th>{l s='Net price'}</th>
                    </tr>
                    {foreach from=$sousRefs item=sousref}
                        {if 'tarifs'|array_key_exists:$sousref}
                            {foreach from=$sousref->tarifs key=key item=line}
                                {if $key == 0}
                                    <tr style="border-top:1px solid #000;background: LightGrey;">
                                        <td style="text-align: left"><strong>{$sousref->sref1_Des}</strong></td>
                                        <td colspan="5" style="text-align: right;">
                                            <strong>{l s='Stock:'}</strong>
                                            {if $sousref->qteStock != -1}
                                                <span class="availability">
                                                    {if $sousref->qteStock > 0}
                                                        <span id="availability_value" class="label label-success">
                                                            {$sousref->qteStock}
                                                        </span>
                                                    {else}
                                                        <span id="availability_value" class="label label-danger">
                                                            0
                                                        </span>
                                                    {/if}
                                                </span>
                                            {/if}
                                            {if $sousref->qteDispo != -1}
                                                <span class="availability">
                                                    {if $sousref->qteDispo > 0}
                                                        <span id="availability_value" class="label-success">
                                                            {l s='Available'}
                                                        </span>
                                                    {else}
                                                        <span id="availability_value" class="label-danger">
                                                            {l s='Unavailable'}
                                                        </span>
                                                    {/if}
                                                </span>
                                            {/if}
                                            {if $sousref->qteJauge != -1}
                                                <span class="availability">
                                                    <span class="availability-gauge{if $sousref->qteJauge == 1} orange{elseif $sousref->qteJauge > 0} green{/if}"></span>
                                                    <span class="availability-gauge{if $sousref->qteJauge > 1} green{/if}"></span>
                                                    <span class="availability-gauge{if $sousref->qteJauge > 2} green{/if}"></span>
                                                </span>
                                            {/if}
                                        </td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td></td>
                                    <td>{$line->qte}</td>
                                    <td>{$line->ppar}</td>
                                    <td>{convertPrice price=$line->pub}</td>
                                    <td>{$line->remise}</td>
                                    <td>{convertPrice price=$line->pun}</td>
                                </tr>
                            {/foreach}
                        {/if}
                    {/foreach}
                </table>
            </div>
        {/if}
    </div> <!-- end primary_block -->
    {if !$content_only}
{if (isset($quantity_discounts) && count($quantity_discounts) > 0)}
            <!-- quantity discount -->
            <section class="page-product-box">
                <h3 class="page-product-heading">{l s='Volume discounts'}</h3>
                <div id="quantityDiscount">
                    <table class="std table-product-discounts">
                        <thead>
                            <tr>
                                <th>{l s='Quantity'}</th>
                                <th>{if $display_discount_price}{l s='Price'}{else}{l s='Discount'}{/if}</th>
                                <th>{l s='You Save'}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
                            {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                                {$realDiscountPrice=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                            {else}
                                {$realDiscountPrice=$productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                            {/if}
                            <tr id="quantityDiscount_{$quantity_discount.id_product_attribute}" class="quantityDiscount_{$quantity_discount.id_product_attribute}" data-real-discount-value="{convertPrice price = $realDiscountPrice}" data-discount-type="{$quantity_discount.reduction_type}" data-discount="{$quantity_discount.real_value|floatval}" data-discount-quantity="{$quantity_discount.quantity|intval}">
                                <td>
                                    {$quantity_discount.quantity|intval}
                                </td>
                                <td>
                                    {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                                        {if $display_discount_price}
                                            {if $quantity_discount.reduction_tax == 0 && !$quantity_discount.price}
                                                {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction_with_tax)|floatval}
                                            {else}
                                                {convertPrice price=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                                            {/if}
                                        {else}
                                            {convertPrice price=$quantity_discount.real_value|floatval}
                                        {/if}
                                    {else}
                                        {if $display_discount_price}
                                            {if $quantity_discount.reduction_tax == 0}
                                                {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction_with_tax)|floatval}
                                            {else}
                                                {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                                            {/if}
                                        {else}
                                            {$quantity_discount.real_value|floatval}%
                                        {/if}
                                    {/if}
                                </td>
                                <td>
                                    <span>{l s='Up to'}</span>
                                    {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                                        {$discountPrice=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                                    {else}
                                        {$discountPrice=$productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                                    {/if}
                                    {$discountPrice=$discountPrice * $quantity_discount.quantity}
                                    {$qtyProductPrice=$productPriceWithoutReduction|floatval * $quantity_discount.quantity}
                                    {convertPrice price=$qtyProductPrice - $discountPrice}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </section>
        {/if}
        <div id="more_info_block" class="clear">
    <ul id="more_info_tabs" class="idTabs idTabsShort clearfix">
        {if 'tab1'|array_key_exists:$tabs[0] AND (int) $tabs[0]->tab1 == 1 AND $features}<li><a id="more_info_tab_data_sheet" href="#idTab1">{l s='Data sheet'}</a></li>{/if}
        {if 'tab2'|array_key_exists:$tabs[0] AND (int) $tabs[0]->tab2 == 1 AND isset($correspondences) AND $correspondences}<li><a id="more_info_tab_data_sheet" href="#idTab2">{l s='Correspondences'}</a></li>{/if}
        {if 'tab3'|array_key_exists:$tabs[0] AND (int) $tabs[0]->tab3 == 1 AND $product->description}<li><a id="more_info_tab_more_info" href="#idTab3">{l s='More info'}</a></li>{/if}
        {if 'tab4'|array_key_exists:$tabs[0] AND (int) $tabs[0]->tab4 == 1 AND $attachments}<li><a id="more_info_tab_attachments" href="#idTab4">{l s='Download'}</a></li>{/if}
        {if 'tab5'|array_key_exists:$tabs[0] AND (int) $tabs[0]->tab5 == 1 AND isset($accessories) AND $accessories}<li><a href="#idTab5">{l s='Accessories'}</a></li>{/if}
        {$HOOK_PRODUCT_TAB}
    </ul>

    <div id="more_info_sheets" class="sheets align_justify">
        {if $tabs[0]->tab1 == 1 AND isset($features) AND $features}
            <!-- Data sheet -->
            <div id="idTab1" class="rte">
                <table class="table-data-sheet">
                    {foreach from=$features item=feature}
                        <tr class="{cycle values="odd,even"}">
                            {if isset($feature.value)}
                                <td>
                                    {if $feature.id_feature == null}
                                        {l s='Link'}
                                    {/if}
                                    {$feature.name|escape:'html':'UTF-8'}
                                </td>
                                <td>
                                    {$feature.value}
                                </td>
                            {/if}
                        </tr>
                    {/foreach}
                </table>
            </div>
            <!--end Data sheet -->
        {/if}

        {if $tabs[0]->tab2 == 1 AND isset($correspondences) AND $correspondences}
            <!-- Correspondences -->
            <div id="idTab2" class="rte">
                <table class="table-data-sheet">
                    {foreach from=$correspondences item=correspondence}
                        <tr class="{cycle values="odd,even"}">
                            {if isset($correspondence.value)}
                                <td>
                                    {$correspondence.name|escape:'html':'UTF-8'}
                                </td>
                                <td>
                                    {$correspondence.value}
                                </td>
                            {/if}
                        </tr>
                    {/foreach}
                </table>
            </div>
            <!--end Correspondences -->
        {/if}

        {if $tabs[0]->tab3 == 1 AND isset($product) AND $product->description}
            <!-- More info -->
            <div id="idTab3" class="rte">
                {$product->description|stripslashes}
            </div>
            <!--end  More info -->
        {/if}

        {if $tabs[0]->tab4 == 1 AND isset($attachments) AND $attachments}
            <!--Download -->
            <div id="idTab4" class="rte page-product-box">
                <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{l s='File'}</th>
                                <th>{l s='Size'}</th>
                                <th width="15%">{l s='Download'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$attachments item=attachment name=attachements}
                                <tr>
                                    <td>{$attachment.name|escape:'html':'UTF-8'}</td>
                                    <td>{Tools::formatBytes($attachment.file_size, 2)}</td>
                                    <td>
                                        <a class="btn btn-default btn-block" href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id_attachment}")|escape:'html':'UTF-8'}">
                                        <i class="icon-file-text"></i> {l s='Download file'}
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
            </div>
            <!--end Download -->
        {/if}

        {if $tabs[0]->tab5 == 1 AND isset($accessories) AND $accessories}
            <!--Accessories -->
            <div id="idTab5" class="page-product-box">
                {include file="$tpl_dir./product-list.tpl" products=$accessories}
            </div>
            <!--end Accessories -->
        {/if}

        <!-- extra tabs -->
        {$HOOK_PRODUCT_TAB_CONTENT}
        <!-- END extra tabs -->
    </div>
   {if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}
    {/if}
</div> <!-- itemscope product wrapper -->
{strip}
{if isset($smarty.get.ad) && $smarty.get.ad}
    {addJsDefL name=ad}{$base_dir|cat:$smarty.get.ad|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.adtoken) && $smarty.get.adtoken}
    {addJsDefL name=adtoken}{$smarty.get.adtoken|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{addJsDef allowBuyWhenOutOfStock=$allow_oosp|boolval}
{addJsDef availableNowValue=$product->available_now|escape:'quotes':'UTF-8'}
{addJsDef availableLaterValue=$product->available_later|escape:'quotes':'UTF-8'}
{addJsDef attribute_anchor_separator=$attribute_anchor_separator|escape:'quotes':'UTF-8'}
{addJsDef attributesCombinations=$attributesCombinations}
{addJsDef currentDate=$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}
{if isset($combinations) && $combinations}
    {addJsDef combinations=$combinations}
    {addJsDef combinationsFromController=$combinations}
    {addJsDef displayDiscountPrice=$display_discount_price}
    {addJsDefL name='upToTxt'}{l s='Up to' js=1}{/addJsDefL}
{/if}
{if isset($combinationImages) && $combinationImages}
    {addJsDef combinationImages=$combinationImages}
{/if}
{addJsDef customizationId=$id_customization}
{addJsDef customizationFields=$customizationFields}
{addJsDef default_eco_tax=$product->ecotax|floatval}
{addJsDef displayPrice=$priceDisplay|intval}
{addJsDef ecotaxTax_rate=$ecotaxTax_rate|floatval}
{if isset($cover.id_image_only)}
    {addJsDef idDefaultImage=$cover.id_image_only|intval}
{else}
    {addJsDef idDefaultImage=0}
{/if}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef img_prod_dir=$img_prod_dir}
{addJsDef id_product=$product->id|intval}
{addJsDef jqZoomEnabled=$jqZoomEnabled|boolval}
{addJsDef maxQuantityToAllowDisplayOfLastQuantityMessage=$last_qties|intval}
{addJsDef minimalQuantity=$product->minimal_quantity|intval}
{addJsDef noTaxForThisProduct=$no_tax|boolval}
{if isset($customer_group_without_tax)}
    {addJsDef customerGroupWithoutTax=$customer_group_without_tax|boolval}
{else}
    {addJsDef customerGroupWithoutTax=false}
{/if}
{if isset($group_reduction)}
    {addJsDef groupReduction=$group_reduction|floatval}
{else}
    {addJsDef groupReduction=false}
{/if}
{addJsDef oosHookJsCodeFunctions=Array()}
{addJsDef productHasAttributes=isset($groups)|boolval}
{addJsDef productPriceTaxExcluded=($product->getPriceWithoutReduct(true)|default:'null' - $product->ecotax)|floatval}
{addJsDef productPriceTaxIncluded=($product->getPriceWithoutReduct(false)|default:'null' - $product->ecotax * (1 + $ecotaxTax_rate / 100))|floatval}
{addJsDef productBasePriceTaxExcluded=($product->getPrice(false, null, 6, null, false, false) - $product->ecotax)|floatval}
{addJsDef productBasePriceTaxExcl=($product->getPrice(false, null, 6, null, false, false)|floatval)}
{addJsDef productBasePriceTaxIncl=($product->getPrice(true, null, 6, null, false, false)|floatval)}
{addJsDef productReference=$product->reference|escape:'html':'UTF-8'}
{addJsDef productAvailableForOrder=$product->available_for_order|boolval}
{addJsDef productPriceWithoutReduction=$productPriceWithoutReduction|floatval}
{addJsDef productPrice=$productPrice|floatval}
{addJsDef productUnitPriceRatio=$product->unit_price_ratio|floatval}
{addJsDef productShowPrice=(!$PS_CATALOG_MODE && $product->show_price)|boolval}
{addJsDef PS_CATALOG_MODE=$PS_CATALOG_MODE}
{if $product->specificPrice && $product->specificPrice|@count}
    {addJsDef product_specific_price=$product->specificPrice}
{else}
    {addJsDef product_specific_price=array()}
{/if}
{if $display_qties == 1 && $product->quantity}
    {addJsDef quantityAvailable=$product->quantity}
{else}
    {addJsDef quantityAvailable=0}
{/if}
{addJsDef quantitiesDisplayAllowed=$display_qties|boolval}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'percentage'}
    {addJsDef reduction_percent=$product->specificPrice.reduction*100|floatval}
{else}
    {addJsDef reduction_percent=0}
{/if}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'amount'}
    {addJsDef reduction_price=$product->specificPrice.reduction|floatval}
{else}
    {addJsDef reduction_price=0}
{/if}
{if $product->specificPrice && $product->specificPrice.price}
    {addJsDef specific_price=$product->specificPrice.price|floatval}
{else}
    {addJsDef specific_price=0}
{/if}
{addJsDef specific_currency=($product->specificPrice && $product->specificPrice.id_currency)|boolval} {* TODO: remove if always false *}
{addJsDef stock_management=$PS_STOCK_MANAGEMENT|intval}
{addJsDef taxRate=$tax_rate|floatval}
{addJsDefL name=doesntExist}{l s='This combination does not exist for this product. Please select another combination.' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMore}{l s='This product is no longer in stock' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMoreBut}{l s='with those attributes but is available with others.' js=1}{/addJsDefL}
{addJsDefL name=fieldRequired}{l s='Please fill in all the required fields before saving your customization.' js=1}{/addJsDefL}
{addJsDefL name=uploading_in_progress}{l s='Uploading in progress, please be patient.' js=1}{/addJsDefL}
{addJsDefL name='product_fileDefaultHtml'}{l s='No file selected' js=1}{/addJsDefL}
{addJsDefL name='product_fileButtonHtml'}{l s='Choose File' js=1}{/addJsDefL}
{/strip}
{/if}
