<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

/**
 * Class to handler language translations.
 */
class Vtiger_Language_Handler
{
    //Contains module language translations
    protected static $languageContainer;

    /**
     * Functions that gets translated string.
     *
     * @param string $key             string which need to be translated
     * @param string $module          module scope in which the translation need to be check
     * @param string $currentLanguage
     *
     * @return string translated string
     */
    public static function getTranslatedString($key, $module = 'Vtiger', $currentLanguage = '')
    {
        if (!$currentLanguage) {
            $currentLanguage = \App\Language::getLanguage();
        }
        //decoding for Start Date & Time and End Date & Time
        if (!is_array($key)) {
            $key = App\Purifier::decodeHtml($key);
        }
        $translatedString = self::getLanguageTranslatedString($currentLanguage, $key, $module);

        // label not found in users language pack, then check in the default language pack(config.inc.php)
        if ($translatedString === null) {
            $defaultLanguage = \AppConfig::main('default_language');
            if (!empty($defaultLanguage) && strcasecmp($defaultLanguage, $currentLanguage) !== 0) {
                $translatedString = self::getLanguageTranslatedString($defaultLanguage, $key, $module);
            }
        }
        // If translation is not found then return label
        if ($translatedString === null) {
            $translatedString = \App\Purifier::encodeHtml($key);
        }

        return $translatedString;
    }

    /**
     * Function returns language specific translated string.
     *
     * @param string $language - en_us etc
     * @param string $key      - label
     * @param string $module   - module name
     *
     * @return string translated string or null if translation not found
     */
    public static function getLanguageTranslatedString($language, $key, $module = 'Vtiger')
    {
        if ($key === '') { // nothing to translate
            return '';
        }
        if (is_array($module)) {
            App\Log::warning('Invalid module name - module: '.var_export($module, true));

            return null;
        }
        if (is_numeric($module)) {
            // ok, we have a tab id, lets turn it into name
            $module = \App\Module::getModuleName($module);
        } else {
            $module = str_replace(':', '.', $module);
        }
        $moduleStrings = self::getModuleStringsFromFile($language, $module);
        if (!empty($moduleStrings['languageStrings'][$key])) {
            return stripslashes($moduleStrings['languageStrings'][$key]);
        }
        // Lookup for the translation in base module, in case of sub modules, before ending up with common strings
        if (strpos($module, '.') > 0) {
            $baseModule = substr($module, 0, strpos($module, '.'));
            if ($baseModule == 'Settings') {
                $baseModule = 'Settings.Vtiger';
            }
            $moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
            if (!empty($moduleStrings['languageStrings'][$key])) {
                return stripslashes($moduleStrings['languageStrings'][$key]);
            }
        }
        $commonStrings = self::getModuleStringsFromFile($language);
        if (!empty($commonStrings['languageStrings'][$key])) {
            return stripslashes($commonStrings['languageStrings'][$key]);
        }
        \App\Log::info("cannot translate this: '$key' for module '$module' (or base or Vtiger), lang: $language");

        return null;
    }

    /**
     * Functions that gets translated string for Client side.
     *
     * @param string $key    - string which need to be translated
     * @param string $module - module scope in which the translation need to be check
     *
     * @return string - translated string
     */
    public static function getJSTranslatedString($language, $key, $module = 'Vtiger')
    {
        $module = str_replace(':', '.', $module);
        $moduleStrings = self::getModuleStringsFromFile($language, $module);
        if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
            return $moduleStrings['jsLanguageStrings'][$key];
        }
        // Lookup for the translation in base module, in case of sub modules, before ending up with common strings
        if (strpos($module, '.') > 0) {
            $baseModule = substr($module, 0, strpos($module, '.'));
            if ($baseModule == 'Settings') {
                $baseModule = 'Settings.Vtiger';
            }
            $moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
            if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
                return $moduleStrings['jsLanguageStrings'][$key];
            }
        }
        $commonStrings = self::getModuleStringsFromFile($language);
        if (!empty($commonStrings['jsLanguageStrings'][$key])) {
            return $commonStrings['jsLanguageStrings'][$key];
        }
        \App\Log::info("cannot translate this: '$key' for module '$module' (or base or Vtiger), lang: $language");

        return $key;
    }

    /**
     * Function that returns translation strings from file.
     *
     * @global <array> $languageStrings - language specific string which is used in translations
     *
     * @param string $module - module Name
     *
     * @return <array> - array if module has language strings else returns empty array
     */
    public static function getModuleStringsFromFile($language, $module = 'Vtiger')
    {
        $module = str_replace(':', '.', $module);
        if (!isset(self::$languageContainer[$language][$module])) {
            $qualifiedName = 'languages.'.$language.'.'.$module;
            $file = Vtiger_Loader::resolveNameToPath($qualifiedName);
            $languageStrings = $jsLanguageStrings = [];
            if (file_exists($file)) {
                require $file;
            } else {
                \App\Log::warning("Language file does not exist, module: $module ,language: $language");
            }
            self::$languageContainer[$language][$module]['languageStrings'] = $languageStrings;
            self::$languageContainer[$language][$module]['jsLanguageStrings'] = $jsLanguageStrings;
            if (AppConfig::performance('LOAD_CUSTOM_LANGUAGE')) {
                $qualifiedName = 'custom.languages.'.$language.'.'.$module;
                $file = Vtiger_Loader::resolveNameToPath($qualifiedName);
                if (file_exists($file)) {
                    require $file;
                    foreach ($languageStrings as $key => $val) {
                        self::$languageContainer[$language][$module]['languageStrings'][$key] = $val;
                    }
                    foreach ($jsLanguageStrings as $key => $val) {
                        self::$languageContainer[$language][$module]['jsLanguageStrings'][$key] = $val;
                    }
                }
            }
        }
        if (isset(self::$languageContainer[$language][$module])) {
            return self::$languageContainer[$language][$module];
        }

        return [];
    }

    /**
     * Function returns module strings.
     *
     * @param string $module - module Name
     * @param string languageStrings or jsLanguageStrings
     *
     * @return <Array>
     */
    public static function export($module, $type = 'languageStrings')
    {
        $userSelectedLanguage = \App\Language::getLanguage();
        $defaultLanguage = \AppConfig::main('default_language');
        $languages = [$userSelectedLanguage];
        //To merge base language and user selected language translations
        if ($userSelectedLanguage != $defaultLanguage) {
            array_push($languages, $defaultLanguage);
        }
        $resultantLanguageString = [];
        foreach ($languages as $currentLanguage) {
            $exportLangString = [];
            $moduleStrings = self::getModuleStringsFromFile($currentLanguage, $module);
            if (!empty($moduleStrings[$type])) {
                $exportLangString = $moduleStrings[$type];
            }
            // Lookup for the translation in base module, in case of sub modules, before ending up with common strings
            if (strpos($module, '.') > 0) {
                $baseModule = substr($module, 0, strpos($module, '.'));
                if ($baseModule == 'Settings') {
                    $baseModule = 'Settings.Vtiger';
                }
                $moduleStrings = self::getModuleStringsFromFile($currentLanguage, $baseModule);
                if (!empty($moduleStrings[$type])) {
                    $exportLangString += $commonStrings[$type];
                }
            }
            $commonStrings = self::getModuleStringsFromFile($currentLanguage);
            if (!empty($commonStrings[$type])) {
                $exportLangString += $commonStrings[$type];
            }
            $resultantLanguageString += $exportLangString;
        }

        return $resultantLanguageString;
    }
}
