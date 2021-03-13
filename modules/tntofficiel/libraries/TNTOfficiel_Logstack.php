<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Logstack
{
    /**
     * @var bool Is debugging enabled ?
     * _PS_MODE_DEV_ should be false to catch all error.
     */
    private static $boolEnabled = false;
    /**
     * @var array List of allowed client IP. No client IP means all allowed.
     */
    private static $arrRemoteIPAddressAllowed = array();

    /**
     * @var int Backtrace maximum items. 0 to disable backtrace.
     */
    private static $intBackTraceMaxDeepDefault = 0;
    private static $intBackTraceMaxDeepException = 64;
    private static $intBackTraceMaxDeepError = 64;

    /**
     * @var array Error type and message pattern to exclude.
     */
    private static $arrPHPErrorExclude = array(
        'E_WARNING' => array(
            '/^filemtime\(\): stat failed for/ui',
            '/^file_exists\(\): open_basedir restriction in effect\./ui',
            '/^mkdir\(\): File exists /ui'
        ),
        'E_NOTICE' => array(
            '/^(ArrayObject::)?serialize\(\): &quot;[\S\s]+?&quot; returned as member variable from __sleep\(\) but does not exist$/ui'
        )
    );



    /**
     * @var array PHP Errors constant name list.
     */
    private static $arrPHPErrorNames = array(
        'E_ERROR',
        'E_RECOVERABLE_ERROR',
        'E_WARNING',
        'E_PARSE',
        'E_NOTICE',
        'E_STRICT',
        'E_DEPRECATED', // PHP 5.3+
        'E_CORE_ERROR',
        'E_CORE_WARNING',
        'E_COMPILE_ERROR',
        'E_COMPILE_WARNING',
        'E_USER_ERROR',
        'E_USER_WARNING',
        'E_USER_NOTICE',
        'E_USER_DEPRECATED' // PHP 5.3+
    );
    /**
     * @var array PHP Errors map. Dynamically generated using getPHPErrorType().
     */
    private static $arrPHPErrorMap = null;

    /**
     * @var array JSON Errors constant name list. PHP 5.3.0+.
     */
    private static $arrJSONErrorNames = array(
        'JSON_ERROR_NONE',
        'JSON_ERROR_DEPTH',
        'JSON_ERROR_STATE_MISMATCH',
        'JSON_ERROR_CTRL_CHAR',
        'JSON_ERROR_SYNTAX',
        'JSON_ERROR_UTF8', // PHP 5.3.3+
        'JSON_ERROR_RECURSION', // PHP 5.5.0+
        'JSON_ERROR_INF_OR_NAN', // PHP 5.5.0+
        'JSON_ERROR_UNSUPPORTED_TYPE', // PHP 5.5.0+
    );
    /**
     * @var array JSON Errors map. PHP 5.3.0+.
     */
    private static $arrJSONErrorMap = null;


    /**
     * @var bool
     */
    private static $boolHandlerRegistered = false;

    /**
     * @var string
     */
    private static $strRoot = null;
    /**
     * @var string
     */
    private static $strFileName = null;


    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Check if client IP address allowed to use debug.
     *
     * @return bool
     */
    public static function isClientIPAddressAllowed()
    {
        $strRemoteIPAddress = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;

        $boolIPAllowed = count(TNTOfficiel_Logstack::$arrRemoteIPAddressAllowed) === 0
            || in_array($strRemoteIPAddress, TNTOfficiel_Logstack::$arrRemoteIPAddressAllowed, true) === true;

        return $boolIPAllowed;
    }

    /**
     * Encode to JSON.
     *
     * @param array $mxdArgValue
     * @param int $intArgPettyLevel
     *
     * @return string
     */
    public static function encJSON($mxdArgValue, $intArgPettyLevel = 3)
    {
        $flagJSONEncode = 0;
        if ($intArgPettyLevel > 0) {
            $flagJSONEncode |= defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
        }
        // Unescape.
        $flagJSONEncode |= defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
        $flagJSONEncode |= defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0;
        // Display 0.0
        $flagJSONEncode |= defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0;

        //$flagJSONEncode |= defined('JSON_PARTIAL_OUTPUT_ON_ERROR') ? JSON_PARTIAL_OUTPUT_ON_ERROR : 0;
        //$flagJSONEncode |= defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0;

        // PHP < 5.3 return null if second parameter is used.
        $strJSON = ($flagJSONEncode === 0 ? json_encode($mxdArgValue) : json_encode($mxdArgValue, $flagJSONEncode));

        if ($intArgPettyLevel > 0 && $intArgPettyLevel <= 2) {
            // indent to 2 spaces.
            $strJSON = preg_replace_callback('/(^|\n)(\ ++)/ui', array('TNTOfficiel_Logstack', 'cbIndentSpace'), $strJSON);
            // before }
            $strJSON = preg_replace('/(?<![\}\]])\n\s*+(?=\}(?!$))/ui', '', $strJSON);
            // before }
            $strJSON = preg_replace('/(?<![\}])\n\s*+(?=\},)/ui', '', $strJSON);
            // before {
            $strJSON = preg_replace('/(?<=\[|,)\n\s*+(?=\{)/ui', '', $strJSON);
            // after }
            $strJSON = preg_replace('/(?<=\})\n\s*+(?=\])/ui', '', $strJSON);
            // after }
            $strJSON = preg_replace('/(?<=\})\n\s++(?=}(?!\]))/ui', '', $strJSON);
            // before ]
            $strJSON = preg_replace('/\n\s*+(?=\])/ui', '', $strJSON);
        }
        if ($intArgPettyLevel > 0 && $intArgPettyLevel <= 1) {
            $strJSON = preg_replace('/(?<=,)\n\s*+/ui', ' ', $strJSON);
            $strJSON = preg_replace('/(?<=\{|\[)\n\s*+/ui', '', $strJSON);
            $strJSON = preg_replace('/\n\s*+(?=\})/ui', '', $strJSON);
        }

        return $strJSON;
    }

    /**
     * Callback.
     *
     * @param $arrArgMatches
     *
     * @return string
     */
    public static function cbIndentSpace($arrArgMatches)
    {
        return $arrArgMatches[1].str_repeat(' ', (int)(strlen($arrArgMatches[2])/2));
    }

    /**
     * Get PHP Error name from value.
     *
     * @param int $intArgType
     *
     * @return string
     */
    public static function getPHPErrorType($intArgType)
    {
        // Generate constant name mapping.
        if (TNTOfficiel_Logstack::$arrPHPErrorMap === null) {
            TNTOfficiel_Logstack::$arrPHPErrorMap = array();
            foreach (TNTOfficiel_Logstack::$arrPHPErrorNames as $strPHPErrorTypeName) {
                if (defined($strPHPErrorTypeName)) {
                    // Get constant value.
                    $intPHPErrorType = constant($strPHPErrorTypeName);
                    TNTOfficiel_Logstack::$arrPHPErrorMap[ $intPHPErrorType ] = $strPHPErrorTypeName;
                }
            }
        }

        $strPHPErrorType = (string)$intArgType;
        if (array_key_exists($intArgType, TNTOfficiel_Logstack::$arrPHPErrorMap)) {
            $strPHPErrorType = TNTOfficiel_Logstack::$arrPHPErrorMap[ $intArgType ];
        }

        return $strPHPErrorType;
    }


    /**
     * Get JSON Error name from value.
     *
     * @param int $intArgType
     *
     * @return string
     */
    public static function getJSONErrorType($intArgType)
    {
        // Generate constant name mapping.
        if (TNTOfficiel_Logstack::$arrJSONErrorMap === null) {
            TNTOfficiel_Logstack::$arrJSONErrorMap = array();
            foreach (TNTOfficiel_Logstack::$arrJSONErrorNames as $strJSONErrorTypeName) {
                if (defined($strJSONErrorTypeName)) {
                    // Get constant value.
                    $intJSONErrorType = constant($strJSONErrorTypeName);
                    TNTOfficiel_Logstack::$arrJSONErrorMap[ $intJSONErrorType ] = $strJSONErrorTypeName;
                }
            }
        }

        $strJSONErrorType = (string)$intArgType;
        if (array_key_exists($intArgType, TNTOfficiel_Logstack::$arrJSONErrorMap)) {
            $strJSONErrorType = TNTOfficiel_Logstack::$arrJSONErrorMap[ $intArgType ];
        }

        return $strJSONErrorType;
    }

    /**
     * Capture an Error.
     *
     * @param int $intArgType
     * @param string $strArgMessage
     * @param string|null $strArgFile
     * @param int $intArgLine
     * @param array $arrArgContext
     * @param bool $boolArgIsLast
     *
     * @return bool
     */
    public static function captureError(
        $intArgType,
        $strArgMessage,
        $strArgFile = null,
        $intArgLine = 0,
        $arrArgContext = array(),
        $boolArgIsLast = false
    ) {
        $arrLogError = array(
            'type' => $boolArgIsLast ? 'LastError' : 'Error'
        );

        if ($strArgFile !== null) {
            $arrLogError['file'] = $strArgFile;
            $arrLogError['line'] = $intArgLine;
        }

        if ($boolArgIsLast) {
            $arrLogError['trace'] = array();
        }

        $strPHPErrorType = TNTOfficiel_Logstack::getPHPErrorType($intArgType);
        $arrLogError['msg'] = 'Type '.$strPHPErrorType.': '.$strArgMessage;

        if (array_key_exists($strPHPErrorType, TNTOfficiel_Logstack::$arrPHPErrorExclude)
            && is_array(TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType])
        ) {
            if (count(TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType]) === 0) {
                return false;
            }
            foreach (TNTOfficiel_Logstack::$arrPHPErrorExclude[$strPHPErrorType] as $strPattern) {
                if (preg_match($strPattern, $strArgMessage) === 1) {
                    return false;
                }
            }
        }

        TNTOfficiel_Logstack::stack($arrLogError, TNTOfficiel_Logstack::$intBackTraceMaxDeepError);

        // Internal error handler continues (displays/log error, …)
        return false;
    }

    /**
     * Capture last Error.
     * Useful at script end for E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, …
     */
    public static function captureLastError()
    {
        $arrPHPLastError = error_get_last();

        if (is_array($arrPHPLastError)) {
            TNTOfficiel_Logstack::captureError(
                $arrPHPLastError['type'],
                $arrPHPLastError['message'],
                $arrPHPLastError['file'],
                $arrPHPLastError['line'],
                array(),
                true
            );
        }

        // PHP 5.3.0+
        if (function_exists('json_last_error')) {
            $intTypeJSONLastError = json_last_error();
            $strJSONLastErrorType = TNTOfficiel_Logstack::getJSONErrorType($intTypeJSONLastError);
            if ($strJSONLastErrorType !== 'JSON_ERROR_NONE') {
                // PHP 5.5.0+
                $strJSONLastErrorMessage = function_exists('json_last_error_msg') ? json_last_error_msg() : 'N/A';
                TNTOfficiel_Logstack::captureError(
                    $strJSONLastErrorType,
                    $strJSONLastErrorMessage,
                    null,
                    0,
                    array(),
                    true
                );
            }
        }

        // PHP 5.3.0+
        if (function_exists('date_get_last_errors')) {
            $arrTypeDateLastError = date_get_last_errors();
            if (is_array($arrTypeDateLastError)) {
                if (array_key_exists('errors', $arrTypeDateLastError)
                    && count($arrTypeDateLastError['errors']) > 0
                ) {
                    $strDateLastErrorType = 'DATE_ERROR';
                    $strDateLastErrorMessage = TNTOfficiel_Logstack::encJSON($arrTypeDateLastError['errors']);
                    TNTOfficiel_Logstack::captureError(
                        $strDateLastErrorType,
                        $strDateLastErrorMessage,
                        null,
                        0,
                        array(),
                        true
                    );
                }
                if (array_key_exists('warnings', $arrTypeDateLastError)
                    && count($arrTypeDateLastError['warnings']) > 0
                ) {
                    $strDateLastErrorType = 'DATE_WARNING';
                    $strDateLastErrorMessage = TNTOfficiel_Logstack::encJSON($arrTypeDateLastError['warnings']);
                    TNTOfficiel_Logstack::captureError(
                        $strDateLastErrorType,
                        $strDateLastErrorMessage,
                        null,
                        0,
                        array(),
                        true
                    );
                }
            }
        }
    }

    /**
     * Capture an Exception.
     *
     * @param Exception $objArgException
     */
    public static function captureException($objArgException, $boolArgReturn = false)
    {
        $arrLogException = array(
            'type' => 'Exception'
        );

        if ($objArgException->getFile() !== null) {
            $arrLogException['file'] = $objArgException->getFile();
            $arrLogException['line'] = $objArgException->getLine();
        }

        $arrLogException['msg'] = 'Code '.$objArgException->getCode().': '.$objArgException->getMessage();
        $arrLogException['trace'] = $objArgException->getTrace();

        return TNTOfficiel_Logstack::stack(
            $arrLogException,
            TNTOfficiel_Logstack::$intBackTraceMaxDeepException,
            $boolArgReturn
        );
    }

    /**
     * Capture connection status.
     */
    public static function captureConnectionStatus()
    {
        // is non normal connection status.
        $intStatus = connection_status();
        // connection_aborted()
        if ($intStatus & 1) {
            TNTOfficiel_Logstack::stack(array(
                'type' => 'Shutdown',
                'msg' => sprintf('Connection was aborted by user.')
            ));
        }
        if ($intStatus & 2) {
            TNTOfficiel_Logstack::stack(array(
                'type' => 'Shutdown',
                'msg' => sprintf('Script exceeded maximum execution time.')
            ));
        }
    }

    /**
     * Capture output buffer status.
     */
    public static function captureOutPutBufferStatus()
    {
        $msg = 'Output was not sent yet.';

        // is output buffer was sent
        $strOutputBufferFile = null;
        $intOutputBufferLine = null;
        $boolOutputBufferSent = headers_sent($strOutputBufferFile, $intOutputBufferLine);
        if ($boolOutputBufferSent) {
            $msg = sprintf('Output was sent in \'%s\' on line %s.', $strOutputBufferFile, $intOutputBufferLine);
        }

        TNTOfficiel_Logstack::stack(array(
            'type' => 'Shutdown',
            'msg' => $msg,
            'headers' => headers_list(),
            'level' => ob_get_level()
        ), 0);
    }

    /**
     * Capture at shutdown.
     */
    public static function captureAtShutdown()
    {
        TNTOfficiel_Logstack::captureLastError();
        TNTOfficiel_Logstack::captureConnectionStatus();
        TNTOfficiel_Logstack::captureOutPutBufferStatus();
        TNTOfficiel_Logstack::addLogContent('{}]');
    }


    /**
     * Register capture handlers once.
     */
    public static function registerHandlers()
    {
        /*
        $mxdPrevExceptionHandler = true;
        while ($mxdPrevExceptionHandler !== null) {
            // Get previous error handler.
            $mxdPrevExceptionHandler = set_error_handler(array('TNTOfficiel_Logstack', 'captureError'));
            restore_error_handler();
            //
            if ($mxdPrevExceptionHandler !== null) {
                // Remove previous error handler like ControllerCore::myErrorHandler().
                restore_error_handler();
            }
        }
        set_error_handler(array('TNTOfficiel_Logstack', 'captureError'));

        $mxdPrevExceptionHandler = true;
        while ($mxdPrevExceptionHandler !== null) {
            // Get previous exception handler.
            $mxdPrevExceptionHandler = set_exception_handler(array('TNTOfficiel_Logstack', 'captureException'));
            restore_exception_handler();
            //
            if ($mxdPrevExceptionHandler !== null) {
                // Remove previous exception handler.
                restore_exception_handler();
            }
        }
        set_exception_handler(array('TNTOfficiel_Logstack', 'captureException'));
        */

        if (TNTOfficiel_Logstack::$boolHandlerRegistered !== true) {
            TNTOfficiel_Logstack::$boolHandlerRegistered = true;
            set_error_handler(array('TNTOfficiel_Logstack', 'captureError'));
            set_exception_handler(array('TNTOfficiel_Logstack', 'captureException'));
            register_shutdown_function(array('TNTOfficiel_Logstack', 'captureAtShutdown'));
        }
    }

    /**
     * Get the script start time.
     *
     * @return float
     */
    public static function getStartTime()
    {
        return (float)(array_key_exists('REQUEST_TIME_FLOAT', $_SERVER) ? $_SERVER['REQUEST_TIME_FLOAT'] :
            $_SERVER['REQUEST_TIME']);
    }


    /**
     * Set log root directory.
     *
     * @param string $strArgRoot
     *
     * @return bool
     */
    public static function setRootDirectory($strArgRoot)
    {
        TNTOfficiel_Logstack::$strRoot = null;

        $strRealpath = realpath($strArgRoot);

        // If not a writable directory.
        if ($strRealpath === false || !is_dir($strRealpath) || !is_writable($strRealpath)) {
            return false;
        }

        // Add final separator.
        if (Tools::substr($strRealpath, -1) !== DIRECTORY_SEPARATOR) {
            $strRealpath .= DIRECTORY_SEPARATOR;
        }

        // Save.
        TNTOfficiel_Logstack::$strRoot = $strRealpath;

        return true;
    }

    /**
     * Get log filename.
     *
     * @return string
     */
    public static function getFilename()
    {
        // If root directory is defined, but not the filename.
        if (TNTOfficiel_Logstack::$strRoot !== null && TNTOfficiel_Logstack::$strFileName === null) {
            // Get Optional IP folder.
            $strClientIPFolder = '';
            if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
                $strClientIPFolder = preg_replace('/[^a-z0-9_]+/ui', '-', $_SERVER['REMOTE_ADDR']).DIRECTORY_SEPARATOR;
            }
            // Create folder.
            TNTOfficiel_Tools::makeDirectory(TNTOfficiel_Logstack::$strRoot.$strClientIPFolder);
            // Check.
            if (!is_dir(TNTOfficiel_Logstack::$strRoot.$strClientIPFolder)) {
                $strClientIPFolder = '';
            }

            $floatScriptTime = TNTOfficiel_Logstack::getStartTime();
            $strTimeStampExport = var_export($floatScriptTime, true);
            $strTimeStampMaxWidth = preg_replace('/^([0-9]+(?:\.[0-9]{1,6}))[0-9]*$/ui', '$1', $strTimeStampExport);
            $strTimeStampNum = preg_replace('/\./ui', '', $strTimeStampMaxWidth);
            $strTimeStamp = sprintf('%-016s', $strTimeStampNum);

            $strShopFullContextID = 'G'.(int)Shop::getContextShopGroupID().'S'.(int)Shop::getContextShopID();

            $strFileName = $strTimeStamp.'-'.$strShopFullContextID.'-'
                .preg_replace('/[^a-z0-9_]+/ui', '-', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

            TNTOfficiel_Logstack::$strFileName = TNTOfficiel_Logstack::$strRoot.$strClientIPFolder.$strFileName.'.json';
        }

        return TNTOfficiel_Logstack::$strFileName;
    }

    /**
     * Create log file if do not exist.
     * Log global info at creation.
     *
     * @param string $strArgDebugInfo
     *
     * @return bool
     */
    public static function addLogContent($strArgDebugInfo = '')
    {
        $strFileName = TNTOfficiel_Logstack::getFilename();

        if ($strFileName === null) {
            return false;
        }

        // If file don't already exist.
        if (!file_exists($strFileName)) {
            $arrDebugInfo = array(
                'type' => 'Intro',
                'uri' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null,
                'referer' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : null,
                'client' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
                'post' => $_POST,
                'get' => $_GET,
                'cookie' => $_COOKIE,
                'config' => array(
                    'php' => TNTOfficiel_Logstack::getPHPConfig(),
                    'ps' => TNTOfficiel_Logstack::getPSConfig()
                )
            );

            $strDebugInfo = '['.TNTOfficiel_Logstack::encJSON($arrDebugInfo, 2).',';

            $strArgDebugInfo = $strDebugInfo.$strArgDebugInfo;
        }

        touch($strFileName);
        @chmod($strFileName, 0660);

        return file_put_contents($strFileName, $strArgDebugInfo, FILE_APPEND) > 0;
    }

    /**
     * Get variable type for dump.
     *
     * @param $args
     *
     * @return string
     */
    public static function dumpType($mxdArgValue, $arrExclude = array('NULL', 'boolean', 'integer', 'string'))
    {
        // Get type.
        $strType = gettype($mxdArgValue);

        if (in_array($strType, $arrExclude)) {
            return '';
        }

        if (is_object($mxdArgValue)) {
            $strType = '('.$strType.')'.get_class($mxdArgValue);
        } elseif (is_resource($mxdArgValue)) {
            $strType = '('.$strType.')'.get_resource_type($mxdArgValue);
        } else {
            $strType = '('.$strType.')';
        }

        return $strType;
    }

    /**
     * Get variable safe.
     * Prevent circular reference, etc ...
     *
     * @param $mxdArgValue
     *
     * @return mixed. null if unable to serialize or encode to JSON.
     */
    public static function getSafe($mxdArgValue)
    {
        try {
            // If unable to serialize.
            serialize($mxdArgValue);
            // If unable to encode to JSON.
            $strTestJSON = json_encode($mxdArgValue);
            if (!is_string($strTestJSON) || ($mxdArgValue !== '' && $strTestJSON === '')) {
                return null;
            }
        } catch (Exception $objException) {
            return null;
        }

        return $mxdArgValue;
    }

    /**
     * Get memory usage estimation.
     *
     * @param $mxdArgValue
     *
     * @return int
     */
    public static function getMem($mxdArgValue)
    {
        $intMemStart = memory_get_usage();

        try {
            // Temporary assignment is required.
            $strTmp = unserialize(serialize($mxdArgValue));
        } catch (Exception $objException) {
            $strTmp = null;
        }

        return memory_get_usage() - $intMemStart;
    }

    /**
     * Get variable safe for dump usage.
     * Prevent out of memory, circular reference and others recursive endless loop.
     *
     * @param $mxdArgValue
     * @param int $intArgMemLimit Default dump memory limit is 1 Mib.
     * @param int $intArgMaxDepth
     *
     * @return mixed
     */
    public static function dumpSafe($mxdArgValue, $intArgMemLimit = 1048576, $intArgMaxDepth = 4)
    {
        $intMemLimit = (int)$intArgMemLimit;
        $intMaxDepth = (int)$intArgMaxDepth;
        --$intMaxDepth;

        $strType = gettype($mxdArgValue);
        $arrScalarType = array('NULL', 'boolean', 'integer', 'string');
        $boolIsScalar = in_array($strType, $arrScalarType);

        if ($intMaxDepth < 0 && !$boolIsScalar) {
            return '…';
        } elseif ((is_object($mxdArgValue) || is_array($mxdArgValue))) {
            try {
                $arrValueSafe = array();
                $intPropCount = max(count((array)$mxdArgValue), 1);
                $intPropMemLimit = $intMemLimit / $intPropCount;
                foreach ($mxdArgValue as $k => $mxdPropItem) {
                    $intPropMemSize = TNTOfficiel_Logstack::getMem($mxdPropItem);
                    $arrValueSafe[$k] = '…';
                    if ($intPropMemSize <= $intPropMemLimit) {
                        $arrValueSafe[$k] = TNTOfficiel_Logstack::dumpSafe(
                            $mxdPropItem,
                            $intPropMemLimit,
                            $intMaxDepth
                        );
                    }
                }

                return array(TNTOfficiel_Logstack::dumpType($mxdArgValue) => $arrValueSafe);
            } catch (Exception $objException) {
                return '…E';
            }
        }

        $intMemSize = TNTOfficiel_Logstack::getMem($mxdArgValue);
        $mxdArgValueSafe = '…';
        if ($intMemSize <= $intMemLimit) {
            $mxdArgValueSafe = TNTOfficiel_Logstack::getSafe($mxdArgValue);
        }

        return ($boolIsScalar ? $mxdArgValueSafe : array(TNTOfficiel_Logstack::dumpType($mxdArgValue) => $mxdArgValueSafe));
    }


    /**
     * Append stack info.
     *
     * @param array|null $arrArg
     * @param int|null $intArgBackTraceMaxDeep
     *
     * @return string
     */
    public static function stack($arrArg = null, $intArgBackTraceMaxDeep = null, $boolArgReturn = false)
    {
        $intBackTraceMaxDeep = TNTOfficiel_Logstack::$intBackTraceMaxDeepDefault;
        if ($intArgBackTraceMaxDeep >= 0) {
            $intBackTraceMaxDeep = $intArgBackTraceMaxDeep;
        }

        if ($boolArgReturn !== true) {
            // If not enabled or client IP not allowed.
            if (TNTOfficiel_Logstack::$boolEnabled !== true || TNTOfficiel_Logstack::isClientIPAddressAllowed() !== true) {
                return;
            }

            // Set default path.
            //TNTOfficiel_Logstack::setRootDirectory(_PS_ROOT_DIR_.'/log/');
            TNTOfficiel_Logstack::setRootDirectory(
                _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG
            );

            // Register handlers if not.
            TNTOfficiel_Logstack::registerHandlers();
        }

        if (!is_array($arrArg)) {
            $arrArg = array('raw' => $arrArg);
        }

        // If message, file and line exist, then concat.
        if (array_key_exists('msg', $arrArg)
            && array_key_exists('file', $arrArg)
            && array_key_exists('line', $arrArg)
        ) {
            $arrArg['msg'] .= ' \''.$arrArg['file'].'\' on line '.$arrArg['line'];
            unset($arrArg['file']);
            unset($arrArg['line']);
        }

        // If no backtrace and auto backtrace set.
        if ($intBackTraceMaxDeep > 0
            && (!array_key_exists('trace', $arrArg) || !is_array($arrArg['trace']))
        ) {
            // Get current one.
            $arrArg['trace'] = debug_backtrace();
            // TODO : remove all trace from this current method.
            // Remove trace from this current method.
            array_shift($arrArg['trace']);
        }

        // Process backtrace.
        if (array_key_exists('trace', $arrArg) && is_array($arrArg['trace'])) {
            // Final backtrace.
            $arrTraceStack = array();

            // Get each trace, deeper first.
            while ($arrTrace = array_shift($arrArg['trace'])) {
                $intDeepIndex = count($arrTraceStack);
                // Get stack with maximum items.
                if ($intDeepIndex >= $intBackTraceMaxDeep) {
                    break;
                }

                // function
                if (array_key_exists('class', $arrTrace) && is_string($arrTrace['class'])) {
                    // Exclude this class.
                    if (in_array($arrTrace['class'], array(__CLASS__), true)) {
                        continue;
                    }
                    $arrTrace['function'] = $arrTrace['class'].$arrTrace['type'].$arrTrace['function'];
                }
                // file
                if (array_key_exists('file', $arrTrace) && is_string($arrTrace['file'])) {
                    $arrTrace['file'] = '\''.$arrTrace['file'].'\' on line '.$arrTrace['line'];
                } else {
                    $arrTrace['file'] = '[Internal]';
                }

                // arguments
                if (array_key_exists('args', $arrTrace) && is_array($arrTrace['args'])) {
                    $arrTrace['function'] .= '('.count($arrTrace['args']).')';
                }

                if (array_key_exists('args', $arrTrace) && is_array($arrTrace['args'])) {
                    $arrTrace['args'] = TNTOfficiel_Logstack::dumpSafe($arrTrace['args']);

                    // If no arguments.
                    if (count($arrTrace['args']) === 0) {
                        // Remove key (no line output).
                        unset($arrTrace['args']);
                    }
                }

                unset($arrTrace['line']);
                unset($arrTrace['class']);
                unset($arrTrace['type']);
                unset($arrTrace['object']);

                // Add trace.
                $arrTraceStack[ $intDeepIndex ] = $arrTrace;
            }

            // Save processed backtrace.
            $arrArg['trace'] = $arrTraceStack;

            // Remove backtrace key if empty.
            if (count($arrArg['trace']) === 0) {
                unset($arrArg['trace']);
            }
        }

        // Append time and memory consumption.
        $arrArg += array(
            'time' => microtime(true) - TNTOfficiel_Logstack::getStartTime(),
            'mem' => memory_get_peak_usage() / 1024 / 1024,
        );

        // List of sorted selected key.
        $arrKeyExistSort = array_intersect_key(array_flip(
            array('time', 'mem', 'type', 'msg', 'file', 'line', 'raw', 'dump', 'trace')
        ), $arrArg);
        // List of unsorted key left.
        $arrKeyUnExistUnSort = array_diff_key($arrArg, $arrKeyExistSort);
        // Append unsorted list to sorted.
        $arrArg = array_merge($arrKeyExistSort, $arrArg) + $arrKeyUnExistUnSort;

        $strDebugInfo = TNTOfficiel_Logstack::encJSON($arrArg, 2).',';
        $strDebugInfo = preg_replace('/}\s*,\s*{/ui', '},{', $strDebugInfo);

        if ($boolArgReturn !== true) {
            TNTOfficiel_Logstack::addLogContent($strDebugInfo);
        }

        return $strDebugInfo;
    }

    /**
     * Append log info.
     *
     * @param array|null $arrArg
     * @param int|null $intArgBackTraceMaxDeep
     *
     * @return string
     */
    public static function log($arrArg = null, $intArgBackTraceMaxDeep = null, $boolArgReturn = false)
    {
        $intBackTraceMaxDeep = TNTOfficiel_Logstack::$intBackTraceMaxDeepDefault;
        if ($intArgBackTraceMaxDeep > 0) {
            $intBackTraceMaxDeep = $intArgBackTraceMaxDeep;
        }

        if ($arrArg === null && $intBackTraceMaxDeep === 0) {
            return '';
        }

        return TNTOfficiel_Logstack::stack($arrArg, $intBackTraceMaxDeep, $boolArgReturn);
    }

    /**
     * Append dump info.
     *
     * @param array|null $arrArg
     *
     * @return string
     */
    public static function dump($arrArg = null, $boolArgReturn = false)
    {
        return TNTOfficiel_Logstack::stack(array('dump' => $arrArg), 0, $boolArgReturn);
    }

    /**
     * Compat with override.
     *
     * @return string
     */
    public static function trace()
    {
        return TNTOfficiel_Logstack::log();
    }

    /**
     * @return array
     */
    public static function getPHPConfig()
    {
        /*
         * User
         */

        $arrUser = posix_getpwuid(posix_geteuid());

        /*
         * Environment
         */

        $arrEnv = array(
            'http_proxy' => getenv('http_proxy'),
            'https_proxy' => getenv('https_proxy'),
            'ftp_proxy' => getenv('ftp_proxy')
        );

        /*
         * Constant
         */

        $arrPHPConstants = array(
            'PHP_OS' => PHP_OS,
            'PHP_VERSION' => PHP_VERSION,
            'PHP_SAPI' => PHP_SAPI,
            'PHP_INT_SIZE (bits)' => PHP_INT_SIZE * 8
        );

        /*
         * Extension
         */

        $arrPHPExtensions = array_intersect_key(array_flip(get_loaded_extensions()), array(
            'curl' => true,
            'soap' => true,
            'session' => true,
            'mcrypt' => true,
            'mhash' => true,
            'mbstring' => true,
            'iconv' => true,
            'zip' => true,
            'zlib' => true,
            'dom' => true,
            'xml' => true,
            'SimpleXML' => true,
            'Zend OPcache' => true,
            'ionCube Loader' => true
        ));

        /*
         * Configuration
         */

        $arrPHPConfiguration = array_intersect_key(ini_get_all(null, false), array(
            // php
            'magic_quotes' => 'Off',
            'magic_quotes_gpc' => 'Off',
            'max_input_vars' => '8192',
            // core - file uploads
            'upload_max_filesize' => '4M',
            // core - language options
            'disable_functions' => '',
            'disable_classes' => '',
            // core - paths and directories
            'open_basedir' => '',
            // core - data handling
            'register_globals' => 'Off',
            // safe mode
            'safe_mode' => '',
            'safe_mode_gid' => '',
            'safe_mode_exec_dir' => '',
            'safe_mode_include_dir' => '',
            // filesystem
            'allow_url_fopen' => 'On',
            'allow_url_include' => 'Off',
            'default_socket_timeout' => '60',
            // opcache
            'opcache.enable' => 'true'
        ));

        if (array_key_exists('open_basedir', $arrPHPConfiguration)) {
            $arrPHPConfiguration['open_basedir'] = explode(PATH_SEPARATOR, $arrPHPConfiguration['open_basedir']);
        }

        /*
         * Time
         */

        $arrPHPTime = array(
            'date_default_timezone_set' => date_default_timezone_get(),
            'date.timezone' => ini_get('date.timezone'),
            'date' => date('Y-m-d H:i:s P T (e)'),
        );


        return array(
            'user' => $arrUser,
            'env' => $arrEnv,
            'constants' => $arrPHPConstants,
            'extensions' => $arrPHPExtensions,
            'configuration' => $arrPHPConfiguration,
            'time' => $arrPHPTime
        );
    }

    public static function getPSConfig()
    {
        /*
         * Constant
         */

        //$__constants = get_defined_constants(true);
        $arrPSConstant = array(
            '_PS_VERSION_' => _PS_VERSION_,
            '_PS_JQUERY_VERSION_' => _PS_JQUERY_VERSION_,

            '_PS_MODE_DEV_' => _PS_MODE_DEV_,
            '_PS_DEBUG_PROFILING_' => _PS_DEBUG_PROFILING_,

            '_PS_MAGIC_QUOTES_GPC_' => _PS_MAGIC_QUOTES_GPC_,
            '_PS_USE_SQL_SLAVE_' => _PS_USE_SQL_SLAVE_,

            '_PS_CACHE_ENABLED_' => _PS_CACHE_ENABLED_,
            '_PS_CACHING_SYSTEM_' => _PS_CACHING_SYSTEM_,

            '_PS_DEFAULT_THEME_NAME_' => _PS_DEFAULT_THEME_NAME_,
            '_PS_THEME_DIR_' => _PS_THEME_DIR_,
            '_PS_THEME_OVERRIDE_DIR_' => _PS_THEME_OVERRIDE_DIR_,
            '_PS_THEME_MOBILE_DIR_' => _PS_THEME_MOBILE_DIR_,
            '_PS_THEME_MOBILE_OVERRIDE_DIR_' => _PS_THEME_MOBILE_OVERRIDE_DIR_,
            '_PS_THEME_TOUCHPAD_DIR_' => _PS_THEME_TOUCHPAD_DIR_
        );

        /*
         * Context
         */

        $flagShopContext = Shop::getContext();
        $arrConstShopContext = array();

        if ($flagShopContext & Shop::CONTEXT_SHOP) {
            $arrConstShopContext[] = 'Shop::CONTEXT_SHOP';
        }
        if ($flagShopContext & Shop::CONTEXT_GROUP) {
            $arrConstShopContext[] = 'Shop::CONTEXT_GROUP';
        }
        if ($flagShopContext & Shop::CONTEXT_ALL) {
            $arrConstShopContext[] = 'Shop::CONTEXT_ALL';
        }

        $arrPSContext = array(
            'Context::getContext()->shop->id' => Context::getContext()->shop->id,
            'Context::getContext()->shop->id_shop_group' => Context::getContext()->shop->id_shop_group,
            'Shop::getContext()' => $arrConstShopContext,
            'Shop::isFeatureActive()' => Shop::isFeatureActive(),
            'Shop::getContextShopGroupID()' => (int)Shop::getContextShopGroupID(),
            'Shop::getContextShopID()' => (int)Shop::getContextShopID()
        );

        /*
         * Configuration
         */

        $arrPSConfig = Configuration::getMultiple(array(

            /*
             * Carrier
             */

            /* Shipping */

            'PS_SHIPPING_HANDLING',
            'PS_SHIPPING_FREE_PRICE',
            'PS_SHIPPING_FREE_WEIGHT',

            'PS_CARRIER_DEFAULT',
            'PS_CARRIER_DEFAULT_SORT',
            'PS_CARRIER_DEFAULT_ORDER',


            /*
             * Localization
             */

            /* Localization */

            'PS_LANG_DEFAULT',
            'PS_COUNTRY_DEFAULT',
            'PS_CURRENCY_DEFAULT',

            'PS_WEIGHT_UNIT',   // kg
            'PS_DISTANCE_UNIT',
            'PS_VOLUME_UNIT',
            'PS_DIMENSION_UNIT',

            'PS_LOCALE_LANGUAGE',
            'PS_LOCALE_COUNTRY',

            /* Country */

            'PS_RESTRICT_DELIVERED_COUNTRIES',

            /* Taxes */

            'PS_TAX',
            'PS_TAX_DISPLAY',
            'PS_TAX_ADDRESS_TYPE',
            'PS_USE_ECOTAX',
            'PS_ECOTAX_TAX_RULES_GROUP_ID',


            /*
             * Preferences
             */

            /* General */

            'PS_SSL_ENABLED',
            'PS_SSL_ENABLED_EVERYWHERE',
            'PS_PRICE_ROUND_MODE',
            'PS_ROUND_TYPE',
            'PS_PRICE_DISPLAY_PRECISION',
            'PS_MULTISHOP_FEATURE_ACTIVE',
            // PS 1.6.1.16+
            'PS_API_KEY',

            /* Order */

            // General
            'PS_ORDER_PROCESS_TYPE',
            'PS_GUEST_CHECKOUT_ENABLED',
            'PS_DISALLOW_HISTORY_REORDERING',
            'PS_PURCHASE_MINIMUM',
            'PS_SHIP_WHEN_AVAILABLE',
            'PS_CONDITIONS',
            // Multi-Shipping (deprecated)
            'PS_ALLOW_MULTISHIPPING',
            // Gift Wrapping
            'PS_GIFT_WRAPPING',
            'PS_GIFT_WRAPPING_PRICE',
            'PS_GIFT_WRAPPING_TAX_RULES_GROUP',
            'PS_RECYCLABLE_PACK',

            /* Product */

            'PS_ATTRIBUTE_ANCHOR_SEPARATOR',
            'PS_ORDER_OUT_OF_STOCK',
            'PS_STOCK_MANAGEMENT',
            'PS_ADVANCED_STOCK_MANAGEMENT',

            /* Customer */

            'PS_REGISTRATION_PROCESS_TYPE',
            'PS_ONE_PHONE_AT_LEAST',
            'PS_B2B_ENABLE',

            /* SEO & URL */

            'PS_REWRITING_SETTINGS',
            'PS_ALLOW_ACCENTED_CHARS_URL',
            'PS_CANONICAL_REDIRECT',

            /* Stores */
            'PS_SHOP_NAME',
            'PS_SHOP_EMAIL',
            'PS_SHOP_PHONE',


            /*
             * Advanced Parameters
             */

            /* Performance */

            // Compile template 0: never 1: if template updated, 2: on each call.
            'PS_SMARTY_FORCE_COMPILE',
            // Smarty cache enabled ?
            'PS_SMARTY_CACHE',
            // If enabled, cache using filesystem or mysql ?
            'PS_SMARTY_CACHING_TYPE',
            // Clear cache never or everytime ?
            'PS_SMARTY_CLEAR_CACHE',

            'PS_DISABLE_OVERRIDES',

            'PS_CSS_THEME_CACHE',
            'PS_JS_THEME_CACHE',
            'PS_HTML_THEME_COMPRESSION',
            'PS_JS_HTML_THEME_COMPRESSION',
            'PS_JS_DEFER',
            'PS_HTACCESS_CACHE_CONTROL',

            'PS_CIPHER_ALGORITHM',

        ));

        return array(
            'constants' => $arrPSConstant,
            'context' => $arrPSContext,
            'configuration' => $arrPSConfig
        );
    }
}
