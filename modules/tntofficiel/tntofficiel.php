<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficiel.
 */
class TNTOfficiel extends CarrierModule
{
    // Name identifier.
    const MODULE_NAME = 'tntofficiel';
    // Carrier name.
    const CARRIER_NAME = 'TNT Express France';
    // Release stamp : (((+new Date())/1000)|0).toString(36)
    const MODULE_RELEASE = 'q7z6g0';

    // Status that trigger shipment creation (eg: PS_OS_PREPARATION).
    const ORDERSTATE_SAVESHIPMENT = 'PS_OS_SHIPPING';

    // Path to the module Log.
    const PATH_LOG = 'log/';

    /**
     * Request timeout.
     */

    // Timeout for connection to the server.
    const REQUEST_CONNECTTIMEOUT = 8;
    // Timeout global (expiration).
    const REQUEST_TIMEOUT = 48;

    /**
     * Paths to the BT.
     */
    const PATH_MEDIA = 'tnt_media/';
    const PATH_MEDIA_PDF = 'tnt_media/media/';
    const PATH_MEDIA_PDF_BT = 'tnt_media/media/bt/';

    /**
     * Reserved by Cart Model.
     * @var int|null Carrier ID set when retrieving shipping cost from module.
     * see getOrderShippingCost()
     */
    public $id_carrier = null;


    /**
     * TNTOfficiel constructor.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Module is compliant with bootstrap. PS1.6+
        $this->bootstrap = true;

        // Version.
        $this->version = '1.3.8';
        // Prestashop supported version. PS1.6.0.5+
        $this->ps_versions_compliancy = array('min' => '1.6.0.5', 'max' => '1.6.99.99');
        // Prestashop modules dependencies.
        $this->dependencies = array();

        // Name.
        $this->name = 'tntofficiel'; // TNTOfficiel::MODULE_NAME;
        // Displayed Name.
        $this->displayName = $this->l('TNT Express France'); // TNTOfficiel::CARRIER_NAME;
        // Description.
        $this->description = $this->l('Offer your customers, different delivery methods with TNT');

        // Type.
        $this->tab = 'shipping_logistics';

        // Confirmation message before uninstall.
        //$this->confirmUninstall = $this->l('Are you sure you want to delete this module?');

        // Author.
        $this->author = 'Gfi Informatique';

        // Module key provided by addons.prestashop.com.
        $this->module_key = '1cf0bbdc13a4d4f319266cfe0bfac777';

        // Is this instance required on module when it is displayed in the module list.
        // This can be useful if the module has to perform checks on the PrestaShop configuration,
        // and display warning message accordingly.
        $this->need_instance = 0;

        // Check each days credential state for auto invalidation (e.g: password changed).
        // If invalidated, module is disabled and carrier are not displayed on front-office.
        if (TNTOfficiel_Credentials::getValidatedDateTime() !== null) {
            TNTOfficiel_Credentials::updateValidation(60 * 60 * 24);
        }

        // Module Constructor.
        parent::__construct();

        /*
         * Display error or warning message in the module list.
         */

