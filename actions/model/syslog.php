<?php

namespace model;

class syslog {

    public static function save($idproject, $iduser, $sqlstatement, $sqlparams) {

        $idproject = $idproject ?? 0;
        
        if(is_array($sqlparams)) {
            $jasonparams = json_encode($sqlparams);
        } else {
            $jasonparams = $sqlparams;
        }

        if ($idproject !== 0) {
            $params = [
                $idproject,
                $iduser,
                $sqlstatement,
                $jasonparams,
                \model\utils::forDatabaseDateTime(new \DateTime())
            ];
        } else {
            $params = [
                $iduser,
                $sqlstatement,
                $jasonparams,
                \model\utils::forDatabaseDateTime(new \DateTime())
            ];
        }
        $jasonrow = json_encode($params);

        $filename = DATA_PATH . 'log/' . ($idproject !== 0 ? 't' . $idproject : 'master') . '.log';

         try {
              file_put_contents($filename, $jasonrow, FILE_APPEND);
        } catch (Exception $ex) {
              \model\message::system($idproject, $sqlstatement, $jasonparams, $ex->getMessage());
        }
    }

}
