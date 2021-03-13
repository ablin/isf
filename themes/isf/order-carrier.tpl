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
{if !$opc}
	{capture name=path}{l s='Shipping:'}{/capture}
	{assign var='current_step' value='shipping'}
	<div id="carrier_area">
		<h1 class="page-heading">{l s='Shipping:'}</h1>
		{include file="$tpl_dir./order-steps.tpl"}
		{include file="$tpl_dir./errors.tpl"}
		<form id="form" action="{$link->getPageLink('order', true, NULL, "{if $multi_shipping}multi-shipping={$multi_shipping}{/if}")|escape:'html':'UTF-8'}" method="post" name="carrier_area">
{else}
	<div id="carrier_area" class="opc-main-block">
		<h1 class="page-heading step-num"><span>2</span> {l s='Delivery methods'}</h1>
			<div id="opc_delivery_methods" class="opc-main-block">
				<div id="opc_delivery_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}
<div class="order_carrier_content box">
	{if isset($virtual_cart) && $virtual_cart}
		<input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
        <p class="alert alert-warning">{l s='No carrier is needed for this order.'}</p>
	{else}
		<div id="HOOK_BEFORECARRIER">
			{if isset($carriers) && isset($HOOK_BEFORECARRIER)}
				{$HOOK_BEFORECARRIER}
			{/if}
		</div>
		{if isset($isVirtualCart) && $isVirtualCart}
			<p class="alert alert-warning">{l s='No carrier is needed for this order.'}</p>
		{else}
			<div class="delivery_options_address">
                <p class="carrier_title">
                    {if isset($address_label)}
                        {l s='Choose a shipping option for this address:'} {$address_label}
                    {else}
                        {l s='Choose a shipping option'}
                    {/if}
                </p>
                <div class="delivery_options">
                    {foreach $carriers as $carrier}
                        <div class="delivery_option {if ($carrier@index % 2)}alternate_{/if}item">
                            <div>
                                <table class="resume table table-bordered">
                                    <tr>
                                        <td class="delivery_option_radio">
                                            <input id="delivery_option_{$carrier@index}" class="delivery_option_radio" type="radio" name="delivery_option" value="{$carrier.code}" required />
                                        </td>
                                        <td>
                                            <strong>{$carrier.label|escape:'htmlall':'UTF-8'}</strong><br />{$carrier.texte|escape:'UTF-8'}
                                        </td>
                                        <td class="delivery_option_price">
                                            <div class="delivery_option_price">
                                                {if $carrier.tarif > 0}
                                                    {convertPrice price=$carrier.tarif}
                                                {else}
                                                    {l s='Free'}
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div> <!-- end delivery_option -->
                    {/foreach}
                </div> <!-- end delivery_options -->
            </div> <!-- end delivery_options_address -->
            {if $opc}
                <p class="carrier_title">{l s='Leave a message'}</p>
                <div>
                    <p>{l s='If you would like to add a comment about your order, please write it in the field below.'}</p>
                    <textarea class="form-control" cols="120" rows="2" name="message" id="message">{strip}
                        {if isset($oldMessage)}{$oldMessage|escape:'html':'UTF-8'}{/if}
                    {/strip}</textarea>
                </div>
            {/if}
            {if $recyclablePackAllowed}
                <p class="carrier_title">{l s='Recyclable Packaging'}</p>
                <div class="checkbox recyclable">
                    <label for="recyclable">
                        <input type="checkbox" name="recyclable" id="recyclable" value="1"{if $recyclable == 1} checked="checked"{/if} />
                        {l s='I would like to receive my order in recycled packaging.'}
                    </label>
                </div>
            {/if}
            {if $giftAllowed}
                {if $opc}
                    <hr style="" />
                {/if}
                <p class="carrier_title">{l s='Gift'}</p>
                <div class="checkbox gift">
                    <input type="checkbox" name="gift" id="gift" value="1"{if $cart->gift == 1} checked="checked"{/if} />
                    <label for="gift">
                        {l s='I would like my order to be gift wrapped.'}
                        {if $gift_wrapping_price > 0}
                            &nbsp;<i>({l s='Additional cost of'}
                            <span class="price" id="gift-price">
                                {if $priceDisplay == 1}
                                    {convertPrice price=$total_wrapping_tax_exc_cost}
                                {else}
                                    {convertPrice price=$total_wrapping_cost}
                                {/if}
                            </span>
                            {if $use_taxes && $display_tax_label}
                                {if $priceDisplay == 1}
                                    {l s='(tax excl.)'}
                                {else}
                                    {l s='(tax incl.)'}
                                {/if}
                            {/if})
                            </i>
                        {/if}
                    </label>
                </div>
                <p id="gift_div">
                    <label for="gift_message">{l s='If you\'d like, you can add a note to the gift:'}</label>
                    <textarea rows="2" cols="120" id="gift_message" class="form-control" name="gift_message">{$cart->gift_message|escape:'html':'UTF-8'}</textarea>
                </p>
            {/if}
        {/if}
    {/if}
    {if $conditions && $cms_id && (! isset($advanced_payment_api) || !$advanced_payment_api)}
        {if $opc}
            <hr style="" />
        {/if}
        {if isset($override_tos_display) && $override_tos_display}
            {$override_tos_display}
        {else}
            <div class="box">
                <p class="checkbox">
                    <input type="checkbox" name="cgv" required id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
                    <label for="cgv">{l s='I agree to the terms of service and will adhere to them unconditionally.'}</label>
                    <a href="{$link_conditions|escape:'html':'UTF-8'}" class="iframe" rel="nofollow">{l s='(Read the Terms of Service)'}</a>
                </p>
            </div>
        {/if}
    {/if}
