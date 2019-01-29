<?php

namespace model\ext\market\budget;

class workspace {

    static private $periods;
    static private $idfiscalperiod;
    static private $previousidfiscalperiod;
    static public $budgetbooks;
    static private $taxpercentage;
    static private $taxapplied = 0;
    static private $totalperiods = 0;
    static private $formulatrack = '';

    static public function setFiscalPeriod($idfiscalperiod, $fiscalperiods) {
        // find next fiscal period (if any)
        // fiscalperiods sorted from latest to oldest
        self::$previousidfiscalperiod = null;

        // get default
        if ($idfiscalperiod === 0 & count($fiscalperiods) > 0) {
            foreach ($fiscalperiods as $fiscalperiod) {
                $idfiscalperiod = $fiscalperiod->idfiscalperiod;
                break;
            }
        }

        // get previos fiscal
        $foundrecord = false;
        foreach ($fiscalperiods as $fiscalperiod) {
            if ($foundrecord) {
                self::$previousidfiscalperiod = $fiscalperiod->idfiscalperiod;
                break;
            }

            if ($fiscalperiod->idfiscalperiod === $idfiscalperiod)
                $foundrecord = true;
        }

        self::$idfiscalperiod = $idfiscalperiod;
        return $idfiscalperiod;
    }

    static public function getFiscalPeriod() {
        return self::$idfiscalperiod ?? '';
    }

    static public function getPreviousFiscalPeriod() {
        return self::$previousidfiscalperiod;
    }

    static public function setPeriods($periods) {
        self::$periods = $periods;
        self::$totalperiods = count(self::$periods);
    }

    static public function getPeriods() {
        return self::$periods;
    }

    static public function setTaxPercentage($tax) {
        self::$taxpercentage = $tax;
        self::$taxapplied = $tax * (1 + ($tax / 100));
    }

    static public function getTaxPercentage() {
        return self::$taxpercentage;
    }

    static public function getValueTaxAppied($value, $tax = null) {
        if (!isset($tax))
            $tax = self::$taxapplied;

        return $tax > 0 ? $value / $tax : 0;
    }

    static public function getTotalPeriods() {
        return self::$totalperiods;
    }

    static public function _getNewRow() {
        $periods = [];
        // summary in a single row
        for ($period = 0; $period < self::$totalperiods; $period++)
            $periods[] = 0;

        return $periods;
    }

    static private $auditformulaopen = 0;
    static private $clearaudit = true; // false kept the full audit

    /**
     * Active formula audit. 
     * <p>meta representation in how formulas been used by the business plan, use <b>getFormulaTrack()</b> to retrieve audit list  
     * @param none
     * @throws none
     * @return none
     */
    static public function setActiveAudit() {
        self::$clearaudit = false;
    }

    static public function startFormulaAudit($text) {
        self::$auditformulaopen++;
        self::$formulatrack .= '[' . $text . ' = ';
    }

    static public function addFormulaAudit($text) {
        self::$formulatrack .= ' ' . $text;
    }

    static public function endFormulaAudit() {
        self::$formulatrack .= ']';
        self::$auditformulaopen--;
        if (self::$auditformulaopen <= 0) {
            self::$auditformulaopen = 0;
            self::$formulatrack .= ',';

            if (self::$clearaudit)
                self::$formulatrack = '';
        }
    }

    /**
     * Get a meta representation of the formulas applied at business plan. 
     * <p>use <b>setActiveAudit()</b> to generate all formulas. Otherwise it will return empty    
     * @param none
     * @throws none
     * @return string data
     */
    static public function getFormulaTrack() {
        return self::$formulatrack;
    }

}
