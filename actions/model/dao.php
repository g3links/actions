<?php

namespace model;

use \PDO;
use \PDOStatement;

abstract class dao {

    private $db = null;
//    private $isRunningTransaction = false;
    private $packagename;
    private $db_section = 'db';
    private $idproject = 0;

    public function __construct($packagename, $idproject = 0) {

        $this->packagename = $packagename;
        $this->idproject = $idproject;
    }

    public function __destruct() {
        $this->db = null;
    }

    protected function getRecord() {
        //expected 
        // args[0] = sql statement
        // args[1]...[n] values

        $args = func_get_args();
        $results = $this->_getRecords($args);
        return isset($results) ? (count($results) > 0 ? $results[0] : null) : null;
    }

    protected function getRecords() {
        //expected 
        // args[0] = sql statement
        // args[1]...[n] values

        $args = func_get_args();
        return $this->_getRecords($args) ?? [];
    }

    private function _getRecords($args) {
        if (!isset($args[0]))
            return null;

        // first arg must be the sql statement
        $sql = $args[0];
        unset($args[0]);

        // set args to array
        $params = array_values($args);
        // if already array was passed
        if (isset($args[1]))
            if (is_array($args[1]))
                $params = array_values($args[1]);

        return $this->_executeQuery($sql, $params);
    }

    protected function startTransaction() {
        $this->_getDb()->beginTransaction();
    }

    protected function endTransaction() {
        try {
            $this->_getDb()->commit();
        } catch (Exception $ex) {
            $this->_throwDbError('transaction: ' . $ex->getMessage());
        }
    }

    protected function executeSql() {
        //expected 
        // args[0] = sql statement
        // args[1]...[n] values
        $args = func_get_args();

        if (!isset($args[0]))
            return null;

        // first arg must be the sql statement
        $sql = $args[0];
        unset($args[0]);

        // set args to array
        $params = array_values($args);
        // if already array was passed
        if (isset($args[1]))
            if (is_array($args[1]))
                $params = array_values($args[1]);


        $result = $this->_prepareSql($sql, $params);

        \model\syslog::save($this->idproject, \model\env::getIdUser(), $sql, $params);

        return $result;
    }

    private function _getDb() {
        if (isset($this->db))
            return $this->db;

        $config = \model\env::getConfig($this->db_section, $this->packagename);

        $datapath = DATA_PATH . '/';
        
        $dbpath = $config->provider . ':' . $datapath . $config->dsn;

        // inject idproject 
        if (strpos($config->dsn, '{0}') !== false) {
            //if no project get user database
            if ($this->idproject > 0) {
                $dsn = \str_replace('{0}', $this->idproject, $config->dsn);
            } else {
                $dsn = \str_replace('{0}', \model\env::getUserIdProject(), $config->dsn);
            }

            $localpath = $datapath . $dsn;
            if (!\is_file($localpath)) {
                //get master database 
                $localpathsource = $datapath . \str_replace('{0}', '', $config->dsn);
                // intention to create database
                \copy($localpathsource, $localpath);
            }

            $dbpath = $config->provider . ':' . $datapath . $dsn;
        }

        if (strpos($dbpath, '{0}') !== false) {
            $this->_throwDbError('incorrect or missing database path: ' . $config->dsn);
        } else {
            try {
                $this->db = new PDO($dbpath, $config->username, $config->password);
                return $this->db;
            } catch (Exception $ex) {
                $this->_throwDbSqlError((string) $this->idproject, $dbpath, '', $ex->getMessage());
                die();
            }
        }
    }

    private function _throwDbError($errorInfo) {
        if (isset($this->db)) {
            if ($this->db->inTransaction())
                $this->db->rollback();

            $this->db = null;
        }

        \model\message::severe('database', $errorInfo);
    }

