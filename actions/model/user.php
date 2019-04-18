<?php

namespace model;

class user extends \model\dbconnect {

    public function __construct() {
        $this->src = \model\env::src(0);
        parent::__construct(\model\env::CONFIG_CORE);
    }

    public function getProfileSession($iduser = null) {
        $result = new \stdClass();
        $result->user = $this->getuser($iduser);

        if ($result->user->theme === '') {
            $result->user->theme = '1';
        }
        $result->theme = (new \model\project)->getTheme((int) $result->user->theme);

        return $result;
    }

    public function getUserProfile($iduser = null) {
        $result = new \stdClass();
        $result->user = $this->getuser($iduser);

        if ($result->user->theme === '') {
            $result->user->theme = '1';
        }
        $result->themes = (new \model\project)->getThemes();

        return $result;
    }

    public function getuser($iduser = null) {
        if (!isset($iduser))
            $iduser = \model\env::getIdUser();

        return $this->getRecord('SELECT iduser,lastaccesson,deleted,isvalidated,email,name,keyname,theme,idproject FROM user WHERE iduser = ?', (int) $iduser);
    }

    public function isActive($iduser = null) {
        if (!isset($iduser))
            $iduser = \model\env::getIdUser();

        $result = $this->getRecord('SELECT count(*) AS result FROM user WHERE iduser = ? AND isvalidated = ? AND deleted = ?', (int) $iduser, 0, 0);
        return ($result->result ?? 0) ? true : false;
    }

    public function getinvitationsbyEmail() {
        return $this->getRecords('SELECT projectinvitation.idprojectinv,projectinvitation.idrole,projectinvitation.idproject,project.title FROM projectinvitation JOIN project USING ( idproject ) WHERE projectinvitation.useremailinvited = ?', \trim((string) \model\env::getUserEmail()));
    }

    public function search($search, $take) {
        $search = \model\utils::getSearchText($search);
        return $this->getRecords('SELECT name,email,iduser FROM user WHERE ( name LIKE ? OR email LIKE ? ) AND deleted = ? AND isvalidated = ? LIMIT ?', $search, $search, 0, 0, (int) $take);
    }

    public function getpeople($search, $take) {
        $search = '%' . \trim((string) $search) . '%';
        return $this->getRecords('SELECT name,email FROM user WHERE name LIKE ? OR email LIKE ? LIMIT ?', $search, $search, (int) $take);
    }

}