        if (!extension_loaded('curl')) {
            $this->displayAdminError(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'cURL'),
                null,
                array('AdminModules', 'AdminTNTOfficiel')
            );
        }
        if (!extension_loaded('soap')) {
            $this->displayAdminError(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'SOAP'),
                null,
                array('AdminModules', 'AdminTNTOfficiel')
            );
        }
        if (!extension_loaded('zip')) {
            $this->displayAdminWarning(
                sprintf($this->l('You have to enable the PHP %s extension on your server.'), 'Zip'),
                null,
                array('AdminModules', 'AdminTNTOfficiel')
            );
        }

        // Account Auth.
        if (TNTOfficiel_Credentials::getValidatedDateTime() === null) {
            $this->displayAdminWarning(
                $this->l('The authentication data must be validated on the configuration page.'),
                $this->context->link->getAdminLink('AdminModules').'&amp;configure='.TNTOfficiel::MODULE_NAME,
                array('AdminTNTOfficiel', 'AdminCarriers')
            );
        }
        // Weight Unit.
        if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT')) !== 'kg') {
            $this->displayAdminWarning(
                sprintf(
                    $this->l('The supported weight unit is \'kg\', but is currently \'%s\'.'),
                    Configuration::get('PS_WEIGHT_UNIT')
                ),
                $this->context->link->getAdminLink('AdminLocalization'),
                array('AdminModules', 'AdminTNTOfficiel', 'AdminCarriers')
            );
        }

        // Apply default carriers values if required.
        TNTOfficiel_Carrier::forceAllCurrentCarrierDefaultValues();
    }

    /**
     * Get a message for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * @return bool
     */
    public function getAdminMessage($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
        $objContext = $this->context;

        if (!property_exists($objContext, 'controller')) {
            return false;
        }

        // Controller.
        $objController = $objContext->controller;

        // If not an AdminController or is an AJAX request.
        if (!($objController instanceof AdminController) || $objController->ajax) {
            return false;
        }

        // Controller Name.
        $strControllerName = preg_replace('/Controller$/ui', '', get_class($objController));
        // If controller filter list exist but not in list.
        if (!is_array($arrArgControllers)
            || (count($arrArgControllers) > 0 && !in_array($strControllerName, $arrArgControllers))
        ) {
            return false;
        }

        if (!is_string($strArgMessage) || Tools::strlen($strArgMessage) === 0) {
            return false;
        }

        //
        if ($strControllerName === 'AdminModules' && Tools::getValue('configure') !== TNTOfficiel::MODULE_NAME) {
            return false;
        }

        if (is_string($strArgURL)) {
            $strArgMessage = '<a href="'.$strArgURL.'">'.$strArgMessage.'</a>';
        }

        $strArgMessage = TNTOfficiel::CARRIER_NAME.' : '.$strArgMessage;

        return $strArgMessage;
    }

    /**
     * Display a warning for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * @return bool
     */
    public function displayAdminWarning($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
         $strArgMessage = $this->getAdminMessage($strArgMessage, $strArgURL, $arrArgControllers);

        if (is_string($strArgMessage)) {
            $this->context->controller->warnings[] = $strArgMessage;
        }

        return $strArgMessage;
    }

    /**
     * Display a error for admin controller.
     *
     * @param string $strArgMessage
     * @param string $strArgURL
     * @param array $arrArgControllers
     *
     * @return bool
     */
    public function displayAdminError($strArgMessage, $strArgURL = null, $arrArgControllers = array())
    {
        $strArgMessage = $this->getAdminMessage($strArgMessage, $strArgURL, $arrArgControllers);

        if (is_string($strArgMessage)) {
            $this->context->controller->errors[] = $strArgMessage;
        }

        return $strArgMessage;
    }

    /**
     * Module install.
     *
     * @return bool
     */
    public function install()
    {
        TNTOfficiel_Logstack::log();

        // If MultiShop and more than 1 Shop.
        if (Shop::isFeatureActive()) {
            // Define Shop context to all Shops.
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        TNTOfficiel_Logger::logInstall(
            '____ '.TNTOfficiel::CARRIER_NAME.' ('.TNTOfficiel::MODULE_NAME.') : install init. ____'
        );

        // Check tntofficiel release version.
        $strRLPrevious = (string)Configuration::get('TNTOFFICIEL_RELEASE');
        $intTSPrevious = (int)base_convert($strRLPrevious, 36, 10);
        $intTSCurrent = base_convert(TNTOfficiel::MODULE_RELEASE, 36, 10);
        // If current release is older than the previously installed, then do not install.
        if ($intTSCurrent < $intTSPrevious) {
            TNTOfficiel_Logger::logInstall('Downgrade not allowed', false);
            $this->_errors[] =
                $this->l('Downgrade not allowed : Previously installed version is greater than the current one.');

            return false;
        }

        // Check compatibility.
        if (!extension_loaded('curl')) {
            TNTOfficiel_Logger::logInstall('PHP cURL extension is required', false);
            $this->_errors[] = sprintf(
                $this->l('You have to enable the PHP %s extension on your server.'),
                'cURL'
            );

            return false;
        }
        if (!extension_loaded('soap')) {
            TNTOfficiel_Logger::logInstall('PHP SOAP extension is required', false);
            $this->_errors[] = sprintf(
                $this->l('You have to enable the PHP %s extension on your server.'),
                'SOAP'
            );

            return false;
        }
        if (!extension_loaded('zip')) {
            TNTOfficiel_Logger::logInstall('PHP Zip extension is required', false);
            $this->_errors[] = sprintf(
                $this->l('You have to enable the PHP %s extension on your server.'),
                'Zip'
            );

            return false;
        }

        // Store release.
        Configuration::updateGlobalValue('TNTOFFICIEL_RELEASE', TNTOfficiel::MODULE_RELEASE);

        // Fix uninstallOverrides process.
        TNTOfficiel_Install::uninstallOverridesFix();
        // Remove deprecated files.
        TNTOfficiel_Install::uninstallDeprecatedFiles();

        // Prestashop install.
        if (!parent::install()) {
            TNTOfficiel_Logger::logInstall('Module::install', false);
            $this->_errors[] = Tools::displayError('Impossible d\'installer Module::install().');

            return false;
        }
        TNTOfficiel_Logger::logInstall('Module::install');

        // Update settings.
        if (!TNTOfficiel_Install::updateSettings()) {
            $this->_errors[] = Tools::displayError('Impossible de définir la configuration.');

            return false;
        }

        // Register hooks.
        foreach (TNTOfficiel_Install::$arrHookList as $strHookName) {
            if (!$this->registerHook($strHookName)) {
                TNTOfficiel_Logger::logInstall('Module::registerHook ('.$strHookName.')', false);
                $this->_errors[] = Tools::displayError(sprintf('Impossible d\'inscrire le hook "%s".', $strHookName));

                return false;
            }
        }
        TNTOfficiel_Logger::logInstall('Module::registerHook');

        // Create the TNT tab.
        if (!TNTOfficiel_Install::createTab()) {
            $this->_errors[] = Tools::displayError('Impossible d\'ajouter l\'onglet du menu.');

            return false;
        }

        // Create the tables.
        if (!TNTOfficiel_Install::createTables()) {
            $this->_errors[] = Tools::displayError('Impossible de créer les tables de la base de donnée.');

            return false;
        }

        // Create the TNT carrier (true to renew carrier).
        if (!TNTOfficiel_Carrier::installAllCurrentCarrier(/*true*/)) {
            $this->_errors[] = Tools::displayError('Impossible d\'installer le transporteur.');

            return false;
        }

        // Create directories.
        if (!TNTOfficiel_Tools::makeDirectory(
            array(TNTOfficiel::PATH_MEDIA, TNTOfficiel::PATH_MEDIA_PDF, TNTOfficiel::PATH_MEDIA_PDF_BT),
            _PS_MODULE_DIR_
        )) {
            $this->_errors[] = Tools::displayError('Impossible de créer les dossiers.');

            return false;
        }

        // Clear cache.
        TNTOfficiel_Install::clearCache();

        // Check override.
        TNTOfficiel_Install::checkOverride();


        TNTOfficiel_Logger::logInstall(
            '____ '.TNTOfficiel::CARRIER_NAME.' ('.TNTOfficiel::MODULE_NAME.') : install complete. ____'
        );

        return true;
    }

    /**
     * Module uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        TNTOfficiel_Logstack::log();

        TNTOfficiel_Logger::logInstall(
            '____ '.TNTOfficiel::CARRIER_NAME.' ('.TNTOfficiel::MODULE_NAME.') : uninstall init. ____'
        );

        // Uninstall class or controllers override.
        // Already done by parent::uninstall, but used to display error message.
        if (!$this->uninstallOverrides()) {
            TNTOfficiel_Logger::logInstall('Module::uninstallOverrides', false);
            $this->_errors[] = Tools::displayError('Impossible de supprimer la surcharge de classe.');

            return false;
        }
        TNTOfficiel_Logger::logInstall('Module::uninstallOverrides');

        // Fix uninstallOverrides process.
        TNTOfficiel_Install::uninstallOverridesFix();

        // Unregister Hooks.
        foreach (TNTOfficiel_Install::$arrHookList as $strHookName) {
            if (!$this->unregisterHook($strHookName)) {
                TNTOfficiel_Logger::logInstall('Module::unregisterHook ('.$strHookName.')', false);
                $this->_errors[] = sprintf(Tools::displayError('Impossible de supprimer le hook "%s".'), $strHookName);

                // No return to allow re-initialisation.
                //return false;
            }
        }
        TNTOfficiel_Logger::logInstall('Module::unregisterHook');

        // Delete Tab.
        if (!TNTOfficiel_Install::deleteTab()) {
            $this->_errors[] = Tools::displayError('Impossible de supprimer l\'onglet du menu.');

            return false;
        }

        // Delete Carrier.
        if (!TNTOfficiel_Carrier::uninstallAllCurrentCarrier()) {
            $this->_errors[] = Tools::displayError('Impossible de désactiver le transporteur.');

            return false;
        }

        // Delete Settings.
        if (!TNTOfficiel_Install::deleteSettings()) {
            $this->_errors[] = Tools::displayError('Impossible de supprimer la configuration.');

            return false;
        }

        //
        if (!parent::uninstall()) {
            TNTOfficiel_Logger::logInstall('Module::uninstall', false);
            $this->_errors[] = Tools::displayError('Impossible de désinstaller Parent::uninstall().');

            return false;
        }
        TNTOfficiel_Logger::logInstall('Module::uninstall');

        // Fix Module::isInstalled() for PS 1.6.0.5 to 1.6.0.8.
        // Allow Module::install() right after.
        Cache::clean('Module::isInstalled'.$this->name);
        Cache::clean('Module::getModuleIdByName_'.pSQL($this->name));

        TNTOfficiel_Logger::logInstall(
            '____ '.TNTOfficiel::CARRIER_NAME.' ('.TNTOfficiel::MODULE_NAME.') : uninstall complete. ____'
        );

        // TODO: check default carrier is not TNT.
        // Configuration::get('PS_CARRIER_DEFAULT')

        return true;
    }

    /**
     * Module configuration page content.
     *
     * @return string HTML content.
     */
    public function getContent()
    {
        TNTOfficiel_Logstack::log();

        // Form Helper.
        $objHelperForm = new HelperForm();
        // Form Structure used as parameter for Helper 'generateForm' method.
        $arrFormStruct = array();
        // Form Values used for Helper 'fields_value' property.
        $arrFieldsValue = array();


        $strDisplayGlobalErrors = '';
        if ((bool)Configuration::get('PS_DISABLE_OVERRIDES')) {
            $strDisplayGlobalErrors .= $this->displayError(
                $this->l('The module is disabled, because overloads are disabled (Debug).')
            );
        }

        // Smarty assign().
        // /modules/<MODULE>/views/templates/admin/_configure/helpers/form/form.tpl
        // extends /<ADMIN>/themes/default/template/helpers/form/form.tpl
        $objHelperForm->tpl_vars['tntofficiel'] = array(
            'srcTNTLogoImage' =>
                $this->getPathUri().'views/img/logo/500x100.png',
            'hrefExportLog' =>
                $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=downloadLogs',
            'langExportLog' =>
                $this->l('Export logs'),
            'hrefManualPDF' =>
                'http://www.tnt.fr/Telechargements/cit/manuel-prestashop.pdf',
            // $this->getPathUri().'manuel-prestashop.pdf',
            'langManualPDF' =>
                $this->l('Installation manual')
        );


        // Module using this form.
        $objHelperForm->module = $this;
        // Controller name.
        $objHelperForm->name_controller = TNTOfficiel::MODULE_NAME;
        // Token.
        $objHelperForm->token = Tools::getAdminTokenLite('AdminModules');
        // Form action attribute.
        $objHelperForm->currentIndex = AdminController::$currentIndex.'&configure='.TNTOfficiel::MODULE_NAME;


        // Get default language.
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        // Language.
        $objHelperForm->default_form_language = $default_lang;
        $objHelperForm->allow_employee_form_lang = $default_lang;

        /*
         * Authentication Form
         */

        $strIdFormAuth = 'submit'.TNTOfficiel::MODULE_NAME.'AuthConfig';
        // Form structure.
        $arrFormStructAuth = $this->getFormAuthConfig($strIdFormAuth);
        // Add to form.
        $arrFormStruct[$strIdFormAuth] = array('form' => $arrFormStructAuth);
        // Form fields values.
        $arrFieldsValue += array(
            'TNTOFFICIEL_ACCOUNT_LOGIN' => trim(Configuration::get('TNTOFFICIEL_ACCOUNT_LOGIN')),
            'TNTOFFICIEL_ACCOUNT_NUMBER' => trim(Configuration::get('TNTOFFICIEL_ACCOUNT_NUMBER'))
        );

        /*
         * Configuration Form
         */

        $strIdFormOpt = 'submit'.TNTOfficiel::MODULE_NAME.'OptConfig';
        // Form structure.
        $arrFormStructOpt = $this->getFormOptConfig($strIdFormOpt);
        // Add to form.
        $arrFormStruct[$strIdFormOpt] = array('form' => $arrFormStructOpt);
        // Form fields values.
        $arrFieldsValue += array(
            'TNTOFFICIEL_PICKUP_DISPLAY_NUMBER' => Configuration::get('TNTOFFICIEL_PICKUP_DISPLAY_NUMBER'),
            'TNTOFFICIEL_GMAP_API_KEY' => Configuration::get('TNTOFFICIEL_GMAP_API_KEY')
        );

        /*
         * Carriers Form
         */

        $strIdFormCarrier = 'submit'.TNTOfficiel::MODULE_NAME.'CarrierConfig';
        // Form structure.
        $arrFormStructCarrier = $this->getFormCarrierConfig($strIdFormCarrier);
        // Add to form.
        $arrFormStruct[$strIdFormCarrier] = array('form' => $arrFormStructCarrier);
        // Form fields values.
        $arrAllCurrentCarrier = TNTOfficiel_Carrier::getAllCurrentCarrierID();
        foreach ($arrAllCurrentCarrier as $strCarrierCode => $intCarrierID) {
            $objCarrier = new Carrier($intCarrierID);
            $arrFieldsValue += array(
                $strCarrierCode => 'ID '.$intCarrierID.' | '.$objCarrier->name,
            );
        }


        // Set all form fields values.
        $objHelperForm->fields_value = $arrFieldsValue;
        // Global Submit ID.
        //$objHelperForm->submit_action = 'submit'.TNTOfficiel::MODULE_NAME;
        // Get generated forms.
        $strDisplayForms = $objHelperForm->generateForm($arrFormStruct);

        return $strDisplayGlobalErrors.$strDisplayForms
        //.'<pre>'.var_export(TNTOfficiel_Carrier::getAllCurrentCarrierID(), true).'</pre>'
        //.'<pre>'.var_export(TNTOfficiel_Carrier::getAllAccountCarrierID(), true).'</pre>'
        //.'<pre>'.var_export(TNTOfficiel_JsonRPCClient::getAccountInfos(), true).'</pre>'
        ;
    }

    /**
     * Get the Auth form structure for Helper.
     *
     * @return array
     */
    private function getFormAuthConfig($strArgIdForm)
    {
        TNTOfficiel_Logstack::log();

        // Validate.
        $arrFormValidateAuth = $this->validateFormAuthConfig($strArgIdForm);

        $arrAlertHTML = TNTOfficiel_Tools::getAlertHTML($arrFormValidateAuth);
        if (count($arrAlertHTML) > 0) {
            $arrAlertHTML = array(
                array(
                    'type' => 'html',
                    'name' => implode('', $arrAlertHTML)
                )
            );
        }

        // Return form struct.
        return array(
            'legend' => array(
                'title' => $this->l('Authentication'),
                'icon' => 'icon-user'
            ),
            'input' => array_merge(array(
                array(
                    'type' => 'text',
                    'label' => $this->l('username'),
                    'hint' => $this->l('Your identifier (email)'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_LOGIN',
                    'size' => 80,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('account'),
                    'hint' => $this->l('Your account number (8 digits)'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_NUMBER',
                    'size' => 10,
                    'required' => true,
                ),
                array(
                    'type' => 'password',
                    'label' => $this->l('password'),
                    'name' => 'TNTOFFICIEL_ACCOUNT_PASSWORD',
                    'size' => 40,
                    'required' => true,
                )
            ), $arrAlertHTML),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
                'name' => $strArgIdForm
            ),
        );
    }

    /**
     * Validate the Auth configuration form and return error messages.
     *
     * @return array
     */
    private function validateFormAuthConfig($strArgIdForm)
    {
        TNTOfficiel_Logstack::log();

        $arrAlert = array(
            'error' => array(),
            'warning' => array(),
            'success' => array()
        );

        // If form submitted.
        if (Tools::isSubmit($strArgIdForm)) {
            $strAccountLogin = trim(pSQL(Tools::getValue('TNTOFFICIEL_ACCOUNT_LOGIN')));
            $strAccountNumber = trim(pSQL(Tools::getValue('TNTOFFICIEL_ACCOUNT_NUMBER')));
            $strAccountPassword = pSQL(Tools::getValue('TNTOFFICIEL_ACCOUNT_PASSWORD'));

            if (!$strAccountLogin) {
                $arrAlert['error'][] = $this->l('The username is required.');
            }
            if (!$strAccountNumber) {
                $arrAlert['error'][] = $this->l('The account is required.');
            }
            if (!$strAccountPassword) {
                $arrAlert['error'][] = $this->l('The password is required.');
            }

            /*
             * Save
             */

            // If no errors.
            if (count($arrAlert['error']) === 0) {
                Configuration::updateValue('TNTOFFICIEL_ACCOUNT_LOGIN', $strAccountLogin);
                Configuration::updateValue('TNTOFFICIEL_ACCOUNT_NUMBER', $strAccountNumber);
                if ($strAccountPassword != '%p#c`Q9,6GSP?U4]e]Zst') {
                    Configuration::updateValue('TNTOFFICIEL_ACCOUNT_PASSWORD', sha1($strAccountPassword));
                }
            }

            /*
             * Messages (Errors, Warning, etc.)
             */

            if (count($arrAlert['error']) === 0) {
                // Validate the TNT credentials.
                $mxdStateValidation = TNTOfficiel_Credentials::updateValidation();
                if ($mxdStateValidation === null) {
                    $arrAlert['warning'][] = $this->l('A connection error occurred.');
                } elseif (!$mxdStateValidation) {
                    $arrAlert['error'][] = $this->l('The authentication data is incorrect.');
                } else {
                    $arrAlert['success'][] = $this->l('Settings updated.');
                }
            }
        } else {
            $mxdStateValidation = TNTOfficiel_Credentials::updateValidation();
            if ($mxdStateValidation === null) {
                $arrAlert['warning'][] = $this->l('A connection error occurred.');
            } elseif (!$mxdStateValidation) {
                $arrAlert['error'][] = $this->l('The authentication data is incorrect.');
            }
        }

        return $arrAlert;
    }

    /**
     * Get the Opt form structure for Helper.
     *
     * @return array
     */
    private function getFormOptConfig($strArgIdForm)
    {
        TNTOfficiel_Logstack::log();

        $strAPIKGMDesc = $this->l('This is the API key to use the mapping service of Google Maps.').' '
            .$this->l('After a number of daily uses of the service, you will have a key to continue using it.').'<br />'
            .sprintf(
                $this->l('For more information about this key, %sclick here%s or refer to our installation manual.'),
                '<a target="_blank"
                    href="https://console.developers.google.com/apis/credentials/wizard?api=maps_backend"
                 >',
                '</a>'
            );

        // Validate.
        $arrFormValidateOpt = $this->validateFormOpt($strArgIdForm);

        $arrAlertHTML = TNTOfficiel_Tools::getAlertHTML($arrFormValidateOpt);
        if (count($arrAlertHTML) > 0) {
            $arrAlertHTML = array(
                array(
                    'type' => 'html',
                    'name' => implode('', $arrAlertHTML)
                )
            );
        }

        // Return form struct.
        return array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array_merge(array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show pickup number'),
                    'name' => 'TNTOFFICIEL_PICKUP_DISPLAY_NUMBER',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => '1',
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google Maps API'),
                    'name' => 'TNTOFFICIEL_GMAP_API_KEY',
                    'size' => 60,
                    'desc' => $strAPIKGMDesc,
                    'required' => false,
                ),
            ), $arrAlertHTML),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
                'name' => $strArgIdForm
            )
        );
    }

    /**
     * Validate the Opt configuration form and return error messages.
     *
     * @return array
     */
    private function validateFormOpt($strArgIdForm)
    {
        TNTOfficiel_Logstack::log();

        $arrAlert = array(
            'error' => array(),
            'warning' => array(),
            'success' => array()
        );

        // If form submitted.
        if (Tools::isSubmit($strArgIdForm)) {
            $arrErrors = array();

            $boolShowPickupNumber = (bool)pSQL(Tools::getValue('TNTOFFICIEL_PICKUP_DISPLAY_NUMBER'));
            $strGMAPAPIKey = pSQL(Tools::getValue('TNTOFFICIEL_GMAP_API_KEY'));

            /*
             * Save
             */

            // If no errors.
            if (count($arrErrors) == 0) {
                Configuration::updateValue('TNTOFFICIEL_PICKUP_DISPLAY_NUMBER', $boolShowPickupNumber);
                Configuration::updateValue('TNTOFFICIEL_GMAP_API_KEY', $strGMAPAPIKey);
            }

            /*
             * Messages (Errors, Warning, etc.)
             */

            if (count($arrErrors) > 0) {
                $arrAlert['error'][] = (count($arrErrors) === 1 ? array_pop($arrErrors) : $arrErrors);
            } else {
                $arrAlert['success'][] = $this->l('Settings updated.');
            }
        }

        return $arrAlert;
    }

    /**
     * Get the Carrier form structure for Helper.
     *
     * @return array
     */
    private function getFormCarrierConfig($strArgIdForm)
    {
        TNTOfficiel_Logstack::log();

        // Validate.
        $arrFormValidateCarrier = $this->validateFormCarrier($strArgIdForm);

        $arrAlertHTML = TNTOfficiel_Tools::getAlertHTML($arrFormValidateCarrier);
        if (count($arrAlertHTML) > 0) {
            $arrAlertHTML = array(
                array(
                    'type' => 'html',
                    'name' => implode('', $arrAlertHTML)
                )
            );
        }

        $arrInputCarriers = array();
        $arrAllCurrentCarrier = array_flip(TNTOfficiel_Carrier::getAllAccountCarrierID());
        foreach ($arrAllCurrentCarrier as $strCarrierCode) {
            $arrInputCarriers[] = array(
                'type' => 'text',
                'label' => TNTOfficiel_Carrier::getCarrierCodeName($strCarrierCode),
                'name' => $strCarrierCode,
                'disabled' => true,
                'required' => false,
                'size' => 250
            );
        }

        // Return form struct.
        return array(
            'legend' => array(
                'title' => $this->l('Carriers'),
                'icon' => 'icon-truck'
            ),
            'description' => sprintf(
                $this->l('This list is used to check the carriers associated with the "%s" module.'),
                TNTOfficiel::MODULE_NAME
            ).'<br />'.$this->l('If carriers are re-created, existing ones are deleted and new ones are added.'),
            'input' => array_merge($arrInputCarriers, $arrAlertHTML),
            'buttons' => array(
                'back' => array(
                    'title' => $this->l('Recreate all carriers'),
                    'href' => '#',
                    'id' => 'TNTRenewCarriers',
                    //'name' => $strArgIdForm
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-refresh'
                )
            )
        );
    }

    /**
     * Validate the Carrier configuration form and return error messages.
     *
     * @return array
     */
    private function validateFormCarrier()
    {
        TNTOfficiel_Logstack::log();

        $arrAlert = array(
            'error' => array(),
            'warning' => array(),
            'success' => array()
        );

        // Check if no carrier has been created (eg : updating module).
        if (count(TNTOfficiel_Carrier::getAllCurrentCarrierID()) === 0) {
            // Creating them.
            TNTOfficiel_Carrier::installAllCurrentCarrier();
        }

        $arrDissociated = array();
        $arrDeleted = array();
        $arrDisabled = array();
        $arrExcluded = array();

        $arrAllCurrentCarrier = TNTOfficiel_Carrier::getAllAccountCarrierID();
        foreach ($arrAllCurrentCarrier as $intCarrierCurrentID) {
            $objCarrierCurrent = new Carrier($intCarrierCurrentID);
            if ($objCarrierCurrent->deleted) {
                $arrDeleted[] = $intCarrierCurrentID;
            } elseif (!$objCarrierCurrent->active) {
                $arrDisabled[] = $intCarrierCurrentID;
            }
            if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($intCarrierCurrentID)) {
                $arrDissociated[] = $intCarrierCurrentID;
            }
        }

        $arrAllCarrier = Carrier::getCarriers(
            //(int)$this->context->language->id,
            (int)Configuration::get('PS_LANG_DEFAULT'),
            false,
            false,
            false,
            null,
            Carrier::CARRIERS_MODULE
        );
        foreach ($arrAllCarrier as $arrCarrier) {
            $intCarrierID = (int)$arrCarrier['id_carrier'];

            if (!in_array($intCarrierID, $arrAllCurrentCarrier)
                && TNTOfficiel_Carrier::isTNTOfficielCarrierID($intCarrierID)
            ) {
                $boolResult = false;
                // Load TNT carrier.
                $objCarrierTNT = new Carrier($intCarrierID);
                // If TNT carrier object not available.
                if (Validate::isLoadedObject($objCarrierTNT) && (int)$objCarrierTNT->id === $intCarrierID) {
                    $objCarrierTNT->active = false;
                    $objCarrierTNT->deleted = true;
                    $boolResult = $objCarrierTNT->save();
                }
                if (!$boolResult) {
                    $arrExcluded[] = $intCarrierID;
                }
            }
        }

        if (count($arrDissociated) > 0) {
            $arrAlert['error'][] = sprintf(
                $this->l('Dissociated carrier(s) : %s.'),
                'ID '.implode(', ', $arrDissociated)
            );
        }
        if (count($arrDeleted) > 0) {
            $arrAlert['error'][] = sprintf(
                $this->l('Deleted carrier(s) : %s.'),
                'ID '.implode(', ', $arrDeleted)
            );
        }
        if (count($arrDisabled) > 0) {
            $arrAlert['warning'][] = sprintf(
                $this->l('Disabled carrier(s) : %s.'),
                'ID '.implode(', ', $arrDisabled)
            );
        }
        if (count($arrExcluded) > 0) {
            $arrAlert['warning'][] = sprintf(
                $this->l('Excluded carrier(s) : %s.'),
                'ID '.implode(', ', $arrExcluded)
            );
        }

        return $arrAlert;
    }

    /**
     * Is ready.
     */
    public static function isReady()
    {
        TNTOfficiel_Logstack::log();

        $objContext = Context::getContext();
        if (property_exists($objContext, 'controller')) {
            // Controller.
            $objController = $objContext->controller;
            if ($objController !== null) {
                // Get Controller Name.
                $strCurrentControllerName = Tools::strtolower(get_class($objController));

                switch ($strCurrentControllerName) {
                    // Prevent extra processing (ex: .map file).
                    case 'pagenotfoundcontroller':
                    case 'adminnotfoundcontroller':
                        return false;
                    default:
                        // Nothing to do by default.
                        break;
                }
            }
        }

        // If module not installed (ps_module:id_module) $this->id > 0
        // or module not activated (ps_module:active) $this->active ps_module_shop
        // or module configuration not authenticated
        // or override class and controllers are disabled (performance/debug).
        if (!Module::isInstalled(TNTOfficiel::MODULE_NAME)
            || !Module::isEnabled(TNTOfficiel::MODULE_NAME)
            || TNTOfficiel_Credentials::getValidatedDateTime() === null
            || (bool)Configuration::get('PS_DISABLE_OVERRIDES')
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getCommonVariable()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objLink = $objContext->link;
        $objShop = $objContext->shop;

        // Controller.
        $objController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = get_class($objController);

        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $arrCountryList = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $arrCountryList = Country::getCountries($this->context->language->id, true);
        }

        // Javascript config.
        $arrTNTOfficiel = array(
            'timestamp' => microtime(true) * 1000,
            'module' => array(
                'name' => TNTOfficiel::MODULE_NAME,
                'title' => TNTOfficiel::CARRIER_NAME,
                'version' => $this->version,
                'enabled' => TNTOfficiel_Credentials::getValidatedDateTime() !== null,
                'ready' => TNTOfficiel::isReady()
            ),
            'config' => array(
                'google' => array(
                    'map' => array(
                        'url' => 'https://maps.googleapis.com/maps/api/js',
                        'data' => array(
                            'v' => '3.20',
                            'key' => Configuration::get('TNTOFFICIEL_GMAP_API_KEY')
                        ),
                        'default' => array(
                            "lat"  => 46.227638,
                            "lng"  => 2.213749,
                            "zoom" => 4
                        )
                    )
                )
            ),
            'translate' => array(
                'successRenewCarriers' => htmlentities(
                    sprintf($this->l('The \'%s\' carriers have been successfully renewed'), TNTOfficiel::CARRIER_NAME)
                ),
                'validateDeliveryAddress' => htmlentities($this->l('Validate your delivery address')),
                'unknownPostalCode' => htmlentities($this->l('Unknown postal code')),
                'validatePostalCodeDeliveryAddress' => htmlentities(
                    $this->l('Please edit and validate the postal code of your delivery address.')
                ),
                'unrecognizedCity' => htmlentities($this->l('Unrecognized city')),
                'selectCityDeliveryAddress' => htmlentities(
                    $this->l('Please select the city from your delivery address.')
                ),
                'postalCode' => htmlentities($this->l('Postal code')),
                'city' => htmlentities($this->l('City')),
                'validate' => htmlentities($this->l('Validate')),
                'validateAdditionalCarrierInfo' => htmlentities(
                    $this->l('Please confirm the form with additional information for the carrier.')
                ),
                'errorInvalidPhoneNumber' => htmlentities($this->l('The phone number must be 10 digits')),
                'errorInvalidEMail' => htmlentities($this->l('The email is invalid')),
                'errorNoDeliveryOptionSelected' => htmlentities($this->l('No delivery options selected.')),
                'errorNoDeliveryAddressSelected' => htmlentities($this->l('No delivery address selected.')),
                'errorNoDeliveryPointSelected' => htmlentities($this->l('No delivery point selected.')),
                'errorUnknow' => htmlentities($this->l('An error has occurred.')),
                'errorTechnical' => htmlentities($this->l('A technical error occurred.')),
                'errorConnection' => htmlentities($this->l('A connection error occurred.'))
            ),
            'link' => array(
                'controller' => Tools::strtolower($strCurrentControllerName),
                'front' => array(
                    'shop' => $objShop->getBaseURL(true),
                    'module' => array(
                        'boxRelayPoints' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'boxRelayPoints'),
                            true
                        ),
                        'boxDropOffPoints' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'boxDropOffPoints'),
                            true
                        ),
                        'saveProductInfo' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'saveProductInfo'),
                            true
                        ),
                        'checkPaymentReady' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'carrier',
                            array('action' => 'checkPaymentReady'),
                            true
                        ),
                        'storeDeliveryInfo' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'storeDeliveryInfo'),
                            true
                        ),
                        'getAddressCities' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'getCities'),
                            true
                        ),
                        'updateAddressDelivery' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'updateDeliveryAddress'),
                            true
                        ),
                        'checkAddressPostcodeCity' => $objLink->getModuleLink(
                            TNTOfficiel::MODULE_NAME,
                            'address',
                            array('action' => 'checkPostcodeCity'),
                            true
                        )
                    ),
                    'page' => array(
                        'order' => $objLink->getPageLink('order', true)
                    )
                ),
                'back' => null,
                'image' => _MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/img/'
            ),
            'country' => array(
                'list' => $arrCountryList
            ),
            'carrier' => array_flip(TNTOfficiel_Carrier::getAllCurrentCarrierID()),
            'cart' => array(
                // Is One Page Checkout.
                // $strCurrentControllerName === 'OrderOpcController'
                'isOPC' => Configuration::get('PS_ORDER_PROCESS_TYPE') == 1,
                'isCarrierListDisplay' => false
            ),
            'order' => array(
                'isTNT' => false
            )
        );

        return $arrTNTOfficiel;
    }

    /**
     * HOOK (AKA backOfficeHeader) called between the HEAD tags. Ideal location for adding JavaScript and CSS files.
     * Hook called even if module is disabled !
     *
     * @param $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayBackOfficeHeader($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $objHookCookie = $arrArgHookParams['cookie'];

        // Controller.
        $objController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = Tools::strtolower(get_class($objController));

        $strAssetCSSPath = $this->getPathUri().'views/css/'.TNTOfficiel::MODULE_RELEASE.'/';
        //$strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

        // Global Admin CSS.
        $objController->addCSS($strAssetCSSPath.'Admin.css', 'all');


        $arrJSONTNTOfficiel = $this->getCommonVariable();
        $arrJSONTNTOfficiel['link']['back'] = array(
            'module' => array(
                'addParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=addParcel&ajax=true',
                'removeParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=removeParcel&ajax=true',
                'updateParcelUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=updateParcel&ajax=true',
                'checkShippingDateValidUrl' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=checkShippingDateValid&ajax=true',
                'storeDeliveryInfo' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=storeDeliveryInfo&ajax=true',
                'boxRelayPoints' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=boxRelayPoints&ajax=true',
                'boxDropOffPoints' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=boxDropOffPoints&ajax=true',
                'saveProductInfo' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=saveProductInfo&ajax=true',
                'renewCurrentCarriers' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=renewCurrentCarriers&ajax=true',
                'getAddressCities' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=getCities&ajax=true',
                'updateAddressDelivery' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=updateDeliveryAddress&ajax=true',
                'checkAddressPostcodeCity' =>
                    $this->context->link->getAdminLink('AdminTNTOfficiel').'&action=checkPostcodeCity&ajax=true',
            )
        );

        $arrJSONTNTOfficiel['translate']['back'] = array(
            'updateSuccessfulStr' => htmlentities($this->l('Update successful')),
            'deleteStr' => htmlentities($this->l('Delete')),
            'updateStr' => htmlentities($this->l('Update')),
            'atLeastOneParcelStr' => htmlentities($this->l('An order requires at least one parcel'))
        );

        if (!array_key_exists('alert', $arrJSONTNTOfficiel)
            || !is_array($arrJSONTNTOfficiel['alert'])
        ) {
            $arrJSONTNTOfficiel['alert'] = array(
                'error' => array(),
                'warning' => array(),
                'success' => array()
            );
        }

        // Cookie TNTOfficielError is used to display error message once after redirect.
        if (!empty($objHookCookie->TNTOfficielError)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['error'][] = $objHookCookie->TNTOfficielError;
            // Delete cookie.
            $objHookCookie->TNTOfficielError = null;
        }
        if (!empty($objHookCookie->TNTOfficielWarning)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['warning'][] = $objHookCookie->TNTOfficielWarning;
            // Delete cookie.
            $objHookCookie->TNTOfficielWarning = null;
        }
        if (!empty($objHookCookie->TNTOfficielSuccess)) {
            // Add error message to the admin page if exists.
            $arrJSONTNTOfficiel['alert']['success'][] = $objHookCookie->TNTOfficielSuccess;
            // Delete cookie.
            $objHookCookie->TNTOfficielSuccess = null;
        }

        // Add TNTOfficiel global variable with others in main inline script.
        // This Media method is not applied on Admin page in PS 1.6.0.5.
        //Media::addJsDef(array('TNTOfficiel' => $arrJSONTNTOfficiel));

        TNTOfficiel_Logstack::dump(array(
            'ajax' => $objController->ajax,
            'controller_type' => $objController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController()
        ));

        $strJSONTNTOfficiel = Tools::jsonEncode($arrJSONTNTOfficiel);

        return <<<HTML
<script type="text/javascript">

    var TNTOfficiel = TNTOfficiel || {};
    $.extend(true, TNTOfficiel, $strJSONTNTOfficiel);

</script>
HTML;
    }

    /**
     * HOOK called to include CSS or JS files in the Back-Office header.
     *
     * @param array $arrArgHookParams
     */
    public function hookActionAdminControllerSetMedia($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        // Controller.
        $objController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = Tools::strtolower(get_class($objController));

        $strAssetCSSPath = $this->getPathUri().'views/css/'.TNTOfficiel::MODULE_RELEASE.'/';
        $strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

        $objController->addCSS($strAssetCSSPath.'global.css', 'all');
        $objController->addJS($strAssetJSPath.'global.js');

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Do nothing.
            return;
        }

        switch ($strCurrentControllerName) {
            // Back-Office Order.
            case 'adminorderscontroller':
                // Get Order.
                $intOrderIDView = Tools::getValue('vieworder');
                // No order to view : Order list.
                if ($intOrderIDView === false) {
                    $objController->addJS($strAssetJSPath.'AdminOrdersSubmitBulk.js');
                } else {
                    // Form.css required for address-city-check, ExtraData
                    $objController->addCSS($strAssetCSSPath.'form.css', 'all');
                    //
                    $objController->addCSS($strAssetCSSPath.'carrier.css', 'all');

                    // FancyBox required to display form (cp/ville check).
                    $objController->addJqueryPlugin('fancybox');
                    $objController->addJS($strAssetJSPath.'address.js');

                    // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
                    $objController->addJS($strAssetJSPath.'lib/string.js');
                    // $.fn.nanoScroller, required by carrierDeliveryPoint.js
                    $objController->addJS($strAssetJSPath.'lib/nanoscroller/jquery.nanoscroller.min.js');
                    $objController->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

                    $objController->addJS($strAssetJSPath.'carrierDeliveryPoint.js');
                    $objController->addJS($strAssetJSPath.'carrierAdditionalInfo.js');
                    $objController->addJS($strAssetJSPath.'AdminOrder.js');
                }
                break;
            case 'adminaddressescontroller':
                // Form.css required for address-city-check, ExtraData
                $objController->addCSS($strAssetCSSPath.'form.css', 'all');

                // FancyBox required to display form (cp/ville check).
                $objController->addJqueryPlugin('fancybox');
                $objController->addJS($strAssetJSPath.'address.js');
                break;
            default:
                break;
        }

        TNTOfficiel_Logstack::dump(array(
            'ajax' => $objController->ajax,
            'controller_type' => $objController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController()
        ));
    }

    /**
     * HOOK (AKA Header) displayed in head tag on Front-Office.
     *
     * @param array $arrArgHookParams
     *
     * @return string
     */
    public function hookDisplayHeader($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        //$objHookCart = $arrArgHookParams['cart'];

        $objContext = $this->context;

        // Controller.
        $objController = $objContext->controller;
        // Get Controller Name.
        $strCurrentControllerName = Tools::strtolower(get_class($objController));

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Display nothing.
            return '';
        }

        $arrJSONTNTOfficiel = $this->getCommonVariable();
        // Add TNTOfficiel global variable with others in main inline script.
        Media::addJsDef(array('TNTOfficiel' => $arrJSONTNTOfficiel));

        // Google Font: Open Sans.
        $objController->addCSS('https://fonts.googleapis.com/css?family=Open+Sans:400,700', 'all');

        $strAssetCSSPath = $this->getPathUri().'views/css/'.TNTOfficiel::MODULE_RELEASE.'/';
        $strAssetJSPath = $this->getPathUri().'views/js/'.TNTOfficiel::MODULE_RELEASE.'/';

        $objController->addCSS($strAssetCSSPath.'global.css', 'all');
        $objController->addJS($strAssetJSPath.'global.js');

        // Switch Controller Name.
        switch ($strCurrentControllerName) {
            // Front-Office Order History +guest.
            case 'historycontroller':
            case 'guesttrackingcontroller':
                // Form.css required for displayOrderDetail.tpl
                $objController->addCSS($strAssetCSSPath.'form.css', 'all');
                break;
            // Front-Office Address.
            case 'addresscontroller':
                // Front-Office 5 step Guess Checkout Address.
            case 'authcontroller':
                // Form.css required for address-city-check, ExtraData
                $objController->addCSS($strAssetCSSPath.'form.css', 'all');

                // FancyBox required to display form (cp/ville check).
                $objController->addJqueryPlugin('fancybox');
                $objController->addJS($strAssetJSPath.'address.js');
                break;

            // Front-Office Cart Process.
            case 'ordercontroller':
                // (int)Tools::getValue('step')
                $intOrderStep = (int)$objController->step;

                // Fix for undefined constant in PS 1.6.0.5.
                $intOrderStepAddresses = (int)(defined('OrderController::STEP_ADDRESSES') ?
                    OrderController::STEP_ADDRESSES : 1);
                $intOrderStepDelivery = (int)(defined('OrderController::STEP_DELIVERY') ?
                    OrderController::STEP_DELIVERY : 2);
                //$intOrderStepPayment = (int)(defined('OrderController::STEP_PAYMENT') ?
                //  OrderController::STEP_PAYMENT : 3);

                if ($intOrderStep == $intOrderStepAddresses) {
                    /* Step 1 : 03. Address. */

                    // Form.css required for address-city-check
                    $objController->addCSS($strAssetCSSPath.'form.css', 'all');

                    // FancyBox required to display form (cp/ville check).
                    $objController->addJqueryPlugin('fancybox');
                    $objController->addJS($strAssetJSPath.'address.js');
                } elseif ($intOrderStep == $intOrderStepDelivery) {
                    /* Step 2 : 04. Delivery. */

                    // Form.css required for address-city-check, ExtraData
                    $objController->addCSS($strAssetCSSPath.'form.css', 'all');
                    //
                    $objController->addCSS($strAssetCSSPath.'carrier.css', 'all');

                    // Prestashop Validation system.
                    $objController->addJS(_PS_JS_DIR_.'validate.js');

                    // FancyBox required to display form (cp/ville check).
                    $objController->addJqueryPlugin('fancybox');
                    $objController->addJS($strAssetJSPath.'address.js');

                    // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
                    $objController->addJS($strAssetJSPath.'lib/string.js');
                    // $.fn.nanoScroller, required by carrierDeliveryPoint.js
                    $objController->addJS($strAssetJSPath.'lib/nanoscroller/jquery.nanoscroller.min.js');
                    $objController->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

                    $objController->addJS($strAssetJSPath.'carrierDeliveryPoint.js');
                    $objController->addJS($strAssetJSPath.'carrierAdditionalInfo.js');
                    // TNTOfficiel_deliveryPointsBox, used in displayAjaxBoxDeliveryPoints.tpl
                    $objController->addJS($strAssetJSPath.'carrier.js');
                }
                break;

            // Front-Office Cart Process (OnePageCheckout).
            case 'orderopccontroller':
            //case 'supercheckoutsupercheckoutmodulefrontcontroller':
                // Form.css required for address-city-check, ExtraData
                $objController->addCSS($strAssetCSSPath.'form.css', 'all');
                //
                $objController->addCSS($strAssetCSSPath.'carrier.css', 'all');

                // Prestashop Validation system.
                $objController->addJS(_PS_JS_DIR_.'validate.js');

                // FancyBox required to display form (cp/ville check).
                $objController->addJqueryPlugin('fancybox');
                $objController->addJS($strAssetJSPath.'address.js');

                // TNTOfficiel_inflate() TNTOfficiel_deflate(), required by carrierDeliveryPoint.js
                $objController->addJS($strAssetJSPath.'lib/string.js');
                // $.fn.nanoScroller, required by carrierDeliveryPoint.js
                $objController->addJS($strAssetJSPath.'lib/nanoscroller/jquery.nanoscroller.min.js');
                $objController->addCSS($strAssetJSPath.'lib/nanoscroller/nanoscroller.css', 'all');

                $objController->addJS($strAssetJSPath.'carrierDeliveryPoint.js');
                $objController->addJS($strAssetJSPath.'carrierAdditionalInfo.js');
                // TNTOfficiel_deliveryPointsBox, used in displayAjaxBoxDeliveryPoints.tpl
                $objController->addJS($strAssetJSPath.'carrier.js');
                break;

            default:
                break;
        }


        TNTOfficiel_Logstack::dump(array(
            'ajax' => $objController->ajax,
            'controller_type' => $objController->controller_type,
            'controllername' => $strCurrentControllerName,
            'controllerfilename' => Dispatcher::getInstance()->getController(),
            'js' => $arrJSONTNTOfficiel
        ));

        // Display nothing.
        return '';
    }


    /**
     * HOOK (AKA beforeCarrier) displayed before the carrier list on Front-Office.
     *
     * @param type $arrArgHookParams
     *
     * @return type
     */
    public function hookDisplayBeforeCarrier($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCart = $objContext->cart;

        //$arrHookCarrierList = $arrArgHookParams['carriers'];

        //$arrHookDeliveryOptionList = $arrArgHookParams['delivery_option_list'];
        //$arrHookDeliveryOption = $arrArgHookParams['delivery_option'];
        //$strHookCarrierChecked = Cart::desintifier($arrArgHookParams['checked']);

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Display nothing.
            return '';
        }

        // Force $objCart->id_carrier Update using autoselect if not set (without using cache).
        // $objCart->id_carrier maybe incorrectly set when autoselection determine current selected carrier.
        // e.g: only one core carrier available, input radio is always already preselected,
        // but not $objCart->id_carrier since setDeliveryOption() was not used (and no change is possible).
        $objCart->setDeliveryOption($objCart->getDeliveryOption(null, false, false));
        $objCart->save();

        $boolCityPostCodeIsValid = TNTOfficiel_Address::checkPostCodeCityForCart($objCart);

        $this->smarty->assign(array(
            'boolCityPostCodeIsValid' => $boolCityPostCodeIsValid,
            'id_address_delivery' => (int)$objCart->id_address_delivery
        ));

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayBeforeCarrier.tpl')
        /*.'<pre>'.Cart::desintifier($arrArgHookParams['checked'])."\n"
        .$arrArgHookParams['checked']."\n"
        .TNTOfficiel_Logstack::encJSON($arrArgHookParams['delivery_option_list'])."\n"
        .TNTOfficiel_Logstack::encJSON($arrArgHookParams['delivery_option'])
        .'</pre>'*/;
    }

    /**
     * HOOK (AKA extraCarrier) called after the list of available carriers, during the order process.
     * Ideal location to add a carrier, as added by a module.
     * Display TNT products during the order process.
     *
     * 5 Steps :
     *  global.js:28            ready()
     *      global.js:428           bindUniform()
     *  Output of this hook is also applied after a delivery mode selection.
     *  cart-summary.js:1047    updateExtraCarrier()            {"content":"…"} inside #HOOK_EXTRACARRIER_<IDADDRESS>.
     *
     * OPC :
     *  order-opc.js:539        updateCarrierSelectionAndGift()
     *  AJAX: updateCarrierAndGetPayments >>> HOOK: actionCarrierProcess
     *      cart-summary.js:786     updateCartSummary()         {"summary":"…"}
     *      order-opc.js:315        updatePaymentMethods()      {"HOOK_TOP_PAYMENT":"…","HOOK_PAYMENT":"…"}
     *      order-opc.js:306        updateCarrierList()         {"HOOK_BEFORECARRIER":"…"}
     *      global.js:428           bindUniform()
     *
     * @param $arrArgHookParams array
     *
     * @return mixed
     */
    public function hookDisplayCarrierList($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;

        $objHookCart = $arrArgHookParams['cart'];
        $intCartID = (int)$objHookCart->id;
        $intCarrierIDSelected = (int)$objHookCart->id_carrier;

        $objHookAddress = $arrArgHookParams['address'];

        $objCustomer = $objContext->customer;
        //$objCustomer = new Customer($objHookCart->id_customer);


        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Display nothing.
            return '';
        }

        // Prevent AJAX bug with carrier id inconsistency.
        $objHookCart->save();


        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        // If fail.
        if ($objTNTCartModel === null) {
            // Display nothing.
            return '';
        }

        // Get the current selected TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($intCarrierIDSelected);
        // Get the carriers from the middleware
        $arrAvailableTNTCarriers = TNTOfficiel_Carrier::getMDWTNTCarrierList($objHookCart, $objCustomer);

        // Update cart carrier code
        $objTNTCartModel->setCarrierCode($strCarrierCode);
        $objTNTCartModel->save();

        // Validate using the customer and address as default values.
        $arrFormCartAddressValidate = TNTOfficiel_Address::storeDeliveryInfo(
            $objHookCart,
            $objTNTCartModel->customer_email ? $objTNTCartModel->customer_email : $objCustomer->email,
            $objTNTCartModel->customer_mobile ? $objTNTCartModel->customer_mobile : $objHookAddress->phone_mobile,
            $objTNTCartModel->address_building,
            $objTNTCartModel->address_accesscode,
            $objTNTCartModel->address_floor
        );

        $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();

        $this->smarty->assign(array(
            'carriers' => $arrAvailableTNTCarriers,
            'idcurrentTntCarrier' => $intCarrierIDSelected,
            'strCurrentCarrierCode' => TNTOfficiel_Carrier::getCurrentCarrierCode($intCarrierIDSelected),
            'extraAddressDataValues' => $arrFormCartAddressValidate,
            'extraAddressDataValid' => $arrFormCartAddressValidate['stored'] ? 'true' : 'false',
            'current_delivery_option' => TNTOfficiel_Cart::getDeliveryOption($objHookCart),
            'deliveryPoint' => $arrDeliveryPoint,
        ));

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayCarrierList.tpl')
        /*.'<pre>'.$strCarrierCode."\n"
        .TNTOfficiel_Logstack::encJSON($objHookCart)."\n"
        .TNTOfficiel_Logstack::encJSON($objTNTCartModel)
        .'</pre>'*/;
    }

    /**
     * HOOK (AKA newOrder) called during the new order creation process, right after it has been created.
     * Called from /classes/PaymentModule.php
     *
     * Create XETT/PEX address if required and create parcels.
     *
     * @param $arrArgHookParams array
     *
     * @return bool
     */
    public function hookActionValidateOrder($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookOrder = $arrArgHookParams['order'];
        $intOrderID = (int)$objHookOrder->id;

        //$objHookCustomer = $arrArgHookParams['customer'];
        //$objHookCurrency = $arrArgHookParams['currency'];
        //$objHookOrderStatus = $arrArgHookParams['orderStatus'];

        // Get the Cart from Order (not from context).
        $objCart = Cart::getCartByOrderId($intOrderID);
        // $objCart = new Cart($objHookOrder->id_cart);
        $intCartID = (int)$objCart->id;

        // Get the current TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($objHookOrder->id_carrier);
        // If carrier of cart is not a current carrier from TNT module.
        if ($strCarrierCode === null) {
            // Do not have to save this cart.
            return true;
        }

        $arrDeliveryPoint = array();

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        if ($objTNTCartModel !== null) {
            $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();
        }

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID);
        if ($objTNTOrderModel !== null) {
            $objTNTOrderModel->setCarrierCode($strCarrierCode);

            // Create Address for delivery point.
            // DEPOT (Agence TNT) : PEX
            // DROPOFFPOINT (Commerçant partenaire) : XETT
            if (isset($arrDeliveryPoint['pex']) && strpos($strCarrierCode, 'DEPOT')) {
                $objHookOrder->id_address_delivery = (int)TNTOfficiel_Order::createNewAddress(
                    $arrDeliveryPoint,
                    $intOrderID
                );
                $objTNTOrderModel->carrier_pex = $arrDeliveryPoint['pex'];
            } elseif (isset($arrDeliveryPoint['xett']) && strpos($strCarrierCode, 'DROPOFFPOINT')) {
                $objHookOrder->id_address_delivery = (int)TNTOfficiel_Order::createNewAddress(
                    $arrDeliveryPoint,
                    $intOrderID
                );
                $objTNTOrderModel->carrier_xett = $arrDeliveryPoint['xett'];
            }

            // Save TNT order.
            $objTNTOrderModel->save();

            // Creates parcels for order.
            TNTOfficiel_Parcel::createParcels($objCart, $intOrderID);

            // Update shipping date if available.
            TNTOfficiel_Order::updateShippingDate($intOrderID);
        }

        return true;
    }

    /**
     * HOOK (AKA adminOrder) called when the order's details are displayed, below the Client Information block.
     * Parcel management for orders with a tnt carrier.
     *
     * @param $arrArgHookParams array
     *
     * @return mixed
     */
    public function hookDisplayAdminOrder($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        //$objHookCookie = $arrArgHookParams['cookie'];
        //$objHookCart = $arrArgHookParams['cart'];

        $intHookOrderID = (int)$arrArgHookParams['id_order'];

        // Get the Cart from Order (not from context).
        $objCart = Cart::getCartByOrderId($intHookOrderID);
        $intCartID = (int)$objCart->id;

        $objPSOrder = new Order($intHookOrderID);

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Display nothing.
            return '';
        }

        // If not a TNT Carrier.
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
            // Display nothing.
            return '';
        }

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        // If fail.
        if ($objTNTCartModel === null) {
            // Display nothing.
            return '';
        }

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID);
        if ($objTNTOrderModel === null) {
            $this->displayAdminError(
                sprintf($this->l('Error : %s'), 'Unable to load shipping date for id #'.$intHookOrderID)
            );

            // Display nothing.
            return '';
        }

        // Get the current TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($objPSOrder->id_carrier);
        // If not shipped, carrier_code not set and available from carrier from order.
        if (!$objTNTOrderModel->isShipped()
            && $strCarrierCode !== null
            && $objTNTOrderModel->getCarrierCode() !== $strCarrierCode
        ) {
            // Update carrier code according to carrier id.
            $objTNTOrderModel->setCarrierCode($strCarrierCode);
        }


        $strPickUpNumber = Configuration::get('TNTOFFICIEL_PICKUP_DISPLAY_NUMBER') ?
            $objTNTOrderModel->pickup_number : null;

        // Creates parcels for order if not already done.
        TNTOfficiel_Parcel::createParcels($objCart, $intHookOrderID);
        // Get the parcels.
        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intHookOrderID);


        // Check and display error about shipping date.
        if (!Tools::isSubmit('submitState')) {
            // Update shipping date if available.
            $arrResult = TNTOfficiel_Order::checkShippingDateBeforeUpdateOrderStatus($objPSOrder);
            if (is_array($arrResult)) {
                if (count($arrResult['errors']) > 0) {
                    $this->displayAdminError(array_shift($arrResult['errors']));
                }
            }
        }

        $shippingDate = $objTNTOrderModel->shipping_date;
        $dueDate = '';
        if ($objTNTOrderModel->due_date && $objTNTOrderModel->due_date !== '0000-00-00') {
            $arrTmpDate = explode('-', $objTNTOrderModel->due_date);
            $dueDate = $arrTmpDate[2].'/'.$arrTmpDate[1].'/'.$arrTmpDate[0];
        }

        $arrTmpDate = explode('-', $objTNTOrderModel->start_date);
        $firstAvailableDate = $arrTmpDate[2].'/'.$arrTmpDate[1].'/'.$arrTmpDate[0];


        $strDeliveryPointType = null;
        if (strpos($objTNTOrderModel->getCarrierCode(), 'DEPOT')) {
            $strDeliveryPointType = 'pex';
        } elseif (strpos($objTNTOrderModel->getCarrierCode(), 'DROPOFFPOINT')) {
            $strDeliveryPointType = 'xett';
        }
        $strDeliveryPointCode = null;
        if (strpos($objTNTOrderModel->getCarrierCode(), 'DEPOT') && $objTNTOrderModel->carrier_pex) {
            $strDeliveryPointCode = $objTNTOrderModel->carrier_pex;
        } elseif (strpos($objTNTOrderModel->getCarrierCode(), 'DROPOFFPOINT') && $objTNTOrderModel->carrier_xett) {
            $strDeliveryPointCode = $objTNTOrderModel->carrier_xett;
        }

        // Validate using the customer and address as default values.
        $objCustomer = new Customer((int)$objPSOrder->id_customer);
        $objAddress = new Address((int)$objPSOrder->id_address_delivery);

        $arrFormCartAddressValidate = TNTOfficiel_Address::storeDeliveryInfo(
            $objCart,
            $objTNTCartModel->customer_email ? $objTNTCartModel->customer_email : $objCustomer->email,
            $objTNTCartModel->customer_mobile ? $objTNTCartModel->customer_mobile : $objAddress->phone_mobile,
            $objTNTCartModel->address_building,
            $objTNTCartModel->address_accesscode,
            $objTNTCartModel->address_floor
        );

        $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();

        $strDeliveryPointCodeCart = null;
        if (strpos($objTNTOrderModel->getCarrierCode(), 'DEPOT')
            && array_key_exists('pex', $arrDeliveryPoint)
            && $arrDeliveryPoint['pex']
        ) {
            $strDeliveryPointCodeCart = $arrDeliveryPoint['pex'];
        } elseif (strpos($objTNTOrderModel->getCarrierCode(), 'DROPOFFPOINT')
            && array_key_exists('xett', $arrDeliveryPoint)
            && $arrDeliveryPoint['xett']
        ) {
            $strDeliveryPointCodeCart = $arrDeliveryPoint['xett'];
        }

        if ($strDeliveryPointCode !== $strDeliveryPointCodeCart) {
            $arrDeliveryPoint = array();
        }

        $arrSchedules = TNTOfficiel_Address::getSchedules($arrDeliveryPoint);

        $boolIsReceiverB2B = !!trim($objAddress->company);
        if (!(
            strpos($objTNTOrderModel->getCarrierCode(), 'DROPOFFPOINT')
            || strpos($objTNTOrderModel->getCarrierCode(), 'DEPOT')
            || ($boolIsReceiverB2B === true && strpos($objTNTOrderModel->getCarrierCode(), 'ENTERPRISE'))
            || ($boolIsReceiverB2B === false && strpos($objTNTOrderModel->getCarrierCode(), 'INDIVIDUAL'))
        )) {
            $this->displayAdminError(
                sprintf(
                    $this->l('Delivery address is %s, but not the carrier.'),
                    $boolIsReceiverB2B ? $this->l('B2B') : $this->l('B2C')
                ) . ' ' . $this->l('Please verify "Company" field for expedition creation.'),
                $this->context->link->getAdminLink('AdminAddresses')
                .'&amp;id_address='.$objPSOrder->id_address_delivery.'&amp;updateaddress'
                .'&amp;back='.urlencode($this->context->link->getAdminLink('AdminOrders', false)
                .'&id_order='.$objPSOrder->id.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders'))
            );
        }

        // Check if the city match the postcode in the selected delivery address.
        $boolDirectAddressCheck = !TNTOfficiel_Address::checkPostCodeCityForCart($objPSOrder);
        if ($boolDirectAddressCheck) {
            $this->displayAdminError(
                sprintf(
                    $this->l('Unrecognized zipcode or city in delivery address.'),
                    $objPSOrder->id_address_delivery
                ),
                $this->context->link->getAdminLink('AdminAddresses')
                .'&amp;id_address='.$objPSOrder->id_address_delivery.'&amp;updateaddress'
                .'&amp;back='.urlencode($this->context->link->getAdminLink('AdminOrders', false)
                .'&id_order='.$objPSOrder->id.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders'))
            );
        }

        $this->smarty->assign(array(
            'order' => $objPSOrder,
            'objAddress' => $objAddress,
            'objTNTOrderModel' => $objTNTOrderModel,
            'objTNTCartModel' => $objTNTCartModel,
            'strPickUpNumber' => $strPickUpNumber,
            'arrTNTParcelList' => $arrTNTParcelList,
            'firstAvailableDate' => $firstAvailableDate,
            'shippingDate' => $shippingDate,
            'dueDate' => $dueDate,
            'boolDirectAddressCheck' => $boolDirectAddressCheck,
            'isShipped' => (bool)$objTNTOrderModel->isShipped(),
            'strDeliveryPointType' => $strDeliveryPointType,
            'strDeliveryPointCode' => $strDeliveryPointCode,
            'extraAddressDataValues' => $arrFormCartAddressValidate,
            'arrDeliveryPoint' => $arrDeliveryPoint,
            'arrSchedules' => $arrSchedules
        ));

        $objOrderStateSaveShipment = new OrderState(
            (int)Configuration::get(TNTOfficiel::ORDERSTATE_SAVESHIPMENT),
            (int)$this->context->language->id
        );

        if ($strDeliveryPointType !== null && $strDeliveryPointCode === null) {
            $this->displayAdminError(
                sprintf(
                    $this->l('This order must be finalized before passing the status to %s.'),
                    $objOrderStateSaveShipment->name
                ).' '
                .$this->l('In the CLIENT section, DELIVERY ADDRESS tab, SELECT a delivery point.')
            );
        }
        if ($arrFormCartAddressValidate['length'] !== 0) {
            $this->displayAdminError(
                sprintf(
                    $this->l('This order must be finalized before passing the status to %s.'),
                    $objOrderStateSaveShipment->name
                ).' '
                .$this->l('In the CUSTOMER section, DELIVERY ADDRESS tab, CONFIRM the ADDITIONAL INFORMATION form.')
            );
        }

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
    }


    /**
     * HOOK (AKA updateCarrier) called when a carrier is updated.
     * After editing a Carrier, it is automatically archived and a new carrier is created.
     *
     * @param $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionCarrierUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $intHookCarrierIDModified = $arrArgHookParams['id_carrier'];
        $objHookCarrierNew = $arrArgHookParams['carrier'];

        // Get the current TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($intHookCarrierIDModified);

        // If current carrier code found.
        if ($strCarrierCode !== null) {
            // Update it.
            return TNTOfficiel_Carrier::updateCurrentCarrierID(
                $intHookCarrierIDModified,
                $objHookCarrierNew->id
            );
        }

        return true;
    }

    /**
     * Carrier module : Method triggered form Cart Model if $carrier->need_range == false.
     * Get the cart shipping price without using the ranges.
     * (best price).
     *
     * @param Cart $objArgCart
     *
     * @return mixed
     */
    public function getOrderShippingCostExternal($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $fltPrice = $this->getOrderShippingCost($objArgCart, 0.0);

        return $fltPrice;
    }

    /**
     * Carrier module : Method triggered form Cart Model if $carrier->need_range == true.
     * Get the shipping price depending on the ranges that were set in the back office.
     * Get the shipping cost for a cart (best price), if carrier need range (default).
     *
     * @param Cart $objArgCart
     *
     * @return mixed
     */
    public function getOrderShippingCost($objArgCart, $fltArgShippingCost)
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCustomer = $objContext->customer;

        $intCartID = (int)$objArgCart->id;
        // Unused, Range not included.
        $fltCartExtraShippingCost = (float)$fltArgShippingCost;

        // See comment about current class $id_carrier property.
        $intCarrierID = (int)$this->id_carrier;

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // No shipping cost, not available.
            return false;
        }

        // Do not waste time to get price for some AJAX request.
        // Note : Payment module at order creation, may not set controller property.
        if (property_exists($objContext, 'controller')
            && property_exists($objContext->controller, 'dngp')
            && $objContext->controller->dngp
        ) {
            return false;
        }

        // Get the current TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($intCarrierID);
        // If carrier of cart is not a current carrier from TNT module.
        if ($strCarrierCode === null) {
            // No shipping cost, not available.
            return false;
        }

        $arrAvailableTNTCarriers = TNTOfficiel_Carrier::getMDWTNTCarrierList($objArgCart, $objCustomer);
        // If no available carrier.
        if (empty($arrAvailableTNTCarriers) || empty($arrAvailableTNTCarriers['products'])) {
            // No shipping cost, not available.
            return false;
        }

        // Multi-Shipping with multiple address or different carrier not supported.
        $boolMultiShippingSupport = TNTOfficiel_Cart::isMultiShippingSupport($objArgCart);
        if (!$boolMultiShippingSupport) {
            return false;
        }

        // Get additional shipping cost for cart.
        $fltCartExtraShippingCost = TNTOfficiel_Cart::getCartExtraShippingCost($objArgCart, $intCarrierID);

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        if ($objTNTCartModel !== null) {
            // Get price from selected product (middleware).
            foreach ($arrAvailableTNTCarriers['products'] as $arrProduct) {
                // Found current TNT carrier code.
                if ($strCarrierCode === $arrProduct['id']) {
                    $fltPrice = (float)$arrProduct['price'];

                    return $fltPrice + $fltCartExtraShippingCost;
                }
            }
        }

        // No shipping cost, not available.
        return false;
    }

    /**
     * HOOK (AKA updateOrderStatus) called when an order's status is changed, right before it is actually changed.
     * If status become ORDERSTATE_SAVESHIPMENT, then creates a shipment using middleware service 'saveShipment'.
     *
     * @param $arrArgHookParams
     */
    public function hookActionOrderStatusUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCookie = $arrArgHookParams['cookie'];

        $objHookOrderStateNew = $arrArgHookParams['newOrderStatus'];
        $intHookOrderID = (int)$arrArgHookParams['id_order'];
        $objPSOrder = new Order($intHookOrderID);
        $intOrderStateIDNew = (int)$objHookOrderStateNew->id;

        // Check if it's a TNT carrier.
        $intCarrierID = (int)$objPSOrder->id_carrier;
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($intCarrierID)) {
            // Do nothing.
            return;
        }

        // If new order status must trigger shipment creation.
        if (Configuration::get(TNTOfficiel::ORDERSTATE_SAVESHIPMENT) == $intOrderStateIDNew) {
            //
            $arrResult = TNTOfficiel_Order::checkShippingDateBeforeUpdateOrderStatus($objPSOrder);

            if (is_array($arrResult)) {
                if (count($arrResult['errors']) > 0) {
                    $objHookCookie->TNTOfficielError = sprintf(
                        $this->l('Error : %s'),
                        array_shift($arrResult['errors'])
                    );
                } else {
                    // Save.
                    $boolResponse = TNTOfficiel_Order::saveShipment($objPSOrder);
                    // If the response is a string, there is an error.
                    if (is_string($boolResponse)) {
                        // If response from the TNT SOAP WS.
                        if (Tools::strpos($boolResponse, 'error-tnt-ws:')===0) {
                            $boolResponse = Tools::substr($boolResponse, Tools::strlen('error-tnt-ws:'));
                        }
                        $objHookCookie->TNTOfficielError = $boolResponse;
                    }
                }
                if (count($arrResult['warnings']) > 0) {
                    $objHookCookie->TNTOfficielError = sprintf(
                        $this->l('Error : %s'),
                        array_shift($arrResult['warnings'])
                    );
                }
            }

            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);

            // If order has no shipment created.
            if ($objTNTOrderModel === null || !$objTNTOrderModel->isShipped()) {
                // Default error message.
                if (!$objHookCookie->TNTOfficielError) {
                    $objHookCookie->TNTOfficielError = $this->l('Error create shipping');
                }
                // Log.
                TNTOfficiel_Logger::logException(new Exception($objHookCookie->TNTOfficielError));
                // Redirect to prevent new order state (cleaner than reverting).
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders', false)
                    .'&id_order='.$objPSOrder->id.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders')
                );
            }
        }
    }

    /**
     * HOOK (AKA postUpdateOrderStatus) called when an order's status is changed, right after it is actually changed.
     * Alert if the shipment was not saved (for an unknown reason).
     */
    public function hookActionOrderStatusPostUpdate($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookCookie = $arrArgHookParams['cookie'];

        $objHookOrderStateNew = $arrArgHookParams['newOrderStatus'];
        $intHookOrderID = (int)$arrArgHookParams['id_order'];
        $objPSOrder = new Order($intHookOrderID);
        $intOrderStateIDNew = (int)$objHookOrderStateNew->id;

        // Check if it's a TNT carrier.
        $intCarrierID = (int)$objPSOrder->id_carrier;
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($intCarrierID)) {
            // Do nothing.
            return;
        }

        // Check if the new order status is the one that must trigger shipment creation.
        if (Configuration::get(TNTOfficiel::ORDERSTATE_SAVESHIPMENT) == $intOrderStateIDNew) {
            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);

            // If order has no shipment created.
            if ($objTNTOrderModel === null || !$objTNTOrderModel->isShipped()) {
                TNTOfficiel_Logger::logException(new Exception($this->l('Error create shipping')));
                if (!$objHookCookie->TNTOfficielError) {
                    $objHookCookie->TNTOfficielError = $this->l('Error create shipping');
                }
            }
        }
    }

    /**
     * HOOK (AKA orderDetailDisplayed) displayed on order detail on Front-Office.
     * Insert parcel tracking block on order detail.
     */
    public function hookDisplayOrderDetail($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        $objHookOrder = $arrArgHookParams['order'];
        $intHookOrderID = (int)$objHookOrder->id;

        // If Order does not have a TNT carrier.
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($objHookOrder->id_carrier)) {
            // Display nothing.
            return '';
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intHookOrderID, false);

        // If order has no shipment created.
        if ($objTNTOrderModel === null || !$objTNTOrderModel->isShipped()) {
            // Display nothing.
            return '';
        }

        $this->smarty->assign(array(
            'trackingUrl' => $this->context->link->getModuleLink(
                TNTOfficiel::MODULE_NAME,
                'tracking',
                array('action' => 'tracking', 'orderId' => $intHookOrderID),
                true
            )
        ));

        // Display template.
        return $this->display(__FILE__, 'views/templates/hook/displayOrderDetail.tpl');
    }

    /**
     * Add mail template variable. PS 1.6.1.0+
     *
     * @param $arrArgHookParams
     *
     * @return bool
     */
    public function hookActionGetExtraMailTemplateVars($arrArgHookParams)
    {
        TNTOfficiel_Logstack::log();

        if (!array_key_exists('extra_template_vars', $arrArgHookParams)) {
            return false;
        }

        // Variables default is immediately available (empty).
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_text}'] = '';
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_html}'] = '';

        $intLangID = (int)$arrArgHookParams['id_lang'];
        $strLangISO = Language::getIsoById($intLangID);

        // If id_order not provided.
        if (!array_key_exists('{id_order}', $arrArgHookParams['template_vars'])) {
            return false;
        }

        $intOrderID = (int)$arrArgHookParams['template_vars']['{id_order}'];

        $objPSOrder = new Order($intOrderID);

        // If not an order associated with a TNT Carrier.
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
            return false;
        }

        // Translation.
        $strLinkTrack = 'Track my TNT packages';
        if ($strLangISO === 'fr') {
            $strLinkTrack = 'Suivre mes colis TNT';
        }

        // mails/fr/shipped.txt; mails/fr/shipped.html
        // if ($arrArgHookParams['template'] === 'shipped') {}

        // Get tracking URL if available.
        $strTrackingURL = TNTOfficiel_Parcel::getTrackingURLFromAnOrder($intOrderID);
        if (!is_string($strTrackingURL)) {
            return false;
        }

        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_text}'] =
            $strLinkTrack.' : ['.$strTrackingURL.']';
        $arrArgHookParams['extra_template_vars']['{tntofficiel_tracking_url_html}'] =
            '<a href="'.$strTrackingURL.'" style="color:#337FF1">'.$strLinkTrack.'</a>';

        return true;
    }
}
