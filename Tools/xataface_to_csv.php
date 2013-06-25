<?php


/**
 * usage:
 * php xataface_to_csv.php <xatafacesettingsfile> <number-of-environmentsinfile> <outputfilename>
 *
 * example
 * php xataface_to_csv.php deploymentsettings.php 5 output.csv
 *
 * @author Dmytro Zavalkin
 */
class XatafaceToCsvConverter
{
    /**
     * Number of parameter columns in EnvSettingsTool csv file format
     */
    const CSV_FORMAT_PARAM_COLUMNS_NUMBER = 3;

    /**
     * Map xataface handler::method to EnvSettingsTool handler
     *
     * @var array
     */
    private static $xatafaceToCsvHandlersMap = array(
        'Magento_CoreConfigData::updateByScopeAndScopeIdAndPath' => 'Est_Handler_Magento_CoreConfigData',
        'MarkerReplacement::updateFile'                          => 'Est_Handler_MarkerReplace',
        'XmlFile::updateNodeByFileAndXPath'                      => 'Est_Handler_XmlFile',
    );

    /**
     * Max number of params supported by each handler
     *
     * @var array
     */
    private static $envToolHandlerParamsNumber = array(
        'Est_Handler_XmlFile'                => 2,
        'Est_Handler_Magento_CoreConfigData' => 3,
        'Est_Handler_MarkerReplace'          => 2,
        'Est_Handler_PrependFileContent'     => 2,
    );

    /**
     * Xataface settings file content
     *
     * @var array
     */
    private $fileContent;

    /**
     * Number of environments which should be present in xataface file
     *
     * @var int
     */
    private $environmentNumber;

    /**
     * Class constructor
     *
     * @param string $fileName
     * @param int $environmentNumber
     * @throws InvalidArgumentException
     */
    public function __construct($fileName, $environmentNumber)
    {
        if (empty($fileName)) {
            throw new InvalidArgumentException('No xataface settings file set.');
        }
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException('Could not read xataface settings file.');
        }
        if (empty($environmentNumber)) {
            throw new InvalidArgumentException("Environment number parameter isn't set.");
        }

