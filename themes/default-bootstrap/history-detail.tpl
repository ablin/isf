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
    <span class="navigation_page">{l s='Order detail'} {$id}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}

{if $order == 1}
    <p class="order-confirm">{l s='Your order has been successfully registered. You will find the details below:'}</p>
{/if}

<h1 class="page-heading bottom-indent">{l s='Order detail'} {$id}</h1>

{if isset($lignes)}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{l s='Quantity'}</th>
                <th>{l s='Reference'}</th>
                <th>{l s='Description'}</th>
                <th>{l s='Sub reference 1'}</th>
                <th>{l s='Sub reference 2'}</th>
                <th>{l s='Unit price'}</th>
                <th>{l s='Discount'}</th>
                <th>{l s='Amount'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$lignes item=ligne}
                <tr>
                    <td>{$ligne->qte}</td>
                    <td>{$ligne->ref}</td>
                    <td>{$ligne->des}</td>
                    <td>{$ligne->sref1}</td>
                    <td>{$ligne->sref2}</td>
                    <td>{displayPrice price=$ligne->Pub}</td>
                    <td>{$ligne->Rem}</td>
                    <td>{displayPrice price=$ligne->Mont}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{elseif isset($submit)}
    <h4 align="center">{l s='No result'}</h4>
{/if}

<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$base_dir}">
            <span><i class="icon-chevron-left"></i> {l s='Home'}</span>
        </a>
    </li>
</ul>