<?php

/**
 * Translit представляет набор функций для транслитерации строки
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Translit
{
    /**
     * Транслитерация @urlstr в подходящий для url формат
     * @return строка после транслита
     */
    public static function url($urlstr, $ignoreSlash = false)
    {
        if (preg_match('/[^A-Za-z0-9_\-]/', $urlstr)) {
            $urlstr = self::str($urlstr, $ignoreSlash);
            if ($ignoreSlash) {
                $urlstr = preg_replace('/[^A-Za-z0-9\/_\-]/', '', $urlstr);
            } else {
                $urlstr = preg_replace('/[^A-Za-z0-9_\-]/', '', $urlstr);
            }

        }
        return $urlstr;
    }

    /**
     * Транслитирация
     * @return строка после транслита
     */
    public static function str($str, $ignoreSlash = false)
    {
        $str = strtolower($str);
        $tr = array(
            "А" => "a", "Б" => "b", "В" => "v", "Г" => "g",
            "Д" => "d", "Е" => "e", "Є" => "ye", "Ж" => "j", "З" => "z", "И" => "i", "І" => "i", "Ї" => "yi",
            "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n",
            "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t",
            "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch",
            "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "",
            "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "є" => "ye", "ж" => "j",
            "з" => "z", "и" => "i", "і" => "i", "ї" => "yi", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            " " => "_", "." => "", "/" => "_",
        );
        if ($ignoreSlash) {
            $tr["/"] = "/";
        }

        return strtr($str, $tr);
    }
}
