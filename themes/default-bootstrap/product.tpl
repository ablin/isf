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
		<!-- end left infos-->
		<!-- center infos -->
		<div class="pb-center-column col-xs-12 col-sm-9">
            <div class="col-sm-5">
    			{if $product->online_only}
    				<p class="online_only">{l s='Online only'}</p>
    			{/if}
    			<h1 itemprop="name">{$product->name|escape:'html':'UTF-8'}</h1>
                {if $product->description_short || $packItems|@count > 0}
                    <div id="short_description_block">
                        {if $product->description_short}
                            <div id="short_description_content" class="rte align_justify" itemprop="description">{$product->description_short}</div>
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
                <p id="product_manufacturer_name"{if empty($product->manufacturer_name) || !$product->manufacturer_name} style="display: none;"{/if}>
                    <label>{l s='Manufacturer:'} </label>
                    <span class="editable" itemprop="sku"{if !empty($product->manufacturer_name) && $product->manufacturer_name} content="{$product->manufacturer_name}"{/if}>{if !isset($groups)}{$product->manufacturer_name|escape:'html':'UTF-8'}{/if}</span>
                </p>
    			{if ($display_qties == 1 && !$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && $product->available_for_order)}
    				<!-- number of item in stock -->
    				<p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
    					<span id="quantityAvailable">{$product->quantity|intval}</span>
    					<span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='Item'}</span>
    					<span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='Items'}</span>
    				</p>
    			{/if}
                <p id="availability_statut">
                    <label>{l s='Stock:'}</label>
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
                </p>
                {if $tarif == 0}
                    <p id="price_on_demand">
                        <span class="label label-warning">{l s='Price on demand'}</span>
                    </p>
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
            {if $tarif > 0 && $sousRefs|@count > 0}
                <div class="col-sm-7">
                    <table id="productTarifs">
                        {foreach from=$sousRefs item=sousref}
                            {if 'tarifs'|array_key_exists:$sousref}
                                <tr>
                                    <th>{l s='Minimal quantity'}</th>
                                    <th>{l s='Price by'}</th>
                                    <th>{l s='Unit price'}</th>
                                    <th>{l s='Discount'}</th>
                                    <th>{l s='Net price'}</th>
                                </tr>
                                {foreach from=$sousref->tarifs item=line}
                                    <tr>
                                        <td>{$line->qte}</li>
                                        <td>{$line->ppar}</li>
                                        <td>{convertPrice price=$line->pub}</li>
                                        <td>{$line->remise}</li>
                                        <td>{convertPrice price=$line->pun}</li>
                                    <tr>
                                {/foreach}
                            {/if}
                        {/foreach}
                    </table>
                </div>
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
					<div class="box-cart-bottom">
						<div>
                            <!-- quantity wanted -->
                            <p id="quantity_wanted_p" class="quantity-wanted"{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                                <label>{l s='Quantity:'}</label>
                                <input type="text" name="qty" id="quantity_wanted" class="text" value="{if isset($quantityBackup)}{$quantityBackup|intval}{else}{if $product->minimal_quantity > 1}{$product->minimal_quantity}{else}1{/if}{/if}" />
                                <a href="#" data-field-qty="qty" class="btn btn-default button-minus product_quantity_down">
                                    <span><i class="icon-minus"></i></span>
                                </a>
                                <a href="#" data-field-qty="qty" class="btn btn-default button-plus product_quantity_up">
                                    <span><i class="icon-plus"></i></span>
                                </a>
                                <span class="clearfix"></span>
                            </p>
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
		{if isset($features) && $features}
			<!-- Data sheet -->
			<section class="page-product-box">
				<h3 class="page-product-heading">{l s='Data sheet'}</h3>
				<table class="table-data-sheet">
					{foreach from=$features item=feature}
					<tr class="{cycle values="odd,even"}">
						{if isset($feature.value)}
						<td>{$feature.name|escape:'html':'UTF-8'}</td>
						<td>{$feature.value|escape:'html':'UTF-8'}</td>
						{/if}
					</tr>
					{/foreach}
				</table>
			</section>
			<!--end Data sheet -->
		{/if}
		{if isset($product) && $product->description}
			<!-- More info -->
			<section class="page-product-box">
				<h3 class="page-product-heading">{l s='More info'}</h3>
				<!-- full description -->
				<div  class="rte">{$product->description}</div>
			</section>
			<!--end  More info -->
		{/if}
		{if isset($packItems) && $packItems|@count > 0}
		<section id="blockpack">
			<h3 class="page-product-heading">{l s='Pack content'}</h3>
			{include file="$tpl_dir./product-list.tpl" products=$packItems}
		</section>
		{/if}
		<!--HOOK_PRODUCT_TAB -->
		<section class="page-product-box">
			{$HOOK_PRODUCT_TAB}
			{if isset($HOOK_PRODUCT_TAB_CONTENT) && $HOOK_PRODUCT_TAB_CONTENT}{$HOOK_PRODUCT_TAB_CONTENT}{/if}
		</section>
		<!--end HOOK_PRODUCT_TAB -->
		{if isset($accessories) && $accessories}
			<!--Accessories -->
			<section class="page-product-box">
				<h3 class="page-product-heading">{l s='Accessories'}</h3>
				<div class="block products_block accessories-block clearfix">
					<div class="block_content">
						<ul id="bxslider" class="bxslider clearfix">
							{foreach from=$accessories item=accessory name=accessories_list}
								{if ($accessory.allow_oosp || $accessory.quantity_all_versions > 0 || $accessory.quantity > 0) && $accessory.available_for_order && !isset($restricted_country_mode)}
									{assign var='accessoryLink' value=$link->getProductLink($accessory.id_product, $accessory.link_rewrite, $accessory.category)}
									<li class="item product-box ajax_block_product{if $smarty.foreach.accessories_list.first} first_item{elseif $smarty.foreach.accessories_list.last} last_item{else} item{/if} product_accessories_description">
										<div class="product_desc">
											<a href="{$accessoryLink|escape:'html':'UTF-8'}" title="{$accessory.legend|escape:'html':'UTF-8'}" class="product-image product_image">
												<img class="lazyOwl" src="{$link->getImageLink($accessory.link_rewrite, $accessory.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{$accessory.legend|escape:'html':'UTF-8'}" width="{$homeSize.width}" height="{$homeSize.height}"/>
											</a>
											<div class="block_description">
												<a href="{$accessoryLink|escape:'html':'UTF-8'}" title="{l s='More'}" class="product_description">
													{$accessory.description_short|strip_tags|truncate:25:'...'}
												</a>
											</div>
										</div>
										<div class="s_title_block">
											<h5 itemprop="name" class="product-name">
												<a href="{$accessoryLink|escape:'html':'UTF-8'}">
													{$accessory.name|truncate:20:'...':true|escape:'html':'UTF-8'}
												</a>
											</h5>
											{if $accessory.show_price && !isset($restricted_country_mode) && !$PS_CATALOG_MODE}
											<span class="price">
												{if $priceDisplay != 1}
													{displayWtPrice p=$accessory.price}
												{else}
													{displayWtPrice p=$accessory.price_tax_exc}
												{/if}
												{hook h="displayProductPriceBlock" product=$accessory type="price"}
											</span>
											{/if}
											{hook h="displayProductPriceBlock" product=$accessory type="after_price"}
										</div>
										<div class="clearfix" style="margin-top:5px">
											{if !$PS_CATALOG_MODE && ($accessory.allow_oosp || $accessory.quantity > 0) && isset($add_prod_display) && $add_prod_display == 1}
												<div class="no-print">
													<a class="exclusive button ajax_add_to_cart_button" href="{$link->getPageLink('cart', true, NULL, "qty=1&amp;id_product={$accessory.id_product|intval}&amp;token={$static_token}&amp;add")|escape:'html':'UTF-8'}" data-id-product="{$accessory.id_product|intval}" title="{l s='Add to cart'}">
														<span>{l s='Add to cart'}</span>
													</a>
												</div>
											{/if}
										</div>
									</li>
								{/if}
							{/foreach}
						</ul>
					</div>
				</div>
			</section>
			<!--end Accessories -->
		{/if}
		{if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}
		<!-- description & features -->
		{if (isset($product) && $product->description) || (isset($features) && $features) || (isset($accessories) && $accessories) || (isset($HOOK_PRODUCT_TAB) && $HOOK_PRODUCT_TAB) || (isset($attachments) && $attachments) || isset($product) && $product->customizable}
			{if isset($attachments) && $attachments}
			<!--Download -->
			<section class="page-product-box">
				<h3 class="page-product-heading">{l s='Download'}</h3>
				{foreach from=$attachments item=attachment name=attachements}
					{if $smarty.foreach.attachements.iteration %3 == 1}<div class="row">{/if}
						<div class="col-lg-4">
							<h4><a href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id_attachment}")|escape:'html':'UTF-8'}">{$attachment.name|escape:'html':'UTF-8'}</a></h4>
							<p class="text-muted">{$attachment.description|escape:'html':'UTF-8'}</p>
							<a class="btn btn-default btn-block" href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id_attachment}")|escape:'html':'UTF-8'}">
								<i class="icon-download"></i>
								{l s="Download"} ({Tools::formatBytes($attachment.file_size, 2)})
							</a>
							<hr />
						</div>
					{if $smarty.foreach.attachements.iteration %3 == 0 || $smarty.foreach.attachements.last}</div>{/if}
				{/foreach}
			</section>
			<!--end Download -->
			{/if}
			{if isset($product) && $product->customizable}
			<!--Customization -->
			<section class="page-product-box">
				<h3 class="page-product-heading">{l s='Product customization'}</h3>
				<!-- Customizable products -->
				<form method="post" action="{$customizationFormTarget}" enctype="multipart/form-data" id="customizationForm" class="clearfix">
					<p class="infoCustomizable">
						{l s='After saving your customized product, remember to add it to your cart.'}
						{if $product->uploadable_files}
						<br />
						{l s='Allowed file formats are: GIF, JPG, PNG'}{/if}
					</p>
					{if $product->uploadable_files|intval}
						<div class="customizableProductsFile">
							<h5 class="product-heading-h5">{l s='Pictures'}</h5>
							<ul id="uploadable_files" class="clearfix">
								{counter start=0 assign='customizationField'}
								{foreach from=$customizationFields item='field' name='customizationFields'}
									{if $field.type == 0}
										<li class="customizationUploadLine{if $field.required} required{/if}">{assign var='key' value='pictures_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
											{if isset($pictures.$key)}
												<div class="customizationUploadBrowse">
													<img src="{$pic_dir}{$pictures.$key}_small" alt="" />
														<a href="{$link->getProductDeletePictureLink($product, $field.id_customization_field)|escape:'html':'UTF-8'}" title="{l s='Delete'}" >
															<img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="customization_delete_icon" width="11" height="13" />
														</a>
												</div>
											{/if}
											<div class="customizationUploadBrowse form-group">
												<label class="customizationUploadBrowseDescription">
													{if !empty($field.name)}
														{$field.name}
													{else}
														{l s='Please select an image file from your computer'}
													{/if}
													{if $field.required}<sup>*</sup>{/if}
												</label>
												<input type="file" name="file{$field.id_customization_field}" id="img{$customizationField}" class="form-control customization_block_input {if isset($pictures.$key)}filled{/if}" />
											</div>
										</li>
										{counter}
									{/if}
								{/foreach}
							</ul>
						</div>
					{/if}
					{if $product->text_fields|intval}
						<div class="customizableProductsText">
							<h5 class="product-heading-h5">{l s='Text'}</h5>
							<ul id="text_fields">
							{counter start=0 assign='customizationField'}
							{foreach from=$customizationFields item='field' name='customizationFields'}
								{if $field.type == 1}
									<li class="customizationUploadLine{if $field.required} required{/if}">
										<label for ="textField{$customizationField}">
											{assign var='key' value='textFields_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
											{if !empty($field.name)}
												{$field.name}
											{/if}
											{if $field.required}<sup>*</sup>{/if}
										</label>
										<textarea name="textField{$field.id_customization_field}" class="form-control customization_block_input" id="textField{$customizationField}" rows="3" cols="20">{strip}
											{if isset($textFields.$key)}
												{$textFields.$key|stripslashes}
											{/if}
										{/strip}</textarea>
									</li>
									{counter}
								{/if}
							{/foreach}
							</ul>
						</div>
					{/if}
					<p id="customizedDatas">
						<input type="hidden" name="quantityBackup" id="quantityBackup" value="" />
						<input type="hidden" name="submitCustomizedDatas" value="1" />
						<button class="button btn btn-default button button-small" name="saveCustomization">
							<span>{l s='Save'}</span>
						</button>
						<span id="ajax-loader" class="unvisible">
							<img src="{$img_ps_dir}loader.gif" alt="loader" />
						</span>
					</p>
				</form>
				<p class="clear required"><sup>*</sup> {l s='required fields'}</p>
			</section>
			<!--end Customization -->
			{/if}
		{/if}
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
