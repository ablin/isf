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
	<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
		{l s='My account'}
	</a>
	<span class="navigation-pipe">{$navigationPipe}</span>
	<span class="navigation_page">{l s='Order history'}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}
<h1 class="page-heading bottom-indent">{l s='Order history'}</h1>
<p class="info-title">{l s='Here are the orders you\'ve placed since your account was created.'}</p>
{if $slowValidation}
	<p class="alert alert-warning">{l s='If you have just placed an order, it may take a few minutes for it to be validated. Please refresh this page if your order is missing.'}</p>
{/if}

<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post">

    <div class="block-center" id="block-history">
        <label for="picod">{l s='Type of part:'}</label>
        <select name="picod" class="form-control">
            <option value="1" {if isset($picod) && $picod == 1}selected="selected"{/if}>Devis</option>
            <option value="2" {if isset($picod) && $picod == 2}selected="selected"{/if}>Commande</option>
            <option value="3" {if isset($picod) && $picod == 3}selected="selected"{/if}>Bon de livraison</option>
            <option value="4" {if isset($picod) && $picod == 4}selected="selected"{/if}>Facture</option>
        </select>
        <br />
        <label for="Date_Month">{l s='BeginPeriod:'}</label>
        {if isset($begin_Year)}
            {assign var="begin" value="`$begin_Day`-`$begin_Month`-`$begin_Year`"}
        {else}
            {assign var="begin" value="`$smarty.now|date_format:"%D%M%Y"`"}
        {/if}
        {html_select_date time=$begin start_year='2010' reverse_years=true display_days=true display_months=true field_order='DMY' prefix='begin_' day_extra='class="form-control form-control-select"' month_extra='class="form-control form-control-select"' year_extra='class="form-control form-control-select"'}
        <br />
        <label>{l s='EndPeriod:'}</label>
        {if isset($end_Year)}
            {assign var="end" value="`$end_Day`-`$end_Month`-`$end_Year`"}
        {else}
            {assign var="end" value="`$smarty.now|date_format:"%D%M%Y"`"}
        {/if}
        {html_select_date time=$end start_year='2010' reverse_years=true display_days=true display_months=true field_order='DMY' prefix='end_' day_extra='class="form-control form-control-select"' month_extra='class="form-control form-control-select"' year_extra='class="form-control form-control-select"'}
        <br />
        <input type="submit" class="button btn btn-default " value="{l s='Submit'}"></input>
        <br />
        <br />
    </div>

    <input type="hidden" name="submit">

</form>

{if isset($entetes)}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{l s='Number'}</th>
                <th>{l s='Date'}</th>
                <th>{l s='Description'}</th>
                <th>{l s='Amount'}</th>
                <th>{l s='Shipping'}</th>
                <th>{l s='Document'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$entetes item=entete}
                {$params = ['id' => $entete->numero, 'picod' => $picod]}
                <tr>
                    <td><a target="_blank" href="{$link->getPageLink('history-detail', true, NULL, $params)|escape:'html':'UTF-8'}">{$entete->numero}</a></td>
                    <td>{$entete->date}</td>
                    <td>{$entete->description}</td>
                    <td>{displayPrice price=$entete->montant}</td>
                    <td>{displayPrice price=$entete->montantPort}</td>
                    <td><i class="icon-file-text"></i> <a target="_blank" href="{$link->getPageLink('history-print', true, NULL, $params)|escape:'html':'UTF-8'}">{l s='Print'}</a></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{elseif isset($submit)}
    <h4 align="center">{l s='No result'}</h4>
{/if}

<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-chevron-left"></i> {l s='Back to Your Account'}
			</span>
		</a>
	</li>
	<li>
		<a class="btn btn-default button button-small" href="{$base_dir}">
			<span><i class="icon-chevron-left"></i> {l s='Home'}</span>
		</a>
	</li>
</ul>
