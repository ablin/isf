<?php /*%%SmartyHeaderCode:115801181558d6a0f688c9c9-06201716%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ca9584bf0f9a26278f4b4889670a373883dbe9ba' => 
    array (
      0 => '/var/www/html/prestashop/themes/default-bootstrap/modules/blockmyaccountfooter/blockmyaccountfooter.tpl',
      1 => 1487172978,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '115801181558d6a0f688c9c9-06201716',
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_58d6ae046482e8_19055006',
  'has_nocache_code' => false,
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_58d6ae046482e8_19055006')) {function content_58d6ae046482e8_19055006($_smarty_tpl) {?>
<!-- Block myaccount module -->
<section class="footer-block col-xs-12 col-sm-4">
	<h4><a href="http://localhost/prestashop/mon-compte" title="Gérer mon compte client" rel="nofollow">Mon compte</a></h4>
	<div class="block_content toggle-footer">
		<ul class="bullet">
			<!--<li><a href="http://localhost/prestashop/historique-commandes" title="Mes commandes" rel="nofollow">Mes commandes</a></li>
						<li><a href="http://localhost/prestashop/avoirs" title="Mes avoirs" rel="nofollow">Mes avoirs</a></li>-->
			<li><a href="http://localhost/prestashop/adresses" title="Mes adresses" rel="nofollow">Mes adresses</a></li>
			<!--<li><a href="http://localhost/prestashop/identite" title="Gérer mes informations personnelles" rel="nofollow">Mes informations personnelles</a></li>-->
						
            <li><a href="http://localhost/prestashop/?mylogout" title="Déconnexion" rel="nofollow">Déconnexion</a></li>		</ul>
	</div>
</section>
<!-- /Block myaccount module -->
<?php }} ?>
