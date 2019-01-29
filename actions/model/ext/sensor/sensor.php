<?php

namespace model\ext\sensor;

class sensor extends \model\dbconnect {

    public function __construct($src) {
        $this->src = $src;

        parent::__construct(\model\env::CONFIG_ACTIONS);
    }

    const ROLE_SENSORUPDATE = '015';
            
    public function hasDeviceService() {
        $result = $this->getRecord('SELECT count(*) AS result FROM device WHERE deleted = ?', 0);
        return ($result->result ?? 0) > 0;
    }

    public function insertDevice($newdevice) {
        $lastrowid = $this->_insertDevice($this->src->idproject, \model\env::getIdUser(), $newdevice);

        if ($lastrowid !== false) {
            $texto = \model\lexi::get('', 'sys032') . ': ' . $newdevice->name;
            (new \model\action($this->src))->addSystemNote($texto);
        }

        return $lastrowid;
    }

    public function updateDevice($updateddevice) {
        $this->_updateDevice($this->src->idproject, \model\env::getIdUser(), $updateddevice);
    }

    public function deleteDevice($iddevice) {
        $deviceselected = $this->getDevice($iddevice);
        if (!isset($deviceselected))
            return;

        $allok = $this->_deleteDevice($this->src->idproject, \model\env::getIdUser(), $iddevice);

        if ($allok !== false) {
            $texto = \model\lexi::get('', 'sys033') . ': ' . $deviceselected->iddevice . ', ' . $deviceselected->name;
            (new \model\action($this->src))->addSystemNote($texto);
        }
    }

    public function getDeviceRawData($iddevice, $navpage, $max_records = 50) {
        return $this->getRecords('SELECT rawdata,createdon FROM devicedata WHERE iddevice = ? ORDER BY createdon DESC LIMIT ?, ?', (int) $iddevice, (int) $navpage * $max_records, (int) $max_records);
    }

    public function getTotalDeviceRawData($iddevice) {
        $result = $this->getRecord('SELECT count(*) AS result FROM devicedata WHERE iddevice = ?', (int) $iddevice);
        return $result->result ?? 0;
    }

    public function getDevice($iddevice) {
        return $this->getRecord('SELECT iddevice,idproject,name,partnro,token,secretcode,lastupdated,createdon,inactive,deleted,ip,url,gps,country,city,state,address1,address2,address3,phone,zipcode,email,weburl,contact FROM device WHERE iddevice = ? AND deleted = ?', (int) $iddevice, 0);
    }

    public function getListDevices() {
        return $this->getRecords('SELECT iddevice,name,inactive,createdon,partnro FROM device WHERE deleted = ?', 0);
    }

    public function insertRawData($iddevice, $partnro, $data, $source) {
        $returndevice = $this->getRecord('SELECT partnro FROM device WHERE iddevice = ? AND deleted = ? AND inactive = ?', (int) $iddevice, 0, 0);

//find valid device
        $isok = false;
        if (isset($returndevice)) 
            $isok = \strtolower(\trim($returndevice->partnro)) === \strtolower(\trim($partnro));

        $this->_insertRawData($this->src->idproject, \model\env::getIdUser(), $iddevice, $isok, $data, $source);
    }

    private function _deleteDevice($idproject, $iduser, $iddevice) {
        if (!$this->isuserallow(self::ROLE_SENSORUPDATE, self::class))
            return false;

        $this->executeSql('UPDATE device SET deleted = ? WHERE iddevice = ? AND deleted = ?', 1, (int) $iddevice, 0);
    }

    private function _insertDevice($idproject, $iduser, $newdevice) {
        if (!$this->isuserallow(self::ROLE_SENSORUPDATE, self::class))
            return false;

        return $this->executeSql('INSERT INTO device (deleted, idproject, name, inactive, secretcode, token, partnro, ip, url, gps, country, city, state, address1, address2, address3, phone, zipcode, email, weburl, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 0, (int) $idproject, \trim((string) $newdevice->name), \model\utils::formatBooleanToInt($newdevice->inactive), \trim((string) $newdevice->secretcode), \trim((string) $newdevice->token), \trim((string) $newdevice->partnro), \trim((string) $newdevice->ip), \trim((string) $newdevice->url), \trim((string) $newdevice->gps), \trim((string) $newdevice->country), \trim((string) $newdevice->city), \trim((string) $newdevice->state), \trim((string) $newdevice->address1), \trim((string) $newdevice->address2), \trim((string) $newdevice->address3), \trim((string) $newdevice->phone), \trim((string) $newdevice->zipcode), \trim((string) $newdevice->email), \trim((string) $newdevice->weburl), \trim((string) $newdevice->contact));
    }

    private function _updateDevice($idproject, $iduser, $updateddevice) {
        if (!$this->isuserallow(self::ROLE_SENSORUPDATE, self::class))
            return false;

        $this->executeSql('UPDATE device SET name = ?, lastupdated = ?, inactive = ?, secretcode = ?, partnro = ?, ip = ?, url = ?, gps = ?, country = ?, city = ?, state = ?, address1 = ?, address2 = ?, address3 = ?, phone = ?, zipcode = ?, email = ?, weburl = ?, contact = ? WHERE iddevice = ? AND deleted = ?', \trim((string) $updateddevice->name), \model\utils::forDatabaseDateTime(new \DateTime()), \model\utils::formatBooleanToInt($updateddevice->inactive), \trim((string) $updateddevice->secretcode), \trim((string) $updateddevice->partnro), \trim((string) $updateddevice->ip), \trim((string) $updateddevice->url), \trim((string) $updateddevice->gps), \trim((string) $updateddevice->country), \trim((string) $updateddevice->city), \trim((string) $updateddevice->state), \trim((string) $updateddevice->address1), \trim((string) $updateddevice->address2), \trim((string) $updateddevice->address3), \trim((string) $updateddevice->phone), \trim((string) $updateddevice->zipcode), \trim((string) $updateddevice->email), \trim((string) $updateddevice->weburl), \trim((string) $updateddevice->contact), (int) $updateddevice->iddevice, 0);

        //change pass only when it has data
        if (!empty($updateddevice->token)) 
            $this->executeSql('UPDATE device SET token = ? WHERE iddevice = ? AND deleted = ?', \trim((string) $updateddevice->token), (int) $updateddevice->iddevice, 0);
    }

    private function _insertRawData($idproject, $iduser, $iddevice, $isok, $data, $source) {
        if (!$this->isuserallow(self::ROLE_SENSORUPDATE, self::class))
            return false;

        return $this->executeSql('INSERT INTO devicedata (iddevice, rawdata, validated) VALUES (?, ?, ?)', (int) $iddevice, (string) $data, \model\utils::formatBooleanToInt($isok));
    }

}
