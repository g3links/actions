<?php

namespace model;

final class message {

    public static function render($message = '', $source = '', $sendemail = false) {

        //pass data to message page
        $message = str_replace('\\', '-', str_replace('/', '-', $message));
        $notificationpage = WEB_APP . 'view/m_notification.html';

        if (!empty($source)) 
            $source = \model\utils::format('<label class="txtLabel">[{0}]</label>', str_replace('\\', '-', str_replace('/', '-', $source)));
        
        $messageInfo = \model\utils::format('<div><b>{0}</b> {1}</div>', $message, $source);

//        error_log('*** g3 mssg: ' . $message);
        require_once \model\route::script('style.php');
        require DIR_APP . 'model/script/message_js.phtml';

        if ($sendemail) {
            try {
                $messageInfo .= \model\utils::format('<div>host: {0}</div>', WEB_HOST);

                \model\env::sendErroremail($source, $messageInfo);
            } catch (Exception $ex) {
                error_log('*** G3 system error: ' . $ex->getMessage());
            }
        }
    }

    // severe message 
    public static function severe($codeerror, $message, $source = '', $sendemail = false) {

        require_once \model\route::script('style.php');
        $data = [
            'codeerror' => $codeerror,
            'message' => $message,
        ];
        \model\route::render('g3/error.twig', $data);

        if ($sendemail) {
            try {
                $messageInfo = \model\utils::format('<div>host: {0}, {1} - {2}</div>', WEB_HOST, $codeerror, $message);

                \model\env::sendErroremail($source, $messageInfo);
            } catch (Exception $ex) {
                error_log('*** G3 system error: ' . $ex->getMessage());
            }
        }
        die();
    }

    public static function system($idproject = 0, $sql = '', $params = '', $message = '') {

        if (is_array($params))
            $params = json_encode($params);

        error_log('*** ' . \model\utils::format('idproject: {0}, sql: {1}, msg: {2}, args: {3}', $idproject, $sql, $message, $params));

        try {
            \model\env::sendErroremail('G3 system error', \model\utils::format('<div>project: {0}</div><div>sql: {1}</div><div>msg: {2}</div><div>args: {3}</div><div>host: {4}</div>', $idproject, $sql, $message, $params, WEB_HOST));
        } catch (Exception $ex) {
            error_log('*** G3 system error: ' . $ex->getMessage());
        }
    }

}
