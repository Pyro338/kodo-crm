<?php
/**
 * Created by PhpStorm.
 * User: Nikolay
 * Date: 27.08.2018
 * Time: 16:21
 */

namespace App\Helpers;

class HtmlHelper
{

    public static function findLinks($text)
    {
        $preg_autolinks = array(
            'pattern'     => array(
                "'[\w\+]+://[A-z0-9\.\?\+\-/_=&%#:;]+[\w/=]+'si",
                "'([^/])(www\.[A-z0-9\.\?\+\-/_=&%#:;]+[\w/=]+)'si",
            ),
            'replacement' => array(
                '<a href="$0" target="_blank" rel="nofollow">$0</a>',
                '$1<a href="http://$2" target="_blank" rel="nofollow">$2</a>',
            )
        );
        $search         = $preg_autolinks['pattern'];
        $replace        = $preg_autolinks['replacement'];

        return preg_replace($search, $replace, $text);
    }

    public static function userColor($string)
    {
        return ('#' . mb_substr(md5($string), 0, 6));
    }

    public static function getUserLink($user)
    {
        return '<a href="/list/' . $user->workspace_id . '/' . $user->id . '/0/0/">';
    }

    public static function getDate($date_str){
        $date      = $date_str->Format('d.m.Y');
        $date_year = $date_str->Format('Y');

        $date_time = $date_str->Format('H:i');

        $ndate_exp = explode('.', $date);
        $nmonth    = array(
            1  => 'янв',
            2  => 'фев',
            3  => 'мар',
            4  => 'апр',
            5  => 'мая',
            6  => 'июн',
            7  => 'июл',
            8  => 'авг',
            9  => 'сен',
            10 => 'окт',
            11 => 'ноя',
            12 => 'дек'
        );

        $nmonth_name = '';
        foreach ($nmonth as $key => $value) {
            if ($key == intval($ndate_exp[1])) {
                $nmonth_name = $value;
            }
        }

        if ($date == date('d.m.Y')) {
            $datetime = 'Cегодня в ' . $date_time;
        } else {
            if ($date == date('d.m.Y', strtotime('-1 day'))) {
                $datetime = 'Вчера в ' . $date_time;
            } else {
                if ($date != date('d.m.Y') && $date_year != date('Y')) {
                    $datetime = $ndate_exp[0] . ' ' . $nmonth_name . ' ' . $ndate_exp[2] . ' в ' . $date_time;
                } else {
                    $datetime = $ndate_exp[0] . ' ' . $nmonth_name . ' в ' . $date_time;
                }
            }
        }

        return $datetime;
    }

}