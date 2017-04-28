<?
    require_once 'l10n/' . (defined('DBWEBGEN_LANG') ? DBWEBGEN_LANG : 'en') . '.php';

    //--------------------------------------------------------------------------------------
    function l10n($key /* + add'l arguments for replacing %x placeholders */) {
    //--------------------------------------------------------------------------------------
        global $_L10N;
        $str = isset($_L10N[$key]) ? $_L10N[$key] : $key;
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
?>
