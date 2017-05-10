<?php /* Smarty version Smarty-3.1.19, created on 2017-05-09 16:57:47
         compiled from "/var/www/html/prestashop/themes/default-bootstrap/address.tpl" */ ?>
<?php /*%%SmartyHeaderCode:21386452745911d8eb3c7c35-49532713%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6ecedbf29f6723372549dddbbd41899a7ec1a1c2' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/address.tpl',
      1 => 1490717104,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '21386452745911d8eb3c7c35-49532713',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'id_address' => 0,
    'address' => 0,
    'link' => 0,
    'ordered_adr_fields' => 0,
    'field_name' => 0,
    'address_validation' => 0,
    'required_fields' => 0,
    'countries_list' => 0,
    'back' => 0,
    'mod' => 0,
    'select_address' => 0,
    'token' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5911d8eb41e9b7_47883092',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5911d8eb41e9b7_47883092')) {function content_5911d8eb41e9b7_47883092($_smarty_tpl) {?>
<?php $_smarty_tpl->_capture_stack[0][] = array('path', null, null); ob_start(); ?><?php echo smartyTranslate(array('s'=>'Your addresses'),$_smarty_tpl);?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<div class="box">
	<h1 class="page-subheading"><?php echo smartyTranslate(array('s'=>'Your addresses'),$_smarty_tpl);?>
</h1>
	<p class="info-title">
		<?php if (isset($_smarty_tpl->tpl_vars['id_address']->value)&&(isset($_POST['alias'])||isset($_smarty_tpl->tpl_vars['address']->value->alias))) {?>
			<?php echo smartyTranslate(array('s'=>'Modify address'),$_smarty_tpl);?>

			<?php if (isset($_POST['alias'])) {?>
				"<?php echo $_POST['alias'];?>
"
			<?php } else { ?>
				<?php if (isset($_smarty_tpl->tpl_vars['address']->value->alias)) {?>"<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->alias, ENT_QUOTES, 'UTF-8', true);?>
"<?php }?>
			<?php }?>
		<?php } else { ?>
			<?php echo smartyTranslate(array('s'=>'To add a new address, please fill out the form below.'),$_smarty_tpl);?>

		<?php }?>
	</p>
	<?php echo $_smarty_tpl->getSubTemplate (((string)$_smarty_tpl->tpl_vars['tpl_dir']->value)."./errors.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

	<p class="required"><sup>*</sup><?php echo smartyTranslate(array('s'=>'Required field'),$_smarty_tpl);?>
</p>
	<form action="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('address',true), ENT_QUOTES, 'UTF-8', true);?>
" method="post" class="std" id="add_address">
		<!--h3 class="page-subheading"><?php if (isset($_smarty_tpl->tpl_vars['id_address']->value)) {?><?php echo smartyTranslate(array('s'=>'Your address'),$_smarty_tpl);?>
<?php } else { ?><?php echo smartyTranslate(array('s'=>'New address'),$_smarty_tpl);?>
<?php }?></h3-->
		<?php  $_smarty_tpl->tpl_vars['field_name'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['field_name']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ordered_adr_fields']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['field_name']->key => $_smarty_tpl->tpl_vars['field_name']->value) {
$_smarty_tpl->tpl_vars['field_name']->_loop = true;
?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='alias') {?>
                <div class="required form-group">
                    <label for="alias"><?php echo smartyTranslate(array('s'=>'Alias'),$_smarty_tpl);?>
 <sup>*</sup></label>
                    <input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" name="alias" id="alias" value="<?php if (isset($_POST['alias'])) {?><?php echo $_POST['alias'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->alias)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->alias, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
                </div>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['field_name']->value=='firstname') {?>
				<div class="required form-group">
					<label for="firstname"><?php echo smartyTranslate(array('s'=>'First name'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" name="firstname" id="firstname" value="<?php if (isset($_POST['firstname'])) {?><?php echo $_POST['firstname'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->firstname)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->firstname, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
				</div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='lastname') {?>
				<div class="required form-group">
					<label for="lastname"><?php echo smartyTranslate(array('s'=>'Last name'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="lastname" name="lastname" value="<?php if (isset($_POST['lastname'])) {?><?php echo $_POST['lastname'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->lastname)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->lastname, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
				</div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='address1') {?>
				<div class="required form-group">
					<label for="address1"><?php echo smartyTranslate(array('s'=>'Address'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="address1" name="address1" value="<?php if (isset($_POST['address1'])) {?><?php echo $_POST['address1'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->address1)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->address1, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
				</div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='address2') {?>
				<div class="required form-group">
					<label for="address2"><?php echo smartyTranslate(array('s'=>'Address (Line 2)'),$_smarty_tpl);?>
<?php if (isset($_smarty_tpl->tpl_vars['required_fields']->value)&&in_array($_smarty_tpl->tpl_vars['field_name']->value,$_smarty_tpl->tpl_vars['required_fields']->value)) {?> <sup>*</sup><?php }?></label>
					<input class="validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="address2" name="address2" value="<?php if (isset($_POST['address2'])) {?><?php echo $_POST['address2'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->address2)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->address2, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
				</div>
			<?php }?>
            <?php if ($_smarty_tpl->tpl_vars['field_name']->value=='address3') {?>
                <div class="required form-group">
                    <label for="address3"><?php echo smartyTranslate(array('s'=>'Address (Line 3)'),$_smarty_tpl);?>
<?php if (isset($_smarty_tpl->tpl_vars['required_fields']->value)&&in_array($_smarty_tpl->tpl_vars['field_name']->value,$_smarty_tpl->tpl_vars['required_fields']->value)) {?> <sup>*</sup><?php }?></label>
                    <input class="validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="address3" name="address3" value="<?php if (isset($_POST['address3'])) {?><?php echo $_POST['address3'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->address3)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->address3, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
                </div>
            <?php }?>
            <?php if ($_smarty_tpl->tpl_vars['field_name']->value=='locality') {?>
                <div class="required form-group">
                    <label for="locality"><?php echo smartyTranslate(array('s'=>'Locality'),$_smarty_tpl);?>
<?php if (isset($_smarty_tpl->tpl_vars['required_fields']->value)&&in_array($_smarty_tpl->tpl_vars['field_name']->value,$_smarty_tpl->tpl_vars['required_fields']->value)) {?> <sup>*</sup><?php }?></label>
                    <input class="validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="locality" name="locality" value="<?php if (isset($_POST['locality'])) {?><?php echo $_POST['locality'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->locality)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->locality, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
                </div>
            <?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='postcode') {?>
				<div class="required form-group">
					<label for="postcode"><?php echo smartyTranslate(array('s'=>'Zip/Postal Code'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" id="postcode" name="postcode" value="<?php if (isset($_POST['postcode'])) {?><?php echo $_POST['postcode'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->postcode)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->postcode, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" />
				</div>
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='city') {?>
				<div class="required form-group">
					<label for="city"><?php echo smartyTranslate(array('s'=>'City'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<input class="is_required validate form-control" data-validate="<?php echo $_smarty_tpl->tpl_vars['address_validation']->value[$_smarty_tpl->tpl_vars['field_name']->value]['validate'];?>
" type="text" name="city" id="city" value="<?php if (isset($_POST['city'])) {?><?php echo $_POST['city'];?>
<?php } else { ?><?php if (isset($_smarty_tpl->tpl_vars['address']->value->city)) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['address']->value->city, ENT_QUOTES, 'UTF-8', true);?>
<?php }?><?php }?>" maxlength="64" />
				</div>
				
			<?php }?>
			<?php if ($_smarty_tpl->tpl_vars['field_name']->value=='country'||$_smarty_tpl->tpl_vars['field_name']->value=='Country:iso_code') {?>
				<div class="required form-group">
					<label for="id_country"><?php echo smartyTranslate(array('s'=>'Country'),$_smarty_tpl);?>
 <sup>*</sup></label>
					<select id="id_country" class="form-control" name="id_country"><?php echo $_smarty_tpl->tpl_vars['countries_list']->value;?>
</select>
				</div>
			<?php }?>
		<?php } ?>
		<p class="submit2">
			<?php if (isset($_smarty_tpl->tpl_vars['id_address']->value)) {?><input type="hidden" name="id_address" value="<?php echo intval($_smarty_tpl->tpl_vars['id_address']->value);?>
" /><?php }?>
			<?php if (isset($_smarty_tpl->tpl_vars['back']->value)) {?><input type="hidden" name="back" value="<?php echo $_smarty_tpl->tpl_vars['back']->value;?>
" /><?php }?>
			<?php if (isset($_smarty_tpl->tpl_vars['mod']->value)) {?><input type="hidden" name="mod" value="<?php echo $_smarty_tpl->tpl_vars['mod']->value;?>
" /><?php }?>
			<?php if (isset($_smarty_tpl->tpl_vars['select_address']->value)) {?><input type="hidden" name="select_address" value="<?php echo intval($_smarty_tpl->tpl_vars['select_address']->value);?>
" /><?php }?>
            <?php if (isset($_smarty_tpl->tpl_vars['address']->value->alias)) {?><input type="hidden" name="previous_alias" value="<?php echo $_smarty_tpl->tpl_vars['address']->value->alias;?>
" /><?php }?>
			<input type="hidden" name="token" value="<?php echo $_smarty_tpl->tpl_vars['token']->value;?>
" />
			<button type="submit" name="submitAddress" id="submitAddress" class="btn btn-default button button-medium">
				<span>
					<?php echo smartyTranslate(array('s'=>'Save'),$_smarty_tpl);?>

					<i class="icon-chevron-right right"></i>
				</span>
			</button>
		</p>
	</form>
</div>
<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-defaul button button-small" href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['link']->value->getPageLink('addresses',true), ENT_QUOTES, 'UTF-8', true);?>
">
			<span><i class="icon-chevron-left"></i> <?php echo smartyTranslate(array('s'=>'Back to your addresses'),$_smarty_tpl);?>
</span>
		</a>
	</li>
</ul>
<?php }} ?>
