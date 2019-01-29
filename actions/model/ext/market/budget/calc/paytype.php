<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class paytype extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function paymentsflow() {
        $totalsales = $this->getValue(177);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $result = $this->targetbookrow->paypercentage * $totalsales[$period] / 100;
            // @TODO move values foward to next fical period
            if(($period + $this->targetbookrow->periodgap) < ws::getTotalPeriods())
                $this->targetbookrow->periods[$period + $this->targetbookrow->periodgap] += $result;
        }
    }

}
