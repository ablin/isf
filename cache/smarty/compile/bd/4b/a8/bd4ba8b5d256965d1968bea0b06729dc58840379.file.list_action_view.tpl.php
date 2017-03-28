<?php /* Smarty version Smarty-3.1.19, created on 2017-03-25 18:00:48
         compiled from "/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/list/list_action_view.tpl" */ ?>
<?php /*%%SmartyHeaderCode:47928338858d6a24089f0e4-36112260%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bd4ba8b5d256965d1968bea0b06729dc58840379' => 
    array (
      0 => '/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/list/list_action_view.tpl',
      1 => 1465999274,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '47928338858d6a24089f0e4-36112260',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'href' => 0,
    'action' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_58d6a2408a4295_69396279',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58d6a2408a4295_69396279')) {function content_58d6a2408a4295_69396279($_smarty_tpl) {?>
<a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['href']->value, ENT_QUOTES, 'UTF-8', true);?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>
" >
	<i class="icon-search-plus"></i> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>

</a><?php }} ?>
