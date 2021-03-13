<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Tools
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Create a new directory with default index.php file.
     * Don't do log here.
     *
     * @param array $arrArgDirectoryList an array of directories.
     *
     * @return bool
     */
    public static function makeDirectory($arrArgDirectoryList, $strRoot = '')
    {
        $strIndexFileContent = <<<PHP
<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: ../");
exit;
PHP;

        $arrDirectoryList = (array)$arrArgDirectoryList;

        foreach ($arrDirectoryList as $strDirectory) {
            // If directory do not exist, create it.
            $boolSuccess = true;

            if (!is_string($strDirectory)) {
                continue;
            }

            // If directory do not exist, create it.
            if (!is_dir($strRoot.$strDirectory)) {
                $intUMask = umask(0);
                $boolSuccess = mkdir($strRoot.$strDirectory, 0770, true) && $boolSuccess;
                umask($intUMask);

                if (!$boolSuccess) {
                    return false;
                }
            }

            $strFileName = $strRoot.$strDirectory.'index.php';
            // If index file does not exist, create it.
            if (!file_exists($strFileName)) {
                touch($strFileName);
                @chmod($strFileName, 0660);

                $rscFile = fopen($strFileName, 'w');
                if ($rscFile === false) {
                    return false;
                }
                fwrite($rscFile, $strIndexFileContent);
                fclose($rscFile);
            }
        }

        return true;
    }

    /**
     * @param $strArgInflateValue
     * @return string
     */
    public static function deflate($strArgInflateValue)
    {
        return (string)base64_encode(gzdeflate($strArgInflateValue));
    }
    /**
     * @param $strArgDeflateValue
     * @return string
     */
    public static function inflate($strArgDeflateValue)
    {
        return (string)gzinflate(base64_decode($strArgDeflateValue));
    }

    /**
     * Get Bootstrap HTML alert.
     *
     * @param type $arrArgAlert
     *
     * @return string
     */
    public static function getAlertHTML($arrArgAlert)
    {
        $arrAlertHTML = array();

        foreach ($arrArgAlert as $strAlertType => $arrAlertMsg) {
            if (count($arrAlertMsg) > 0) {
                $arrAlertMsg = array_map('htmlentities', $arrAlertMsg);
                if ($strAlertType == 'warning') {
                    $arrAlertHTML['warning'] = '<div class="alert alert-warning">'
                        .(count($arrAlertMsg) === 1 ?
                            $arrAlertMsg[0] : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                } elseif ($strAlertType == 'success') {
                    $arrAlertHTML['success'] = '<div class="alert alert-success">'
                        .(count($arrAlertMsg) === 1 ?
                            $arrAlertMsg[0] : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                } elseif ($strAlertType == 'error') {
                    $arrAlertHTML['error'] = '<div class="alert alert-danger">'
                        .(count($arrAlertMsg) === 1 ?
                            $arrAlertMsg[0] : ('<ul><li>'.implode('</li><li>', $arrAlertMsg).'</li></ul>'))
                        .'</div>';
                }
            }
        }

        return $arrAlertHTML;
    }

    /**
     * Donwload an existing file or content.
     *
     * @param string $strFileLocation
     * @param string|null $strContent
     * @param string $strContentType
     *
     * @return bool false if error.
     */
    public static function download($strFileLocation, $strContent = null, $strContentType = 'application/octet-stream')
    {
        // File location must be a string.
        if (!is_string($strFileLocation)) {
            return false;
        }
        // If content, must be a string.
        if ($strContent !== null && !is_string($strContent)) {
            return false;
        }
        // If no content, file must exist.
        if ($strContent === null && !file_exists($strFileLocation)) {
            return false;
        }

        // End output buffer.
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        // Set header.
        ob_start();
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: '.$strContentType);
        header('Content-Disposition: attachment; filename="'.basename($strFileLocation).'"');
        header('Content-Transfer-Encoding: binary');
        ob_end_flush();

        // Output content.
        if ($strContent !== null) {
            echo $strContent;
        } else {
            readfile($strFileLocation);
        }

        // We want to be sure that download content is the last thing this controller will do.
        exit;
    }

    /**
     * Update the cacert.pem file.
     *
     * @return bool|null true if update, false if not. null on error.
     */
    public static function updateCACert()
    {
        $strCACertURL = 'https://curl.haxx.se/ca/cacert.pem';
        $strCACertFilename = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/libraries/certs/cacert.pem';
        $intCACertTimestamp = file_exists($strCACertFilename) ? @filemtime($strCACertFilename) : 0;

        // If modification time exist and is less than 15 days old.
        if (($intCACertTimestamp > 0)
            && ((time() - $intCACertTimestamp) <= 60*60*24*15)
        ) {
            // No update needed.
            return false;
        }

        // Get last cacert.pem file content.
        $arrResult = TNTOfficiel_Tools::cURLRequest($strCACertURL);
        // Check content is valid.
        if ($arrResult['response'] === false
            || !preg_match('/(.*-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----){50}$/Uims', $arrResult['response'])
            || substr(rtrim($arrResult['response']), -1) !== '-'
        ) {
            // Download error.
            return null;
        }

        // If file does not exist, create it.
        if (!file_exists($strCACertFilename)) {
            touch($strCACertFilename);
            @chmod($strCACertFilename, 0660);
        }

        // Write content.
        $rscFile = fopen($strCACertFilename, 'w');
        if ($rscFile === false) {
            // Error.
            return null;
        }
        fwrite($rscFile, $arrResult['response']);
        fclose($rscFile);

        return true;
    }

    /**
     * @param string $strArgURL
     * @param array $arrArgOptions
     *
     * @return array
     */
    public static function cURLRequest($strArgURL, $arrArgOptions = null)
    {
        $strURL = trim($strArgURL);

        $strCACertFilename = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/libraries/certs/cacert.pem';
        $intCACertTimestamp = file_exists($strCACertFilename) ? @filemtime($strCACertFilename) : 0;

        $arrResult = array(
            'options' => array(
                // Check server certificate's name against host.
                // 0: disable, 2: enable.
                CURLOPT_SSL_VERIFYHOST => 0,
                // Check server peer's certificate authenticity through certification authority (CA) for SSL/TLS.
                CURLOPT_SSL_VERIFYPEER => $intCACertTimestamp > 0,
                // Path to Certificate Authority (CA) bundle.
                // https://curl.haxx.se/docs/caextract.html
                // https://curl.haxx.se/ca/cacert.pem
                // Default : ini_get('curl.cainfo') PHP 5.3.7+
                // Alternative : ini_get('openssl.cafile') PHP 5.6+
                CURLOPT_CAINFO => $strCACertFilename,
                // Start a new cookie session (ignore all previous cookies session)
                CURLOPT_COOKIESESSION => true,
                // Follow HTTP 3xx redirects.
                CURLOPT_FOLLOWLOCATION => true,
                // Max redirects allowed.
                CURLOPT_MAXREDIRS => 8,
                // curl_exec return response string instead of true (no direct output).
                CURLOPT_RETURNTRANSFER => true,
                // Include response header in output.
                //CURLOPT_HEADER => false,
                // Include request header ?
                //CURLINFO_HEADER_OUT => false,
                // HTTP code >= 400 considered as error. Use curl_error (curl_exec return false ?).
                //CURLOPT_FAILONERROR => true,
                // Proxy.
                //CURLOPT_PROXY => $strProxy
                //CURLOPT_PROXYUSERPWD => 'user:password',
                //CURLOPT_PROXYAUTH => 1,
                //CURLOPT_PROXYPORT => 80,
                //CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
                // Timeout for connection to the server.
                CURLOPT_CONNECTTIMEOUT => TNTOfficiel::REQUEST_CONNECTTIMEOUT,
                // Timeout global.
                CURLOPT_TIMEOUT => TNTOfficiel::REQUEST_TIMEOUT
            ),
            'response' => null,
            'info' => array(
                'http_code' => 0
            ),
            'error' => null
        );

        if (is_array($arrArgOptions)) {
            $arrResult['options'] = $arrArgOptions + $arrResult['options'];
        }

        // Check extension.
        if (!extension_loaded('curl')) {
            $objException = new Exception(sprintf('PHP cURL extension is required'));
            TNTOfficiel_Logger::logException($objException);
            // Communication Error.
            $arrResult['response'] = false;
            $arrResult['error'] = 'PHP cURL extension is required';

            return $arrResult;
        }

        $rscCURLHandler = curl_init();

        foreach ($arrResult['options'] as $intCURLConst => $mxdValue) {
            // May warn if open_basedir or deprecated safe_mode set.
            if ((ini_get('safe_mode') || ini_get('open_basedir'))
                && $intCURLConst === CURLOPT_FOLLOWLOCATION
            ) {
                continue;
            }
            curl_setopt($rscCURLHandler, $intCURLConst, $mxdValue);
        }

        curl_setopt($rscCURLHandler, CURLOPT_URL, $strURL);

        // curl_exec return false on error.
        $arrResult['response'] = curl_exec($rscCURLHandler);
        $arrResult['info'] = curl_getinfo($rscCURLHandler);
        $arrResult['error'] = curl_error($rscCURLHandler);

        curl_close($rscCURLHandler);

        return $arrResult;
    }
}
