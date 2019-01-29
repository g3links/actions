<?php

namespace model;

class utils {

    public static function formatBooleanToInt($bool) {
        return $bool ? 1 : 0;
    }

    public static function formatBooleanToString($bool) {
        return $bool ? 'true' : 'false';
    }

    public static function getSearchText($search) {
//            return '%' . str_replace(' ', '%', preg_replace("/[[:blank:]]+/’,", " ", $search)) . '%';
        return '%' . str_replace(' ', '%', preg_replace("/\s+/", " ", $search)) . '%';
    }

    public static function createDateTime($input, $timezoneinterval) {
        if (!isset($input))
            return null;

        $datetime = new \DateTime($input);
        // conver to UTC time (input given in user local time)
        $userInterval = \DateInterval::createFromDateString((string) (- $timezoneinterval) . 'minutes');
        $datetime->add($userInterval);

        return $datetime;
    }

    public static function getDateTimeStamp() {
        return (new \DateTime('now'))->format('Ymd-His');
    }

    public static function forDatabaseDateTime(\DateTime $datetime) {
//        return $datetime->format('Y-m-d H:i:sP');
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function offsetDateTime($datetime, $timezoneinterval) {
//if string, convert to datatime
        if (!$datetime instanceof \DateTime) {
            $datetime = new \DateTime($datetime);
            if ($datetime === false)
                $datetime = new \DateTime();
        }

        //adjust to UTC
        $userInterval = \DateInterval::createFromDateString((string) $timezoneinterval . 'minutes');
        $datetime->add($userInterval);

        return $datetime;
    }

    public static function firstOrDefault($source_list, $predicate) {
        if (!isset($source_list))
            return null;

        return \YaLinqo\Enumerable::from($source_list)->firstOrDefault(null, $predicate);
    }

    public static function lastOrDefault($source_list, $predicate) {
        if (!isset($source_list))
            return null;

        return \YaLinqo\Enumerable::from($source_list)->lastOrDefault(null, $predicate);
    }

    public static function filter($source_list, $predicate) {
        return \YaLinqo\Enumerable::from($source_list)->where($predicate)->toList();
    }

    public static function getDueDateFormatted($datetime, $timezoneinterval) {
        if (!isset($datetime) || $datetime === '')
            return array('', '', '', '');

        $utctime = self::offsetDateTime($datetime, $timezoneinterval);

        $duedate = $utctime->format('Y-n-j');
        $duehour = '';
        $dueminute = '';
        for ($i = 0; $i < 24; ++$i) {
            $duehour = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($duehour == $utctime->format('H'))
                break;
        }
        for ($i = 0; $i < 60; $i = $i + 15) {
            $dueminute = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($dueminute == $utctime->format('i'))
                break;
        }

        $duedatetime = self::forDatabaseDateTime(self::createDateTime(self::format('{0} {1}:{2}:00', $duedate, $duehour, $dueminute), $timezoneinterval));
        return array($duedatetime, $duedate, $duehour, $dueminute);
    }

    public static function readAttachedFile($filenamepath) {
        if (!\is_file($filenamepath))
            return;

        header('Content-Type: application/octet-stream');
//            header('Content-Description: File Transfer');
//            header('Content-Transfer-Encoding: binary');
//            header('Expires: 0');
//            header('Content-Disposition: attachment; filename=' . basename($filenamepath));
//            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//            header('Pragma: public');
        header('Content-Length: ' . filesize($filenamepath));
        ob_clean();
        flush();
        readfile($filenamepath);
    }

    public static function unsetCookie($cookiename, $deletestartwith = false) {
        if (filter_input(INPUT_SERVER, 'HTTP_COOKIE') === null)
            return;

        $cookies = explode(';', filter_input(INPUT_SERVER, 'HTTP_COOKIE'));
        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = \trim($parts[0]);

            if ($name == $cookiename || ($deletestartwith & $cookiename == substr($name, 0, \strlen($cookiename)))) {
                unset($_COOKIE[$name]);
                setcookie($name, '', time() - 3600, '/');
            }
        }
    }

    public static function setCookie($cookiename, $cookievalue) {
        setcookie($cookiename, $cookievalue, time() + (86400 * 15), "/"); // 86400 = 1 day
    }