    private function _throwDbSqlError($idproject, $sql, $params, $errorInfo) {
        if (isset($this->db)) {
            if ($this->db->inTransaction())
                $this->db->rollback();

            $this->db = null;
        }

        $errorDB = $errorInfo;
        if (is_array($errorInfo))
            $errorDB = 'DB error [' . $errorInfo[0] . ', ' . $errorInfo[1] . ']: ' . $errorInfo[2];

        \model\message::system($idproject, $sql, $params, $errorDB);
    }

    private function _executeQuery($sql, $params) {
        try {
            $qrystatement = $this->_getDb()->prepare($sql);
            if ($qrystatement !== false) {
                if ($qrystatement->execute($params) === false) {
                    $this->_throwDbSqlError((string) $this->idproject, $sql, $params, $this->_getDb()->errorInfo());
                } else {
                    return $this->_fetchAll($qrystatement);
                }
            } else {
                $this->_throwDbSqlError((string) $this->idproject, $sql, $params, 'g3 failed to prepare sql statemenet');
            }
        } catch (Exception $ex) {
            $this->_throwDbSqlError((string) $this->idproject, $sql, $params, $ex->getMessage());
        }

        return null;
    }

    private function _fetchAll(PDOStatement $statement) {
        $manualFetch = false;
        $booleanColumns = [];
        $integerColumns = [];
        $datetimeColumns = [];
        $numericColumns = [];

        try {
            $columnCount = $statement->columnCount();
            for ($i = 0; $i < $columnCount; $i++) {
                $meta = $statement->getColumnMeta($i);
                if ($meta === false)
                    continue;

                $nativetype = strtolower($meta['sqlite:decl_type'] ?? ($meta['native_type'] ?? ''));
                switch ($nativetype) {
                    //                    case 'text' :
                    //                       break;
                    case 'integer' :
                        $manualFetch = true;
                        $integerColumns[] = $meta['name'];
                        break;
                    case 'bigint' :
                        $manualFetch = true;
                        $booleanColumns[] = $meta['name'];
                        break;
                    case 'datetime' :
                        $manualFetch = true;
                        $datetimeColumns[] = $meta['name'];
                        break;
                    case 'numeric' :
                        $manualFetch = true;
                        $numericColumns[] = $meta['name'];
                        break;
                }
            }
        } catch (PDOException $e) {
            error_log('g3 *** fetch: ' . $e->getMessage());
            \model\env::sendErroremail('g3 fetch', $e->getMessage());
        } catch (\Exception $exec) {
            error_log('g3 *** fetch: ' . $exec->getMessage());
            \model\env::sendErroremail('g3 fetch', $exec->getMessage());
        }

        $rows = $statement->fetchAll(PDO::FETCH_OBJ);

        if ($manualFetch) {
            foreach ($rows as $row) {
                foreach ($booleanColumns as $column) {
                    $row->{$column} = $row->{$column} == 1;
                }
                foreach ($integerColumns as $column) {
                    $row->{$column} = (int) $row->{$column};
                }
                foreach ($datetimeColumns as $column) {
                    if (!empty($row->{$column})) {
                        $row->{$column} = \model\utils::offsetDateTime($row->{$column}, \model\env::getTimezone());
                    }
                }
                foreach ($numericColumns as $column) {
                    $row->{$column} = (float) $row->{$column};
                }
            }
        }

        return $rows;
    }

    private function _prepareSql($sql, $params) {
        try {
            $qrystatement = $this->_getDb()->prepare($sql);
            if ($qrystatement !== false) {
                if ($qrystatement->execute($params) === false) {
                    $this->_throwDbSqlError((string) $this->idproject, $sql, $params, $this->_getDb()->errorInfo());
                } else {
                    return $this->_getDb()->lastInsertId();
                }
            } else {
                $this->_throwDbSqlError((string) $this->idproject, $sql, $params, ' g3 failed to prepare sql statement');
            }
        } catch (Exception $ex) {
            $this->_throwDbSqlError((string) $this->idproject, $sql, $params, $ex->getMessage());
        }

        return null;
    }

}