</div> <!-- end delivery_options_address -->
{if !$opc}
        <p class="cart_navigation clearfix">
            <input type="hidden" name="step" value="3" />
            <input type="hidden" name="back" value="{$back}" />
            {if !$is_guest}
                {if $back}
                    <a href="{$link->getPageLink('order', true, NULL, "step=1&back={$back}{if $multi_shipping}&multi-shipping={$multi_shipping}{/if}")|escape:'html':'UTF-8'}" title="{l s='Previous'}" class="button-exclusive btn btn-default">
                        <i class="icon-chevron-left"></i>
                        {l s='Continue shopping'}
                    </a>
                {else}
                    <a href="{$link->getPageLink('order', true, NULL, "step=1{if $multi_shipping}&multi-shipping={$multi_shipping}{/if}")|escape:'html':'UTF-8'}" title="{l s='Previous'}" class="button-exclusive btn btn-default">
                        <i class="icon-chevron-left"></i>
                        {l s='Continue shopping'}
                    </a>
                {/if}
            {else}
                <a href="{$link->getPageLink('order', true, NULL, "{if $multi_shipping}multi-shipping={$multi_shipping}{/if}")|escape:'html':'UTF-8'}" title="{l s='Previous'}" class="button-exclusive btn btn-default">
                    <i class="icon-chevron-left"></i>
                    {l s='Continue shopping'}
                </a>
            {/if}
            {if isset($virtual_cart) && $virtual_cart || (isset($carriers) && !empty($carriers))}
                <button type="submit" name="processCarrier" class="button btn btn-default standard-checkout button-medium">
                    <span>
                        {l s='Proceed to checkout'}
                        <i class="icon-chevron-right right"></i>
                    </span>
                </button>
            {/if}
        </p>
    </form>
{else}
</div> <!-- end opc_delivery_methods -->
{/if}
</div> <!-- end carrier_area -->
{strip}
{if !$opc}
	{addJsDef orderProcess='order'}
	{if isset($virtual_cart) && !$virtual_cart && $giftAllowed && $cart->gift == 1}
		{addJsDef cart_gift=true}
	{else}
		{addJsDef cart_gift=false}
	{/if}
	{addJsDef orderUrl=$link->getPageLink("order", true)|escape:'quotes':'UTF-8'}
	{addJsDefL name=txtProduct}{l s='Product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='Products' js=1}{/addJsDefL}
{/if}
{if $conditions}
	{addJsDefL name=msg_order_carrier}{l s='You must agree to the terms of service before continuing.' js=1}{/addJsDefL}
	{addJsDefL name=msg_choice_order_carrier}{l s='You must choose at least one option before continuing.' js=1}{/addJsDefL}
{/if}
{/strip}
