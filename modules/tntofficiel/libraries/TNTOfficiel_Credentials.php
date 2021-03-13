<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Credentials
{
    public static $strSalt = '0hp6df7j46df4gc6df42nfhf6i9gfdzz';

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Get TNT credentials.
     *
     * @param bool $boolArgHashPassword
     *
     * @return array
     */
    public static function getCredentials()
    {
        TNTOfficiel_Logstack::log();

        return array(
            'identity' => trim(Configuration::get('TNTOFFICIEL_ACCOUNT_LOGIN')),
            'password' => trim(Configuration::get('TNTOFFICIEL_ACCOUNT_PASSWORD')),
            'merchant_number' => trim(Configuration::get('TNTOFFICIEL_ACCOUNT_NUMBER')),
        );
    }

    /**
     * Get DateTime of the last credentials validation.
     *
     * @return DateTime|null null if invalid credentials.
     */
    public static function getValidatedDateTime()
    {
        TNTOfficiel_Logstack::log();

        $strCredentialCurrentState = Configuration::get('TNTOFFICIEL_CREDENTIALS_VALIDATED');

        if (!$strCredentialCurrentState) {
            return null;
        }

        try {
            $obValidatedDateTime = new DateTime('@'.$strCredentialCurrentState);
        } catch (Exception $objException) {
            $obValidatedDateTime = null;
        }

        return $obValidatedDateTime;
    }

    /**
     * Call the middleware to check the WS credentials.
     * Save invalidation or validation date.
     *
     * @param int $intArgRefreshDelay
     *
     * @return bool true if valid or always valid, false if invalid, null if error.
     */
    public static function updateValidation($intArgRefreshDelay = 0)
    {
        TNTOfficiel_Logstack::log();

        $intRefreshDelay = (int)$intArgRefreshDelay;
        if (!($intRefreshDelay >= 0)) {
            $intRefreshDelay = 0;
        }

        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $intTSNow = (int)$objDateTimeNow->format('U');

        $objValidatedDateTime = TNTOfficiel_Credentials::getValidatedDateTime();

        // If activated
        if ($objValidatedDateTime !== null) {
            $intTSValidated = (int)$objValidatedDateTime->format('U');
            // If current timestamp earlier than previous saved timestamp + delay before refreshing.
            // Means that check is always done until validated
            // or been recheck after an amount of time from last validation.
            if ($intTSNow < ($intTSValidated + $intRefreshDelay)) {
                return true;
            }
        }

        // Get Middleware Response.
        $boolMDWCheckResult = TNTOfficiel_JsonRPCClient::isCorrectAuthentication();

        // If request fail.
        if ($boolMDWCheckResult === null) {
            return null;
        } elseif ($boolMDWCheckResult !== true) {
            // Disable the module if authentication fail.
            Configuration::updateValue('TNTOFFICIEL_CREDENTIALS_VALIDATED', false);

            return false;
        }

        Configuration::updateValue('TNTOFFICIEL_CREDENTIALS_VALIDATED', $intTSNow);

        return true;
    }
}
