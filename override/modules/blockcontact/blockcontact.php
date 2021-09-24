<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class BlockcontactOverride extends Blockcontact
{
    public function hookDisplayRightColumn($params)
    {
        global $smarty;
        $tpl = 'blockcontact';
        if (isset($params['blockcontact_tpl']) && $params['blockcontact_tpl'])
            $tpl = $params['blockcontact_tpl'];
        $smarty->assign(array(
            'telnumber' => Configuration::get('BLOCKCONTACT_TELNUMBER'),
            'email' => Configuration::get('BLOCKCONTACT_EMAIL'),
            'clients' => unserialize($this->context->cookie->clients),
            'tiers' => $this->context->cookie->tiers
        ));
        return $this->display(__FILE__, $tpl.'.tpl');
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS(($this->_path).'blockcontact.css', 'all');
        $this->context->controller->addJS(_THEME_JS_DIR_.'modules/blockcontact/blockcontact.js');
    }
}
