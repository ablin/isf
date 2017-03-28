<?php /* Smarty version Smarty-3.1.19, created on 2017-03-25 18:00:48
         compiled from "/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/list/list_action_edit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1127515258d6a240899ab6-06391721%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e7ef115576e464ff912853ecd4f9488773e9266c' => 
    array (
      0 => '/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/list/list_action_edit.tpl',
      1 => 1465999274,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1127515258d6a240899ab6-06391721',
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
  'unifunc' => 'content_58d6a24089c987_87644522',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58d6a24089c987_87644522')) {function content_58d6a24089c987_87644522($_smarty_tpl) {?>
<a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['href']->value, ENT_QUOTES, 'UTF-8', true);?>
" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>
" class="edit">
	<i class="icon-pencil"></i> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['action']->value, ENT_QUOTES, 'UTF-8', true);?>

</a><?php }} ?>
