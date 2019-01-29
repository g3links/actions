<?php

namespace model\ext\market;

class bp extends \model\dbconnect {

    const BUDGET_BOOK = 'books';
    const BUDGET_PROFIT = 'profit';
    const ROLE_SALEFORECAST = '100';

    public function __construct($src) {
        $this->src = $src;

        parent::__construct(\model\env::CONFIG_ACTIONS);
    }

    public function getBudgetSession($idbg, $idfiscalperiod = 0) {
        $result = new \stdClass();

        $result->isrole = $this->isuserallow(self::ROLE_SALEFORECAST, self::class);

        $result->budgetgroup = $this->getBudgetGroup($idbg);

        if (!isset($result->budgetgroup)) {
            $mssg = \model\lexi::get('actions/bp', 'sys044') . ': ' . $idbg;
            \model\message::severe('sys044', $mssg);
        }

//************************************
// find the idfiscalperiod default, stop at failure
//************************************
        $existfiscalperiod = false;

// find the latest group
        $fiscalperiodname = '';
        $result->fiscalperiods = $this->getFiscalPeriods();
        if ($idfiscalperiod === 0) {
            foreach ($result->fiscalperiods as $fiscalperiod) {
                $fiscalperiodname = $fiscalperiod->fiscalname;
                $existfiscalperiod = true;
                break;
            }
        } else {
            $searchficalperiod = \model\utils::filter($result->fiscalperiods, '$v->idfiscalperiod === ' . $idfiscalperiod);
            if (count($searchficalperiod) > 0)
                $existfiscalperiod = true;
        }

        if (!$existfiscalperiod) {
            $mssg = \model\lexi::get('actions/bp', 'sys002') . ' ' . $idfiscalperiod;
            \model\message::severe('sys002', $mssg);
        }

        $result->fiscalperiodname = $fiscalperiodname;
        return $result;
    }

    public function getBudgetGridData($idbg, $idfiscalperiod) {
        $result = new \stdClass();

        $result->isrole = $this->isuserallow(self::ROLE_SALEFORECAST, self::class);

        $budgetgroup = $this->getBudgetGroup($idbg);

        $financial = $this->getFinancial();
        $taxpercentage = 0;
        if (isset($financial))
            if ($financial->taxincluded)
                $taxpercentage = $financial->taxpercentage;

        $budget_grids = (new \model\ext\market\budget\worksheet)->run($this->src, $idfiscalperiod, $taxpercentage);

        $result->periods = $this->getPeriods();
        $result->fiscalperiodname = $this->getFiscalPeriod($idfiscalperiod)->fiscalname ?? '';
        $result->budgetgroupreport = \trim(\strtolower($budgetgroup->report));
        $result->budget_rows = \model\utils::filter($budget_grids, '$v->idbg === ' . $idbg);

        return $result;
    }

    public function getFinancialSession() {
        $result = new \stdClass();
        $result->isrole = $this->isuserallow(\model\action::ROLE_FINANCIALS, self::class);
        $result->financial = (new \model\ext\market\bp(\model\env::session_src()))->getFinancial();

        return $result;
    }

    public function getFinancial() {
        $result = $this->getRecord('SELECT createon,idfinancial,lastmodifiedon,taxincluded,taxpercentage FROM financial LIMIT 1');

        if (!isset($result)) {
            $result = new \stdClass();
            $result->taxincluded = false;
            $result->taxpercentage = 0;
        }
        return $result;
    }

