<?php /* Smarty version Smarty-3.1.19, created on 2017-03-28 18:14:42
         compiled from "/var/www/html/prestashop/themes/default-bootstrap/category-count.tpl" */ ?>
<?php /*%%SmartyHeaderCode:149063156758da8bf2ba7105-26809745%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd31d4cb9022e0ff58d24625b0bb301ac0105470b' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/category-count.tpl',
      1 => 1490717104,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '149063156758da8bf2ba7105-26809745',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'category' => 0,
    'nb_products' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_58da8bf2bae7c0_21925732',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58da8bf2bae7c0_21925732')) {function content_58da8bf2bae7c0_21925732($_smarty_tpl) {?>
<span class="heading-counter"><?php if ((isset($_smarty_tpl->tpl_vars['category']->value)&&$_smarty_tpl->tpl_vars['category']->value->id==1)||(isset($_smarty_tpl->tpl_vars['nb_products']->value)&&$_smarty_tpl->tpl_vars['nb_products']->value==0)) {?><?php echo smartyTranslate(array('s'=>'There are no products in this category.'),$_smarty_tpl);?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['nb_products']->value)&&$_smarty_tpl->tpl_vars['nb_products']->value==1) {?><?php echo smartyTranslate(array('s'=>'There is 1 product.'),$_smarty_tpl);?>
<?php } elseif (isset($_smarty_tpl->tpl_vars['nb_products']->value)) {?><?php echo smartyTranslate(array('s'=>'There are %d products.','sprintf'=>$_smarty_tpl->tpl_vars['nb_products']->value),$_smarty_tpl);?>
<?php }?><?php }?></span>
<?php }} ?>
