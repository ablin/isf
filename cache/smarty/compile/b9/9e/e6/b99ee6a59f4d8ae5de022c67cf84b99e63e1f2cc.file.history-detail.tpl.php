<?php /* Smarty version Smarty-3.1.19, created on 2017-05-10 23:02:02
         compiled from "/var/www/html/prestashop/themes/default-bootstrap/history-detail.tpl" */ ?>
<?php /*%%SmartyHeaderCode:91646062359135f4692d500-90476462%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b99ee6a59f4d8ae5de022c67cf84b99e63e1f2cc' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/history-detail.tpl',
      1 => 1494450118,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '91646062359135f4692d500-90476462',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_59135f46953242_84248836',
  'variables' => 
  array (
    'link' => 0,
    'navigationPipe' => 0,
    'id' => 0,
    'order' => 0,
    'lignes' => 0,
    'ligne' => 0,
    'submit' => 0,
    'base_dir' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59135f46953242_84248836')) {function content_59135f46953242_84248836($_smarty_tpl) {?>
<?php $_smarty_tpl->_capture_stack[0][] = array('path', null, null); ob_start(); ?>
    <a href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('my-account',true), ENT_QUOTES, 'UTF-8', true);?>
">
        <?php echo smartyTranslate(array('s'=>'My account'),$_smarty_tpl);?>

    </a>
    <span class="navigation-pipe"><?php echo $_smarty_tpl->tpl_vars['navigationPipe']->value;?>
</span>
    <span class="navigation_page"><?php echo smartyTranslate(array('s'=>'Order detail'),$_smarty_tpl);?>
 <?php echo $_smarty_tpl->tpl_vars['id']->value;?>
</span>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['tpl_dir']->value)."./errors.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<?php if ($_smarty_tpl->tpl_vars['order']->value==1) {?>
    <p class="order-confirm"><?php echo smartyTranslate(array('s'=>'Your order has been successfully registered. You will find the details below:'),$_smarty_tpl);?>
</p>
<?php }?>

<h1 class="page-heading bottom-indent"><?php echo smartyTranslate(array('s'=>'Order detail'),$_smarty_tpl);?>
 <?php echo $_smarty_tpl->tpl_vars['id']->value;?>
</h1>

<?php if (isset($_smarty_tpl->tpl_vars['lignes']->value)) {?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?php echo smartyTranslate(array('s'=>'Quantity'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Reference'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Description'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Sub reference 1'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Sub reference 2'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Unit price'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Discount'),$_smarty_tpl);?>
</th>
                <th><?php echo smartyTranslate(array('s'=>'Amount'),$_smarty_tpl);?>
</th>
            </tr>
        </thead>
        <tbody>
            <?php  $_smarty_tpl->tpl_vars['ligne'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ligne']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['lignes']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ligne']->key => $_smarty_tpl->tpl_vars['ligne']->value) {
$_smarty_tpl->tpl_vars['ligne']->_loop = true;
?>
                <tr>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->qte;?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->ref;?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->des;?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->sref1;?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->sref2;?>
</td>
                    <td><?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['displayPrice'][0][0]->displayPriceSmarty(array('price'=>$_smarty_tpl->tpl_vars['ligne']->value->Pub),$_smarty_tpl);?>
</td>
                    <td><?php echo $_smarty_tpl->tpl_vars['ligne']->value->Rem;?>
</td>
                    <td><?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['displayPrice'][0][0]->displayPriceSmarty(array('price'=>$_smarty_tpl->tpl_vars['ligne']->value->Mont),$_smarty_tpl);?>
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
        <a class="btn btn-default button button-small" href="<?php echo $_smarty_tpl->tpl_vars['base_dir']->value;?>
">
            <span><i class="icon-chevron-left"></i> <?php echo smartyTranslate(array('s'=>'Home'),$_smarty_tpl);?>
</span>
        </a>
    </li>
</ul><?php }} ?>