    public function setFinancial($taxincluded, $taxpercentage) {
        $result = $this->getRecord('SELECT createon,idfinancial,lastmodifiedon,taxincluded,taxpercentage FROM financial LIMIT 1');

        if (!isset($result)) {
            $allok = $this->_insertFinancial($taxincluded, $taxpercentage);
        } else {
            $allok = $this->_updateFinancial($taxincluded, $taxpercentage);
        }

        if ($allok !== false)
            (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys018'));
    }

    public function getFiscalPeriodsSession() {
        $result = new \stdClass();
        $result->isrole = $this->isuserallow(\model\action::ROLE_FISCALPERIOD, self::class);
        $result->fiscalperiods = $this->getFiscalPeriods();

        return $result;
    }

    public function getFiscalPeriods() {
        return $this->getRecords('SELECT idfiscalperiod,fiscalname,seq,isclosed FROM fiscalperiod ORDER BY seq,idfiscalperiod DESC,idfiscalperiod DESC');
    }

    public function getFiscalPeriod($idfiscalperiod) {
        return $this->getRecord('SELECT idfiscalperiod,fiscalname,seq,isclosed FROM fiscalperiod WHERE idfiscalperiod = ? ORDER BY seq DESC,idfiscalperiod DESC', (int) $idfiscalperiod);
    }

    public function setFiscalPeriod($idfiscalperiod, $updatefiscalperiod) {
        if ($idfiscalperiod === 0) {
            $return = $this->_insertFiscalPeriod($updatefiscalperiod);
        } else {
            $return = $this->_updateFiscalPeriod($idfiscalperiod, $updatefiscalperiod);
        }

        if ($return !== false)
            (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys044', $updatefiscalperiod->fiscalname));
    }

    public function deleteFiscalPeriod($idfiscalperiod) {
        // do not delete when data is included in sales
        // @TODO  do not delete when data is included in budget
//        $fiscalperiod = $this->getFiscalPeriod($idfiscalperiod);
//        if (!isset($fiscalperiod->fiscalname))
//            return;

        $budgetjournals = $this->getRecords('SELECT idbj FROM budgetjournal WHERE idfiscalperiod = ?', (int) $idfiscalperiod);
        if (count($budgetjournals) > 0) {
            \model\message::render(\model\lexi::get('', 'sys046', $idfiscalperiod));
        } else {
// @TODO verify it not been included in planner
            $allok = $this->_deleteFiscalPeriod($idfiscalperiod);

            if ($allok !== false) {
                $period = $this->getFiscalPeriod($idfiscalperiod);
                (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys045', $period->fiscalname));
            }
        }
    }

    //******** PERIOD ******************
    private $periodcache;

    public function getPeriod($idperiod) {
        if ($idperiod > 0) {
            if (!isset($this->periodcache))
                $this->getPeriods();

            return \model\utils::firstOrDefault($this->periodcache, '$v->idperiod === ' . $idperiod);
        }
        return null;
    }

    public function getPeriods($all = false) {
        if (!isset($this->periodcache)) {
            $this->periodcache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_PERIOD);
            if (!$all)
                $this->periodcache = \model\utils::filter($this->periodcache, '$v->deleted === 0');
        }
        return $this->periodcache;
    }

    //******** BUDGETGROUP ******************
    private $bgcache;

    public function getBudgetGroup($idbg) {
        if ($idbg > 0) {
            if (!isset($this->bgcache))
                $this->getBudgetGroups();

            return \model\utils::firstOrDefault($this->bgcache, '$v->idbg === ' . $idbg);
        }
        return null;
    }

    public function getBudgetGroups() {
        if (!isset($this->bgcache))
            $this->bgcache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_BUDGETGROUP);

        return $this->bgcache;
    }

    //******** BUDGETBOOK ******************
    private $bbcache;

    public function getBudgetBook($idbb) {
        if ($idbb > 0) {
            if (!isset($this->bbcache))
                $this->bbcache = $this->getBudgetBooks();

            return \model\utils::firstOrDefault($this->bbcache, '$v->idbb === ' . $idbb);
        }
        return null;
    }

    public function getBudgetBookForGroup($idbg) {
        if ($idbg > 0) {
            if (!isset($this->bbcache))
                $this->getBudgetBooks();

            return \model\utils::filter($this->bbcache, '$v->idbg === ' . $idbg);
        }
        return null;
    }

    public function getBudgetBooks() {
        if (!isset($this->bbcache))
            $this->bbcache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_BUDGETBOOK);

        return $this->bbcache;
    }

    public function getBudgetJournal($idfiscalperiod) {
        return $this->getRecords('SELECT idbj,idbb,idfiscalperiod,idperiod,periodvalue,idproduct,createdon,lastmodifiedon FROM budgetjournal WHERE idfiscalperiod = ?', (int) $idfiscalperiod);
    }

