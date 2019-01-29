<?php

namespace model\ext\market\budget\calc;

use \model\ext\market\budget\workspace as ws;

abstract class extractvalue {

    protected $targetbookrow;

    private function _getCleanPeriodArray() {
        $periods = [];
        // summary in a single row
        for ($period = 0; $period < ws::getTotalPeriods(); $period++)
            $periods[] = 0;

        return $periods;
    }

    protected function setupBooks($budgetgroups) {
        //clean-up books
        foreach ($budgetgroups as $budgetgroup) {
            $groupbudgetbooks = \model\utils::filter(ws::$budgetbooks, '$v->idbg === ' . $budgetgroup->idbg);
            foreach ($groupbudgetbooks as $budgetbook) {
                $budgetbook->balanceend = 0;
                $budgetbook->lock = false;
                $budgetbook->hidden = false;

                if (!isset($budgetbook->acc) & !isset($budgetbook->name))
                    $budgetbook->hidden = true;

                if (!isset($budgetbook->name))
                    $budgetbook->name = '';

                if (!isset($budgetbook->acc))
                    $budgetbook->acc = '';

                if (!isset($budgetbook->level))
                    $budgetbook->level = '';

                if (!isset($budgetbook->formula))
                    $budgetbook->formula = '';

                if (!isset($budgetbook->style))
                    $budgetbook->style = '';

                if (\trim(\strtolower($budgetbook->level)) === 'e')
                    $budgetbook->style = 'bentry';

                if (!isset($budgetbook->template))
                    $budgetbook->template = $budgetgroup->template;

                if (!isset($budgetbook->idproduct))
                    $budgetbook->idproduct = 0;

                if (isset($budgetbook->paypercentage))
                    $budgetbook->name .= ' ' . $budgetbook->paypercentage . '%';

                if (empty(\trim($budgetbook->level)) & empty(\trim($budgetbook->formula)))
                    $budgetbook->level = 'T';
            }
        }
    }

    protected function setProducts($products) {
        //build individual products
        $producttemplates = \model\utils::filter(ws::$budgetbooks, '$v->template === "product"');
        foreach ($products as $product) {
            foreach ($producttemplates as $template) {
                $row = clone($template);

                if ($template === $producttemplates[0]) // title product
                    $row->name = $product->name;

                $row->idproduct = $product->idproduct;

                ws::$budgetbooks[] = $row;
            }
        }
    }

    protected function setRawData($allbudgetjournals) {
        $hasdata = (count($allbudgetjournals) > 0);

        // set raw data 
        foreach (ws::$budgetbooks as $budgetbook) {
            $budget_period_row = [];

            // get data for a single account            
            $budgetjournals = \model\utils::filter($allbudgetjournals, \model\utils::format('$v->idbb === {0} & $v->idproduct === {1}', $budgetbook->idbb, $budgetbook->idproduct));

            // build value by period ******************************
            foreach (ws::getPeriods() as $period) {
                $totalaccount = 0;

                if ($hasdata) {
                    $journalperiods = \model\utils::filter($budgetjournals, '$v->idperiod === ' . $period->idperiod);

                    foreach ($journalperiods as $journalperiod)
                        $totalaccount += (float) $journalperiod->periodvalue;
                }

                $budget_period_row[] = $totalaccount;
            }

            $budgetbook->periods = $budget_period_row;
        }
    }

    protected function runFormula($budgetbook) {
        if (empty($budgetbook->formula))
            return false;

        if ($budgetbook->lock) {
            echo '<label  class="onHoldSelection">id: ' . $budgetbook->idbb . ', circular formula: ' . $budgetbook->formula . ', path: ' . $budgetbook->template . '</label> audit: ' . ws::getFormulaTrack() . '<br>';
            return false;
        }

        $budgetbook->lock = true; // control circular formula

        $pathobjectlogic = str_replace('\\', '/', __NAMESPACE__ . '/' . $budgetbook->template);

        $localfilepath = \model\utils::format('{0}/{1}.php', DIR_APP . $pathobjectlogic);
        if (!\is_file($localfilepath)) {
            echo '<label  class="onHoldSelection">file not found: ' . $localfilepath . '.php</label><br>';
            return false;
        }

        $pathobjectlogic = '\\' . str_replace('/', '\\', __NAMESPACE__ . '\\' . $budgetbook->template);

        $logic = new $pathobjectlogic($budgetbook);
        $function = \rtrim(\strtolower($budgetbook->formula));

        ws::startFormulaAudit($budgetbook->formula . ($budgetbook->idproduct > 0 ? '(prd:' . $budgetbook->idproduct . ')' : ''));

        if (!method_exists($logic, $function)) {
            echo '<label  class="onHoldSelection">id: ' . $budgetbook->idbb . ', undefined formula: ' . $function . ', path: ' . $budgetbook->template . '</label><br>';
            return false;
        }

        // clear formula: since it will be executed, no need to run again
        $budgetbook->formula = '';
        $logic->$function();
        ws::endFormulaAudit();
    }

    protected function getValue($idbb, $idproduct = 0) {
        if (is_array($idbb))
            return $this->_getValues($idbb, $idproduct);

        return $this->_buildPeriodValues($idbb, $idproduct);
    }

    private function _getValues($idbbs, $idproduct = 0) {
        $summary_periods = $this->_getCleanPeriodArray();
        foreach ($idbbs as $idbb) {
            $results = $this->_buildPeriodValues($idbb, $idproduct);

            for ($period = 0; $period < ws::getTotalPeriods(); $period++)
                $summary_periods[$period] += $results[$period];
        }

        return $summary_periods;
    }

    private function _buildPeriodValues($idbb, $idproduct = 0) {
        $summary_periods = $this->_getCleanPeriodArray();

        $predicate = '$v->idbb === ' . $idbb;
        if ($idproduct > 0)
            $predicate = \model\utils::format('$v->idbb === {0} & $v->idproduct === {1}', $idbb, $idproduct);

        $budgetbookprds = \model\utils::filter(ws::$budgetbooks, $predicate);

        if (count($budgetbookprds) === 0) {
            echo '<label  class="onHoldSelection">account: ' . $idbb . ', idproduct: ' . $idproduct . ', not found.</label><br>';
            return $summary_periods;
        }

        // calculate formulas
        foreach ($budgetbookprds as $budgetbookprd) {
            if ($budgetbookprd === $budgetbookprds[0])
                ws::addFormulaAudit('(' . $budgetbookprd->acc . ') ' . $budgetbookprd->name);

            $this->runFormula($budgetbookprd);
        }

        // added up all values
        foreach ($budgetbookprds as $budgetbookprd) {
            for ($period = 0; $period < ws::getTotalPeriods(); $period++)
                $summary_periods[$period] += $budgetbookprd->periods[$period];

        }

        return $summary_periods;
    }

    protected function getTemplate($template) {
        return \model\utils::filter(ws::$budgetbooks, \model\utils::format('$v->template === "{0}"', $template));
    }

}
