<?php

namespace model;

class route {

    const lexipath = 'theme/lexi/';
    const viewpath = 'view/';
    const configwindowpath = 'config/windows.json';

    //********************************************    
    // open window from Client (Javascript) 
    //********************************************    
    public static function window($windowname, $url) {
        $result = self::_mergeargs(func_get_args());
        return call_user_func_array('self::windowpath', $result);
    }

    // CAUTION: there's a call_user_func_array
    public static function windowpath($url, $tabname, $frame, $title = null, $widthlayout = null, $tabtitle = null, $icon = null) {
        if (is_array($url))
            $url = self::_getpath('url', $url);

        $windowpath = self::_getWindow($url, $frame, $title, $tabname, $widthlayout, $tabtitle, $icon);
        // json string with frame setup        
        $windowpath->window = json_encode($windowpath);

        return $windowpath;
    }

    //********************************************    
    // open window from Sever (PHP) 
    //********************************************    
    public static function open($windowname, $url) {
        $result = self::_mergeargs(func_get_args());
        return call_user_func_array('self::_openargs', $result);
    }

    // CAUTION: there's a call_user_func_array
    private static function _openargs() {
        $args = func_get_args();
        for ($i = 3; $i < 7; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        return self::openpath($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
    }

    public static function openpath($url, $tabname, $frame, $title = null, $widthlayout = null, $tabtitle = null, $icon = null) {
        $process = self::windowpath($url, $tabname, $frame, $title, $widthlayout, $tabtitle, $icon);

        require self::script('model/script/openframe.phtml');
    }

    //********************************************    
    // refresh window from Server (only if open)
    //********************************************    
    public static function refresh() {
        $result = self::_mergeargs(func_get_args());
        return call_user_func_array('self::_refreshargs', $result);
    }

    // CAUTION: there's a call_user_func_array
    private static function _refreshargs() {
        $args = func_get_args();
        for ($i = 3; $i < 7; $i++) {
            if (!isset($args[$i]))
                $args[$i] = null;
        }

        return self::refreshpath($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
    }

    public static function refreshpath($url, $tabname, $frame, $title, $widthlayout, $tabtitle, $icon) {
        $process = self::windowpath($url, $tabname, $frame, $title, $widthlayout, $tabtitle, $icon);

        require self::script('model/script/refreshframe.phtml');
    }

    //******************************
    // windows json format
    //******************************
    // url=request url link (req), 
    // tabid=(empty= generic, idproject) (req),
    // frame=iframe name(under idproject suffix added) (req), 
    // title=window title (opt), 
    // width=window width (opt), 
    // tabtitle = title when tab not active (opt), 
    // icon = CSS class icon name (opt)
    //ATTENTION    
    //set arguments with semicolon + ordinal numbers: e.g.: :1,:2,:3 (system will merge data)

    private static $windowsindexes;

    private static function getwindowindexes($windowname) {
        if (!isset(self::$windowsindexes)) {
            //load json setup
            $jwindows = file_get_contents(DATA_PATH . self::configwindowpath);
            self::$windowsindexes = json_decode($jwindows);
        }

        if (isset(self::$windowsindexes->$windowname))
            return self::$windowsindexes->$windowname; // when using true

        echo '<div style="color: red;">route: window code not found: ' . $windowname . '</div>';
        error_log('g3*** route: window code not found: ' . $windowname);
        return $windowname;
    }

    private static function _mergeargs($args) {
        if (count($args) < 2) {
            $mssg = 'minimum 2 arguments required to open window';
            echo '<div style="color: red;">' . $mssg . '</div>';
            error_log('g3*** route: ' . $mssg);
            return [];
        }

        $origin = self::getwindowindexes($args[0]);
        $origin->frame = $args[0];

        $search = []; // general array
        unset($args[0]);

        //if url array, merge attributes 
        if (is_array($args[1]))
            $args[1] = self::_getpath('url', $args[1]);

        $origin->url = $args[1];
        unset($args[1]);

        $index = 1;
        foreach ($args as $arg)
            $search[] = ':' . $index++;

        //create variable
        if (!isset($origin->tabid))
            $origin->tabid = '';

        if (!isset($origin->title))
            $origin->title = '';

        if (!isset($origin->width))
            $origin->width = 450;

        //order to call G3 script
        $array = [$origin->url, $origin->tabid, $origin->frame, $origin->title, $origin->width];
        if (isset($origin->tabtitle))
            $array[] = $origin->tabtitle;

        if (isset($origin->icon))
            $array[] = $origin->icon;

        //replace variables
        $newargs = str_replace($search, $args, $array);
        // set to null any mismatch
        foreach ($newargs as $k => $v) {
            if (substr($v, 0, 1) === ":")
                $newargs[$k] = null;
        }
        return $newargs;
    }

    //********************************************    
    // url Client (javascript)
    //********************************************    
    public static function form() {
        $args = func_get_args();
        return self::_getpath('form', $args);
    }

    public static function close($tabname, $windowname = null) {
        $framename = null;
        if (isset($windowname))
            $framename = $windowname;

        require self::script('model/script/closeframe.phtml');
    }

    public static function hide($elementid) {
        require self::script('model/script/hideelement.phtml');
    }

    //********************************************    
    // refresh master menu
    //********************************************    
    public static function refreshMaster($idproject = 0) {
        $navigationroute = self::form('g3/index.php?idproject={0}', $idproject);

        require self::script('model/script/refreshmaster.phtml');
    }

    //********************************************    
    // get url merged with arguments
    //********************************************    
    public static function url() {
        $args = func_get_args();
        return self::_getpath('url', $args);
    }

    //********************************************    
    // get a full name script (e.g. language)
    //********************************************    
    public static function script() {
        $args = func_get_args();

//        // get url without arguments and build GET arguments 
        $file = self::_buildGetArguments(self::_getpath('script', $args));

        if (!\is_file($filepathname = DIR_APP . 'ctrl/' . $file)) {
            if (!\is_file($filepathname = DIR_APP . $file)) {
                if (!\is_file($filepathname = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . $file)) {
                    if (!\is_file($filepathname = $file)) {
                        if (\is_file(DIR_APP . self::viewpath . $filepathname)) {
                            $filepathname = DIR_APP . self::viewpath . $filepathname;
                        } else {
                            echo '<p style="color: red;">' . basename($filepathname) . ', not found route script</p>';
                            error_log('g3*** ' . $filepathname . ', not found route script');
                            return '';
                        }
                    }
                }
            }
        }

        //**********************************
        // do not wun TwIG
        //**********************************
        $ext = pathinfo($filepathname, PATHINFO_EXTENSION);
        if (\strtolower($ext) === 'twig') {
            echo '<p style="color: red;">' . basename($filepathname) . ', please use method: RENDER, in order to excute TWIG files</p>';
            error_log('g3*** ' . $filepathname . ', please use method: RENDER, in order to excute TWIG files');
            return '';
        }

        return $filepathname;
    }

    private static function _buildGetArguments($filepathname) {

//        //verify the file and URL arguments 
        if (strpos($filepathname, '?') === false)
            return $filepathname;

        // get url args
        $realpathapp = \explode('?', $filepathname);
        if (!isset($realpathapp[0])) {
            echo '<p style="color: red;">' . basename($filepathname) . ', not found route script</p>';
            error_log('g3*** ' . $filepathname . ', not found route script');
            return $filepathname;
        }

        //create $_GET info
        if (isset($realpathapp[1])) {
            $pararms = explode('&', $realpathapp[1]);
            foreach ($pararms as $pararm) {
                $getvalues = explode('=', $pararm);
                if (isset($getvalues[0]) && isset($getvalues[1]))
                    $_GET[$getvalues[0]] = '' . $getvalues[1];
            }
        }

        return $realpathapp[0];
    }

    //********************************************    
    // render TWIG scripts
    //********************************************    
    //only render pages (e.g.: Twig, htm, htnl,...)
    // use scropt when php scripy inclided
    public static function render($filepathname, $data = null) {
        if (!isset($data))
            $data = [];

        //we only are interested in the first part of the array
        if (is_array($filepathname))
            $filepathname = [$filepathname[0]];

        // has specific lang
        $applocalpath = self::_getFileByLocation($filepathname);

        // no file found yet
        if (!isset($applocalpath)) {
            $applocalpath = DIR_APP . self::viewpath . $filepathname;
            if (!\is_file($applocalpath)) {
                $applocalpath = $filepathname;
                if (!\is_file($applocalpath)) {
                    echo '<p style="color: red;">' . basename($applocalpath) . ', not found route render</p>';
                    error_log('g3*** ' . $filepathname . ', not found route render');
                    return '';
                }
            }
        }

        $ext = pathinfo($applocalpath, PATHINFO_EXTENSION);

        //**********************************
        // only TwIG has an specific rendering method
        //**********************************
        if (\strtolower($ext) !== 'twig') {
            if (\strtolower($ext) === 'phtml') {
                echo '<p style="color: red;">' . basename($applocalpath) . ', please use method: SCRIPT, in order to excute PHTML files</p>';
                error_log('g3*** ' . $applocalpath . ', please use method: SCRIPT, in order to excute PHTML files');
                return '';
            }
            return $applocalpath;
        }

        echo self::_runTwig($filepathname, $data);
    }

    private static function _getFileByLocation($filepathname) {
        // has specific lang
        if (strpos($filepathname, '*') === false)
            return null;

        $langpath = DIR_APP . self::lexipath;
        // try lang only
        $lang = \model\lexi::getLang();
        $applocalpath = self::_getFilePathLanguage($langpath . $filepathname, $lang);
        // try full lang + country
        if (!isset($applocalpath))
            $applocalpath = self::_getFilePathLanguage($langpath . $filepathname, \model\lexi::getLangCountry());

        if (!isset($applocalpath)) {
            // try, withoout full path (like forms)
            $applocalpath = self::_getFilePathLanguage($filepathname, $lang);
            if (!isset($applocalpath))
            // try full lang + country
                $applocalpath = self::_getFilePathLanguage($filepathname, \model\lexi::getLangCountry());
        }

        //try lang='en' default
        if (!isset($applocalpath)) {
            $lang = 'en';
            $applocalpath = self::_getFilePathLanguage($langpath . $filepathname, $lang);
            if (!isset($applocalpath))
            // try full lang + country
                $applocalpath = self::_getFilePathLanguage($langpath . $filepathname, $lang . '-' . \model\lexi::getLangCountry());
        }

        if (!isset($applocalpath))
            return null;

        return $applocalpath;
    }

    private static function _getWindow($url, $frame, $title, $tabname, $widthlayout, $tabtitle, $icon = null) {
        $appRoute = new \stdClass();

        $appRoute->appwidth = 450;
        $appRoute->appFramename = '';
        $appRoute->apppath = '';
        $appRoute->tabname = '';
        $appRoute->title = '';
        $appRoute->tabtitle = '';
        $appRoute->icon = '';

        if (isset($title) && !empty($title))
            $appRoute->title = $title;

        if (isset($icon) && !empty($icon))
            $appRoute->icon = $icon;

        if (isset($tabtitle) & !empty($tabtitle))
            $appRoute->tabtitle = $tabtitle;

        if (isset($url) & empty($url))
            unset($url);

        if (isset($frame) & empty($frame))
            unset($frame);

        if (isset($widthlayout) & empty($widthlayout))
            unset($widthlayout);

        //validate path
        if (isset($url))
            $appRoute->apppath = self::_encodehost($url);

        if (isset($tabname))
            $appRoute->tabname = $tabname;

        if (isset($widthlayout))
            $appRoute->appwidth = (int) $widthlayout;

        if (isset($frame))
            $appRoute->appFramename = $frame;

        return $appRoute;
    }

    private static function _getpath($formtype, $rawargs) {
        if (!isset($rawargs[0])) {
            echo '<p style="color: red;">missing arguments for route</p>';
            error_log('g3*** missing arguments route script');
            return '';
        }
        if (is_array($rawargs[0])) {
            //recursibely get path
            $filepathname = self::_getpath('url', $rawargs[0]);
        } else {
            $filepathname = $rawargs[0];
        }

        switch ($formtype) {
            case 'url' :
                $filepathname = str_replace('\\', '/', $filepathname);
                break;
            case 'script' :
                $filepathname = str_replace('\\', '/', $filepathname);
                break;
            default :
                $filepathname = self::_encodehost($filepathname);
                break;
        }
        //*****************
        $args = null;
        if (count($rawargs) > 1) {
            unset($rawargs[0]);
            //attemp to create args from array
            $argstring = '';
            foreach ($rawargs as $argitem) {
                if (is_array($argitem)) {
                    foreach ($argitem as $k => $v) {
                        if (!empty($argstring))
                            $argstring .= '&';

                        $argstring .= $k . '=' . $v;
                    }
                }
            }
            // build url
            if (!empty($argstring))
                $filepathname .= '?' . $argstring;

            // otherwise convert extra data to array for mapping
            if (empty($argstring))
                $args = array_values($rawargs);
        }

        if (!is_null($args))
            return call_user_func_array(
                    'self::_format', array_merge([$filepathname], is_array($args) ? $args : [$args])
            );

        return $filepathname;
    }

    private static function _format() {
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
                return '';
            return $args[$pos];
        }, $val
        );
    }

    private static function _getFilePathLanguage($filepath, $lang) {
        $filename = str_replace('*', $lang, $filepath);
        if (!file_exists($filename))
            return null;

        return $filename;
    }

    private static function _encodehost($url) {
        $url = str_replace('\\', '/', $url);
        // only evaluate relative paths
        if (substr(\strtolower($url), 0, 4) !== 'http') {
            // search under root + ctrl
            $applocalpath = DIR_APP . 'ctrl/' . $url;

            $pathreview = explode('?', $applocalpath);
            if (!isset($pathreview[0])) {
                echo '<div style="color: red;">missing url path: <b>' . $url . '</b></div>';
                error_log('g3*** missing url path: ' . $url);
            }

            if (!file_exists($pathreview[0])) {
                // fall back path to root
                $applocalpath = DIR_APP . $url;
                $pathreview = explode('?', $applocalpath);
                if (!file_exists($pathreview[0])) {
                    //try fall back path
                    echo '<div style="color: red;">missing window path: <b>' . $url . '</b></div>';
                    error_log('g3*** missing url path: ' . $url);
                }
                $url = WEB_APP . $url;
            } else {
                $url = WEB_APP . 'ctrl/' . $url;
            }
        }

        if (substr(\strtolower($url), 0, 4) !== 'http')
            $url = WEB_HOST . $url;

        return $url;
    }

    // TWIG implementation
    //***************************
    private static $twig;

    private static function _setTwig() {
        $loader = new \Twig_Loader_Filesystem(DIR_APP . self::viewpath);

        $options = [
//            'debug' => false,
//            'charset' => 'UTF-8',
//            'base_template_class' => 'Twig_Template',
//            'strict_variables' => false,
//            'autoescape' => 'html',
            'autoescape' => '',
//           'cache' => false,
//            'auto_reload' => null,
//            'optimizations' => -1,
        ];

        //ISSUE: cache is not replacing dynamic data
//        if (\strtolower(filter_input(INPUT_SERVER, 'SERVER_NAME')) === 'localhost') {
        //no cache
        self::$twig = new \Twig_Environment($loader, $options);
//        } else {
//            // cache
//            $twig = new \Twig_Environment($loader, ['cache' => DIR_APP . 'templatescache']);
//        }
    }

    private static function _runTwig($filepathname, $data) {
        if (!isset(self::$twig))
            self::_setTwig();

        echo self::$twig->render($filepathname, $data);
    }

}
