<?php /* Smarty version Smarty-3.1.19, created on 2017-05-09 11:51:10
         compiled from "/var/www/html/prestashop/admin1406yenrh/themes/default/template/content.tpl" */ ?>
<?php /*%%SmartyHeaderCode:5095954925911910e367963-85380505%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'aaf24a1c8c81d302eee3ec70510b25274a1c4951' => 
    array (
      0 => '/var/www/html/prestashop/admin1406yenrh/themes/default/template/content.tpl',
      1 => 1490717103,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5095954925911910e367963-85380505',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5911910e369217_65603940',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5911910e369217_65603940')) {function content_5911910e369217_65603940($_smarty_tpl) {?>
<div id="ajax_confirmation" class="alert alert-success hide"></div>

<div id="ajaxBox" style="display:none"></div>


<div class="row">
	<div class="col-lg-12">
		<?php if (isset($_smarty_tpl->tpl_vars['content']->value)) {?>
			<?php echo $_smarty_tpl->tpl_vars['content']->value;?>

		<?php }?>
	</div>
</div><?php }} ?>