    public function getBudgetJournalBook($idfiscalperiod, $idbb, $idproduct) {
        $result = new \stdClass();
        $result->isrole = $this->isuserallow(self::ROLE_SALEFORECAST, self::class);
        $budgetjournals = $this->getRecords('SELECT idbj,idbb,idfiscalperiod,idperiod,idproduct,periodvalue,createdon,lastmodifiedon FROM budgetjournal WHERE idbb = ? AND idfiscalperiod = ? AND idproduct = ?', (int) $idbb, (int) $idfiscalperiod, (int) $idproduct);

        $result->periods = $this->getPeriods();

        foreach ($result->periods as $period) {
            $journalsperiod = \model\utils::filter($budgetjournals, '$v->idperiod === ' . $period->idperiod);

            $period->value = 0;
            foreach ($journalsperiod as $journalperiod)
                $period->value = $journalperiod->periodvalue;
        }
        return $result;
    }

    public function getBudgetJournalBalance($idfiscalperiod, $idbb, $idproduct) {
        $budgetjournal = $this->getRecord('SELECT idbj,periodvalue,createdon,lastmodifiedon FROM budgetjournal WHERE idbb = ? AND idfiscalperiod = ? AND idperiod = ? AND idproduct = ?', (int) $idbb, (int) $idfiscalperiod, 0, (int) $idproduct);

        if (isset($budgetjournal))
            return $budgetjournal->periodvalue;

        return 0;
    }

    public function setBudgetJournalBalance($idfiscalperiod, $idbb, $idproduct, $balancevalue) {
        if (!$this->isuserallow(self::ROLE_SALEFORECAST, self::class))
            return false;

        $result = $this->getRecord('SELECT isclosed FROM fiscalperiod WHERE idfiscalperiod = ?', (int) $idfiscalperiod);
        if(!$result->isclosed)
            $this->_updateJournal($idbb, $balancevalue, $idfiscalperiod, $idproduct);
    }

