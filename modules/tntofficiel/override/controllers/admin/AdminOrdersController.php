<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

class AdminOrdersController extends AdminOrdersControllerCore
{
    // Unused but preserved for upgrade reinstall
    private $carriers_array = array();

    public function __construct()
    {
        // Warning : Dependencies on construct implies to do not use TNTOfficiel class in static method !
        require_once _PS_MODULE_DIR_.'tntofficiel/tntofficiel.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/classes/TNTOfficielOrder.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logstack.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logger.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Carrier.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Order.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Tools.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDFCreator.php';

        TNTOfficiel_Logstack::trace();

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            return parent::__construct();
        }

        parent::__construct();

        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        -- override start.
        `to`.`bt_filename` as `BT`,
        `to`.`bt_filename` as `tntofficiel_id_order`,
        `to`.`pickup_number` as `tntofficiel_pickup_number`,
        IF((`to`.`carrier_label` IS NULL OR `to`.`carrier_label` = ""),
            c1.`name`,
            CONCAT(c1.`name` ,\' (\', `to`.`carrier_label`,\')\')) AS `carrier`,
        c1.`id_carrier` as id_carrier,
        -- override end.
        IF((SELECT so.id_order
                FROM `'._DB_PREFIX_.'orders` so
                WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0,
            0,
            1) as new,
        country_lang.name as cname,
        IF(a.valid, 1, 0) badge_success';

        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'address` address ON address.id_address = a.id_address_delivery
        LEFT JOIN `'._DB_PREFIX_.'country` country ON address.id_country = country.id_country
        LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang
            ON (country.`id_country` = country_lang.`id_country`
                AND country_lang.`id_lang` = '.(int)$this->context->language->id.')
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state`
                AND osl.`id_lang` = '.(int)$this->context->language->id.')
        -- override start.
        LEFT JOIN `'._DB_PREFIX_.'carrier` c1 ON (a.`id_carrier` = c1.`id_carrier`)
        LEFT JOIN `'._DB_PREFIX_.'tntofficiel_order` `to` ON (a.`id_order` = `to`.`id_order`)
        -- override end.
        ';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $carriers = Carrier::getCarriers(
            (int)$this->context->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        $carriers_array = array();
        foreach ($carriers as $carrier) {
            $carriers_array[$carrier['id_carrier']] = $carrier['name'];
        }

        $this->fields_list = array(
            'id_order'  => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
            ),
            'new'       => array(
                'title'          => $this->l('New client'),
                'align'          => 'text-center',
                'type'           => 'bool',
                'tmpTableFilter' => true,
                'orderby'        => false,
                'callback'       => 'printNewCustomer'
            ),
            'customer'  => array(
                'title'        => $this->l('Customer'),
                'havingFilter' => true
            )
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge(
                $this->fields_list,
                array(
                    'company' => array(
                        'title'      => $this->l('Company'),
                        'filter_key' => 'c!company'
                    )
                )
            );
        }

        $this->fields_list = array_merge(
            $this->fields_list,
            array(
                // Added : Carrier select.
                'carrier'   => array(
                    'title'        => $this->l('Carrier'),
                    'filter_key'   => 'c1!id_carrier',
                    'filter_type'  => 'int',
                    'order_key'    => 'carrier',
                    'havingFilter' => true,
                    'type'         => 'select',
                    'list'         => $carriers_array
                ),
                'total_paid_tax_incl' => array(
                    'title'         => $this->l('Total'),
                    'align'         => 'text-right',
                    'type'          => 'price',
                    'currency'      => true,
                    'callback'      => 'setOrderCurrency',
                    'badge_success' => true
                ),
                'payment'             => array(
                    'title' => $this->l('Payment')
                ),
                'osname'              => array(
                    'title'       => $this->l('Status'),
                    'type'        => 'select',
                    'color'       => 'color',
                    'list'        => $this->statuses_array,
                    'filter_key'  => 'os!id_order_state',
                    'filter_type' => 'int',
                    'order_key'   => 'osname'
                ),
                'date_add'            => array(
                    'title'      => $this->l('Date'),
                    'align'      => 'text-right',
                    'type'       => 'datetime',
                    'filter_key' => 'a!date_add'
                ),
                'id_pdf'              => array(
                    'title'          => $this->l('PDF'),
                    'align'          => 'text-center',
                    'callback'       => 'printPDFIcons',
                    'orderby'        => false,
                    'search'         => false,
                    'remove_onclick' => true
                ),
                // Added after 'id_pdf': TNT BT.
                'tntofficiel_id_order' => array(
                    'title'          => $this->l('TNT'),
                    'align'          => 'text-center',
                    'orderby'        => false,
                    'search'         => false,
                    'callback'       => 'printBtIcon',
                    'remove_onclick' => true
                )
            )
        );

        // Optionally Added : TNT Pickup Number.
        if (Configuration::get('TNTOFFICIEL_PICKUP_DISPLAY_NUMBER')) {
            $this->fields_list = array_merge(
                $this->fields_list,
                array(
                    'tntofficiel_pickup_number' => array(
                        'title'   => $this->l('N° de Ramassage'),
                        'align'   => 'text-right',
                        'orderby' => false,
                        'search'  => false
                    )
                )
            );
        }

        // VALIDATOR : method inherited from ObjectModelCore::isCurrentlyUsed()
        if (Country::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT DISTINCT c.id_country, cl.`name`
            FROM `'._DB_PREFIX_.'orders` o
            '.Shop::addSqlAssociation('orders', 'o').'
            INNER JOIN `'._DB_PREFIX_.'address` a ON a.id_address = o.id_address_delivery
            INNER JOIN `'._DB_PREFIX_.'country` c ON a.id_country = c.id_country
            INNER JOIN `'._DB_PREFIX_.'country_lang` cl
                ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.(int)$this->context->language->id.')
            ORDER BY cl.name ASC');

            $country_array = array();
            foreach ($result as $row) {
                $country_array[$row['id_country']] = $row['name'];
            }

            $part1 = array_slice($this->fields_list, 0, 3);
            $part2 = array_slice($this->fields_list, 3);
            $part1['cname'] = array(
                'title' => $this->l('Delivery'),
                'type' => 'select',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname'
            );
            $this->fields_list = array_merge($part1, $part2);
        }

        // Added Bulk Actions
        $this->bulk_actions = array_merge(
            $this->bulk_actions,
            array(
                // TNT BT.
                'getBT'             => array(
                    'text' => $this->l('Bon de transport de TNT'),
                    'icon' => 'icon-AdminTNTOfficiel'
                ),
                // TNT Manifest.
                'getManifest'       => array(
                    'text' => $this->l('Manifeste TNT'),
                    'icon' => 'icon-file-text'
                )
            )
        );
    }

    /**
     * Load only one API Google Map script.
     */
    public function setMedia()
    {
        TNTOfficiel_Logstack::trace();

        parent::setMedia();

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            // Do nothing.
            return;
        }

        if (Tools::isSubmit('id_order')) {
            $intOrderID = (int)Tools::getValue('id_order');
            if ($intOrderID > 0) {
                $objPSOrder = new Order($intOrderID);
                if (Validate::isLoadedObject($objPSOrder)
                    && TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)
                ) {
                    // Remove script load of API Google Map to prevent conflicts.
                    foreach ($this->context->controller->js_files as $key => $jsFile) {
                        if (preg_match('/^((https?:)?\/\/)?maps\.google(apis)?\.com\/maps\/api\/js/ui', $jsFile)) {
                            unset($this->context->controller->js_files[$key]);
                        }
                    }

                    // Load once using TNTOfficel module API key.
                    $this->context->controller->addJS(
                        // String concat is used because PS 1.6.0.14 think that // inside the string is a comment !
                        'https:/'.'/maps.googleapis.com/maps/api/js?v=3.20&key='
                        .Configuration::get('TNTOFFICIEL_GMAP_API_KEY')
                    );
                }
            }
        }
    }

    /***
     * @param $bt_filename
     * @param $tr
     *
     * @return null|string
     */
    public function printBtIcon($bt_filename, $tr)
    {
        TNTOfficiel_Logstack::trace();

        // Unused but mandatory argument.
        $bt_filename === $bt_filename;

        $intOrderID = (int)$tr['id_order'];

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return null;
        }

        $this->context->smarty->assign(array(
            'objTNTOrderModel' => $objTNTOrderModel,
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.'tntofficiel/views/templates/admin/_print_bt_icon.tpl');
    }

    /**
     * Concatenate PDF for all the BT for the selected orders.
     * /<ADMIN>/index.php?controller=AdminOrders&submitBulkgetBTorder
     *
     * @throws Exception
     */
    public function processBulkGetBT()
    {
        TNTOfficiel_Logstack::trace();

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            return;
        }

        $arrOrderID = (array)Tools::getValue('orderBox');
        $strPDFBTPath = _PS_MODULE_DIR_.TNTOfficiel::PATH_MEDIA_PDF_BT;
        $objPDFMerger = new TNTOfficiel_PDFMerger();
        $intBTCounter = 0;

        foreach ($arrOrderID as $strOrderID) {
            $intOrderID = (int)$strOrderID;
            $objPSOrder = new Order($intOrderID);
            // Check if it's a tnt order.
            if (TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
                // Load TNT order info for it's ID.
                $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
                if ($objTNTOrderModel === null) {
                    continue;
                }
                $strBTFileName = $objTNTOrderModel->bt_filename;
                $strPDFBTLocation = $strPDFBTPath.$strBTFileName;
                if ($strBTFileName && filesize($strPDFBTLocation) > 0) {
                    ++$intBTCounter;
                    $objPDFMerger->addPDF($strPDFBTLocation);
                }
            }
        }

        // Concat.
        if ($intBTCounter > 0) {
            $strOutputFileName = 'bt_list.pdf';
            // Download and exit.
            TNTOfficiel_Tools::download($strOutputFileName, $objPDFMerger->merge('string', $strOutputFileName));
        }
    }

    /**
     * Return all the BT for the selected orders.
     */
    public function processBulkGetManifest()
    {
        TNTOfficiel_Logstack::trace();

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            return;
        }

        if (!Tools::getIsset('orderBox')) {
            return;
        }

        $arrOrderID = (array)Tools::getValue('orderBox');
        $arrOrderIDList = array();
        foreach ($arrOrderID as $strOrderID) {
            $intOrderID = (int)$strOrderID;
            $arrOrderIDList[] = $intOrderID;
        }
        $manifest = new TNTOfficiel_ManifestPDFCreator();
        $manifest->createManifest($arrOrderIDList);
    }
}
