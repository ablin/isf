<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FrontController extends FrontControllerCore
{
    /**
     * Initializes front controller: sets smarty variables,
     * class properties, redirects depending on context, etc.
     *
     * @global bool     $useSSL           SSL connection flag
     * @global Cookie   $cookie           Visitor's cookie
     * @global Smarty   $smarty
     * @global Cart     $cart             Visitor's cart
     * @global string   $iso              Language ISO
     * @global Country  $defaultCountry   Visitor's country object
     * @global string   $protocol_link
     * @global string   $protocol_content
     * @global Link     $link
     * @global array    $css_files
     * @global array    $js_files
     * @global Currency $currency         Visitor's selected currency
     *
     * @throws PrestaShopException
     */
    public function init()
    {
        /**
         * Globals are DEPRECATED as of version 1.5.0.1
         * Use the Context object to access objects instead.
         * Example: $this->context->cart
         */
        global $useSSL, $cookie, $smarty, $cart, $iso, $defaultCountry, $protocol_link, $protocol_content, $link, $css_files, $js_files, $currency;

        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        parent::init();

        if (isset($_GET['logout']) || ($this->context->customer->logged && Customer::isBanned($this->context->customer->id))) {
            $this->context->customer->logout();

            Tools::redirect('authentication.php');
        } elseif (isset($_GET['mylogout'])) {
            $this->context->customer->mylogout();
            Tools::redirect('authentication.php');
        }

        // If current URL use SSL, set it true (used a lot for module redirect)
        if (Tools::usingSecureMode()) {
            $useSSL = true;
        }

        // For compatibility with globals, DEPRECATED as of version 1.5.0.1
        $css_files = $this->css_files;
        $js_files = $this->js_files;

        $this->sslRedirection();

        if ($this->ajax) {
            $this->display_header = false;
            $this->display_footer = false;
        }

        // If account created with the 2 steps register process, remove 'account_created' from cookie
        if (isset($this->context->cookie->account_created)) {
            $this->context->smarty->assign('account_created', 1);
            unset($this->context->cookie->account_created);
        }

        ob_start();

        // Init cookie language
        // @TODO This method must be moved into switchLanguage
        Tools::setCookieLanguage($this->context->cookie);

        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $useSSL = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';
        $link = new Link($protocol_link, $protocol_content);
        $this->context->link = $link;

        if ($id_cart = (int)$this->recoverCart()) {
            $this->context->cookie->id_cart = (int)$id_cart;
        }

        if ($this->auth && !$this->context->customer->isLogged($this->guestAllowed)) {
            Tools::redirect('index.php?controller=authentication'.($this->authRedirection ? '&back='.$this->authRedirection : ''));
        }

        /* Theme is missing */
        if (!is_dir(_PS_THEME_DIR_)) {
            throw new PrestaShopException((sprintf(Tools::displayError('Current theme unavailable "%s". Please check your theme directory name and permissions.'), basename(rtrim(_PS_THEME_DIR_, '/\\')))));
        }

        if (Configuration::get('PS_GEOLOCATION_ENABLED')) {
            if (($new_default = $this->geolocationManagement($this->context->country)) && Validate::isLoadedObject($new_default)) {
                $this->context->country = $new_default;
            }
        } elseif (Configuration::get('PS_DETECT_COUNTRY')) {
            $has_currency = isset($this->context->cookie->id_currency) && (int)$this->context->cookie->id_currency;
            $has_country = isset($this->context->cookie->iso_code_country) && $this->context->cookie->iso_code_country;
            $has_address_type = false;

            if ((int)$this->context->cookie->id_cart && ($cart = new Cart($this->context->cookie->id_cart)) && Validate::isLoadedObject($cart)) {
                $has_address_type = isset($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            }

            if ((!$has_currency || $has_country) && !$has_address_type) {
                $id_country = $has_country && !Validate::isLanguageIsoCode($this->context->cookie->iso_code_country) ?
                    (int)Country::getByIso(strtoupper($this->context->cookie->iso_code_country)) : (int)Tools::getCountry();

                $country = new Country($id_country, (int)$this->context->cookie->id_lang);

                if (!$has_currency && validate::isLoadedObject($country) && $this->context->country->id !== $country->id) {
                    $this->context->country = $country;
                    $this->context->cookie->id_currency = (int)Currency::getCurrencyInstance($country->id_currency ? (int)$country->id_currency : (int)Configuration::get('PS_CURRENCY_DEFAULT'))->id;
                    $this->context->cookie->iso_code_country = strtoupper($country->iso_code);
                }
            }
        }

        $currency = Tools::setCurrency($this->context->cookie);

        if (isset($_GET['logout']) || ($this->context->customer->logged && Customer::isBanned($this->context->customer->id))) {
            $this->context->customer->logout();

            Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
        } elseif (isset($_GET['mylogout'])) {
            $this->context->customer->mylogout();
            Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
        }

        /* Cart already exists */
        if ((int)$this->context->cookie->id_cart) {
            if (!isset($cart)) {
                $cart = new Cart($this->context->cookie->id_cart);
            }

            if (Validate::isLoadedObject($cart) && $cart->OrderExists()) {
                PrestaShopLogger::addLog('Frontcontroller::init - Cart cannot be loaded or an order has already been placed using this cart', 1, null, 'Cart', (int)$this->context->cookie->id_cart, true);
                unset($this->context->cookie->id_cart, $cart, $this->context->cookie->checkedTOS);
                $this->context->cookie->check_cgv = false;
            }
            /* Delete product of cart, if user can't make an order from his country */
            elseif (intval(Configuration::get('PS_GEOLOCATION_ENABLED')) &&
                    !in_array(strtoupper($this->context->cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) &&
                    $cart->nbProducts() && intval(Configuration::get('PS_GEOLOCATION_NA_BEHAVIOR')) != -1 &&
                    !FrontController::isInWhitelistForGeolocation() &&
                    !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
                PrestaShopLogger::addLog('Frontcontroller::init - GEOLOCATION is deleting a cart', 1, null, 'Cart', (int)$this->context->cookie->id_cart, true);
                unset($this->context->cookie->id_cart, $cart);
            }
            // update cart values
            elseif ($this->context->cookie->id_customer != $cart->id_customer || $this->context->cookie->id_lang != $cart->id_lang || $currency->id != $cart->id_currency) {
                if ($this->context->cookie->id_customer) {
                    $cart->id_customer = (int)$this->context->cookie->id_customer;
                }
                $cart->id_lang = (int)$this->context->cookie->id_lang;
                $cart->id_currency = (int)$currency->id;
                $cart->update();
            }
            /* Select an address if not set */
            if (isset($cart) && (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0 ||
                !isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) && $this->context->cookie->id_customer) {
                $to_update = false;
                if (!isset($cart->id_address_delivery) || $cart->id_address_delivery == 0) {
                    $to_update = true;
                    $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                }
                if (!isset($cart->id_address_invoice) || $cart->id_address_invoice == 0) {
                    $to_update = true;
                    $cart->id_address_invoice = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                }
                if ($to_update) {
                    $cart->update();
                }
            }
        }

        if (!isset($cart) || !$cart->id) {
            $cart = new Cart();
            $cart->id_lang = (int)$this->context->cookie->id_lang;
            $cart->id_currency = (int)$this->context->cookie->id_currency;
            $cart->id_guest = (int)$this->context->cookie->id_guest;
            $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
            $cart->id_shop = $this->context->shop->id;
            if ($this->context->cookie->id_customer) {
                $cart->id_customer = (int)$this->context->cookie->id_customer;
                $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                $cart->id_address_invoice = (int)$cart->id_address_delivery;
            } else {
                $cart->id_address_delivery = 0;
                $cart->id_address_invoice = 0;
            }

            // Needed if the merchant want to give a free product to every visitors
            $this->context->cart = $cart;
            CartRule::autoAddToCart($this->context);
        } else {
            $this->context->cart = $cart;
        }

        /* get page name to display it in body id */

        // Are we in a payment module
        $module_name = '';
        if (Validate::isModuleName(Tools::getValue('module'))) {
            $module_name = Tools::getValue('module');
        }

        if (!empty($this->page_name)) {
            $page_name = $this->page_name;
        } elseif (!empty($this->php_self)) {
            $page_name = $this->php_self;
        } elseif (Tools::getValue('fc') == 'module' && $module_name != '' && (Module::getInstanceByName($module_name) instanceof PaymentModule)) {
            $page_name = 'module-payment-submit';
        }
        // @retrocompatibility Are we in a module ?
        elseif (preg_match('#^'.preg_quote($this->context->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
            $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
        } else {
            $page_name = Dispatcher::getInstance()->getController();
            $page_name = (preg_match('/^[0-9]/', $page_name) ? 'page_'.$page_name : $page_name);
        }

        $this->context->smarty->assign(Meta::getMetaTags($this->context->language->id, $page_name));
        $this->context->smarty->assign('request_uri', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

        /* Breadcrumb */
        $navigation_pipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
        $this->context->smarty->assign('navigationPipe', $navigation_pipe);

        // Automatically redirect to the canonical URL if needed
        if (!empty($this->php_self) && !Tools::getValue('ajax')) {
            $this->canonicalRedirection($this->context->link->getPageLink($this->php_self, $this->ssl, $this->context->language->id));
        }

        Product::initPricesComputation();

        $display_tax_label = $this->context->country->display_tax_label;
        if (isset($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) {
            $infos = Address::getCountryAndState((int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
            $country = new Country((int)$infos['id_country']);
            $this->context->country = $country;
            if (Validate::isLoadedObject($country)) {
                $display_tax_label = $country->display_tax_label;
            }
        }

        $languages = Language::getLanguages(true, $this->context->shop->id);
        $meta_language = array();
        foreach ($languages as $lang) {
            $meta_language[] = $lang['iso_code'];
        }

        $compared_products = array();
        if (Configuration::get('PS_COMPARATOR_MAX_ITEM') && isset($this->context->cookie->id_compare)) {
            $compared_products = CompareProduct::getCompareProducts($this->context->cookie->id_compare);
        }

        $this->context->smarty->assign(array(
            // Useful for layout.tpl
            'mobile_device'       => $this->context->getMobileDevice(),
            'link'                => $link,
            'cart'                => $cart,
            'currency'            => $currency,
            'currencyRate'        => (float)$currency->getConversationRate(),
            'cookie'              => $this->context->cookie,
            'page_name'           => $page_name,
            'hide_left_column'    => !$this->display_column_left,
            'hide_right_column'   => !$this->display_column_right,
            'base_dir'            => _PS_BASE_URL_.__PS_BASE_URI__,
            'base_dir_ssl'        => $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__,
            'force_ssl'           => Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
            'content_dir'         => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__,
            'base_uri'            => $protocol_content.Tools::getHttpHost().__PS_BASE_URI__.(!Configuration::get('PS_REWRITING_SETTINGS') ? 'index.php' : ''),
            'tpl_dir'             => _PS_THEME_DIR_,
            'tpl_uri'             => _THEME_DIR_,
            'modules_dir'         => _MODULE_DIR_,
            'mail_dir'            => _MAIL_DIR_,
            'lang_iso'            => $this->context->language->iso_code,
            'lang_id'             => (int)$this->context->language->id,
            'language_code'       => $this->context->language->language_code ? $this->context->language->language_code : $this->context->language->iso_code,
            'come_from'           => Tools::getHttpHost(true, true).Tools::htmlentitiesUTF8(str_replace(array('\'', '\\'), '', urldecode($_SERVER['REQUEST_URI']))),
            'cart_qties'          => (int)$cart->nbProducts(),
            'currencies'          => Currency::getCurrencies(),
            'languages'           => $languages,
            'meta_language'       => implode(',', $meta_language),
            'priceDisplay'        => Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer),
            'is_logged'           => (bool)$this->context->customer->isLogged(),
            'is_guest'            => (bool)$this->context->customer->isGuest(),
            'add_prod_display'    => (int)Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
            'shop_name'           => Configuration::get('PS_SHOP_NAME'),
            'roundMode'           => (int)Configuration::get('PS_PRICE_ROUND_MODE'),
            'use_taxes'           => (int)Configuration::get('PS_TAX'),
            'show_taxes'          => (int)(Configuration::get('PS_TAX_DISPLAY') == 1 && (int)Configuration::get('PS_TAX')),
            'display_tax_label'   => (bool)$display_tax_label,
            'vat_management'      => (int)Configuration::get('VATNUMBER_MANAGEMENT'),
            'opc'                 => (bool)Configuration::get('PS_ORDER_PROCESS_TYPE'),
            'PS_CATALOG_MODE'     => (bool)Configuration::get('PS_CATALOG_MODE') || (Group::isFeatureActive() && !(bool)Group::getCurrent()->show_prices),
            'b2b_enable'          => (bool)Configuration::get('PS_B2B_ENABLE'),
            'request'             => $link->getPaginationLink(false, false, false, true),
            'PS_STOCK_MANAGEMENT' => Configuration::get('PS_STOCK_MANAGEMENT'),
            'quick_view'          => (bool)Configuration::get('PS_QUICK_VIEW'),
            'shop_phone'          => Configuration::get('PS_SHOP_PHONE'),
            'compared_products'   => is_array($compared_products) ? $compared_products : array(),
            'comparator_max_item' => (int)Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'currencySign'        => $currency->sign, // backward compat, see global.tpl
            'currencyFormat'      => $currency->format, // backward compat
            'currencyBlank'       => $currency->blank, // backward compat
        ));

        // Add the tpl files directory for mobile
        if ($this->useMobileTheme()) {
            $this->context->smarty->assign(array(
                'tpl_mobile_uri' => _PS_THEME_MOBILE_DIR_,
            ));
        }

        // Deprecated
        $this->context->smarty->assign(array(
            'id_currency_cookie' => (int)$currency->id,
            'logged'             => $this->context->customer->isLogged(),
            'customerName'       => ($this->context->customer->logged ? $this->context->cookie->customer_firstname.' '.$this->context->cookie->customer_lastname : false)
        ));

        $assign_array = array(
            'img_ps_dir'    => _PS_IMG_,
            'img_cat_dir'   => _THEME_CAT_DIR_,
            'img_lang_dir'  => _THEME_LANG_DIR_,
            'img_prod_dir'  => _THEME_PROD_DIR_,
            'img_manu_dir'  => _THEME_MANU_DIR_,
            'img_sup_dir'   => _THEME_SUP_DIR_,
            'img_ship_dir'  => _THEME_SHIP_DIR_,
            'img_store_dir' => _THEME_STORE_DIR_,
            'img_col_dir'   => _THEME_COL_DIR_,
            'img_dir'       => _THEME_IMG_DIR_,
            'css_dir'       => _THEME_CSS_DIR_,
            'js_dir'        => _THEME_JS_DIR_,
            'pic_dir'       => _THEME_PROD_PIC_DIR_
        );

        // Add the images directory for mobile
        if ($this->useMobileTheme()) {
            $assign_array['img_mobile_dir'] = _THEME_MOBILE_IMG_DIR_;
        }

        // Add the CSS directory for mobile
        if ($this->useMobileTheme()) {
            $assign_array['css_mobile_dir'] = _THEME_MOBILE_CSS_DIR_;
        }

        foreach ($assign_array as $assign_key => $assign_value) {
            if (substr($assign_value, 0, 1) == '/' || $protocol_content == 'https://') {
                $this->context->smarty->assign($assign_key, $protocol_content.Tools::getMediaServer($assign_value).$assign_value);
            } else {
                $this->context->smarty->assign($assign_key, $assign_value);
            }
        }

        /**
         * These shortcuts are DEPRECATED as of version 1.5.0.1
         * Use the Context to access objects instead.
         * Example: $this->context->cart
         */
        self::$cookie = $this->context->cookie;
        self::$cart   = $cart;
        self::$smarty = $this->context->smarty;
        self::$link   = $link;
        $defaultCountry = $this->context->country;

        $this->displayMaintenancePage();

        if ($this->restrictedCountry) {
            $this->displayRestrictedCountryPage();
        }

        if (Tools::isSubmit('live_edit') && !$this->checkLiveEditAccess()) {
            Tools::redirect('index.php?controller=404');
        }

        $this->iso               = $iso;
        $this->context->cart     = $cart;
        $this->context->currency = $currency;
    }
}
