<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class profit extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function sales() {
        $results = $this->getValue(5);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
        $this->targetbookrow->periods[1] = 100;
    }

    public function costsales() {
        $results = $this->getValue(32);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function grosssales() {
        $result1 = $this->getValue(102);
        $result2 = $this->getValue(103);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $result1[$period] - $result2[$period];
            $totalperc += $result1[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function accountancy() {
        $results = $this->getValue(12);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function advertising() {
        $results = $this->getValue(13);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function electricity() {
        $results = $this->getValue(108);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function equipment() {
        $results = $this->getValue(16);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function insurances() {
        $results = $this->getValue(110);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function internet() {
        $results = $this->getValue(111);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function vehicle() {
        $results = $this->getValue(19);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function supplies() {
        $results = $this->getValue(20);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function rent() {
        $results = $this->getValue(114);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function repairs() {
        $results = $this->getValue(22);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function telephone() {
        $results = $this->getValue(116);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function travel() {
        $results = $this->getValue(24);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function compensation() {
        $results = $this->getValue(118);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function other() {
        $results = $this->getValue(22);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        if (ws::getTaxPercentage() > 0)
            $total = $total - ws::getValueTaxAppied($total);

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function banking() {
        $results = $this->getValue(28);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function wages() {
        $results = $this->getValue(29);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function superannuation() {
        $results = $this->getValue(30);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function loaninterest() {
        $results = $this->getValue(123);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function otherexpenses() {
        $results = $this->getValue(33);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function totalexpenses() {
        $results = $this->getValue([106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124]);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function netprofit() {
        $results = $this->getValue(37);
        $perc = $this->getValue(102);
        $total = 0;
        $totalperc = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $total += $results[$period];
            $totalperc += $perc[$period];
        }

        $this->targetbookrow->periods[0] = $total;
        if ($totalperc > 0)
            $this->targetbookrow->periods[1] = $total / $totalperc * 100;
    }

    public function liability() {
        $results = $this->getValue(38);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
    }

    public function drawings() {
        $results = $this->getValue(39);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
    }

    public function loancapital() {
        $results = $this->getValue(40);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
    }

    public function purchases() {
        $results = $this->getValue(41);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
    }

    public function totalotherexpenses() {
        $results = $this->getValue([128,129,130,131]);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period];

        $this->targetbookrow->periods[0] = $total;
    }

    public function reserves() {
        $results = $this->getValue(126);
        $perc = $this->getValue(132);
        $total = 0;
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $total += $results[$period] - $perc[$period];

        $this->targetbookrow->periods[0] = $total;
    }

}
