<?php /* Smarty version Smarty-3.1.19, created on 2017-05-10 22:58:52
         compiled from "/var/www/html/prestashop/themes/default-bootstrap/history.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1500851497591243d9c54d55-70091189%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '90b8c6f1a0c6ab1d3588e56441897f3d507f1da2' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/history.tpl',
      1 => 1494449932,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1500851497591243d9c54d55-70091189',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_591243d9ca9d88_29672212',
  'variables' => 
  array (
    'link' => 0,
    'navigationPipe' => 0,
    'slowValidation' => 0,
    'request_uri' => 0,
    'picod' => 0,
    'Date_Year' => 0,
    'annee' => 0,
    'entetes' => 0,
    'entete' => 0,
    'params' => 0,
    'submit' => 0,
    'base_dir' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_591243d9ca9d88_29672212')) {function content_591243d9ca9d88_29672212($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_date_format')) include '/var/www/html/prestashop/tools/smarty/plugins/modifier.date_format.php';
if (!is_callable('smarty_function_html_select_date')) include '/var/www/html/prestashop/tools/smarty/plugins/function.html_select_date.php';
?>
<?php $_smarty_tpl->_capture_stack[0][] = array('path', null, null); ob_start(); ?>
	<a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('my-account',true), ENT_QUOTES, 'UTF-8', true);?>
">
		<?php echo smartyTranslate(array('s'=>'My account'),$_smarty_tpl);?>

	</a>
	<span class="navigation-pipe"><?php echo $_smarty_tpl->tpl_vars['navigationPipe']->value;?>
</span>
	<span class="navigation_page"><?php echo smartyTranslate(array('s'=>'Order history'),$_smarty_tpl);?>
</span>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['tpl_dir']->value)."./errors.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<h1 class="page-heading bottom-indent"><?php echo smartyTranslate(array('s'=>'Order history'),$_smarty_tpl);?>
</h1>
<p class="info-title"><?php echo smartyTranslate(array('s'=>'Here are the orders you\'ve placed since your account was created.'),$_smarty_tpl);?>
</p>
<?php if ($_smarty_tpl->tpl_vars['slowValidation']->value) {?>
	<p class="alert alert-warning"><?php echo smartyTranslate(array('s'=>'If you have just placed an order, it may take a few minutes for it to be validated. Please refresh this page if your order is missing.'),$_smarty_tpl);?>
</p>
<?php }?>

<form action="<?php echo mb_convert_encoding(htmlspecialchars($_smarty_tpl->tpl_vars['request_uri']->value, ENT_QUOTES, 'UTF-8', true), "HTML-ENTITIES", 'UTF-8');?>
" method="post">

    <div class="block-center" id="block-history">
        <label for="picod"><?php echo smartyTranslate(array('s'=>'Type of part:'),$_smarty_tpl);?>
</label>
        <select name="picod">
            <option value="1" <?php if (isset($_smarty_tpl->tpl_vars['picod']->value)&&$_smarty_tpl->tpl_vars['picod']->value==1) {?>selected="selected"<?php }?>>Devis</option>
            <option value="2" <?php if (isset($_smarty_tpl->tpl_vars['picod']->value)&&$_smarty_tpl->tpl_vars['picod']->value==2) {?>selected="selected"<?php }?>>Comande</option>
            <option value="3" <?php if (isset($_smarty_tpl->tpl_vars['picod']->value)&&$_smarty_tpl->tpl_vars['picod']->value==3) {?>selected="selected"<?php }?>>Bon de livraison</option>
            <option value="4" <?php if (isset($_smarty_tpl->tpl_vars['picod']->value)&&$_smarty_tpl->tpl_vars['picod']->value==4) {?>selected="selected"<?php }?>>Facture</option>
        </select>
        <br />
        <label for="Date_Year"><?php echo smartyTranslate(array('s'=>'Year:'),$_smarty_tpl);?>
</label>
        <?php if (isset($_smarty_tpl->tpl_vars['Date_Year']->value)) {?>
            <?php $_smarty_tpl->tpl_vars["annee"] = new Smarty_variable("01-01-".((string)$_smarty_tpl->tpl_vars['Date_Year']->value), null, 0);?>
        <?php } else { ?>
            <?php $_smarty_tpl->tpl_vars["annee"] = new Smarty_variable("01-01-".((string)smarty_modifier_date_format(time(),"%Y")), null, 0);?>
        <?php }?>
        <?php echo smarty_function_html_select_date(array('time'=>$_smarty_tpl->tpl_vars['annee']->value,'start_year'=>'2010','reverse_years'=>true,'display_days'=>false,'display_months'=>false),$_smarty_tpl);?>

        <br />
        <input type="submit" class="button btn btn-default " value="<?php echo smartyTranslate(array('s'=>'Submit'),$_smarty_tpl);?>
"></input>
        <br />
        <br />
    </div>

    <input type="hidden" name="submit">

</form>

<?php if (isset($_smarty_tpl->tpl_vars['entetes']->value)) {?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?php echo smartyTranslate(array('s'=>'Number'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Date'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Description'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Amount'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Shipping'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Document'),$_smarty_tpl);?>
</th>
            </tr>
        </thead>
        <tbody>
            <?php  $_smarty_tpl->tpl_vars['entete'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['entete']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['entetes']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['entete']->key => $_smarty_tpl->tpl_vars['entete']->value) {
$_smarty_tpl->tpl_vars['entete']->_loop = true;
?>
                <?php $_smarty_tpl->tpl_vars['params'] = new Smarty_variable(array('id'=>$_smarty_tpl->tpl_vars['entete']->value->numero,'picod'=>$_smarty_tpl->tpl_vars['picod']->value), null, 0);?>
                <tr>
                    <td><a target="_blank" href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('history-detail',true,null,$_smarty_tpl->tpl_vars['params']->value), ENT_QUOTES, 'UTF-8', true);?>
"><?php echo $_smarty_tpl->tpl_vars['entete']->value->numero;?>
</a></td>
                    <td><?php echo $_smarty_tpl->tpl_vars['entete']->value->date;?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['entete']->value->description;?>
</td>
                    <td><?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['displayPrice'][0][0]->displayPriceSmarty(array('price'=>$_smarty_tpl->tpl_vars['entete']->value->montant),$_smarty_tpl);?>
</td>
                    <td><?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['displayPrice'][0][0]->displayPriceSmarty(array('price'=>$_smarty_tpl->tpl_vars['entete']->value->montantPort),$_smarty_tpl);?>
</td>
                    <td><i class="icon-file-text"></i> <?php echo smartyTranslate(array('s'=>'Print'),$_smarty_tpl);?>
</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } elseif (isset($_smarty_tpl->tpl_vars['submit']->value)) {?>
    <h4 align="center"><?php echo smartyTranslate(array('s'=>'No result'),$_smarty_tpl);?>
</h4>
<?php }?>

<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-default button button-small" href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('my-account',true), ENT_QUOTES, 'UTF-8', true);?>
">
			<span>
				<i class="icon-chevron-left"></i> <?php echo smartyTranslate(array('s'=>'Back to Your Account'),$_smarty_tpl);?>

			</span>
		</a>
	</li>
	<li>
		<a class="btn btn-default button button-small" href="<?php echo $_smarty_tpl->tpl_vars['base_dir']->value;?>
">
			<span><i class="icon-chevron-left"></i> <?php echo smartyTranslate(array('s'=>'Home'),$_smarty_tpl);?>
</span>
		</a>
	</li>
</ul>
<?php }} ?>