    public function setJournal($idbb, $idfiscalperiod, $idproduct, $arguments) {
        if (!$this->isuserallow(self::ROLE_SALEFORECAST, self::class))
            return false;

        $periodsvalue = [];

        foreach ($arguments as $param_name => $param_val) {
            if (substr($param_name, 0, 2) !== "p-")
                continue;

            $journalrow = explode('-', $param_name);
            $periodsvalue[(int) $journalrow[1]] = (float) $param_val;
        }
        $allok = $this->_updateJournal($idbb, $periodsvalue, $idfiscalperiod, $idproduct);

        if ($allok) {
            $budgetbook = $this->getBudgetBook($idbb);
            (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys047', $budgetbook->acc, $budgetbook->name, $idfiscalperiod));
        }
    }

    public function deleteJournalGroup($idbg, $idfiscalperiod) {
        if (!$this->isuserallow(self::ROLE_SALEFORECAST, self::class))
            return false;

        $budgetgroup = $this->getBudgetGroup($idbg);
        $budgetbooks = $this->getBudgetBookForGroup($idbg);

        $allok = $this->_deleteJournalGroup($idfiscalperiod, $budgetbooks);
        if ($allok !== false)
            (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys048', $budgetgroup->name, $idfiscalperiod));
    }

    public function setOpenningBalance($idbg, $idbb, $idfiscalperiod, $acc, $periodvalue) {
        $budgetgroup = $this->getBudgetGroup($idbg);
        $allok = $this->_setOpenningBalance($idbb, $idfiscalperiod, $acc, $periodvalue);

        if ($allok !== false)
            (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys049', $budgetgroup->name, $idfiscalperiod, $acc, $periodvalue));
    }

    private function _insertFiscalPeriod($updatefiscalperiod) {
        if (!$this->isuserallow(\model\action::ROLE_FINANCIALS, self::class))
            return false;

        return $this->executeSql('INSERT INTO fiscalperiod (fiscalname,seq,isclosed) VALUES (?,?,?)', (string) $updatefiscalperiod->fiscalname, (int) $updatefiscalperiod->seq, \model\utils::formatBooleanToInt($updatefiscalperiod->isclosed));
    }

    private function _updateFiscalPeriod($idfiscalperiod, $updatefiscalperiod) {
        if (!$this->isuserallow(\model\action::ROLE_FINANCIALS, self::class))
            return false;

        $this->executeSql('UPDATE fiscalperiod SET fiscalname = ?, seq = ?, isclosed = ? WHERE idfiscalperiod = ?', (string) $updatefiscalperiod->fiscalname, (int) $updatefiscalperiod->seq, \model\utils::formatBooleanToInt($updatefiscalperiod->isclosed), (int) $idfiscalperiod);
    }

    private function _deleteFiscalPeriod($idfiscalperiod) {
        if (!$this->isuserallow(\model\action::ROLE_FINANCIALS, self::class))
            return false;

        $this->executeSql('DELETE FROM fiscalperiod WHERE idfiscalperiod = ?', (int) $idfiscalperiod);
    }

    private function _insertFinancial($taxincluded, $taxpercentage) {
        if (!$this->isuserallow(\model\action::ROLE_FINANCIALS, self::class))
            return false;

        $this->executeSql('INSERT INTO financial (taxincluded, taxpercentage) VALUES (?, ?)', (int) $taxincluded, (float) $taxpercentage);
    }

    private function _updateFinancial($taxincluded, $taxpercentage) {
        if (!$this->isuserallow(\model\action::ROLE_FINANCIALS, self::class))
            return false;

        $this->executeSql('UPDATE financial SET taxincluded = ?, taxpercentage = ? WHERE idfinancial = ?', (int) $taxincluded, (float) $taxpercentage, 1);
    }

    private function _updateJournal($idbb, $periodsvalue, $idfiscalperiod, $idproduct, $idperiod = 0) {
        $this->startTransaction();
        if (!is_array($periodsvalue)) {
            $this->_updateJournalRecord($idbb, $periodsvalue, $idfiscalperiod, $idproduct, $idperiod);
        }

        if (is_array($periodsvalue)) {
            foreach ($periodsvalue as $key => $value) {
                $this->_updateJournalRecord($idbb, $value, $idfiscalperiod, $idproduct, $key);
            }
        }
        $this->endTransaction();
    }

    private function _updateJournalRecord($idbb, $value, $idfiscalperiod, $idproduct, $idperiod) {
        $result = $this->getRecord('SELECT idbj,periodvalue FROM budgetjournal WHERE idbb = ? AND idperiod = ? AND idfiscalperiod = ? AND idproduct = ?', (int) $idbb, (int) $idperiod, (int) $idfiscalperiod, (int) $idproduct);

        if (isset($result) && $value === 0.0)
            $this->executeSql('DELETE FROM budgetjournal WHERE idbj = ?', (int) $result->idbj);

        if (isset($result) && $value !== 0.0 && $value !== $result->periodvalue)
            $this->executeSql('UPDATE budgetjournal SET periodvalue = ? WHERE idbj = ?', (float) $value, (int) $result->idbj);

        if (!isset($result) && $value !== 0.0)
            $this->executeSql('INSERT INTO budgetjournal (idfiscalperiod,idperiod,idbb,periodvalue,idproduct) VALUES (?, ?, ?, ?,?)', (int) $idfiscalperiod, (int) $idperiod, (int) $idbb, (float) $value, (int) $idproduct);
    }

    private function _deleteJournalGroup($idfiscalperiod, $budgetbooks) {
        foreach ($budgetbooks as $budgetbook) {
            $this->executeSql('DELETE FROM budgetjournal WHERE idbb = ? AND idfiscalperiod = ?', (int) $budgetbook->idbb, (int) $idfiscalperiod);
        }
    }

    private function _setOpenningBalance($idbb, $idfiscalperiod, $acc, $periodvalue) {
        if (!$this->isuserallow(self::ROLE_SALEFORECAST, self::class))
            return false;

        $result = $this->getRecord('SELECT idbj FROM budgetjournal WHERE idbb = ? AND idperiod = ? AND idfiscalperiod = ?', (int) $idbb, 0, (int) $idfiscalperiod);

        if (isset($result))
            $this->executeSql('UPDATE budgetjournal SET periodvalue = ? WHERE idbb = ? AND idperiod = ? AND idfiscalperiod = ?', (float) $periodvalue, (int) $idbb, 0, (int) $idfiscalperiod);

        if (!isset($result))
            $this->executeSql('INSERT INTO budgetjournal (idfiscalperiod, idperiod, idbb, periodvalue) VALUES (?, ?, ?, ?)', (int) $idfiscalperiod, 0, (int) $idbb, (float) $periodvalue);
    }

}
