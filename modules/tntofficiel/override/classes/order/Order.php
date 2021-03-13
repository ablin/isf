<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

class Order extends OrderCore
{
    /**
     * Order constructor.
     * @param null $id
     * @param null $id_lang
     */
    public function __construct($id = null, $id_lang = null)
    {
        // Warning : Dependencies on construct implies to do not use TNTOfficiel class in static method !
        require_once _PS_MODULE_DIR_.'tntofficiel/tntofficiel.php';
        require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logstack.php';

        TNTOfficiel_Logstack::trace();

        parent::__construct($id, $id_lang);
    }

    /**
     * @return array return all shipping method for the current order
     * state_name sql var is now deprecated - use
     * order_state_name for the state name and carrier_name for the carrier_name
     */
    public function getShipping()
    {
        TNTOfficiel_Logstack::trace();

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            return parent::getShipping();
        }

        $strTablePrefix = _DB_PREFIX_;
        $intOrderId = (int)$this->id;
        $intLanguageId = (int)Context::getContext()->language->id;

        // Override preserving backward compatibility with versions prior to tntofficiel 1.3.0
        // Used to get the carrier's full name, since there was only one carrier.
        $strSQLSelectShipping = <<<SQL
SELECT DISTINCT
  oc.`id_order_invoice`,
  oc.`weight`,
  oc.`shipping_cost_tax_excl`,
  oc.`shipping_cost_tax_incl`,
  c.`url`, oc.`id_carrier`,
  -- override start.
  IF (tnt.`carrier_label` IS NULL OR tnt.`carrier_label` = '',
    c.`name`, CONCAT(c.`name`, ' (', tnt.`carrier_label`, ')')
  ) AS `carrier_name`,
  -- override end.
  oc.`date_add`,
  'Delivery' AS `type`,
  'true' AS `can_edit`,
  oc.`tracking_number`,
  oc.`id_order_carrier`,
  osl.`name` AS order_state_name,
  c.`name` AS state_name
FROM `${strTablePrefix}orders` o
LEFT JOIN `${strTablePrefix}order_history` oh
  ON (o.`id_order` = oh.`id_order`)
LEFT JOIN `${strTablePrefix}order_carrier` oc
  ON (o.`id_order` = oc.`id_order`)
LEFT JOIN `${strTablePrefix}carrier` c
  ON (oc.`id_carrier` = c.`id_carrier`)
LEFT JOIN `${strTablePrefix}order_state_lang` osl
  ON (oh.`id_order_state` = osl.`id_order_state`
  AND osl.`id_lang` = ${intLanguageId})
-- override start.
LEFT JOIN `${strTablePrefix}tntofficiel_order` tnt
  ON (o.`id_order` = tnt.`id_order`)
-- override end.
WHERE o.`id_order` = ${intOrderId}
GROUP BY c.id_carrier;
SQL;

        return Db::getInstance()->executeS($strSQLSelectShipping);
    }
}
