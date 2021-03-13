<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficiel_Logger
 * Used in upgrade, do not rename or remove.
 */
class TNTOfficiel_Logger
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Creates the log folder if not exist
     */
    private static function createLogFolder()
    {
        $strLogPath = TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG;

        // Create directories.
        $boolIsPathCreated = TNTOfficiel_Tools::makeDirectory(array(
            $strLogPath.'request/',
            $strLogPath.'error/'
        ), _PS_MODULE_DIR_);

        return $boolIsPathCreated;
    }

    /**
     * Log Request error and success.
     *
     * @param $strType
     * @param $strRequestName
     * @param bool $boolArgSuccess
     * @param null $ftArgDelay
     * @param null $strArgMessage
     *
     * @return bool
     */
    public static function logRequest(
        $strType,
        $strRequestName,
        $boolArgSuccess = true,
        $ftArgDelay = null,
        $strArgMessage = null
    ) {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $strDate = $objDateTimeNow->format('H:i:s');

        try {
            TNTOfficiel_Logger::createLogFolder();
            // Format the message for an request log
            $logMessage = sprintf(
                '%s [%s] %s:%s (%s sec) %s%s',
                $strDate,
                ($boolArgSuccess ? 'success' : 'failure'),
                $strType,
                $strRequestName,
                (($ftArgDelay === null) ? '?' : sprintf('%0.3f', $ftArgDelay)),
                trim(preg_replace('/[[:cntrl:]]+/', ' ', $strArgMessage)),
                chr(10)
            );
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
        }

        $strLogPath = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG.'request/';
        $strFileName = $strLogPath.sprintf('%s.log', $objDateTimeNow->format('Y-m-d'));

        touch($strFileName);
        @chmod($strFileName, 0660);

        return (bool)file_put_contents($strFileName, $logMessage, FILE_APPEND);
    }

    /**
     * Log Exception.
     * Used in upgrade, do not rename or remove.
     *
     * @param $objArgException
     *
     * @return bool
     */
    public static function logException($objArgException)
    {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $strDate = $objDateTimeNow->format('H:i:s');

        // First line of formatted exception message.
        $strArgMessage = strtok((string)$objArgException, "\n");

        try {
            TNTOfficiel_Logger::createLogFolder();
            // Format the message for an request log
            $logMessage = sprintf(
                '%s %s%s',
                $strDate,
                trim(preg_replace('/[[:cntrl:]]+/', ' ', $strArgMessage)),
                chr(10)
            );
        } catch (Exception $objException) {
            // Do nothing.
        }

        $strLogPath = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG.'error/';
        $strFileName = $strLogPath.sprintf('%s.log', $objDateTimeNow->format('Y-m-d'));

        touch($strFileName);
        @chmod($strFileName, 0660);

        $logMessage .= TNTOfficiel_Logstack::captureException($objArgException, true)."\n";

        return (bool)file_put_contents($strFileName, $logMessage, FILE_APPEND);
    }

    /**
     * Log install and uninstall.
     *
     * @param $strArgMessage
     * @param bool $boolArgSuccess
     *
     * @return bool
     */
    public static function logInstall($strArgMessage, $boolArgSuccess = true)
    {
        $objDateTimeNow = new DateTime('now', new DateTimeZone('UTC'));
        $strDate = $objDateTimeNow->format('Y-m-d H:i:s');

        $strLogMessage = sprintf(
            '%s [%s] %s%s',
            $strDate,
            ($boolArgSuccess ? 'success' : 'failure'),
            $strArgMessage,
            chr(10)
        );

        try {
            TNTOfficiel_Logger::createLogFolder();
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
        }

        $strLogPath = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG;
        $strFileName = $strLogPath.'install.log';

        touch($strFileName);
        @chmod($strFileName, 0660);

        return (bool)file_put_contents($strFileName, $strLogMessage, FILE_APPEND);
    }



    /**
     * Generate an archive containing all the logs files
     *
     * @param $zipName
     *
     * @return ZipArchive
     */
    public static function getZip($zipName)
    {
        if (!extension_loaded('zip')) {
            TNTOfficiel_Logger::logException(new Exception(sprintf('PHP Zip extension is required')));
            return false;
        }

        $objZipArchive = new ZipArchive();
        $objZipArchive->open($zipName, ZipArchive::CREATE);

        $strLogPathModule = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG;
        $arrAllowedExt = array('log', 'json');

        foreach ($arrAllowedExt as $strExt) {
            $arrFiles = Tools::scandir($strLogPathModule, $strExt, '', true);
            foreach ($arrFiles as $strFileName) {
                $strFileLocation = $strLogPathModule.$strFileName;
                if (file_exists($strFileLocation)) {
                    $objZipArchive->addFromString($strFileName, Tools::file_get_contents($strFileLocation));
                }
            }
        }

        $objZipArchive->close();

        return $objZipArchive;
    }
}
