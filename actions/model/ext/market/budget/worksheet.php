<?php

namespace model\ext\market\budget;

use \model\ext\market\budget\workspace as ws;

class worksheet extends \model\ext\market\budget\calc\extractvalue {

    public function run($srcp, $idfiscalperiod, $taxpercentage) {
//        ws::setActiveAudit(); //track all formulas 
        
        $modelbp = new \model\ext\market\bp($srcp);
        $modelmarket = new \model\ext\market\market($srcp);

        $idfiscalperiod = ws::setFiscalPeriod($idfiscalperiod, $modelbp->getFiscalPeriods());
        ws::setPeriods($modelbp->getPeriods());

        ws::$budgetbooks = $modelbp->getBudgetBooks();
        ws::setTaxPercentage($taxpercentage);
        
        $this->setupBooks($modelbp->getBudgetGroups());
        $this->setProducts($modelmarket->getProducts());

        // exclude  main product templates
        ws::$budgetbooks = \model\utils::filter(ws::$budgetbooks, '!($v->template === "product" & $v->idproduct === 0)');

        $this->setRawData($modelbp->getBudgetJournal($idfiscalperiod));

        $formulas = \model\utils::filter(ws::$budgetbooks, '$v->formula !== ""');
        foreach ($formulas as $formula) {
            if ($this->runFormula($formula, $taxpercentage) === false)
                continue;
        }

//        echo ws::getFormulaTrack();
        return ws::$budgetbooks;
    }

}
