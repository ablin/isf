<?php /*%%SmartyHeaderCode:19358901555911910f404897-42945411%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b37bc0d28acc544a619cd3f6346e18f53361aa25' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/modules/blocksearch/blocksearch-top.tpl',
      1 => 1490717104,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19358901555911910f404897-42945411',
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_591369c14c39e4_96677694',
  'has_nocache_code' => false,
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_591369c14c39e4_96677694')) {function content_591369c14c39e4_96677694($_smarty_tpl) {?><!-- Block search module TOP -->
<div id="search_block_top" class="col-sm-4 clearfix">
	<form id="searchbox" method="get" action="//localhost/prestashop/recherche" >
		<input type="hidden" name="controller" value="search" />
		<input type="hidden" name="orderby" value="position" />
		<input type="hidden" name="orderway" value="desc" />
		<input class="search_query form-control" type="text" id="search_query_top" name="search_query" placeholder="Rechercher" value="" />
		<button type="submit" name="submit_search" class="btn btn-default button-search">
			<span>Rechercher</span>
		</button>
	</form>
</div>
<!-- /Block search module TOP --><?php }} ?>
