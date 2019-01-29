<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class mybudget extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function totalincome() {
        $results = $this->getValue([48,49,50]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function totalexpenses() {
        $results = $this->getValue([54,55,56,57,58,59,62,63,64,65,66,68,69,70,72,73,74,75,76,77,78,80,81,82,83,84,85,87,88,83,90,91,93,94,95,96,98]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function drawings() {
        $results = $this->getValue(51);
        $expenses = $this->getValue(99);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $results[$period] - $expenses[$period];
    }

}
