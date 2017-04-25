<?
    require_once 'l10n/' . (isset($APP['lang']) ? $APP['lang'] : 'en') . '.php';

    //--------------------------------------------------------------------------------------
    function l10n($key /* + add'l arguments for replacing %x placeholders */) {
    //--------------------------------------------------------------------------------------
        global $_L10N;
        $str = isset($_L10N[$key]) ? $_L10N[$key] : $key;
        for($i = func_num_args() - 1; $i > 0; $i--)
            $str = str_replace('$'.$i, func_get_arg($i), $str);
        return $str;
    }
?>