    public static function getHours() {
        $hours = [];
        $mktime = mktime(00, 00, 00, 01, 01, 2020);
        for ($h = 0; $h < 24; $h++) {
            $mktime = $mktime + 3600;

            $hour = new \stdClass();
            $hour->pad = date("h", $mktime);
            $hour->ampm = \strtolower(date("h A", $mktime));
            $hours[] = $hour;
        }
        return $hours;
    }

    public static function getMinutes() {
        $mins = [];
        for ($i = 0; $i < 60; $i = $i + 15)
            $mins[] = str_pad($i, 2, '0', STR_PAD_LEFT);

        return $mins;
    }

    // max_records = 0, do not paginate (get all)  
    public static function takeList($list, $navpage = 0, $max_records = 50) {
        return self::sorttakeList($list, null, $navpage, $max_records);
    }

    // max_records = 0, do not paginate (get all)  
    public static function sorttakeList($list, $sortfields = null, $navpage = 0, $max_records = 50) {
        if (!is_array($list))
            return $list;

        // nothing to sort or take
        if ((!is_array($sortfields) | !isset($sortfields)) & $max_records === 0)
            return $list;

        //sort
        if (isset($sortfields) && is_array($sortfields)) {
            $isfirst = true;
            foreach ($sortfields as $key => $item) {
                if ($isfirst) {
                    if (empty($item)) {
                        $list = \YaLinqo\Enumerable::from($list)
                                ->orderBy('$v->' . $key);
                    } else {
                        $list = \YaLinqo\Enumerable::from($list)
                                ->orderByDescending('$v->' . $key);
                    }
                    $isfirst = false;
                } else {
                    if (empty($item)) {
                        $list->thenBy('$v->' . $key);
                    } else {
                        $list->thenByDescending('$v->' . $key);
                    }
                }
            }
        }

        if ($max_records === 0)
            return $list->toList();

        return \YaLinqo\Enumerable::from($list)
                        ->skip(((int) $navpage * (int) $max_records))
                        ->take((int) $max_records)
                        ->toList();
    }

    public static function format() {
        $args = func_get_args();
        $val = array_shift($args);
        // Get formatting rules
        $conv = localeconv();
        return preg_replace_callback(
                '/\{\s*(?P<pos>\d+)\s*(?:,\s*(?P<type>\w+)\s*' .
                '(?:,\s*(?P<mod>(?:\w+(?:\s*\{.+?\}\s*,?\s*)?)*)' .
                '(?:,\s*(?P<prop>.+?))?)?)?\s*\}/', function($expr) use($args, $conv) {
            extract($expr);
            extract($conv);
            if (!isset($args[$pos]))
                return $expr[0];

            return $args[$pos];
        }, $val
        );
    }

    public static function http_get_contents($url) {
//            error_log(curl_error('mesaje de get messages'));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (false === ($retval = curl_exec($ch))) {
            error_log(curl_error($ch));
        } else {
            return $retval;
        }
    }

    public static function console($message) {
        echo '<script>console.log("' . $message . '");</script>';
    }

    //  sort by column
    //**********************************************
    public static function getSortDirection($sortname, $prev_sortdirection = null) {
        $prev_sortname = null;
        $sortdirection = false;
        if (isset($prev_sortdirection)) {
            if (!empty($prev_sortdirection)) {
                $result = \explode('_', $prev_sortdirection);
                $prev_sortname = $result[0] ?? '';
                $sortdirection = ($result[1] ?? '') === '' ? false : true;
            }
        }
// compare with previos sort
        if (isset($prev_sortname)) {
            if ($sortname !== $prev_sortname) {
                $prev_sortname = $sortname;
                $sortdirection = false;
            } else {
                $sortdirection = !$sortdirection;
            }
        }

        return $sortdirection;
    }

    public static function echoMessage($messagetext) {
        echo self::format('<h3 style="color: red;">{0}</h3>', $messagetext);
    }

    public static function loadRecords($tablename) {
        //load json records (under views)
        $filename = \model\route::render($tablename);
        if (isset($filename) && !empty($filename)) {
            $jrecords = file_get_contents($filename);
            return json_decode($jrecords);
        }
        return [];
    }

}
