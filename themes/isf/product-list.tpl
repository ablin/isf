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
{if isset($products) && $products}
	{*define number of products per line in other page for desktop*}
	{if $page_name !='index' && $page_name !='product'}
		{assign var='nbItemsPerLine' value=3}
		{assign var='nbItemsPerLineTablet' value=2}
		{assign var='nbItemsPerLineMobile' value=3}
	{else}
		{assign var='nbItemsPerLine' value=4}
		{assign var='nbItemsPerLineTablet' value=3}
		{assign var='nbItemsPerLineMobile' value=2}
	{/if}
	{*define numbers of product per line in other page for tablet*}
	{assign var='nbLi' value=$products|@count}
	{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
	{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
	<!-- Products list -->
	<ul{if isset($id) && $id} id="{$id}"{/if} class="product_list grid row{if isset($class) && $class} {$class}{/if}">
	{foreach from=$products item=product name=products}
		{math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
		{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
		{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
		{if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
		{if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
		{if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
		<li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-xs-12 col-sm-4 col-md-3{else} col-xs-12 col-sm-6 col-md-4{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModuloMobile)} last-mobile-line{/if}">
			<div class="product-container" itemscope itemtype="https://schema.org/Product">
				<div class="left-block">
					<div class="product-image-container">
						<a class="product_img_link" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
							{if preg_match("/-default/", $product.id_image) && !preg_match("/-default/", {$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default', $product.id_product)|escape:'html':'UTF-8'})}
								<div class="filigrane">
									<span>
										{l s='Non contractual photo'}
									</span>
								</div>
							{/if}
							<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default', $product.id_product)|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
						</a>
						{if isset($quick_view) && $quick_view}
							<div class="quick-view-wrapper-mobile">
							<a class="quick-view-mobile" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
								<i class="icon-eye-open"></i>
							</a>
						</div>
						<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
							<span>{l s='Quick view'}</span>
						</a>
						{/if}
						{if isset($product.new) && $product.new == 1}
							<a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
								<span class="new-label">{l s='New'}</span>
							</a>
						{/if}
						{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
							<a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
								<span class="sale-label">{l s='Sale!'}</span>
							</a>
						{/if}
					</div>
					{if isset($product.is_virtual) && !$product.is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
					{hook h="displayProductPriceBlock" product=$product type="weight"}
				</div>
				<div class="right-block">
					<h5 itemprop="name">
						{if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
						<a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
							{$product.name|escape:'html':'UTF-8'}
						</a>
					</h5>
					{capture name='displayProductListReviews'}{hook h='displayProductListReviews' product=$product}{/capture}
					{if $smarty.capture.displayProductListReviews}
						<div class="hook-reviews">
						{hook h='displayProductListReviews' product=$product}
						</div>
					{/if}
                    <p class="product-desc" itemprop="description">
                        {$product.description_short|stripslashes|truncate:360:'...'}
                    </p>
                    <p class="product-reference" itemprop="reference">
                        <label>{l s='Reference:'}</label>
                        {$product.reference|strip_tags:'UTF-8'}
                    </p>
                    {if isset($is_logged) && $is_logged}
                        <p class="product-stock" itemprop="stock">
                            <label>{l s='Stock:'}</label>
    						{if $product.reference|array_key_exists:$references && 'total_stock'|array_key_exists:$references[$product.reference] && $references[$product.reference]['total_stock'] != -1}
    							<span class="availability">
                                    {if $references[$product.reference]['total_stock'] > 0}
                                        <span id="availability_value" class="label label-success">
                                            {$references[$product.reference]['total_stock']}
                                        </span>
                                    {else}
                                        <span id="availability_value" class="label label-danger">
                                            0
                                        </span>
                                    {/if}
                                </span>
    						{/if}
    						{if $product.reference|array_key_exists:$references && 'total_dispo'|array_key_exists:$references[$product.reference] && $references[$product.reference]['total_dispo'] != -1}
                                <span class="availability">
                                    {if $references[$product.reference]['total_dispo'] > 0}
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
    						{if $product.reference|array_key_exists:$references && 'total_jauge'|array_key_exists:$references[$product.reference] && $references[$product.reference]['total_jauge'] != -1}
    							<span class="availability">
    								<span class="availability-gauge{if $references[$product.reference]['total_jauge'] == 1} orange{elseif $references[$product.reference]['total_jauge'] > 0} green{/if}"></span>
    								<span class="availability-gauge{if $references[$product.reference]['total_jauge'] > 1} green{/if}"></span>
    								<span class="availability-gauge{if $references[$product.reference]['total_jauge'] > 2} green{/if}"></span>
    							</span>
    						{/if}
                        </p>
                    {/if}
					<div class="button-container">
						{if !$PS_CATALOG_MODE}
							{if $product.reference|array_key_exists:$references && 'tarif'|array_key_exists:$references[$product.reference] && $references[$product.reference]['tarif'] > 0 && $references[$product.reference]['nb_tarif'] > 0}
								<p class="product-tarifs">
									{convertPrice price=$references[$product.reference]['tarif']}
									{if $references[$product.reference]['nb_tarif'] > 1}
										<span class="product-tarifs-infos">{l s='Declining price according to qty:'}</span>
									{/if}
								</p>
							{else}
							    <p class="product-tarifs">
                                    <span class="product-tarifs-infos">{l s='Price on demand'}</span>
                                </p>
							{/if}
						{else}
							<span class="button ajax_add_to_cart_button btn btn-default disabled">
								<span>{l s='Add to cart'}</span>
							</span>
						{/if}
						<a class="button lnk_view btn btn-default" href="{$product.link|escape:'html':'UTF-8'}" title="{l s='View'}">
							<span>{if (isset($product.customization_required) && $product.customization_required)}{l s='Customize'}{else}{l s='More'}{/if}</span>
						</a>
					</div>
					{if isset($product.color_list)}
						<div class="color-list-container">{$product.color_list}</div>
					{/if}
					<div class="product-flags">
						{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
							{if isset($product.online_only) && $product.online_only}
								<span class="online_only">{l s='Online only'}</span>
							{/if}
						{/if}
						{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
							{elseif isset($product.reduction) && $product.reduction && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
								<span class="discount">{l s='Reduced price!'}</span>
							{/if}
					</div>
				</div>
				{if $page_name != 'index'}
					<div class="functional-buttons clearfix">
						{hook h='displayProductListFunctionalButtons' product=$product}
						{if isset($comparator_max_item) && $comparator_max_item}
							<div class="compare">
								<a class="add_to_compare" href="{$product.link|escape:'html':'UTF-8'}" data-id-product="{$product.id_product}">{l s='Add to Compare'}</a>
							</div>
						{/if}
					</div>
				{/if}
			</div><!-- .product-container> -->
		</li>
	{/foreach}
	</ul>
{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDefL name=Reference}{l s='Reference:' js=1}{/addJsDefL}
{addJsDefL name=Stock}{l s='Stock:' js=1}{/addJsDefL}
{addJsDefL name=Available}{l s='Available' js=1}{/addJsDefL}
{addJsDefL name=Unavailable}{l s='Unavailable' js=1}{/addJsDefL}
{addJsDefL name=Declining_price_according_to_qty}{l s='Declining price according to qty:' js=1}{/addJsDefL}
{addJsDefL name=Price_on_demand}{l s='Price on demand' js=1}{/addJsDefL}
{addJsDefL name=View}{l s='View' js=1}{/addJsDefL}
{addJsDefL name=More}{l s='More' js=1}{/addJsDefL}
{addJsDef comparator_max_item=$comparator_max_item}
{addJsDef comparedProductsIds=$compared_products}
{/if}