        $this->fileContent       = $this->getDeploymentSettingsFromFile($fileName);
        $this->environmentNumber = $environmentNumber;
    }

    /**
     * Convert object to array recursively
     *
     * @param array|object $object
     * @return array
     */
    private function objectToArrayRecursive($object)
    {
        $objectToArray = function(&$object) {
            if (is_object($object)) {
                $object = (array) $object;
            }

            return $object;
        };

        if (is_object($object)) {
            $object = (array) $object;
        }
        if (is_array($object)) {
            array_walk_recursive($object, $objectToArray);
        }

        return $object;
    }

    /**
     * Load deployment settings from file
     *
     * @param string $fileName
     * @return array
     */
    private function getDeploymentSettingsFromFile($fileName)
    {
        include $fileName;

        /** @var $environments array */
        /** @var $settings array */
        return array(
            '$environments' => $this->objectToArrayRecursive($environments),
            '$settings'     => $this->objectToArrayRecursive($settings),
        );
    }

    /**
     * Restructure settings array for export to csv
     *
     * @param array $settings
     * @return array
     */
    private function restructureSettings(array $settings)
    {
        $restructuredSettings = array();
        foreach ($settings as $group) {
            if (is_array($group)) {
                foreach ($group as $option) {
                    if (is_array($option)) {
                        $restructuredOption = $this->restructureOption($option);
                        if ($restructuredOption) {
                            $restructuredSettings[] = $restructuredOption;
                        }
                    }
                }
            }
        }

        return $restructuredSettings;
    }

    /**
     * Validate xataface option
     *
     * @param array $option
     * @return bool|string
     */
    private function validateOption(array $option)
    {
        $errors = array();
        if (!isset($option['handler'])) {
            $errors[] = "Invalid xataface option, 'handler' is absent.";
        }

        if (!isset($option['method'])) {
            $errors[] = "Invalid xataface option, 'method' is absent.";
        }

        if (isset($option['handler']) && isset($option['method'])) {
            $handlerName = $option['handler'] . '::' . $option['method'];
            if (!isset(self::$xatafaceToCsvHandlersMap[$handlerName])) {
                $errors[] = 'EnvSettingsTool handler not found for option.';
            } else {
                if (!isset($option['params'])) {
                    $errors[] = "Invalid xataface option, 'params' is absent.";
                } elseif (!is_array($option['params'])
                    || count($option['params'])
                        != self::$envToolHandlerParamsNumber[self::$xatafaceToCsvHandlersMap[$handlerName]]
                ) {
                    $errors[] = sprintf("Invalid xataface option, 'params' must be array of length %d",
                        self::$envToolHandlerParamsNumber[self::$xatafaceToCsvHandlersMap[$handlerName]]
                    );
                }
            }
        }

        if (!isset($option['values'])) {
            $errors[] = "Invalid xataface option, 'values' is absent.";
        } elseif (!is_array($option['values']) || count($option['values']) != $this->environmentNumber) {
            $errors[] = sprintf("Invalid xataface option, 'values' must be array of length %s",
                $this->environmentNumber
            );
        }

        if (!empty($errors)) {
            $errorsMessage = implode(PHP_EOL, $errors) . PHP_EOL;
            $errorsMessage .= 'Problem option:'  . PHP_EOL . var_export($option, true) . PHP_EOL;
            return $errorsMessage;
        } else {
            return true;
        }
    }

    /**
     * Restructure xataface option to csv row (array)
     *
     * @param array $option
     * @return bool|string
     */
    private function restructureOption(array $option)
    {
        $validationResult = $this->validateOption($option);
        if ($validationResult === true) {
            $handlerName = self::$xatafaceToCsvHandlersMap[$option['handler'] . '::' . $option['method']];
            $params      = $option['params'];
            $values      = $option['values'];
            $uniqueValues =  array_unique($values);
            if (count($uniqueValues) == 1) {
                $default = array_shift($uniqueValues);
                $values = array_fill(0, self::$envToolHandlerParamsNumber[$handlerName], null);
            } else {
                $default = null;
            }

            if (count($params) < self::CSV_FORMAT_PARAM_COLUMNS_NUMBER) {
                $params = array_merge($params,
                    array_fill(0, self::CSV_FORMAT_PARAM_COLUMNS_NUMBER - count($params), null)
                );
            }

            $csvRow = array('Handler' => $handlerName);
            foreach ($params as $key => $param) {
                $csvRow['Param' . ($key + 1)] = $param;
            }

            $csvRow['DEFAULT'] = $default;
            $csvRow = array_merge($csvRow, $values);

            return $csvRow;
        } else {
            echo $validationResult;

            return false;
        }
    }

    /**
     * Save xataface settings as csv file
     *
     * @param string $fleName
     * @throws InvalidArgumentException
     */
    public function saveSettingsToCsv($fleName)
    {
        $restructuredSettings = $this->restructureSettings($this->fileContent['$settings']);

        $fp = fopen($fleName, 'w');
        if ($fp === false) {
            throw new InvalidArgumentException("Can't write to {$fleName} file");
        }

        if (count($restructuredSettings) > 0) {
            $columnHeaders = array_keys(current($restructuredSettings));
            fputcsv($fp, $columnHeaders);
            foreach ($restructuredSettings as $fields) {
                fputcsv($fp, $fields);
            }
        }
        fclose($fp);
    }
}

try {
    if (empty($_SERVER['argv'][1])) {
        throw new Exception('Please specify input file name (it usually has name deploymentsettings.php)');
    }
    if (empty($_SERVER['argv'][2])) {
        throw new Exception('Please specify environment number');
    }
    if (empty($_SERVER['argv'][3])) {
        throw new Exception('Please specify output file name (it usually has name Settings.csv)');
    }

    $xatafaceToCsvConverter = new XatafaceToCsvConverter($_SERVER['argv'][1], $_SERVER['argv'][2]);
    try {
        $xatafaceToCsvConverter->saveSettingsToCsv($_SERVER['argv'][3]);
    }
    catch (Exception $e) {
        $processor->printResults();
        echo "\nERROR: Stopping execution because an error has occured!\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "\nERROR: {$e->getMessage()}\n\n";
    exit(1);
}
