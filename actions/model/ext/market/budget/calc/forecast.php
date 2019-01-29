<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

class forecast extends \model\ext\market\budget\calc\extractvalue {

    public function __construct($targetbookrow) {
        $this->targetbookrow = $targetbookrow;
    }

    public function cashsales() {
        $totalsales = $this->getValue(177);
        $paymethods = $this->getTemplate('paytype');
                
        $paymethod = \model\utils::firstOrDefault($paymethods, '$v->periodgap === 0');

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $result = $totalsales[$period];
            if (isset($paymethod))
                $result = $totalsales[$period] * $paymethod->paypercentage / 100;

            $this->targetbookrow->periods[$period] = $result;
        }
    }

    public function creditsales() {
        $totalsales = $this->getValue(177);
        $paymethods = $this->getTemplate('paytype');
        
        $paymethods = \model\utils::filter($paymethods, '$v->periodgap !== 0');

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $result = 0;
            foreach ($paymethods as $payment) {
                $base_pay_index = $period - $payment->periodgap;
                if ($base_pay_index >= 0)
                    $result = $totalsales[$base_pay_index] * $payment->paypercentage / 100;
            }

            $this->targetbookrow->periods[$period] = $result;
        }
    }

    public function taxreceived() {
        $sales = $this->getValue([2,3]);
        $cogtax = $this->getValue(184);
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
//            $this->targetbookrow->periods[$period] = $cogtax[$period];
            $this->targetbookrow->periods[$period] = ws::getValueTaxAppied($sales[$period]);
    }

    public function totalsales() {
        $sales = $this->getValue([2,3]);
        $tax = $this->getValue(4);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $sales[$period] - $tax[$period];
    }

    public function taxincluded() {
        if (ws::getTaxPercentage() === 0)
            return;

        $accounting = $this->getValue([12,13]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = ws::getValueTaxAppied($accounting[$period]);
    }

    public function cogs() {
        $results = $this->getValue(183);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function taxoncogs() {
        $results = $this->getValue(184);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function totalcashin() {
        $results = $this->getValue([2,3,6,7]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function taxpaidoverheads() {
        if (ws::getTaxPercentage() === 0)
            return;

        $results = $this->getValue([12,13,14,15,16,17,18,19,20,21,22,23,24,25,26]);
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = ws::getValueTaxAppied($results[$period]);
    }

    public function totalexpenses() {
        $results = $this->getValue([12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,29,30,31,32,33]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $this->targetbookrow->periods[$period] = $results[$period];
    }

    public function taxpayabletax() {
        $profit = $this->getValue(4);
        $tax = $this->getValue([34,35]);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $profit[$period] - $tax[$period];
    }

    public function netprofit() {
        $taxsales = $this->getValue(5);
        $expenses = $this->getValue(36);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $taxsales[$period] - $expenses[$period];
    }

    public function totalcashout() {
        $results = $this->getValue([34,35,36,38,39,40,41,42]);
        $insurance = $this->getValue(14);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) {
            $result = $results[$period];
            if (ws::getTaxPercentage() > 0)
                $result = $result - ws::getValueTaxAppied($insurance[$period]);

            $this->targetbookrow->periods[$period] = $result;
        }
    }

    public function netcash() {
        $results = $this->getValue(8);
        $expenses = $this->getValue(43);

        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            $this->targetbookrow->periods[$period] = $results[$period] - $expenses[$period];
    }

    public function openingbalance() {
//        error_log('*** -> cashflow_2, openingblanace required');
        // @TODO las value need to be registered for next year 

        // get previous fiscal period balance
        $previousfiscalperiod = ws::getPreviousFiscalPeriod();
        if(isset($previousfiscalperiod)) {
            $this->targetbookrow->periods[0] = (new \model\ext\market\bp(\model\env::session_src()))->getBudgetJournalBalance($previousfiscalperiod, 45, 0);
        }
        
        $results = $this->getValue(44);
        
        for ($period = 0; $period < ws::getTotalPeriods(); $period++) 
            if(($period + 1) < ws::getTotalPeriods()) 
                $this->targetbookrow->periods[$period + 1] += $results[$period] + $this->targetbookrow->periods[$period];      
    }
    
    public function closingbalance() {
        $results = $this->getValue([44,45]);
        $finalbalance = 0;
        
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)  {
            $this->targetbookrow->periods[$period] = $results[$period];
            $finalbalance = $results[$period];
        }
        
        // save current fincal period balance
        (new \model\ext\market\bp(\model\env::session_src()))->setBudgetJournalBalance(ws::getFiscalPeriod(), 45, 0, $finalbalance);
    }

}
