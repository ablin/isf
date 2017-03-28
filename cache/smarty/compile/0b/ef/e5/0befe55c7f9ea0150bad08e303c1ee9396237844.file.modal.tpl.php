<?php /* Smarty version Smarty-3.1.19, created on 2017-03-28 18:14:37
         compiled from "/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/modules_list/modal.tpl" */ ?>
<?php /*%%SmartyHeaderCode:182834618158da8bede3e3d2-60369052%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0befe55c7f9ea0150bad08e303c1ee9396237844' => 
    array (
      0 => '/var/www/html/prestashop/admin1406yenrh/themes/default/template/helpers/modules_list/modal.tpl',
      1 => 1490717103,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '182834618158da8bede3e3d2-60369052',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_58da8bede3f412_68305228',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58da8bede3f412_68305228')) {function content_58da8bede3f412_68305228($_smarty_tpl) {?><div class="modal fade" id="modules_list_container">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title"><?php echo smartyTranslate(array('s'=>'Recommended Modules and Services'),$_smarty_tpl);?>
</h3>
			</div>
			<div class="modal-body">
				<div id="modules_list_container_tab_modal" style="display:none;"></div>
				<div id="modules_list_loader"><i class="icon-refresh icon-spin"></i></div>
			</div>
		</div>
	</div>
</div>
<?php }} ?>
