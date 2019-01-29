<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class cashflow extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function taxreceived() {
        if (ws::getTaxPercentage() == 0)
            return;

        $results = $this->getValue([135, 136]);
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = ws::getValueTaxAppied($results[$period]);
    }

    public function totalsales() {
        $sales = $this->getValue([135, 136]);
        $tax = $this->getValue(137);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $sales[$period] - $tax[$period];
    }

    public function totalcashin() {
        $results = $this->getValue([135, 136,139,140]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function taxpaidoverheads() {
        if (ws::getTaxPercentage() === 0)
            return;

        $results = $this->getValue([144,145,146,147,148,149,150,151,152,153,154,155,156,157,158]);
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = ws::getValueTaxAppied($results[$period]);
    }

    public function totalexpenses() {
        $results = $this->getValue([144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,160,161,162,163,164,165,166]);
        $tax = $this->getValue(167);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $results[$period] - $tax[$period];
    }

    public function taxpayabletax() {
        $results = $this->getValue(137);
        $tax = $this->getValue(167);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $results[$period] - $tax[$period];
    }

    public function netprofit() {
        $results = $this->getValue(138);
        $expenses = $this->getValue(168);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $results[$period] - $expenses[$period];
    }

    public function ownerdrawings() {
        $results = $this->getValue(100);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function totalcashout() {
        $results = $this->getValue([170,171,172,173,174,168]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

}
