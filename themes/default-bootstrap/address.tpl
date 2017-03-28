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
{capture name=path}{l s='Your addresses'}{/capture}
<div class="box">
	<h1 class="page-subheading">{l s='Your addresses'}</h1>
	<p class="info-title">
		{if isset($id_address) && (isset($smarty.post.alias) || isset($address->alias))}
			{l s='Modify address'}
			{if isset($smarty.post.alias)}
				"{$smarty.post.alias}"
			{else}
				{if isset($address->alias)}"{$address->alias|escape:'html':'UTF-8'}"{/if}
			{/if}
		{else}
			{l s='To add a new address, please fill out the form below.'}
		{/if}
	</p>
	{include file="$tpl_dir./errors.tpl"}
	<p class="required"><sup>*</sup>{l s='Required field'}</p>
	<form action="{$link->getPageLink('address', true)|escape:'html':'UTF-8'}" method="post" class="std" id="add_address">
		<!--h3 class="page-subheading">{if isset($id_address)}{l s='Your address'}{else}{l s='New address'}{/if}</h3-->
		{foreach from=$ordered_adr_fields item=field_name}
			{if $field_name eq 'alias'}
                <div class="required form-group">
                    <label for="alias">{l s='Alias'} <sup>*</sup></label>
                    <input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" name="alias" id="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else}{if isset($address->alias)}{$address->alias|escape:'html':'UTF-8'}{/if}{/if}" />
                </div>
            {/if}
            {if $field_name eq 'firstname'}
				<div class="required form-group">
					<label for="firstname">{l s='First name'} <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" name="firstname" id="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{else}{if isset($address->firstname)}{$address->firstname|escape:'html':'UTF-8'}{/if}{/if}" />
				</div>
			{/if}
			{if $field_name eq 'lastname'}
				<div class="required form-group">
					<label for="lastname">{l s='Last name'} <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{else}{if isset($address->lastname)}{$address->lastname|escape:'html':'UTF-8'}{/if}{/if}" />
				</div>
			{/if}
			{if $field_name eq 'address1'}
				<div class="required form-group">
					<label for="address1">{l s='Address'} <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="address1" name="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{if isset($address->address1)}{$address->address1|escape:'html':'UTF-8'}{/if}{/if}" />
				</div>
			{/if}
			{if $field_name eq 'address2'}
				<div class="required form-group">
					<label for="address2">{l s='Address (Line 2)'}{if isset($required_fields) && in_array($field_name, $required_fields)} <sup>*</sup>{/if}</label>
					<input class="validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="address2" name="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{else}{if isset($address->address2)}{$address->address2|escape:'html':'UTF-8'}{/if}{/if}" />
				</div>
			{/if}
            {if $field_name eq 'address3'}
                <div class="required form-group">
                    <label for="address3">{l s='Address (Line 3)'}{if isset($required_fields) && in_array($field_name, $required_fields)} <sup>*</sup>{/if}</label>
                    <input class="validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="address3" name="address3" value="{if isset($smarty.post.address3)}{$smarty.post.address3}{else}{if isset($address->address3)}{$address->address3|escape:'html':'UTF-8'}{/if}{/if}" />
                </div>
            {/if}
            {if $field_name eq 'locality'}
                <div class="required form-group">
                    <label for="locality">{l s='Locality'}{if isset($required_fields) && in_array($field_name, $required_fields)} <sup>*</sup>{/if}</label>
                    <input class="validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="locality" name="locality" value="{if isset($smarty.post.locality)}{$smarty.post.locality}{else}{if isset($address->locality)}{$address->locality|escape:'html':'UTF-8'}{/if}{/if}" />
                </div>
            {/if}
			{if $field_name eq 'postcode'}
				<div class="required form-group">
					<label for="postcode">{l s='Zip/Postal Code'} <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="postcode" name="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{if isset($address->postcode)}{$address->postcode|escape:'html':'UTF-8'}{/if}{/if}" />
				</div>
			{/if}
			{if $field_name eq 'city'}
				<div class="required form-group">
					<label for="city">{l s='City'} <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{else}{if isset($address->city)}{$address->city|escape:'html':'UTF-8'}{/if}{/if}" maxlength="64" />
				</div>
				{* if customer hasn't update his layout address, country has to be verified but it's deprecated *}
			{/if}
			{if $field_name eq 'country' || $field_name eq 'Country:iso_code'}
				<div class="required form-group">
					<label for="id_country">{l s='Country'} <sup>*</sup></label>
					<select id="id_country" class="form-control" name="id_country">{$countries_list}</select>
				</div>
			{/if}
		{/foreach}
		<p class="submit2">
			{if isset($id_address)}<input type="hidden" name="id_address" value="{$id_address|intval}" />{/if}
			{if isset($back)}<input type="hidden" name="back" value="{$back}" />{/if}
			{if isset($mod)}<input type="hidden" name="mod" value="{$mod}" />{/if}
			{if isset($select_address)}<input type="hidden" name="select_address" value="{$select_address|intval}" />{/if}
            {if isset($address->alias)}<input type="hidden" name="previous_alias" value="{$address->alias}" />{/if}
			<input type="hidden" name="token" value="{$token}" />
			<button type="submit" name="submitAddress" id="submitAddress" class="btn btn-default button button-medium">
				<span>
					{l s='Save'}
					<i class="icon-chevron-right right"></i>
				</span>
			</button>
		</p>
	</form>
</div>
<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-defaul button button-small" href="{$link->getPageLink('addresses', true)|escape:'html':'UTF-8'}">
			<span><i class="icon-chevron-left"></i> {l s='Back to your addresses'}</span>
		</a>
	</li>
</ul>
