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
{capture name=path}
	{if !isset($email_create)}{l s='Authentication'}{else}
		<a href="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Authentication'}">{l s='Authentication'}</a>
		<span class="navigation-pipe">{$navigationPipe}</span>{l s='Create your account'}
	{/if}
{/capture}
<h1 class="page-heading">{if !isset($email_create)}{l s='Authentication'}{else}{l s='Create an account'}{/if}</h1>
{if isset($back) && preg_match("/^http/", $back)}{assign var='current_step' value='login'}{include file="$tpl_dir./order-steps.tpl"}{/if}
{include file="$tpl_dir./errors.tpl"}
{assign var='stateExist' value=false}
{assign var="postCodeExist" value=false}
{assign var="dniExist" value=false}
<div class="row">
	<div class="col-xs-12">
		<form action="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}" method="post" id="login_form" class="box">
			<div class="form_content clearfix">
				<div class="form-group">
					<label for="email">{l s='Email address'}</label>
					<input class="is_required validate account_input form-control" data-validate="isEmail" type="email" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|stripslashes}{/if}" />
				</div>
				<div class="form-group">
					<label for="passwd">{l s='Password'}</label>
					<input class="is_required validate account_input form-control" type="password" data-validate="isPasswd" id="passwd" name="passwd" value="" />
				</div>
				<p class="submit">
					{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'html':'UTF-8'}" />{/if}
					<button type="submit" id="SubmitLogin" name="SubmitLogin" class="button btn btn-default button-medium">
						<span>
							<i class="icon-lock left"></i>
							{l s='Sign in'}
						</span>
					</button>
				</p>
			</div>
		</form>
	</div>
</div>
{strip}
{if isset($smarty.post.id_state) && $smarty.post.id_state}
	{addJsDef idSelectedState=$smarty.post.id_state|intval}
{elseif isset($address->id_state) && $address->id_state}
	{addJsDef idSelectedState=$address->id_state|intval}
{else}
	{addJsDef idSelectedState=false}
{/if}
{if isset($smarty.post.id_state_invoice) && isset($smarty.post.id_state_invoice) && $smarty.post.id_state_invoice}
	{addJsDef idSelectedStateInvoice=$smarty.post.id_state_invoice|intval}
{else}
	{addJsDef idSelectedStateInvoice=false}
{/if}
{if isset($smarty.post.id_country) && $smarty.post.id_country}
	{addJsDef idSelectedCountry=$smarty.post.id_country|intval}
{elseif isset($address->id_country) && $address->id_country}
	{addJsDef idSelectedCountry=$address->id_country|intval}
{else}
	{addJsDef idSelectedCountry=false}
{/if}
{if isset($smarty.post.id_country_invoice) && isset($smarty.post.id_country_invoice) && $smarty.post.id_country_invoice}
	{addJsDef idSelectedCountryInvoice=$smarty.post.id_country_invoice|intval}
{else}
	{addJsDef idSelectedCountryInvoice=false}
{/if}
{if isset($countries)}
	{addJsDef countries=$countries}
{/if}
{if isset($vatnumber_ajax_call) && $vatnumber_ajax_call}
	{addJsDef vatnumber_ajax_call=$vatnumber_ajax_call}
{/if}
{if isset($email_create) && $email_create}
	{addJsDef email_create=$email_create|boolval}
{else}
	{addJsDef email_create=false}
{/if}
{/strip}
