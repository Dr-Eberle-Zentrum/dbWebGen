<?php
    l10n_init();

    //--------------------------------------------------------------------------------------
    function l10n_init() {
    //--------------------------------------------------------------------------------------
        $defaultLang = defined('DBWEBGEN_LANG') ? DBWEBGEN_LANG : 'en';
        $switchLang = defined('DBWEBGEN_SWITCH_LANG') ? DBWEBGEN_SWITCH_LANG : '';
        $currentLang = isset($_SESSION['language']) ? $_SESSION['language'] : '';
        
        if($switchLang !== '' && $switchLang === $currentLang)
            return; // desired lang is current lang

        if($currentLang !== '' && $switchLang === '')
            return; // lang already set and no switching desired

        $desiredLang = ($switchLang !== '' ? $switchLang : $defaultLang);
        if($desiredLang === $currentLang)
            return; // desired language already loaded

        // here we need to load the new language into our session...         
        if(!file_exists(__DIR__ . "/$desiredLang.php"))
            $desiredLang = 'en';
        include "$desiredLang.php";
        $_SESSION['l10n'] = $_L10N;
        $_SESSION['language'] = $desiredLang;
        // ... and invalidate the previously defined plugin localizations
        unset($_SESSION['l10n_custom']);
    }

    //--------------------------------------------------------------------------------------
    function l10n($key /* + add'l arguments for replacing %x placeholders */) {
    //--------------------------------------------------------------------------------------
        $str = isset($_SESSION['l10n'][$key]) ? $_SESSION['l10n'][$key] : $key;
        for($i = func_num_args() - 1; $i > 0; $i--)
            $str = str_replace('$'.$i, func_get_arg($i), $str);
        return $str;
    }

    //--------------------------------------------------------------------------------------
    // localizes the values of the passed array
    function l10n_values($arr) {
    //--------------------------------------------------------------------------------------
        foreach($arr as $k => $v)
            $arr[$k] = l10n($v);
        return $arr;
    }

    //--------------------------------------------------------------------------------------
    // Registers additional string entries (or overrides) for some context, useful for localized plugins.
    // Plugins should call this once from their registered $APP['preprocess_func'] function
    function l10n_register(
        $context,       // the unique caller context. for each session/context combo,
                        // this function is only performed once, regardless how oftenn it is invoked
                        // must not be empty!
        $lang,          // language code (lower case), e.g. 'de'
        $string_table   // array with key => localized string, can overwrite default keys
    ) {
    //--------------------------------------------------------------------------------------
        if($lang != $_SESSION['language'])
            return; // nothing to do
        if(!isset($_SESSION['l10n_custom']))
            $_SESSION['l10n_custom'] = array();
        if(!isset($_SESSION['l10n_custom'][$context])) {
            $_SESSION['l10n'] = array_merge($_SESSION['l10n'], $string_table);
            $_SESSION['l10n_custom'][$context] = true;
        }
    }
?>
