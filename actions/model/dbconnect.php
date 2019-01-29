<?php

namespace model;

abstract class dbconnect extends \model\dao {

    protected $src;

    public function __construct($packagename) {
        parent::__construct($packagename, $this->src->idproject);
    }

    protected function isuserallow($seccode, $source = null) {
        return \model\env::isUserAllow($this->src->idproject, $seccode, $source);
    }

}
