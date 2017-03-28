<?php /* Smarty version Smarty-3.1.19, created on 2017-03-25 17:55:18
         compiled from "/var/www/html/prestashop/themes/default-bootstrap/footer.tpl" */ ?>
<?php /*%%SmartyHeaderCode:74568648758d6a0f6bd30c0-08161649%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '402fbd8f2fa19cba7e6a1324b0cb5befe8331657' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/footer.tpl',
      1 => 1465999274,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '74568648758d6a0f6bd30c0-08161649',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content_only' => 0,
    'right_column_size' => 0,
    'HOOK_RIGHT_COLUMN' => 0,
    'HOOK_FOOTER' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_58d6a0f6bd8b63_64948431',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58d6a0f6bd8b63_64948431')) {function content_58d6a0f6bd8b63_64948431($_smarty_tpl) {?>
<?php if (!isset($_smarty_tpl->tpl_vars['content_only']->value)||!$_smarty_tpl->tpl_vars['content_only']->value) {?>
					</div><!-- #center_column -->
					<?php if (isset($_smarty_tpl->tpl_vars['right_column_size']->value)&&!empty($_smarty_tpl->tpl_vars['right_column_size']->value)) {?>
						<div id="right_column" class="col-xs-12 col-sm-<?php echo intval($_smarty_tpl->tpl_vars['right_column_size']->value);?>
 column"><?php echo $_smarty_tpl->tpl_vars['HOOK_RIGHT_COLUMN']->value;?>
</div>
					<?php }?>
					</div><!-- .row -->
				</div><!-- #columns -->
			</div><!-- .columns-container -->
			<?php if (isset($_smarty_tpl->tpl_vars['HOOK_FOOTER']->value)) {?>
				<!-- Footer -->
				<div class="footer-container">
					<footer id="footer"  class="container">
						<div class="row"><?php echo $_smarty_tpl->tpl_vars['HOOK_FOOTER']->value;?>
</div>
					</footer>
				</div><!-- #footer -->
			<?php }?>
		</div><!-- #page -->
<?php }?>
<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['tpl_dir']->value)."./global.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

	</body>
</html><?php }} ?>
