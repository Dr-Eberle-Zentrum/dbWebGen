<?
    l10n_init();

    //--------------------------------------------------------------------------------------
    function l10n_init() {
    //--------------------------------------------------------------------------------------
        if(isset($_SESSION['l10n']))
            return; // nothing to do
        require_once 'l10n/' . (defined('DBWEBGEN_LANG') ? DBWEBGEN_LANG : 'en') . '.php';
        $_SESSION['l10n'] = $_L10N;
    }

    //--------------------------------------------------------------------------------------
    function l10n($key /* + add'l arguments for replacing %x placeholders */) {
    //--------------------------------------------------------------------------------------
        global $_L10N;
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
    // registers additional string entries (or overrides), useful for localized plugins.
    // Plugins should call this once from their registered $APP['preprocess_func'] function
    function l10n_register($lang, $string_table) {
    //--------------------------------------------------------------------------------------
        if($lang != (defined('DBWEBGEN_LANG') ? DBWEBGEN_LANG : 'en'))
            return; // nothing to do
        if(!isset($_SESSION['l10n_custom'])) {
            $_SESSION['l10n_custom'] = true;
            $_SESSION['l10n'] = array_merge($_SESSION['l10n'], $string_table);
        }
    }
?>
