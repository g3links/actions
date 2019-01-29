<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class sales extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function salestotal() {
        $salestotal = $this->getValue(201);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $salestotal[$period];
    }

    public function costperunittax() {
        $costperunittax = $this->getValue(202);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costperunittax[$period];
    }

    public function costmaterial() {
        $costmaterial = $this->getValue(203);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costmaterial[$period];
    }

    public function costconsumable() {
        $costconsumable = $this->getValue(204);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costconsumable[$period];
    }

    public function costlabor() {
        $costlabor = $this->getValue(205);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costlabor[$period];
    }

    public function costother() {
        $costother = $this->getValue(205);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costother[$period];
    }

    public function costgoodsnotax() {
        $costgoodsnotax = $this->getValue(207);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costgoodsnotax[$period];
    }

    public function costgoodstax() {
        $costgoodstax = $this->getValue(208);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $costgoodstax[$period];
    }

    public function profit() {
        $profit = $this->getValue(209);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $profit[$period];
    }

    public function laborunits() {
        $laborunits = $this->getValue(210);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $laborunits[$period];
    }

}
