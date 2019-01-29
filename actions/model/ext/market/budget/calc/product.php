<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class product extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function salestotal() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $unitsalesprice = $this->getValue(188, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $unitsalesprice[$period] * $soldunits[$period];
    }

    public function costperunittax() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $unitsalesprice = $this->getValue(188, $this->targetbookrow->idproduct);
        $unitcostsalesprice = $this->getValue(191, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = ($unitsalesprice[$period] - $unitcostsalesprice[$period]) * $soldunits[$period];
    }
    
    public function unitsalesprice() {
        $unitsalesprice = $this->getValue(191, $this->targetbookrow->idproduct);
        $tax = $this->getValue(189, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $unitsalesprice[$period] * (1 + $tax[$period] / 100);
    }

    public function costmaterial() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cost = $this->getValue(192, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cost[$period] * $soldunits[$period];
    }

    public function costconsumable() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cost = $this->getValue(193, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cost[$period] * $soldunits[$period];
    }

    public function costlabor() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cost = $this->getValue(194, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cost[$period] * $soldunits[$period];
    }

    public function costother() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cost = $this->getValue(195, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cost[$period] * $soldunits[$period];
    }
    
    public function totalcostgoodsnotax() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cogs = $this->getValue(196, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cogs[$period] * $soldunits[$period];
    }

    public function totalcostgoodstax() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $cogtax = $this->getValue(197, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $cogtax[$period] * $soldunits[$period];
    }
    
    public function totalprofit() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $profit = $this->getValue(198, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $profit[$period] * $soldunits[$period];
    }
    
    public function totallaborunits() {
        $soldunits = $this->getValue(200, $this->targetbookrow->idproduct);
        $laborunits = $this->getValue(199, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $laborunits[$period] * $soldunits[$period];
    }
    
    public function costgoodsnotax() {
        $costmaterials = $this->getValue(192, $this->targetbookrow->idproduct);
        $costconsumables = $this->getValue(193, $this->targetbookrow->idproduct);
        $costlabour = $this->getValue(194, $this->targetbookrow->idproduct);
        $costother = $this->getValue(195, $this->targetbookrow->idproduct);
        $tax = $this->getValue(189, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $cogs = $costmaterials[$period] + $costconsumables[$period] + $costlabour[$period] + $costother[$period];
            $result = $cogs - ws::getValueTaxAppied($cogs, $tax[$period]);

            $this->targetbookrow->periods[$period] = $result;
        }
    }

    public function taxoncostgoods() {
        $costmaterials = $this->getValue(192, $this->targetbookrow->idproduct);
        $costconsumables = $this->getValue(193, $this->targetbookrow->idproduct);
        $costlabour = $this->getValue(194, $this->targetbookrow->idproduct);
        $costother = $this->getValue(195, $this->targetbookrow->idproduct);
        $tax = $this->getValue(189, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $cogs = $costmaterials[$period] + $costconsumables[$period] + $costlabour[$period] + $costother[$period];
            $result = 0;

            if ($tax[$period] > 0)
                $result = $cogs / ($tax[$period] * (1 + ($tax[$period] / 100)));

            $this->targetbookrow->periods[$period] = $result;
        }
    }

    public function profit() {
        $unitsalesprice = $this->getValue(191, $this->targetbookrow->idproduct);
        $ownedpercentage = $this->getValue(190, $this->targetbookrow->idproduct);
        $costmaterials = $this->getValue(192, $this->targetbookrow->idproduct);
        $costconsumables = $this->getValue(193, $this->targetbookrow->idproduct);
        $costlabour = $this->getValue(194, $this->targetbookrow->idproduct);
        $costother = $this->getValue(195, $this->targetbookrow->idproduct);
        $tax = $this->getValue(189, $this->targetbookrow->idproduct);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $cogs = $costmaterials[$period] + $costconsumables[$period] + $costlabour[$period] + $costother[$period];
            if ($tax[$period] > 0)
                $cogs = $cogs - ($cogs / ($tax[$period] * (1 + ($tax[$period] / 100))));
            
            $result = ($unitsalesprice[$period] - $cogs) * ($ownedpercentage[$period] /100);
            $this->targetbookrow->periods[$period] = $result;
        }
    }

}
