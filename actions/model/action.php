<?php

namespace model;

class action extends \model\dbconnect {

    const NOTE_PROJECT = 'project';
    const NOTE_REPLY = 'reply';

    public function __construct($src) {
        $this->src = $src;

        parent::__construct(\model\env::CONFIG_ACTIONS);
    }

    const ROLE_ACTIONUSER = '020';
    const ROLE_ACTIONINSERT = '025';
    const ROLE_ACTIONUPDATE = '030';
    const ROLE_ACTIONGATE = '040';
    const ROLE_ACTIONSUBTASK = '045';
    const ROLE_ACTIONFILES = '050';
    const ROLE_ACTIONCOMMENT = '055';
    const ROLE_ACTIONHOLD = '060';
//    const ROLE_PAYMETHOD = '080';
    const ROLE_FINANCIALS = '085';
    const ROLE_PERIODS = '095';
    const ROLE_ACTIONCATEGORY = '105';
    const ROLE_ACTIONTRACKING = '115';
    const ROLE_FISCALPERIOD = '125';

    public function getActionSession() {
        $result = new \stdClass();
        $result->needtodrawmap = filter_input(INPUT_COOKIE, 'g3actionsmap' . $this->src->idproject) !== null ? 'true' : 'false';
        $result->Gates = $this->getGates();

        return $result;
    }

//    private $todaydate;
//    private $overduepriority;
    //******** GATE ******************
    private $gatecache;

    public function getGate($idgate) {
        if ($idgate > 0) {
            if (!isset($this->gatecache))
                $this->getGates();

            return \model\utils::firstOrDefault($this->gatecache, '$v->idgate === ' . $idgate);
        }
        return null;
    }

    public function getGates($all = false) {
        if (!isset($this->gatecache)) {
            $this->gatecache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_GATE, true);
            if (!$all)
                $this->gatecache = \model\utils::filter($this->gatecache, '$v->deleted === 0');
        }
        return $this->gatecache;
    }

    public function getDefaultGate() {
        if (!isset($this->gatecache))
            $this->gatecache = $this->getGates();

        if (count($this->gatecache) > 0)
            return $this->gatecache[0]->idgate;

        return 0;
    }

    //******** PRIORITY ******************
    private $prioritycache;

    public function getPriority($idpriority) {
        if ($idpriority > 0) {
            if (!isset($this->prioritycache))
                $this->getPriorities();

            return \model\utils::firstOrDefault($this->prioritycache, '$v->idpriority === ' . $idpriority);
        }

        return null;
    }

    public function getPriorities($includedetele = false) {
        if (!isset($this->prioritycache)) {
            $this->prioritycache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_PRIORITY, true);
            if (!$includedetele)
                $this->prioritycache = \model\utils::filter($this->prioritycache, '$v->deleted === 0');
        }

        return $this->prioritycache;
    }

    public function getDefaultPriority() {
        if (!isset($this->prioritycache))
            $this->prioritycache = $this->getPriorities();

        $priority = \model\utils::firstOrDefault($this->prioritycache, '$v->default === 1');
        if (isset($priority))
            return $priority->idpriority;

        if (count($this->prioritycache) > 0)
            return $this->prioritycache[0]->idpriority;

        return 0;
    }

    private function _filterActionsByAccessLevel($actions) {
        $idaccess = (new \model\project)->getUserAccessId($this->src->idproject, \model\env::getIdUser());
        //filter for external users only
        if ($idaccess !== 1)
            $actions = \model\utils::filter($actions, '$v->iduser === ' . \model\env::getIdUser());

        return $actions;
    }

    public function getListActionsMap() {
        // find filters
        $assignedusernames = $this->getFilterSession($this->src->idproject);
        $lastviewgate = $this->getDefaultGate();

        if ($this->src->idproject === 0) {
            // all projects
            $actions = $this->_getAllActiveActions(0, $lastviewgate, $assignedusernames);
        } else {
            if ($this->src->idproject === \model\env::getUserIdProject()) {
                // get user task from all projects
                $justme = [];
                $user = new \stdClass;
                $user->iduser = \model\env::getIdUser();
                $user->idgroup = 0;
                $user->name = '';
                $justme[] = $user;

                $actions = $this->_getAllActiveActions(\model\env::getUserIdProject(), $lastviewgate, $justme);
//                if (count($assignedusernames) !== 0)
//                    $actions = $this->_filterusersactions($actions, $assignedusernames);
            } else {
                // selected project
                $actions = $this->getListTaskByStatus($lastviewgate, $assignedusernames);
            }
        }
        return $actions;
    }

    public function getListActions($lastviewgate, $idtrack = 0, $sortname = '', $prev_sortdirection = '', $navpage = 0) {
        $result = new \stdClass();
        // find filters
        $assignedusernames = $this->getFilterSession($this->src->idproject);

        $result->hasprojname = $this->src->idproject === 0;
        // my list can have multiple projects
        if ($this->src->idproject === \model\env::getUserIdProject())
            $result->hasprojname = true;

        if (!isset($lastviewgate))
            $lastviewgate = $this->getDefaultGate();

        if ($this->src->idproject === 0) {
            // all projects
            $todos = $this->_getAllActiveActions(0, $lastviewgate, $assignedusernames);
        } else {
            if ($this->src->idproject === \model\env::getUserIdProject()) {
                // get user task from all projects
                $justme = [];
                $user = new \stdClass;
                $user->iduser = \model\env::getIdUser();
                $user->idgroup = 0;
                $user->name = '';
                $justme[] = $user;

                $todos = $this->_getAllActiveActions(\model\env::getUserIdProject(), $lastviewgate, $justme);
                $todos = $this->_filterusersactions($todos, $assignedusernames);
            } else {
                // selected project
                $todos = $this->getListTaskByStatus($lastviewgate, $assignedusernames);
            }
        }

        if ($navpage === 0) {
            // tracking
            if ($this->getFilterview(3)->on ?? true) {
                $result->tracks = $this->getTracks();
                if (count($result->tracks) === 0)
                    unset($result->tracks);
            }
        }

        // filter by track action    
        if ($idtrack > 0) {
            $todos = \model\utils::filter($todos, '$v->idtrack === ' . $idtrack);
        }

        //sort and filter
        $result->total_records = count($todos);
        $result->max_records = \model\env::getMaxRecords('actions');

        //summary tags only in first page
        if ($navpage === 0) {
            $result->actiontags = [];
            $result->actionusers = [];
            foreach ($todos as $action) {
                if (isset($action->tags)) {
                    foreach ($action->tags as $tag) {
                        $result->actiontags[] = $tag->tagname;
//            $hastgas = true;
                    }
                }
                if (isset($action->username)) {
                    $result->actionusers[] = $action->username;
                }
            }

            $result->actiontags = array_unique($result->actiontags);
            sort($result->actiontags);
            $result->actionusers = array_unique($result->actionusers);
            sort($result->actionusers);

            if (count($result->actiontags) === 0)
                unset($result->actiontags);
            if (count($result->actionusers) === 0)
                unset($result->actionusers);

            // apply filters

            if ($this->getFilterview(1)->on ?? true) {
                $result->todoshold = \model\utils::filter($todos, '$v->onhold === true');
                if (count($result->todoshold) === 0)
                    unset($result->todoshold);
            }
        }

// sort
        $sortdirection = \model\utils::getSortDirection($sortname, $prev_sortdirection);

        $result->s_priority = '';
        $result->s_description = '';
        $result->s_date = '';
        $result->s_projname = '';

        $result->_sort = $sortdirection ? 'desc' : '';
        $_imgsort = $sortdirection ? 'sortDsc' : 'sortAsc';

        $sortfields = ['idpriority' => $result->_sort, 'dueon' => '', 'projname' => ''];
        if ($sortname === '') {
            $result->s_priority = $_imgsort;
        }
        if ($sortname === 'DATE') {
            $sortfields = ['dueon' => $result->_sort, 'idpriority' => '', 'projname' => ''];
            $result->s_date = $_imgsort;
        }
        if ($sortname === 'TITLE') {
            $sortfields = ['title' => $result->_sort, 'idpriority' => '', 'dueon' => ''];
            $result->s_description = $_imgsort;
        }
        if ($sortname === 'PROJECT') {
            $sortfields = ['projname' => $result->_sort, 'idpriority' => '', 'dueon' => ''];
            $result->s_projname = $_imgsort;
        }

        $todos = \model\utils::sorttakeList($todos, $sortfields, $navpage, $result->max_records);
        $result->actions = $this->_nestedactions($todos);

        foreach ($result->actions as $action) {
            $action->trackname = $this->getTrack($action->idtrack)->name ?? '';
            $action->categoryname = $this->getCategory($action->idcategory)->name ?? '';
        }

        return $result;
    }

    private function _nestedactions($actions) {
        $str_length = 3;
        $index = 0;
        foreach ($actions as $todo) {
            $index++;
            $seq = substr("0000{$index}", -$str_length);
            $todo->sortseq = $seq;
            $todo->indent = 0;
        }

        $attachedtasks = \model\utils::filter($actions, '$v->idparent === 0');
        $this->_nestactions($attachedtasks, $actions);

        // list by new sequence
        $sortfields = ['sortseq' => ''];
        return \model\utils::sorttakeList($actions, $sortfields, 0, 0);
    }

    private function _nestactions($listtasks, $todos, $sortseq = '', $indent = 0) {
        $str_length = 3;
        $index = 0;
        foreach ($listtasks as $todo) {
            if ($todo->idparent > 0) {
                $index++;
                $seq = \substr("0000{$index}", -$str_length);
                $todo->sortseq = $sortseq . '.' . $seq;
                // set max indent 
                $todo->indent = $indent > 7 ? 7 : $indent;
            }
            $todo->selected = 1;

            //find if any attached tasks
            $attachedtasks = \model\utils::filter($todos, '$v->idparent === ' . $todo->idtask);
            if (count($attachedtasks) > 0) {
                $internalindent = $indent + 1;
                $this->_nestactions($attachedtasks, $todos, $todo->sortseq, $internalindent);
            }
        }
        return $sortseq;
    }

    public function getSummaryActions($idgate = 0) {
        if ($idgate === 0)
            $idgate = $this->getDefaultGate();

        $taskresults = $this->getRecords('SELECT task.idtask,taskowner.iduser FROM task LEFT JOIN taskowner USING ( idtask ) WHERE task.idgate = ? AND task.module = ?', (int) $idgate, '');

        $taskresults = $this->_filterActionsByAccessLevel($taskresults);
        foreach ($taskresults as $taskresult) {
            $taskresult->onhold = $this->getRecord('SELECT count(*) AS result FROM taskhold WHERE idtask = ?', (int) $taskresult->idtask)->result > 0;

            $taskresult->taskusernames = $this->_getTaskAssignedUsers($taskresult->idtask);
            $taskresult->taskgroupnames = $this->_getTaskAssignedGroups($taskresult->idtask);
        }

        return $taskresults;
    }

    public function getStartupActions() {
        $result = new \stdClass();
        $projects = (new \model\project)->getprojects();

        $actions = [];

        //get projects actions
        foreach ($projects as $actionproject) {
            $modeltask = new \model\action(\model\env::src($actionproject->idproject));
            $defaulttrack = $modeltask->getDefaultTrack();

            $results = $modeltask->getListTaskByStatus();

            $onholds = [];
            if ($modeltask->getFilterview(1)->on ?? true) {
                $onholds = \model\utils::filter($results, '$v->onhold === true');
            }

            // do not include open actions for single user projects
            if (($modeltask->getFilterview(2)->on ?? true) && (new \model\project)->hasMultipleUsers($actionproject->idproject) && $modeltask->hasTrack()) {
                $unassigned = \model\utils::filter($results, \model\utils::format('($v->idtrack === 0 | $v->idtrack = {0}) & $v->onhold === false', $defaulttrack));

                $onholds = \YaLinqo\Enumerable::from($onholds)
                                ->union($unassigned, '$v->idproject . $v->idtask')->toList();
            }
            foreach ($onholds as $result) {
                $result->lbl_categoryname = $modeltask->getCategory($result->idcategory)->name ?? '';
            }

            if (count($onholds) > 0)
                $actions = \YaLinqo\Enumerable::from($actions)
                                ->union($onholds, '$v->idproject . $v->idtask')->toList();
        }

        $result->openactions = \model\utils::filter($actions, '$v->onhold === false');
        $result->holdactions = \model\utils::filter($actions, '$v->onhold === true');
        return $result;
    }

    public function getSummaryTags($idgate = 0) {
        if ($idgate === 0)
            $idgate = $this->getDefaultGate();

        $taskresults = $this->getRecords('SELECT task.idtask,taskowner.iduser FROM task LEFT JOIN taskowner USING ( idtask ) WHERE task.idgate = ? AND task.module = ?', (int) $idgate, '');

        $taskresults = $this->_filterActionsByAccessLevel($taskresults);
        foreach ($taskresults as $taskresult) {
            $taskresult->taskusernames = $this->_getTaskAssignedUsers($taskresult->idtask);
            $taskresult->taskgroupnames = $this->_getTaskAssignedGroups($taskresult->idtask);

            $taskresult->tags = $this->getRecords('SELECT tagname,idtag FROM tasktag WHERE idtask = ?', (int) $taskresult->idtask);
        }

        return $taskresults;
    }

    public function getActionsToAttach($idtask) {
        $idgate = $this->getDefaultGate();

        //get project title
        $results = $this->getRecords('SELECT task.idtask,task.description,task.title,task.idparent FROM task WHERE task.idgate = ? AND task.module = ?', (int) $idgate, '');

        //block inmediate parent & this task
        $thisaction = \model\utils::firstOrDefault($results, '$v->idtask === ' . $idtask);
        if (isset($thisaction)) {
            if ($thisaction->idparent > 0) {
                $parentaction = \model\utils::firstOrDefault($results, '$v->idtask === ' . $thisaction->idparent);
                if (isset($parentaction))
                    $parentaction->selected = -1;
            }
            //block this action
            $thisaction->selected = -1;
        }

        //Ok attached actions
        $attachedactions = \model\utils::filter($results, '$v->idparent === ' . $idtask);
        foreach ($attachedactions as $attachedaction)
            $attachedaction->selected = 1;

        $rootactions = \model\utils::filter($results, '$v->idparent === 0');
        foreach ($rootactions as $action) {
            if (!isset($action->selected))
                $action->selected = 0;
        }

        // remove all others
        $index = 0;
        foreach ($results as $action) {
            if (isset($action->selected) && $action->selected === -1)
                unset($results[$index]);

            $index++;
        }

        return $results;
    }

    public function getListTaskByStatus($idgate = 0, $assignedusernames = []) {
        if ($idgate === 0)
            $idgate = $this->getDefaultGate();

        //get project title
        $projname = (new \model\project)->getproject($this->src->idproject)->title ?? '';

        $taskresults = $this->getRecords('SELECT task.idtask,task.idpriority,task.createdon,task.description,task.title,task.idparent,task.hasattach,task.progress,task.idcategory,task.idtrack,taskowner.iduser,taskowner.username FROM task LEFT JOIN taskowner USING ( idtask ) WHERE task.idgate = ? AND task.module = ?', (int) $idgate, '');
        $taskresults = $this->_filterActionsByAccessLevel($taskresults);

        foreach ($taskresults as $taskresult) {
            $taskresult->idproject = $this->src->idproject;
            $taskresult->projname = $projname;
            $this->_getTaskDetails($taskresult);
        }

        return $this->_filterusersactions($taskresults, $assignedusernames);
    }

    public function searchActionsFor($search, $type, $idgate = 0) {
        // gate = 0 => search all gates
        if ($type === 'txt')
            $search = \model\utils::getSearchText($search);

        $space = ' ';
        $qry_sql1 = 'SELECT task.idtask,task.description,task.title,task.createdon,task.idgate,task.idpriority,taskowner.iduser,count(tasktag.idtask) AS hastags FROM task LEFT JOIN taskowner USING ( idtask ) LEFT JOIN tasktag USING ( idtask )';
        $qry_sql2 = 'LEFT JOIN comment USING ( idtask ) LEFT JOIN taskuser USING ( idtask ) LEFT JOIN taskgroup USING ( idtask ) ';
        $qry_sql3 = 'WHERE (task.description LIKE ? OR task.title LIKE ? OR comment.description LIKE ? OR comment.username LIKE ? OR tasktag.tagname LIKE ? OR taskuser.taskusername LIKE ? OR taskuser.taskgroupname LIKE ?) AND task.module = ?';
        $qry_sql4 = 'GROUP BY task.idtask';
        $params = [(string) $search, (string) $search, (string) $search, (string) $search, (string) $search, (string) $search, (string) $search, ''];

        switch ($type) {
            case 'tag' :
                $qry_sql2 = '';
                $qry_sql3 = 'WHERE tasktag.tagname = ?';
                $params = [(string) $search];
                break;
            // do not serch for taskuser, it has a separate filter option
            case 'usr' :
                $qry_sql2 = '';
                $qry_sql3 = 'WHERE taskowner.username = ?';
                $params = [(string) $search];
                break;
        }

        $qry_sql = $qry_sql1 . $space . $qry_sql2 . $space . $qry_sql3 . $space . $qry_sql4;
        $taskresults = $this->getRecords($qry_sql, $params);

        // filter by gate specific
        if ($idgate > 0)
            $taskresults = \model\utils::filter($taskresults, '$v->idgate === ' . $idgate);

        return $this->_filterActionsByAccessLevel($taskresults);
    }

    private function _getTaskAssignedUsers($idtask) {
        $taskusers = $this->getRecords('SELECT idtaskuser,idtask,iduser,taskusername,createdon FROM taskuser WHERE idtask = ?', (int) $idtask);
        foreach ($taskusers as $taskuser) {
            $taskuser->name = $taskuser->taskusername;
            $taskuser->title = $this->_getInitialsName($taskuser->taskusername);
        }

        return $taskusers;
    }

    private function _getTaskAssignedGroups($idtask) {
        $taskgroups = $this->getRecords('SELECT idtaskgroup,idtask,idgroup,taskgroupname,createdon FROM taskgroup WHERE idtask = ?', (int) $idtask);
        foreach ($taskgroups as $taskgroup) {
            $taskgroup->name = $taskgroup->taskgroupname;
            $taskgroup->title = $this->_getInitialsName($taskgroup->taskgroupname);

            // get users inside the group
            $taskgroup->users = $this->getRecords('SELECT projgroupuser.iduser FROM projgroupuser JOIN projgroup USING ( idgroup ) WHERE projgroup.idgroup = ?', $taskgroup->idgroup);
            $modeluser = new \model\user();
            foreach ($taskgroup->users as $projgroupuser) {
                $user = $modeluser->getuser($projgroupuser->iduser);
                if (isset($user)) {
                    $projgroupuser->name = $user->name;
//                $projgroupuser->email = $user->email;
                }
            }
        }

        return $taskgroups;
    }

    private function _isuserAssignedToAction($idtask) {
        $result = $this->getRecord('SELECT count(*) AS result FROM taskuser WHERE idtask = ? AND iduser = ?', (int) $idtask, \model\env::getIdUser());
        return ($result->result ?? 0) > 0;
    }

    private function _UnAssignUser($username, $idtask, $iduser) {
        if (!$this->isuserallow(self::ROLE_ACTIONUSER, self::class))
            return false;

        $allok = $this->executeSql('DELETE FROM taskuser WHERE idtask = ? AND iduser = ?', (int) $idtask, (int) $iduser);
        if ($allok === false)
            return;

        $user = (new \model\user)->getuser($iduser);

        $note_text = \model\lexi::get('', 'sys010', $user->name);
        $this->_insertcomment($idtask, $username, \model\env::COMMENT_USER, $note_text);
    }

    private function _UnAssignGroup($username, $idtask, $idgroup) {
        if (!$this->isuserallow(self::ROLE_ACTIONUSER, self::class))
            return false;

        $allok = $this->executeSql('DELETE FROM taskgroup WHERE idtask = ? AND idgroup = ?', (int) $idtask, (int) $idgroup);
        if ($allok === false)
            return;

        $group = $this->_getprojectgroup($idgroup);

        $note_text = \model\lexi::get('', 'sys011', $group->groupname);
        $this->_insertcomment($idtask, $username, \model\env::COMMENT_USER, $note_text);
    }

    private function _setAssignUser($username, $idtask, $iduser) {
        if (!$this->isuserallow(self::ROLE_ACTIONUSER, self::class))
            return false;

        $action = $this->getTaskById($idtask);

        if ($action->idgate !== $this->getDefaultGate())
            return;

        $userassigned = (new \model\user)->getuser($iduser);

        $lastinserted = $this->executeSql('INSERT INTO taskuser (idtask,iduser,taskusername) VALUES (?, ?, ?)', $idtask, $iduser, $userassigned->name);

        if ($lastinserted === false)
            return;

        $email = $userassigned->email;
        $taskname = $action->title;

        $projectname = (new \model\project)->getproject($this->src->idproject)->title ?? '';

//register message
        $note_text = \model\lexi::get('', 'sys015', $userassigned->name);
        $this->addSystemNote($note_text, (string) $idtask);
        $this->_insertcomment($idtask, $username, \model\env::COMMENT_USER, $note_text);

        $data_header = new \stdClass();
        $data_header->usernameassigned = $userassigned->name;
        $data_header->email = $email;
        $data_header->projectname = $projectname;
        $data_header->taskname = $taskname;

        return $data_header;
    }

    private function _AssignGroup($username, $idtask, $idgroup) {
        if (!$this->isuserallow(self::ROLE_ACTIONUSER, self::class))
            return false;

        $action = $this->getTaskById($idtask);

        if ($action->idgate !== $this->getDefaultGate())
            return;

        $projgroup = $this->getRecord('SELECT idgroup,groupname,createdon,deleted FROM projgroup WHERE idgroup = ? ORDER BY groupname', (int) $idgroup);
        if (!isset($projgroup))
            return false;

        $lastinderted = $this->executeSql('INSERT INTO taskgroup (idgroup,idtask,taskgroupname) VALUES (?, ?, ?)', $idgroup, $idtask, $projgroup->groupname);
        if ($lastinderted === false)
            return false;

        $note_text = \model\lexi::get('', 'sys015', $projgroup->groupname);
        $this->addSystemNote($note_text, (string) $idtask);
        $this->_insertcomment($idtask, $username, \model\env::COMMENT_USER, $note_text);
    }

    public function inserttask($newtask, $args, $emailadvicefilename) {
        if (!$this->isuserallow(self::ROLE_ACTIONINSERT, self::class))
            return false;

        $idtaskinserted = $this->_newtask(\model\env::getUserName(), $newtask->title, $newtask->idpriority, '', 0, $newtask->idtaskparent, $newtask->description, $newtask->progress, $newtask->idcategory);

        $this->_insertcomment($idtaskinserted, \model\env::getUserName(), \model\env::COMMENT_USER, $newtask->commenttext);

        $this->updatedassignedusers($idtaskinserted, $args, $emailadvicefilename);

        $this->uploadfile($idtaskinserted);

        return $idtaskinserted;
    }

    public function getNewAction() {
        $result = new \stdClass();
        $result->action = new \stdClass();
        $result->action->isoverdue = false;
        $result->action->taskdues = [];
        $result->action->title = '';
        $result->action->idpriority = $this->getDefaultPriority();
        $result->action->description = '';
        $result->action->idparent = 0;
        $result->action->progress = 0;
        foreach ($result->action->taskdues as $taskdue) {
            $taskdue->lbl_starton = \model\utils::getDueDateFormatted($taskdue->starton, \model\env::getTimezone());
            $taskdue->lbl_dueon = \model\utils::getDueDateFormatted($taskdue->dueon, \model\env::getTimezone());
        }

        $result->isroleusers = $this->isuserallow(self::ROLE_ACTIONUSER, self::class);
        $result->isrole = $this->isuserallow(self::ROLE_ACTIONUPDATE, self::class);
        $result->categories = $this->getCategories();
        $result->allPriorities = $this->getPriorities();
        $result->hours = \model\utils::getHours();
        $result->mins = \model\utils::getMinutes();

        return $result;
    }

    private function _newtask($username, $title, $idpriority, $module = '', $idmodule = 0, $idtaskparent = 0, $description = '', $progress = 0, $idcategory = 0) {
        $idgate = $this->getDefaultGate();

        $this->startTransaction();
        $lastIdInserted = $this->executeSql('INSERT INTO task (idpriority, title, description, idgate, idparent, idcategory, idtrack, progress, module, idmodule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $idpriority, trim((string) $title), (string) $description, (int) $idgate, (int) $idtaskparent, (int) $idcategory, 0, (int) $progress, (string) $module, (int) $idmodule);
        if ($lastIdInserted > 0)
            $this->executeSql('INSERT INTO taskowner (idtask, iduser, username) VALUES (?, ?, ?)', (int) $lastIdInserted, \model\env::getIdUser(), (string) $username);

        $this->endTransaction();

        if ($lastIdInserted > 0) {
            //insert commend
            $val = \model\lexi::get('', 'sys006');
            $this->_insertcomment($lastIdInserted, $username, \model\env::COMMENT_SYS, $val);

            $note_text = $val . ': ' . $title;
            $this->addSystemNote($note_text, (string) $lastIdInserted);
        }

        return $lastIdInserted;
    }

    public function updatetask($idtask, $naction, $args) {
        if (!$this->isuserallow(self::ROLE_ACTIONUPDATE, self::class))
            return false;

        if (!isset($naction))
            return false;

//kept value to refresh master task list
        $action = $this->getTaskById($idtask);
        if (!isset($action))
            return false;

        if (count($action->taskholds) > 0)
            return false;

        // due dates, # record number
        // s- : starton 
        // sh- : starton hour
        // sm- : starton min
        // d- : dueon 
        // dh- : dueon hour
        // dm- : dueon min
        //  for new records
        // si- : starton 
        // sih- : starton hour
        // sim- : starton min
        // di- : dueon 
        // dih- : dueon hour
        // dim- : dueon min
        // 
        // products: only new or delete allow (not edit)
        // pi- : new products
        //flag old records

        foreach ($action->taskdues as $taskdue) {
            $taskdue->selected = 0;
        }
        // @TODO use args instead of filter_input
        foreach ($args as $param_name => $param_val) {

            //*****************
            // DATES
            //*****************
            // update records
            if (substr($param_name, 0, 2) === 's-') {
                $updatestartdate = '';
                $updateduedate = '';

                // update start date
                $src_idtaskdue = (int) str_replace('s-', '', $param_name);
                if (!empty($param_val)) {
                    $entered_hour = str_pad(filter_input(INPUT_POST, 'sh-' . $src_idtaskdue), 2, '0', STR_PAD_LEFT);
                    $entered_min = str_pad(filter_input(INPUT_POST, 'sm-' . $src_idtaskdue), 2, '0', STR_PAD_LEFT);
                    $updatestartdate = \model\utils::forDatabaseDateTime(\model\utils::createDateTime(\model\utils::format('{0} {1}:{2}:00', $param_val, $entered_hour, $entered_min), \model\env::getTimezone()));
                }

                // update due date
                $entereddate = filter_input(INPUT_POST, 'd-' . $src_idtaskdue);
                if (!empty($entereddate)) {
                    $entered_hour = str_pad(filter_input(INPUT_POST, 'dh-' . $src_idtaskdue), 2, '0', STR_PAD_LEFT);
                    $entered_min = str_pad(filter_input(INPUT_POST, 'dm-' . $src_idtaskdue), 2, '0', STR_PAD_LEFT);
                    $updateduedate = \model\utils::forDatabaseDateTime(\model\utils::createDateTime(\model\utils::format('{0} {1}:{2}:00', $entereddate, $entered_hour, $entered_min), \model\env::getTimezone()));
                }
                //validate status
                $taskdue = \model\utils::filter($action->taskdues, '$v->idtaskdue === ' . $src_idtaskdue);
                if (count($taskdue) > 0) {
                    if (empty($updateduedate) & empty($updatestartdate)) {
                        $taskdue[0]->selected = 2;
                    } else {
                        $taskdue[0]->selected = 1;
                        if ($taskdue[0]->dueon !== $updateduedate & !empty($updateduedate)) {
                            $taskdue[0]->dueon = $updateduedate;
                        }
                        if ($taskdue[0]->starton !== $updatestartdate & !empty($updatestartdate)) {
                            $taskdue[0]->starton = $updatestartdate;
                        }
                    }
                }
            }
            // for new records
            if (substr($param_name, 0, 3) === 'si-') {
                $newstartdate = '';
                $newduedate = '';

                $r = str_replace('si-', '', $param_name);
                $entereddate = filter_input(INPUT_POST, 'si-' . $r);
                if (!empty($entereddate)) {
                    $entered_hour = str_pad(filter_input(INPUT_POST, 'sih-' . $r), 2, '0', STR_PAD_LEFT);
                    $entered_min = str_pad(filter_input(INPUT_POST, 'sim-' . $r), 2, '0', STR_PAD_LEFT);
                    $newstartdate = \model\utils::forDatabaseDateTime(\model\utils::createDateTime(\model\utils::format('{0} {1}:{2}:00', $entereddate, $entered_hour, $entered_min), \model\env::getTimezone()));
                }
                $entereddate = filter_input(INPUT_POST, 'di-' . $r);
                if (!empty($entereddate)) {
                    $entered_hour = str_pad(filter_input(INPUT_POST, 'dih-' . $r), 2, '0', STR_PAD_LEFT);
                    $entered_min = str_pad(filter_input(INPUT_POST, 'dim-' . $r), 2, '0', STR_PAD_LEFT);
                    $newduedate = \model\utils::forDatabaseDateTime(\model\utils::createDateTime(\model\utils::format('{0} {1}:{2}:00', $entereddate, $entered_hour, $entered_min), \model\env::getTimezone()));
                }
                if (!empty($newstartdate) | !empty($newduedate)) {
                    $taskdue = new \stdClass();
                    $taskdue->dueon = $newduedate;
                    $taskdue->starton = $newstartdate;
                    $taskdue->idtaskdue = 0; // flag for new record
                    $taskdue->idtask = $idtask;
                    $taskdue->selected = 0;

                    $action->taskdues[] = $taskdue;
                }
            }
        }

        $action->title = $naction->title;
        $action->description = $naction->description;
        $action->idcategory = $naction->idcategory;
        $action->progress = $naction->progress;
        $action->idpriority = $naction->idpriority;
        $action->idtask = \model\env::session_idtaskselected();

        $this->startTransaction();
        $this->executeSql('UPDATE task SET idpriority = ?, title = ?, description = ?, progress = ?, idcategory = ? WHERE idtask = ?', (int) $action->idpriority, trim((string) $action->title), trim((string) $action->description), (int) $action->progress, (int) $action->idcategory, (int) $action->idtask);

// update due dates
        foreach ($action->taskdues as $taskdue) {
            if ($taskdue->idtaskdue === 0)
                $this->executeSql('INSERT INTO taskdue (dueon, idtask, duration, starton) VALUES (?, ?, ?, ?)', $taskdue->dueon, (int) $taskdue->idtask, 0, $taskdue->starton);

            if ($taskdue->selected === 1)
                $this->executeSql('UPDATE taskdue SET dueon = ?, starton = ? WHERE idtaskdue = ?', $taskdue->dueon, $taskdue->starton, (int) $taskdue->idtaskdue);

            if ($taskdue->selected === 2)
                $this->executeSql('DELETE FROM taskdue WHERE idtaskdue = ?', (int) $taskdue->idtaskdue);
        }
        $this->endTransaction();

        if (!empty(\trim($naction->commenttext))) {
            $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_USER, $naction->commenttext);
        }

        $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_SYS, \model\lexi::get('', 'sys005'));
    }

    public function setActionGate($idgate, $idtask, $emailadvicefilename) {
        if (!$this->isuserallow(self::ROLE_ACTIONGATE, self::class))
            return false;

        $entrygate = $this->getDefaultGate();

        // whether any outstanding linked task, do not update
        $results = $this->getRecords('SELECT idtask FROM task WHERE idparent = ? AND idgate = ?', (int) $idtask, (int) $entrygate);
        if (count($results) > 0)
            return false;

        $this->executeSql('UPDATE task SET idgate = ? WHERE idtask = ?', (int) $idgate, (int) $idtask);

        $gatename = $this->getGate($idgate)->name ?? '';
        //insert comment
        $texto = \model\lexi::get('', 'sys003') . ": " . $gatename;

        $this->_insertcomment(\model\env::session_idtaskselected(), \model\env::getUserName(), \model\env::COMMENT_USER, $texto);

        $result = $this->getRecord('SELECT title FROM task WHERE idtask = ?', (int) $idtask);
        $note_text = $texto . ', ' . $result->title;
        $this->addSystemNote($note_text, (string) $idtask);

        $filename = \model\route::render($emailadvicefilename);
        $this->_emailtaskstatus($result->title, $gatename, $filename);
    }

    private function _setUnAssignTask($username, $idtask, $idtaskselected) {
        if ($this->_setSubAction($idtask, 0) === false)
            return;

        $task = $this->getTaskById($idtask);
        $texto = \model\lexi::get('', 'sys013', $task->title);
        $this->_insertcomment($idtaskselected, $username, \model\env::COMMENT_USER, $texto);
    }

    private function _setAssignTask($username, $idtask, $idparent) {
        $action = $this->getTaskById($idtask);

        if ($action->idgate !== $this->getDefaultGate())
            return;

        if ($this->_setSubAction($idtask, $idparent) === false)
            return;

        $texto = \model\lexi::get('', 'sys012', $action->title);
        $this->_insertcomment($idparent, $username, \model\env::COMMENT_USER, $texto);
    }

    private function _setFileAttached($username, $idtask, $filename, $isupload, $hasattach) {
        if ($this->_setFlagFileAttachedTask($idtask, $hasattach) === false)
            return;

        $texto = \model\lexi::get('', 'sys008') . ": " . $filename;
        if (!$isupload)
            $texto = \model\lexi::get('', 'sys009') . ": " . $filename;

        $this->addSystemNote($texto, (string) $idtask);
        $this->_insertcomment($idtask, $username, \model\env::COMMENT_USER, $texto);
    }

    public function geCommenttListHistoryBytask($idtask) {
        return $this->getRecords('SELECT comment.createdon,comment.description,comment.idcomment,comment.lastmodifiedon,comment.deleted,comment.username FROM comment WHERE comment.idtask = ? AND  ( comment.source = ? OR comment.deleted = ? )  ORDER BY comment.createdon DESC', (int) $idtask, (string) \model\env::COMMENT_SYS, 1);
    }

    public function updatecomment($idcomment, $status) {
        if (!$this->isuserallow(self::ROLE_ACTIONCOMMENT, self::class))
            return false;

        $this->executeSql('UPDATE comment SET deleted = ? WHERE idcomment = ?', (int) $status, (int) $idcomment);
    }

    public function uploadfile($idtask) {
        if (!$this->isuserallow(self::ROLE_ACTIONFILES, self::class))
            return false;

        $filesexist = false;
        if (isset($_FILES['upfile']) && $_FILES['upfile']['error'] === 0) {
            $filename = $_FILES['upfile']['name'];
            $filetype = $_FILES['upfile']['type'];
            $filesize = $_FILES['upfile']['size'];
            $tempname = $_FILES['upfile']['tmp_name'];

            $filesexist = true;
        }

        if (!isset($filename))
            return false;

//        $idproject = $this->src->idproject;
//        $iduser = \model\env::getIdUser();
// Verify file size - 5MB maximum
        $config = \model\env::getConfig('filelimits', \model\env::CONFIG_ACTIONS, $this->src->idproject);
        $maxsize = ($config->attachedfile ?? 5) * 1024 * 1024;        
        if ($filesize > $maxsize) {
            \model\message::render(\model\lexi::get('', 'sys027'));
            return false;
        }

        $action = $this->getTaskById($idtask);

        $entrygate = $this->getDefaultGate();
        if ($action->idgate !== \trim($entrygate)) {
            \model\message::render(\model\lexi::get('', 'sys028', $entrygate));
            return false;
        }

        $folderpath = DATA_PATH . "attach/" . $this->src->idproject . "/";
        if (!file_exists($folderpath))
            mkdir($folderpath, 0777, true);

        $folderpath = DATA_PATH . "attach/" . $this->src->idproject . "/" . $idtask . "/";
        if (!file_exists($folderpath))
            mkdir($folderpath, 0777, true);

// Verify MYME type of the file
// Check whether file exists before uploading it
        $targetname = $folderpath . $filename;
        if (file_exists($targetname)) {
            \model\message::render(\model\lexi::get('', 'sys029'));
            return false;
        }

        move_uploaded_file($tempname, $targetname);
        $this->_setFileAttached(\model\env::getUserName(), $idtask, $filename, true, 1);
    }

    public function updatedtaskattach($idtaskselected, $paramnames) {
        if (!$this->isuserallow(self::ROLE_ACTIONSUBTASK, self::class))
            return false;

        $idproject = $this->src->idproject;
//        $iduser = \model\env::getIdUser();
// note: do not update already assigned
// @PENDING do not modify actions diferent to default gate
        $pendingstatus = $this->getDefaultGate();
        $results = $this->getRecords('SELECT idtask,title,idgate FROM task WHERE idparent = ?', (int) $idtaskselected);
        $attachedactions = \model\utils::filter($results, '$v->idgate === ' . $pendingstatus);

// task been assigned and need to be unassigned
        foreach ($attachedactions as $attachedaction) {
            $rcecordfound = false;
            foreach ($paramnames as $param_name => $param_val) {
                if (substr($param_name, 0, 2) === "u-") {
                    $idtask = (int) str_replace('u-', '', $param_name);
                    if ($attachedaction->idtask === $idtask)
                        $rcecordfound = true;
                }
            }

            if (!$rcecordfound)
                $this->_setUnAssignTask(\model\env::getUserName(), $attachedaction->idtask, $idtaskselected);
        }

//******************************************
// new record 
//******************************************
        foreach ($paramnames as $param_name => $param_val) {
            $rcecordfound = false;
            foreach ($attachedactions as $attachedaction) {
                if (substr($param_name, 0, 2) === "u-") {
                    $idtask = (int) str_replace('u-', '', $param_name);
                    if ($attachedaction->idtask === $idtask)
                        $rcecordfound = true;
                }
            }
            if (!$rcecordfound && substr($param_name, 0, 2) === "u-") {
                $idtask = (int) str_replace('u-', '', $param_name);
                $this->_setAssignTask(\model\env::getUserName(), $idtask, $idtaskselected);
            }
        }
    }

    public function filterProjectUserGroup($idproject, $args) {
        $selectedusers = "";
        $selectedgroups = "";

        foreach ($args ?? [] as $param_name => $param_val) {
            if (substr($param_name, 0, 2) === "u-") {
                if (!empty($selectedusers))
                    $selectedusers .= ",";

                $id = str_replace('u-', '', $param_name);
                $selectedusers .= $id . '|' . $param_val;
            }
            if (substr($param_name, 0, 2) === "g-") {
                if (!empty($selectedgroups))
                    $selectedgroups .= ",";

                $id = str_replace('g-', '', $param_name);
                $selectedgroups .= $id . '|' . $param_val;
            }
        }

        if (empty($selectedusers)) {
            \model\utils::unsetCookie('selectedusers' . $idproject);
        } else {
            \model\utils::setCookie('selectedusers' . $idproject, $selectedusers);
        }

        if (empty($selectedgroups)) {
            \model\utils::unsetCookie('selectedgroups' . $idproject);
        } else {
            \model\utils::setCookie('selectedgroups' . $idproject, $selectedgroups);
        }
    }

    public function updatedassignedusers($idtaskselected, $paramnames, $emailadvicefilename) {
        if (!$this->isuserallow(self::ROLE_ACTIONUSER, self::class))
            return false;

        $idproject = $this->src->idproject;
//        $iduser = \model\env::getIdUser();

        $semdemailto = [];

//assign and send email to new groups and users
// note: do not send or update already assigned
        $assignedusers = $this->_getTaskAssignedUsers($idtaskselected);
        $assignedgroups = $this->_getTaskAssignedGroups($idtaskselected);

//******************************************
//user  not longer assigned, remove record
//******************************************
        foreach ($assignedusers as $assigneduser) {
            $rcecordfound = false;
            foreach ($paramnames as $param_name => $param_val) {
                if (substr($param_name, 0, 2) === "u-") {
                    $iduserf = (int) str_replace('u-', '', $param_name);
                    if ($assigneduser->iduser === $iduserf)
                        $rcecordfound = true;
                }
            }

            if (!$rcecordfound)
                if ($assigneduser->iduser > 0)
                    $this->_UnAssignUser(\model\env::getUserName(), $idtaskselected, $assigneduser->iduser);
        }

//******************************************
// group not longer assigned, remove record
//******************************************
        foreach ($assignedgroups as $assignedgroup) {
            $rcecordfound = false;
            foreach ($paramnames as $param_name => $param_val) {
                if (substr($param_name, 0, 2) === "g-") {
                    $idgroup = (int) str_replace('g-', '', $param_name);
                    if ($assignedgroup->idgroup === $idgroup)
                        $rcecordfound = true;
                }
            }

            if (!$rcecordfound)
                if ($assignedgroup->idgroup > 0)
                    $this->_UnAssignGroup(\model\env::getUserName(), $idtaskselected, $assignedgroup->idgroup);
        }

//******************************************
//user new record assign and email
//******************************************
        foreach ($paramnames as $param_name => $param_val) {
            $rcecordfound = false;
            foreach ($assignedusers as $assigneduser) {
                if (substr($param_name, 0, 2) === "u-") {
                    $iduserf = (int) str_replace('u-', '', $param_name);
                    if ($assigneduser->iduser === $iduserf)
                        $rcecordfound = true;
                }
            }
            if (!$rcecordfound) {
                if (substr($param_name, 0, 2) === "u-") {
                    $iduserf = (int) str_replace('u-', '', $param_name);
                    $semdemailto[] = $this->_setAssignUser(\model\env::getUserName(), $idtaskselected, $iduserf);
                }
            }
        }

//******************************************
//user/group new record assign and email
//******************************************
        foreach ($paramnames as $param_name => $param_val) {
            $rcecordfound = false;
            foreach ($assignedgroups as $assignedgroup) {
                if (substr($param_name, 0, 2) === "g-") {
                    $idgroup = (int) str_replace('g-', '', $param_name);
                    if ($assignedgroup->idgroup === $idgroup)
                        $rcecordfound = true;
                }
            }
            if (!$rcecordfound) {
                if (substr($param_name, 0, 2) === "g-") {
                    $idgroup = (int) str_replace('g-', '', $param_name);
                    $this->_AssignGroup(\model\env::getUserName(), $idtaskselected, $idgroup);

                    $taskname = $this->getTaskById($idtaskselected)->title ?? '';

                    $ptoject = (new \model\project)->getproject($idproject);
                    $users = (new \model\action(\model\env::src($idproject)))->getprojectgroupusers($idgroup);

                    foreach ($users as $user) {

                        $data_header = new \stdClass();
                        $data_header->usernameassigned = $user->name;
                        $data_header->email = $user->email;
                        $data_header->projectname = $ptoject->title;
                        $data_header->taskname = $taskname;

                        $semdemailto[] = $data_header;
                    }
                }
            }
        }

        $filename = \model\route::render($emailadvicefilename);
        foreach ($semdemailto as $data_header)
            $this->_emailassigneduser($data_header->usernameassigned, $data_header->email, $data_header->projectname, $data_header->taskname, $filename);
    }

    private function _emailtaskstatus($taskname, $statusname, $filename) {
        $modelproject = new \model\project();

        $projname = $modelproject->getproject($this->src->idproject)->title;
        $users = $modelproject->getactiveusersinproject($this->src->idproject);

        foreach ($users as $user) {
            $emailstring = array();
            if (empty($user->email))
                continue;

            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                \model\message::render(\model\lexi::get('', 'sys030', $user->email));
                continue;
            }

            $lines = file($filename);
            foreach ($lines as $line) {
                $line = str_replace('[projectname]', $projname, $line);
                $line = str_replace('[membername]', $user->name, $line);
                $line = str_replace('[taskname]', $taskname, $line);
                $line = str_replace('[statusname]', $statusname, $line);
                $emailstring[] = $line;
            }

            \model\env::sendMail($user->name, $user->email, \model\lexi::get('', 'sys005') . ': ' . $taskname, $emailstring);
        }
    }

    public function deleteActionAttachedFile($idtaskselected, $filename) {
        if (!$this->isuserallow(self::ROLE_ACTIONFILES, self::class))
            return false;

//        $idproject = $this->src->idproject;
//        $folderpath = $this->getAttachedFileFolderName($idproject, $idtaskselected, $filename);
        $folderpath = DATA_PATH . "attach/" . $this->src->idproject . "/" . $idtaskselected . "/";
        if (!file_exists($folderpath))
            return false;

        $targetname = $folderpath . $filename;
        if (!file_exists($targetname))
            return false;

        if (!unlink($targetname))
            return false;

        //susccess, check if any attachment left
        $hasattach = 0;

        $cdir = scandir($folderpath);
        foreach ($cdir as $key => $value) {
            if (substr($value, 0, 1) === '.')
                continue;

            if ($value !== $filename) { // ignore if file been deleted still active
                $hasattach = 1;
                break;
            }
        }

        $this->_setFileAttached(\model\env::getUserName(), $idtaskselected, $filename, false, $hasattach);
    }

    public function downloadAttachedFile($filename, $idtaskselected) {
        $folderpath = DATA_PATH . "attach/" . $this->src->idproject . "/" . $idtaskselected . "/";
        if (!file_exists($folderpath))
            return;

        $targetname = $folderpath . $filename;
        if (!file_exists($targetname))
            return;

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($targetname));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($targetname));
        ob_clean();
        flush();
        readfile($targetname);
    }

    public function getTagsForAction($idtask) {
        return $this->getRecords('SELECT tagname,idtag FROM tasktag WHERE idtask = ?', (int) $idtask);
    }

    public function updateTag($idtask, $args) {
        if (!$this->isuserallow(self::ROLE_ACTIONCOMMENT, self::class))
            return false;

        $tasktags = $this->getRecords('SELECT tagname,idtag FROM tasktag WHERE idtask = ?', (int) $idtask);

        foreach ($tasktags as $tasktag)
            $tasktag->selected = 0;

// update records
        foreach ($args as $param_name => $param_val) {
            if (substr($param_name, 0, 2) === 't-') {
                $src_idtag = (int) str_replace('t-', '', $param_name);
                //validate status
                $tasktag = \model\utils::filter($tasktags, '$v->idtag === ' . $src_idtag);
                if (count($tasktag) > 0) {
                    if (empty($param_val)) {
                        $tasktag[0]->selected = 2;
                    } else {
                        $tasktag[0]->selected = 1;
                        if ($tasktag[0]->tagname !== $param_val)
                            $tasktag[0]->tagname = $param_val;
                    }
                }
            }
        }
// for new records
        foreach ($args as $param_name => $param_val) {
            if (empty($param_val))
                continue;

            if (substr($param_name, 0, 3) === 'ti-') {
                $tasktag = new \stdClass();
                $tasktag->tagname = $param_val;
                $tasktag->idtag = 0; // flag for new record
                $tasktag->selected = 0;

                $tasktags[] = $tasktag;
            }
        }

        $tasktagstoupdate = \model\utils::filter($tasktags, '$v->idtag === 0 | $v->selected === 1 | $v->selected === 2');
        if (count($tasktagstoupdate) > 0) {
            $this->startTransaction();
            foreach ($tasktagstoupdate as $tasktag) {
                if ($tasktag->idtag === 0)
                    $this->executeSql('INSERT INTO tasktag (tagname, idtask, iduser) VALUES (?, ?, ?)', (string) $tasktag->tagname, (int) $idtask, (int) \model\env::getIdUser());

                if ($tasktag->selected === 1)
                    $this->executeSql('UPDATE tasktag SET tagname = ? WHERE idtag = ?', $tasktag->tagname, (int) $tasktag->idtag);

                if ($tasktag->selected === 2)
                    $this->executeSql('DELETE FROM tasktag WHERE idtag = ?', (int) $tasktag->idtag);
            }
            $this->endTransaction();
        }
    }

    public function searchAllActions($searchtext, $type = 'txt', $idgate = 0, $idproject = 0, $navpage = 0) {
        $projects = (new \model\project)->getprojects();
        if ($idproject > 0)
            $projects = \model\utils::filter($projects, '$v->idproject === ' . $idproject);

        $allactions = [];

        foreach ($projects as $actionproject) {
            $modeltask = new \model\action(\model\env::src($actionproject->idproject));
            $results = $modeltask->searchActionsFor($searchtext, $type, $idgate);
            if (!isset($results) || count($results) === 0)
                continue;

            foreach ($results as $result) {
                $result->idproject = $actionproject->idproject;
                $result->projname = $actionproject->title;
            }

//append actions from projects
            $allactions = \YaLinqo\Enumerable::from($allactions)
                            ->union($results, '$v->idproject . $v->idtask')->toList();
        }

        $sortfieds = ['createdon' => 'desc'];

        $result = new \stdClass();
        $result->total_records = count($allactions);
        $result->max_records = \model\env::getMaxRecords('search');
        $result->actions = \model\utils::sorttakeList($allactions, $sortfieds, $navpage, $result->max_records);

        foreach ($result->actions as $action) {
            $action->lbl_gatename = $modeltask->getGate($action->idgate)->name ?? '';
            $action->lbl_username = isset($action->username) ? ' - ' . $action->username : '';

            // get tags associated with action
            if ($action->hastags > 0)
                $action->tags = (new \model\action(\model\env::src($action->idproject)))->getTagsForAction($action->idtask);
        }

        return $result;
    }

    public function getTotalActiveActions($useridproject) {
        $stdout = new \stdClass();
        $stdout->todosall = 0;
        $stdout->mytodos = 0;
        $stdout->todoshold = 0;
        $stdout->mytodoshold = 0;

        $justme = [];
        $user = new \stdClass();
        $user->iduser = \model\env::getIdUser();
        $user->idgroup = 0;
        $user->name = '';
        $justme[] = $user;

        $defaultgate = $this->getDefaultGate();

        $projects = (new \model\project)->getprojects();
//find all projects
        foreach ($projects as $actionproject) {
            $results = (new \model\action(\model\env::src($actionproject->idproject)))->getSummaryActions($defaultgate);

            foreach ($results as $result) {
                $stdout->todosall++;
                if ($result->onhold)
                    $stdout->todoshold++;

                if ($actionproject->idproject === $useridproject) {
                    $stdout->mytodos++;
                    if ($result->onhold)
                        $stdout->mytodoshold++;
                } else {
//match assigned users
//                    if (!isset($result->taskusernames))
//                        continue;
//look for iduser                           
                    $foundrecord = false;

                    $taskusername = \model\utils::firstOrDefault($result->taskusernames, '$v->iduser === ' . \model\env::getIdUser());
                    if (isset($taskusername)) {
                        $foundrecord = true;
                    }

//                    foreach ($result->taskusernames as $taskusername) {
//                        if ($taskusername->iduser === \model\env::getIdUser()) {
//                            $stdout->mytodos++;
//                            if ($result->onhold)
//                                $stdout->mytodoshold++;
//
//                            break;
//                        }
//                    }

                    foreach ($result->taskgroupnames as $taskgroupname) {
                        $taskusername = \model\utils::firstOrDefault($taskgroupname->users, '$v->iduser === ' . \model\env::getIdUser());
                        if (isset($taskusername)) {
                            $foundrecord = true;

                            break;
                        }
                    }

                    if ($foundrecord) {
                        $stdout->mytodos++;
                        if ($result->onhold)
                            $stdout->mytodoshold++;
                    }
                }
            }
        }

        return $stdout;
    }

    private function _filterusersactions($results, $assignedusernames) {
        $index = 0;

        if (count($assignedusernames) > 0) {
            foreach ($results as $result) {
                $includerecord = false;

                foreach ($result->taskusernames as $taskusername) {
                    $filteruser = \model\utils::firstOrDefault($assignedusernames, '$v->iduser === ' . $taskusername->iduser);
                    if (isset($filteruser)) {
                        $includerecord = true;
                        break;
                    }
                }
                foreach ($result->taskgroupnames as $taskgroupname) {
                    $filtergroup = \model\utils::firstOrDefault($assignedusernames, '$v->idgroup === ' . $taskgroupname->idgroup);
                    if (isset($filtergroup)) {
                        $includerecord = true;
                        break;
                    }
                    if (isset($taskgroupname->users)) {
                        foreach ($taskgroupname->users as $user) {
                            $filtergroup = \model\utils::firstOrDefault($assignedusernames, '$v->iduser === ' . $user->iduser);
                            if (isset($filtergroup)) {
                                $includerecord = true;
                                break;
                            }
                        }
                    }
                }

                if (!$includerecord)
                    unset($results[$index]);

                $index++;
            }
        }

        return $results;
    }

    private function _getAllActiveActions($useridproject, $defaultgate, $assignedusernames = []) {
        $projects = (new \model\project)->getprojects();

        $allactions = [];

        //get projects actions
        foreach ($projects as $actionproject) {
            if ($actionproject->idproject === $useridproject) {
                // get all for user project
                $results = (new \model\action(\model\env::src($actionproject->idproject)))->getListTaskByStatus($defaultgate, []);
            } else {
                $results = (new \model\action(\model\env::src($actionproject->idproject)))->getListTaskByStatus($defaultgate, $assignedusernames);
            }

            $allactions = \YaLinqo\Enumerable::from($allactions)
                            ->union($results, '$v->idproject . $v->idtask')->toList();
        }

        return $allactions;
    }

    //******** CATEGORY ******************
    private $categorycache;

    public function getCategory($idcategory) {
        if ($idcategory > 0) {
            if (!isset($this->categorycache))
                $this->getCategories();

            return \model\utils::firstOrDefault($this->categorycache, '$v->idcategory === ' . $idcategory);
        }
        return null;
    }

    public function getCategories($includedeleted = false) {
        if (!isset($this->categorycache)) {
            $this->categorycache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_CATEGORY);
            if (!$includedeleted)
                $this->categorycache = \model\utils::filter($this->categorycache, '$v->deleted === 0');
        }
        return $this->categorycache;
    }

    public function hasCategory() {
        if (!isset($this->categorycache))
            $this->categorycache = $this->getCategories();

        return count($this->categorycache) > 0;
    }

    public function updatehold($idtask, $args) {
        if (!$this->isuserallow(self::ROLE_ACTIONHOLD, self::class))
            return false;

        $taskholds = $this->getRecords('SELECT idtaskhold,idtask,description,createon,lastmodifiedon FROM taskhold WHERE idtask = ?', (int) $idtask);
        foreach ($taskholds as $taskhold) {
            $taskhold->selected = 0;
        }

// update records
        foreach ($args as $param_name => $param_val) {
            if (substr($param_name, 0, 2) === 'h-') {
                $src_idtaskhold = (int) str_replace('h-', '', $param_name);
                //validate status
                $taskhold = \model\utils::filter($taskholds, '$v->idtaskhold === ' . $src_idtaskhold);
                if (count($taskhold) > 0) {
                    if (empty($param_val)) {
                        $taskhold[0]->selected = 2;
                    } else {
                        $taskhold[0]->selected = 1;
                        if ($taskhold[0]->description !== $param_val) {
                            $taskhold[0]->description = $param_val;
                        }
                    }
                }
            }
        }
// for new records
        for ($r = 1; $r <= 2; $r++) {
            $enteredhold = filter_input(INPUT_POST, 'hi-' . $r);
            if (!empty($enteredhold)) {
                $taskhold = new \stdClass();
                $taskhold->description = $enteredhold;
                $taskhold->idtaskhold = 0; // flag for new record
                $taskhold->selected = 0;
                $taskhold->idtask = $idtask;

                $taskholds[] = $taskhold;
            }
        }

// update due dates
        if (count($taskholds) > 0) {
            $taskholddescription = '';
            $this->startTransaction();
            foreach ($taskholds as $taskhold) {
                $taskholddescription .= $taskhold->description . ',';

                if ($taskhold->idtaskhold === 0)
                    $this->executeSql('INSERT INTO taskhold (description, idtask) VALUES (?, ?)', (string) $taskhold->description, (int) $taskhold->idtask);

                if ($taskhold->selected === 1)
                    $this->executeSql('UPDATE taskhold SET description = ? WHERE idtaskhold = ?', (string) $taskhold->description, (int) $taskhold->idtaskhold);

                if ($taskhold->selected === 2)
                    $this->executeSql('DELETE FROM taskhold WHERE idtaskhold = ?', (int) $taskhold->idtaskhold);
            }
            $this->endTransaction();

            $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_USER, \model\lexi::get('', 'sys016', $taskholddescription));
        }
    }

    //******** TRACK ******************
    private $trackcache;

    public function getTrack($idtrack) {
        if ($idtrack > 0) {
            if (!isset($this->trackcache))
                $this->getTracks();

            return \model\utils::firstOrDefault($this->trackcache, '$v->idtrack === ' . $idtrack);
        }
        return null;
    }

    public function getTracks($includedeteled = false) {
        if (!isset($this->trackcache)) {
            $this->trackcache = (new \model\project)->getServiceRecords($this->src->idproject, \model\env::CONFIG_ACTIONS, \model\env::MODULE_TRACK);
            if (!$includedeteled)
                $this->trackcache = \model\utils::filter($this->trackcache, '$v->deleted === 0');
        }
        return $this->trackcache;
    }

    public function getDefaultTrack() {
        if (!isset($this->trackcache))
            $this->trackcache = $this->getTracks();

        if (count($this->trackcache) > 0)
            return $this->trackcache[0]->idtrack;

        return 0;
    }

    public function hasTrack() {
        if (!isset($this->trackcache))
            $this->trackcache = $this->getTracks();

        return count($this->trackcache) > 0;
    }

    public function updateActionTrack($idtask, $tracking, $emailadvicefilename) {
        if (!$this->isuserallow(self::ROLE_ACTIONTRACKING, self::class))
            return false;

        $action = $this->getTaskById($idtask); // get first record
        if (!isset($action)) {
            \model\message::render(\model\lexi::get('action', 'sys035', $idtask, $this->src->idproject));
            return false;
        }

        if ((bool) $action->onhold)
            return false;
// save
        if (!empty(\trim($tracking->commenttext)))
            $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_USER, $tracking->commenttext);

//only when changes
        if ($action->idtrack !== $tracking->idtrack | $action->idcategory !== $tracking->idcategory) {
// save
            $allok = $this->executeSql('UPDATE task SET title = ?, idtrack = ? WHERE idtask = ?', trim((string) $action->title), (int) $tracking->idtrack, (int) $idtask);
            if ($allok !== false) {
                $categtext = \model\utils::format('({0}) {1}', $tracking->idcategory, $this->getCategory($tracking->idcategory));
                $tracktext = \model\utils::format('({0}) {1}', $tracking->idtrack, $this->geTrack($tracking->idtrack));
                $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_USER, \model\lexi::get('', 'sys014', $categtext, $tracktext));
            }
        }

// do not assign user when only category is been updated
        if (!$tracking->iscategory && !$this->_isuserAssignedToAction($idtask)) {
//update assigned members
            $data_header = $this->_setAssignUser(\model\env::getUserName(), $idtask, \model\env::getIdUser());

            $filename = \model\route::render($emailadvicefilename);
            $this->_emailassigneduser($data_header->usernameassigned, $data_header->email, $data_header->projectname, $data_header->taskname, $filename);
        }
    }

    public function getActionById($idtask) {
        $taskresult = $this->getRecord('SELECT task.idtask,task.idpriority,task.title,task.description,task.idgate,task.createdon,task.lastmodifiedon,task.progress,task.idparent,task.idcategory,task.idtrack,taskowner.username FROM task LEFT JOIN taskowner USING ( idtask ) WHERE task.idtask = ?', (int) $idtask);

        if (isset($taskresult)) {
            $entrygate = $this->getDefaultGate();

            $this->_getTaskDetails($taskresult);

            $taskresult->comments = $this->getRecords('SELECT createdon,description,idcomment,lastmodifiedon,deleted,username FROM comment WHERE idtask = ? AND source = ? AND deleted = ? ORDER BY createdon DESC', (int) $idtask, (string) \model\env::COMMENT_USER, 0);
            $taskresult->attachedactions = $this->getRecords('SELECT idtask,title,idgate FROM task WHERE idparent = ?', (int) $idtask);
            $existgatedependency = count(\model\utils::filter($taskresult->attachedactions, '$v->idgate === ' . $entrygate)) > 0;
            $taskresult->attachedfiles = $this->_getAttachedFilenames($this->src->idproject, $idtask);
            $taskresult->allowedit = $entrygate === $taskresult->idgate;
            $taskresult->trackname = $this->getTrack($taskresult->idtrack)->name ?? '';
            $taskresult->categoryname = $this->getCategory($taskresult->idcategory)->name ?? '';
            $taskresult->gatename = $this->getGate($taskresult->idgate)->name ?? '';
            $taskresult->priorityname = $this->getPriority($taskresult->idpriority)->name ?? '';

            //kept value to refresh master task list
            $taskresult->istracking = false;
            $taskresult->isroleupdate = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONUPDATE);
            $taskresult->isrolecomment = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONCOMMENT);
            $taskresult->isroletrack = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONCATEGORY);
            if ($taskresult->isroletrack && !$this->hasTrack())
                $taskresult->isroletrack = false;

// security to update tracking
            if ($taskresult->isroletrack)
                if (!isset($taskresult->taskusernames) && count($taskresult->taskusernames) > 0)
                    $taskresult->istracking = true;

            $taskresult->isroleusers = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONUSER);
            $taskresult->isrolefile = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONFILES);
            $taskresult->isroletasks = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONSUBTASK);
            $taskresult->isrolegate = \model\env::isUserAllow($this->src->idproject, self::ROLE_ACTIONGATE);
            if ($taskresult->isrolegate && ($existgatedependency | $taskresult->onhold))
                $taskresult->isrolegate = false;

//parent list
            $taskresult->parenttasktitle = null;
            if ($taskresult->idparent > 0) {
                $parenttask = $this->getTaskById($taskresult->idparent);
                if (isset($parenttask))
                    $taskresult->parenttasktitle = $parenttask->title;
            }

            if ($taskresult->isrolegate)
                $taskresult->Gates = $this->getGates();
        }

        return $taskresult;
    }

    public function getTaskById($idtask) {
        $taskresult = $this->getRecord('SELECT task.idtask,task.idpriority,task.title,task.description,task.idgate,task.createdon,task.lastmodifiedon,task.progress,task.idparent,task.idcategory,task.idtrack,taskowner.username FROM task LEFT JOIN taskowner USING ( idtask ) WHERE task.idtask = ?', (int) $idtask);
        if (isset($taskresult))
            $this->_getTaskDetails($taskresult);

        return $taskresult;
    }

    public function getActionOnHold($idtask) {
        return $this->getRecords('SELECT idtaskhold,idtask,description,createon,lastmodifiedon FROM taskhold WHERE idtask = ?', (int) $idtask);
    }

    private function _getTaskDetails($task) {
        $this->todaydate = \model\utils::offsetDateTime(new \DateTime('now'), \model\env::getTimezone());
        $this->overduepriority = $this->getDefaultPriority();

        //get some more details
        $task->taskdues = $this->getRecords('SELECT idtaskdue,idtask,starton,dueon,duration,createon,lastmodifiedon FROM taskdue WHERE idtask = ?', (int) $task->idtask);

        $task->taskholds = $this->getRecords('SELECT idtaskhold,idtask,description,createon,lastmodifiedon FROM taskhold WHERE idtask = ?', (int) $task->idtask);
        $task->tags = $this->getRecords('SELECT tagname,idtag FROM tasktag WHERE idtask = ?', (int) $task->idtask);
        // get assigned users
        //***************************
        $task->taskusernames = $this->_getTaskAssignedUsers($task->idtask);
        $task->taskgroupnames = $this->_getTaskAssignedGroups($task->idtask);

        //***************************
        //calculate overdue
        //***************************
        $task->isoverdue = false;
        $task->dueon = $task->createdon;
        $task->onhold = count($task->taskholds) > 0;

        foreach ($task->taskdues as $taskdue) {
            if (!empty($taskdue->dueon) && $taskdue->dueon < $this->todaydate) {
                $task->dueon = $taskdue->dueon;
                $task->isoverdue = true;
                $task->idpriority = $this->overduepriority;
            }
        }

        $task->selected = 0;
    }

    private function _getInitialsName($taskusername) {
        //get initial names
        $names = explode(' ', \trim($taskusername));
        $membertitle = "";
        $maxinitials = 0;
        foreach ($names as $name) {
            if ($maxinitials < 2)
                $membertitle .= substr($name, 0, 1);

            $maxinitials++;
        }

//        $taskusername = new \stdClass;
//        $taskusername->idgroup = $taskuser->idgroup;
//        $taskusername->iduser = $taskuser->iduser;
//        $taskusername->name = \trim($taskuser->taskusername);
//        $taskusername->title = $membertitle;
//        return $taskusername;
        return $membertitle;
    }

    public function insertcomment($idtask, $description = '') {
        if (empty($description) || !$this->isuserallow(self::ROLE_ACTIONCOMMENT, self::class))
            return false;

        $action = $this->getTaskById($idtask);
        if (isset($action) && count($action->taskholds) === 0)
            $this->_insertcomment($idtask, \model\env::getUserName(), \model\env::COMMENT_USER, $description);
    }

    private function _emailassigneduser($name, $email, $projname, $taskname, $emailadvicefilename) {
        $emailstring = array();
        if (empty($email))
            return false;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \model\message::render(\model\lexi::get('', 'sys030', $email));
            return;
        }

        $lines = file($emailadvicefilename);
        foreach ($lines as $line) {
            $line = str_replace('[projectname]', $projname, $line);
            $line = str_replace('[membername]', $name, $line);
            $line = str_replace('[taskname]', $taskname, $line);
            $emailstring[] = $line;
        }
        $texto = \model\lexi::get('', 'sys031', $taskname, $name);
        \model\env::sendMail($name, $email, $texto, $emailstring);
    }

    private function _getAttachedFilenames($idproject, $idtaskselected) {
        $attachedfiles = [];
        $folderpath = DATA_PATH . "attach/" . $idproject . "/" . $idtaskselected . "/";

        if (file_exists($folderpath)) {
            $cdir = scandir($folderpath);
            foreach ($cdir as $key => $value) {
                if (substr($value, 0, 1) !== '.')
                    $attachedfiles[] = $value;
            }
        }
        return $attachedfiles;
    }

    public function getUsersAndGroups($idtask) {
        $result = new \stdClass();

        $taskusers = $this->_getTaskAssignedUsers($idtask);
        $taskgroups = $this->_getTaskAssignedGroups($idtask);

        $result->users = (new \model\project)->getactiveusersinproject($this->src->idproject);
        $result->groups = $this->getprojectactivegroupsusers();
//select already assigned members
        foreach ($result->users as $user) {
            $taskuser = \model\utils::firstOrDefault($taskusers, '$v->iduser === ' . $user->iduser);
            if (isset($taskuser))
                $user->selected = true;
        }
        foreach ($result->groups as $group) {
            $taskgroup = \model\utils::firstOrDefault($taskgroups, '$v->idgroup === ' . $group->idgroup);
            if (isset($taskgroup))
                $group->selected = true;
        }

        return $result;
    }

    //******** FILTER VIEWS ******************
    private $filterviewcache;

    public function getFilterview($idview) {
        if (isset($idview)) {
            if (!isset($this->filterviewcache))
                $this->getFilterviews();

            return \model\utils::firstOrDefault($this->filterviewcache, '$v->idview === ' . $idview);
        }
        return null;
    }

    public function getFilterviews() {
        if (!isset($this->filterviewcache))
            $this->filterviewcache = (new \model\project)->getServiceRecordsSys(\model\env::CONFIG_CORE, \model\env::MODULE_FILTERVIEW);

        // find status view for this particular project and user
        $userfilterviews = $this->_getUserFilterViews();

        foreach ($this->filterviewcache as $filterview) {
            $on = \model\utils::firstOrDefault($userfilterviews, '$v->idview === ' . $filterview->idview);
            $filterview->on = !isset($on);
        }
        return $this->filterviewcache;
    }

    private function _getUserFilterViews() {
        return $this->getRecords('SELECT iduser,idview FROM filterview WHERE iduser = ?', \model\env::getIdUser());
    }

    public function setFilterUserViews($args) {
        $userfilterviews = $this->getFilterviews();
        foreach ($userfilterviews as $userfilterview)
            $userfilterview->confirmed = false;

        foreach ($args as $k => $v) {
            if (substr($k, 0, 2) === "v-") {
                $id = (int) str_replace('v-', '', $k);
                $userfilterview = \model\utils::firstOrDefault($userfilterviews, '$v->idview === ' . $id);
                if (isset($userfilterview))
                    $userfilterview->confirmed = true;
            }
        }

        foreach ($userfilterviews as $userfilterview) {
            // if changes
            // $userfilterview->on: true do not register in database
            // $userfilterview->confirmed: TRUE do not register in database
            if ($userfilterview->confirmed !== $userfilterview->on) {
                if (!$userfilterview->on) {
                    $this->executeSql('DELETE FROM filterview WHERE iduser = ? AND idview = ?', \model\env::getIdUser(), (int) $userfilterview->idview);
                } else {
                    $this->executeSql('INSERT INTO filterview (iduser, idview) VALUES (?, ?)', \model\env::getIdUser(), (int) $userfilterview->idview);
                }
            }
        }
    }

    private function _setSubAction($idtask, $idparent) {
        $this->executeSql('UPDATE task SET idparent = ? WHERE idtask = ?', (int) $idparent, (int) $idtask);
    }

    private function _setFlagFileAttachedTask($idtask, $hasattach) {
        $this->executeSql('UPDATE task SET hasattach = ? WHERE idtask = ?', (int) $hasattach, (int) $idtask);
    }

    private function _insertcomment($idtask, $username, $source, $description) {
        if (!$this->isuserallow(self::ROLE_ACTIONCOMMENT, self::class))
            return false;

        $lastIdInserted = $this->executeSql('INSERT INTO comment (idtask, description, source, iduser, username, deleted) VALUES (?, ?, ?, ?, ?, ?)', (int) $idtask, trim((string) $description), trim((string) $source), \model\env::getIdUser(), trim((string) $username), 0);

        if ($source !== \model\env::COMMENT_SYS) {
            $action = $this->gettaskById($idtask);
            if (isset($action)) {
                $note_text = \model\lexi::get('', 'sys007') . ': ' . $action->title . ' -> ' . $description;
                $this->addSystemNote($note_text, (string) $idtask);
            }
        }

        return $lastIdInserted;
    }

    public function getListNoteByUser() {
        return $this->getRecords('SELECT notebroadcast.isnew,notedetail.idnote,notedetail.createdon,notedetail.iduser,notedetail.notetext,notedetail.sendername,notedetail.url FROM notebroadcast JOIN notedetail USING ( idnote ) WHERE notebroadcast.iduser = ? AND notebroadcast.deleted = ? ORDER BY notedetail.idnote DESC,notedetail.createdon DESC', \model\env::getIdUser(), 0);
    }

    public function getMembersByNote($idnote) {
        return $this->getRecords('SELECT iduser,membername FROM notebroadcast WHERE idnote = ?', (int) $idnote);
    }

    public function setNote($actiontype, $args, $notetext = '', $idnote = 0) {
        if (empty($notetext))
            return false;

        if ($actiontype === \model\action::NOTE_REPLY) {
            $this->_addNote(\model\env::getUserName(), $notetext, $idnote);
            $this->marNoteAasRead($idnote);
        }

        if ($actiontype === \model\action::NOTE_PROJECT) {
            $includeusers = '';
            $includegroups = '';
            foreach ($args as $param_name => $param_val) {
                if (substr($param_name, 0, 2) === 'u-') {
                    if (!empty($includeusers)) {
                        $includeusers .= ',';
                    }
                    $includeusers .= str_replace('u-', '', $param_name);
                }
                if (substr($param_name, 0, 2) === 'g-') {
                    if (!empty($includegroups)) {
                        $includegroups .= ',';
                    }
                    $includegroups .= str_replace('g-', '', $param_name);
                }
            }

            $this->_addSelectedUsersNote(\model\env::getUserName(), $notetext, $includeusers, $includegroups);
        }
    }

    private function _addNote($src_username, $notetext, $idnote, $package = "", $url = "") {
        if ($idnote === 0)
            return false;

        $lastIdInserted = $this->executeSql('INSERT INTO notedetail (idnote, iduser, notetext, sendername, package, url) VALUES (?, ?, ?, ?, ?, ?)', (int) $idnote, \model\env::getIdUser(), $notetext, $src_username, $package, $url);

//ONLY BROADCAST TO MEMBERS IN CONVERSATION , e.g.: private conversations
        $this->executeSql('UPDATE notebroadcast SET deleted = ?, isnew = ? WHERE idnote = ?', 0, 1, (int) $idnote);

        return $lastIdInserted;
    }

    private function _addSelectedUsersNote($src_username, $notetext, $includeusers, $includegroups, $package = "", $url = "") {
        $activeprojectusers = (new \model\project)->getactiveusersinproject($this->src->idproject);

// set all users as active to receive message
        foreach ($activeprojectusers as $activeprojectuser) {
            $activeprojectuser->useractive = false;
            $activeprojectuser->groupactive = false;
        }

//remove exluded users 
        if (!empty($includeusers)) {
            $includeselectedusers = explode(",", $includeusers);

            foreach ($activeprojectusers as $activeprojectuser) {
                foreach ($includeselectedusers as $selecteduser) {
                    if ($activeprojectuser->iduser === $selecteduser) {
                        $activeprojectuser->useractive = true;
                        break;
                    }
                }
            }
        }

//excluded groups            
        if (!empty($includegroups)) {
            $includeselectedgroups = explode(",", $includegroups);

//find users by each group
            foreach ($includeselectedgroups as $idgroup) {
                $activeprojectgroupusers = $this->getprojectgroupusers($idgroup);

                foreach ($activeprojectusers as $activeprojectuser) {
                    foreach ($activeprojectgroupusers as $selecteduser) {
                        if ($activeprojectuser->iduser === $selecteduser->iduser) {
                            $activeprojectuser->groupactive = true;
                            break;
                        }
                    }
                }
            }
        }

//remove users not receiving messages            
        $counter = 0;
        foreach ($activeprojectusers as $activeprojectuser) {
            if (!$activeprojectuser->groupactive & !$activeprojectuser->useractive)
                unset($activeprojectusers[$counter]);

            $counter++;
        }

        return $this->_insertNote(\model\env::getIdUser(), $src_username, $this->src->idproject, $notetext, $activeprojectusers, true, $package, $url);
    }

    public function archiveNote($idnote) {
        $this->executeSql('UPDATE notebroadcast SET inactiveon = ?, deleted = ?, isnew = ? WHERE idnote = ? AND iduser = ?', \model\utils::forDatabaseDateTime(new \DateTime()), 1, 0, (int) $idnote, \model\env::getIdUser());
    }

    public function marNoteAasRead($idnote) {
        if ($idnote === 0)
            return false;

        $this->executeSql('UPDATE notebroadcast SET isnew = ? WHERE idnote = ? AND iduser = ?', 0, (int) $idnote, \model\env::getIdUser());
    }

    public function totalUnreadMessages() {
        $result = $this->getRecord('SELECT count(*) AS result FROM notebroadcast WHERE iduser = ? AND deleted = ? AND isnew = ?', \model\env::getIdUser(), 0, 1);
        return ($result->result ?? 0);
    }

    public function addSystemNote($notetext, $url = "", $package = "") {
        $activeprojectusers = (new \model\project)->getactiveusersinproject($this->src->idproject);

        $this->_insertNote(\model\env::getIdUser(), \model\env::getUserName(), $this->src->idproject, $notetext, $activeprojectusers, false, $package, $url);
    }

    private function _insertNote($iduser, $username, $idproject, $notetext, $activeprojectusers, $broadcasttosender, $package = "", $url = "") {
        $lastIdInsertedNote = $this->executeSql('INSERT INTO note DEFAULT VALUES');

        $lastIdInsertedNoteDetatil = $this->executeSql('INSERT INTO notedetail (idnote, iduser, notetext, sendername, package, url) VALUES (?, ?, ?, ?, ?, ?)', (int) $lastIdInsertedNote, (int) $iduser, $notetext, $username, $package, $url);

//broadcast to user
        if ($broadcasttosender)
            $lastIdInsertedUserBroadcast = $this->executeSql('INSERT INTO notebroadcast (idnote, iduser, membername, deleted, isnew) VALUES (?, ?, ?, ?, ?)', (int) $lastIdInsertedNote, (int) $iduser, (string) $username, 0, 0);

// broadcast others, only when a project
        if (!isset($activeprojectusers))
            return;

        foreach ($activeprojectusers as $activeprojectuser) {
            if ($activeprojectuser->iduser !== $iduser)
                $lastIdInsertedBroadcast = $this->executeSql('INSERT INTO notebroadcast (idnote, iduser, membername, deleted, isnew) VALUES (?, ?, ?, ?, ?)', (int) $lastIdInsertedNote, (int) $activeprojectuser->iduser, (string) $activeprojectuser->name, 0, 1);
        }

        return $lastIdInsertedNote;
    }

    public function getNotes() {
        $total_new_notes = 0;
        $notes = [];

        // get project notes
        $projects = (new \model\project)->getprojects();

        foreach ($projects as $project) {
            $results = $this->_buildNote($project);
            $total_new_notes += $results[1];

            foreach ($results[0] as $note)
                $notes[] = $note;
        }

        return [$notes, $total_new_notes];
    }

    private function _buildNote($project) {
        $total_new_notes = 0;
        $notes = [];
        $head_idnote = -1;

        $modelnote = new \model\action(\model\env::src($project->idproject));

        $pnotes = $modelnote->getListNoteByUser();

//*********************************
// count total new notes
//*********************************
        foreach ($pnotes as $note) {

            if ($head_idnote !== $note->idnote) {
                // create note header (once)
                $note_h = new \stdClass();
                $note_h->idproject = $project->idproject;
                $note_h->title = $project->title;
                $note_h->idnote = $note->idnote;
                $note_h->isnew = '0';
                $note_h->detail = [];

                $note_h->members = '';
                $members = $modelnote->getMembersByNote($note->idnote);
                foreach ($members as $member)
                    $note_h->members .= (empty($note_h->members) ? '' : ', ') . $member->membername;

                $head_idnote = $note->idnote;
                $notes[] = $note_h;

                if ((bool) $note->isnew)
                    $total_new_notes++;
            }

            $note->link = [];
            if (!empty(\trim($note->url)))
                $note->link = \model\route::window('action', ['actions/action/index.php?idproject={0}&idtask={1}', $project->idproject, $note->url], $project->idproject, \model\lexi::get('actions', 'sys067'), $project->title);

            $note->iduser = $note->iduser;
            if ((bool) $note->isnew)
                $note_h->isnew = '1';

            $note_h->detail[] = $note;
        }

        return [$notes, $total_new_notes];
    }

    public function setActiveUser() {
        $this->executeSql('UPDATE projgroupuser SET deleted = ? WHERE iduser = ?', 0, (int) \model\env::getIdUser());
    }

    public function setInactiveUser() {
        if (!$this->isuserallow(\model\project::ROLE_TEAMUSERS, self::class))
            return false;

//        //if $idproject = 0 then inactive from all projects
        //delete any previous invitations
        //get records belong to user and project
        $results = $this->getRecords('SELECT projgroupuser.idgroupuser FROM projgroupuser JOIN projgroup USING ( idgroup ) WHERE projgroupuser.iduser = ?', \model\env::getIdUser());

        foreach ($results as $result)
            $this->executeSql('UPDATE projgroupuser SET deleted = ? WHERE idgroupuser = ?', 1, (int) $result->idgroupuser);
    }

    private function _getprojectgroup($idgroup) {
        return $this->getRecord('SELECT idgroup,groupname,createdon,deleted FROM projgroup WHERE idgroup = ? ORDER BY groupname', (int) $idgroup);
    }

    public function getprojectgroups() {
        $result = new \stdClass();

        $result->isrole = $this->isuserallow(\model\project::ROLE_TEAMUSERS, self::class);
        $result->groups = $this->getRecords('SELECT idgroup,groupname,createdon,deleted FROM projgroup ORDER BY groupname');
        //add users to each group
        $projgroupusers = $this->getRecords('SELECT projgroupuser.idgroup,projgroupuser.iduser FROM projgroupuser JOIN projgroup USING ( idgroup )');
        $modeluser = new \model\user();
        foreach ($projgroupusers as $projgroupuser) {
            $user = $modeluser->getuser($projgroupuser->iduser);
            if (isset($user)) {
                $projgroupuser->name = $user->name;
//                $projgroupuser->email = $user->email;
            }
        }

        foreach ($result->groups as $group)
            $group->activeusers = \model\utils::filter($projgroupusers, '$v->idgroup === ' . $group->idgroup);

        return $result;
    }

    public function updateprojgroup($updatetype, $idgroup, $groupname, $args) {
        if (!$this->isuserallow(\model\project::ROLE_TEAMUSERS, self::class))
            return false;

        if ($updatetype === 'update') {
            $users = '';
            foreach ($args as $postname => $val) {
                $pos = strpos($postname, 'user');
                if ($pos === 0) {
                    if (!empty($users))
                        $users .= ',';

                    $users .= str_replace('user', '', $postname);
                }
            }

// groups may contain multiple users
//    $modeltask->updateprojgroup($idgroup, $groupname, $users);
            $selectedusers = null;
            if (!empty($users))
                $selectedusers = explode(",", $users);

            if ($idgroup > 0) {
                $this->executeSql('DELETE FROM projgroupuser WHERE idgroup = ?', (int) $idgroup);
                //update group name
                $this->executeSql('UPDATE projgroup SET groupname = ? WHERE idgroup = ?', (string) $groupname, (int) $idgroup);

                $this->_insertprojgroupuser($idgroup, $selectedusers);
            } else {
                $lastrowid = $this->executeSql('INSERT INTO projgroup (groupname, deleted) VALUES (?, ?)', (string) $groupname, 0);
                $this->_insertprojgroupuser($lastrowid, $selectedusers);

                if ($lastrowid !== false) {
                    $texto = \model\lexi::get('', 'sys039') . ' -> ' . $groupname;
                    $this->addSystemNote($texto);
                }
            }
        }

        if ($updatetype === 'active') {
            $result = $this->getRecord('SELECT deleted FROM projgroup WHERE idgroup = ?', (int) $idgroup);
            if (isset($result))
                $this->executeSql('UPDATE projgroup SET deleted = ? WHERE idgroup = ?', $result->deleted ? 0 : 1, (int) $idgroup);
        }

        if ($updatetype === 'delete') {
            //get group to record log
            $group = $this->getRecord('SELECT groupname FROM projgroup WHERE idgroup = ?', (int) $idgroup);
            if (isset($group)) {
                $this->executeSql('DELETE FROM projgroupuser WHERE idgroup = ?', (int) $idgroup);

                //register email invitation, this record will remain until user accept or revoke
                $this->executeSql('DELETE FROM projgroup WHERE idgroup = ?', (int) $idgroup);

                $texto = \model\lexi::get('', 'sys038') . ' -> ' . $group->groupname;
                $this->addSystemNote($texto);
            }
        }
    }

    private function _insertprojgroupuser($idgroup, $selectedusers) {
        // add users
        if (!isset($selectedusers))
            return;

        foreach ($selectedusers as $selecteduser)
            $this->executeSql('INSERT INTO projgroupuser (deleted, idgroup, iduser) VALUES (?, ?, ?)', 0, (int) $idgroup, (int) $selecteduser);
    }

    public function getprojectactivegroupsusers() {
        $results = $this->getRecords('SELECT projgroup.idgroup,projgroup.groupname FROM projgroup LEFT JOIN projgroupuser USING ( idgroup ) WHERE projgroup.deleted = ? ORDER BY projgroup.idgroup', 0);
        foreach ($results as $group)
            $group->selected = false;

        return $results;
    }

    public function getprojectgroupusersactive($idgroup) {
        $projectgroup = $this->getRecord('SELECT idgroup,groupname,createdon,deleted FROM projgroup WHERE idgroup = ? ORDER BY groupname', (int) $idgroup);

        if (!isset($projectgroup)) {
            $projectgroup = new \stdClass();
            $projectgroup->idgroup = 0;
            $projectgroup->groupname = '';
            $projectgroup->createdon = \model\utils::offsetDateTime(new \DateTime('now'), \model\env::getTimezone());
            $projectgroup->deleted = false;
        }
        $projectgroup->isrole = \model\env::isUserAllow(\model\env::session_idproject(), \model\project::ROLE_TEAMUSERS);

// merge actuve users with group access
        $projectgroupusers = $this->getRecords('SELECT projgroupuser.idgroup,projgroupuser.iduser FROM projgroupuser JOIN projgroup USING ( idgroup ) WHERE projgroupuser.idgroup = ?', (int) $idgroup);

        $modeluser = new \model\user();
        $projectgroup->activeusers = (new \model\project)->getactiveusersinproject($this->src->idproject);
        foreach ($projectgroup->activeusers as $activeuser) {
            $activeuser->isactive = false;
            $user = $modeluser->getuser($activeuser->iduser);
            if (isset($user))
                $activeuser->name = $user->name;

            $projectgroupuser = \model\utils::firstOrDefault($projectgroupusers, '$v->iduser === ' . $activeuser->iduser);
            if (isset($projectgroupuser))
                $activeuser->isactive = true;
        }

        return $projectgroup;
    }

    public function getprojectgroupusers($idgroup) {
        $resultsprojgroup = $this->getRecords('SELECT projgroupuser.iduser FROM projgroup JOIN projgroupuser USING ( idgroup ) WHERE projgroup.deleted = ? AND projgroup.idgroup = ?', 0, (int) $idgroup);

        $projectusers = (new \model\project)->getprojectusers($this->src->idproject);
        foreach ($resultsprojgroup as $resultprojgroup) {
            $result = \model\utils::firstOrDefault($projectusers, '$v->iduser === ' . $resultprojgroup->iduser);
            if (isset($result)) {
                $resultprojgroup->name = $result->name;
                $resultprojgroup->email = $result->email;
            }
        }

        return $resultsprojgroup;
    }

    public function getprojectsecurity() {
        return $this->getRecords('SELECT idrole,idsecrole, iduser, seccode FROM secrole');
    }

    private function _updateprojectsecurity($seccode, $idrole, $defaultidrole) {
        if (!$this->isprojectowner())
            return false;

        // do not update when identical
        if ($idrole === $defaultidrole)
            return false;

        //check if exist
        $result = $this->getRecord('SELECT idrole FROM secrole WHERE seccode = ? AND iduser = ?', (string) $seccode, 0);

        //insert
        if (!isset($result->idrole))
            $this->executeSql('INSERT INTO secrole (idrole, iduser, seccode) VALUES (?, ?, ?)', (int) $idrole, 0, (string) $seccode);

        //update
        if (isset($result->idrole))
            $this->executeSql('UPDATE secrole SET idrole = ? WHERE seccode = ? AND iduser = ?', (int) $idrole, (string) $seccode, 0);
    }

    public function resetprojectsecurity($seccode) {
        if (!$this->isprojectowner())
            return false;

        $this->executeSql('DELETE FROM secrole WHERE seccode = ?', (string) $seccode);
    }

    public function updateprojectsecurityUsers($seccode, $idrole, $defaultidrole, $args) {
        if (!$this->isprojectowner())
            return false;

        $this->_updateprojectsecurity($seccode, $idrole, $defaultidrole);

        $secroles = [];
        foreach ($args as $param_name => $param_val) {
            if (substr($param_name, 0, 7) === 'iduser-') {
                $secrole = new \stdClass();
                $secrole->iduser = (int) str_replace('iduser-', '', $param_name);
                $secrole->idrole = (int) $param_val;

                $secroles[] = $secrole;
            }
        }

        //*********************************
        // setup customized users 
        //*********************************
        //check if exist customized
        $customusers = $this->getRecords('SELECT idrole, idsecrole, iduser FROM secrole WHERE seccode = ? AND iduser = ?', (string) $seccode, 0);
        foreach ($customusers as $customuser)
            $customuser->updated = false;

        foreach ($secroles as $secuserrole) {
            //skip when identical role
            if ($secuserrole->idrole === 0)
                continue;

            $allok = false;
            $customuser = \model\utils::filter($customusers, '$v->iduser === ' . $secuserrole->iduser);
            foreach ($customuser as $user) {
                if ($user->idrole !== $secuserrole->idrole) {
                    ///update user role
                    $this->executeSql('UPDATE secrole SET idrole = ? WHERE idsecrole = ?', (int) $secuserrole->idrole, (int) $user->idsecrole);
                    $allok = true;
                }
                $user->updated = true;
            }

            if (!$allok)
                $this->executeSql('INSERT INTO secrole (idrole, iduser, seccode) VALUES (?, ?, ?)', (int) $secuserrole->idrole, (int) $secuserrole->iduser, (string) $seccode);
        }

        //delete any other
        foreach ($customusers as $customuser) {
            if (!$customuser->updated)
                $this->executeSql('DELETE FROM secrole WHERE idsecrole = ?', (int) $customuser->idsecrole);
        }
    }

    public function getSecurityLevels() {
        return $this->getRecords('SELECT idrole,seccode FROM secrole WHERE iduser = ? OR iduser = ? ORDER BY iduser', 0, \model\env::getIdUser());
    }

    public function geActiveGroups() {
        $projects = (new \model\project)->getprojects();

        $allgroups = [];
        foreach ($projects as $actionproject) {
            $results = (new \model\action(\model\env::src($actionproject->idproject)))->getprojectactivegroupsusers($this->src->idproject);
            $allusers = \YaLinqo\Enumerable::from($allgroups)
                            ->union($results, '$v->idgroup')->toList();
        }

        return $allgroups;
    }

    public function isprojectowner($iduser = null) {
        if (!isset($iduser))
            $iduser = \model\env::getIdUser();

        $idaccess = (new \model\project)->getprojectuser($this->src->idproject, $iduser)->idaccess ?? 0;
        if ($idaccess !== 1) // must have high security
            return false;

        // allow when no ownership
        $results = $this->getRecords('SELECT iduser,createdon FROM projectowner');
        if (count($results) === 0)
            return true;

        // verify user is the owner
        $user = \model\utils::firstOrDefault($results, '$v->iduser === ' . $iduser);
        return isset($user);
    }

    public function getprojectowners() {
        $project = (new \model\project)->getproject($this->src->idproject);
        if (!isset($project))
            return false;

        $project->isowner = $this->isprojectowner();
        $project->isrole = \model\env::isUserAllow($this->src->idproject, \model\project::ROLE_PROJECT);

        $users = (new \model\project)->getprojectusers($this->src->idproject);

        $project->users = $this->getRecords('SELECT iduser, createdon FROM projectowner');
        foreach ($project->users as $result) {
            $result->name = '';
            $result->email = '';
            $user = \model\utils::firstOrDefault($users, '$v->iduser === ' . $result->iduser);
            if (isset($user)) {
                $result->name = $user->name;
                $result->email = $user->email;
            }
        }

        return $project;
    }

    public function setprojectowner($iduser) {
        if (!(new \model\user)->isActive($iduser)) {
            \model\message::render(\model\lexi::get('', 'sys070', $iduser));
            return false;
        }

        $result = $this->getRecord('SELECT iduser FROM projectowner WHERE iduser = ?', (int) $iduser);

        //  register
        if (isset($result))
            return false;

        $lastrowid = $this->executeSql('INSERT INTO projectowner (iduser) VALUES (?)', (int) $iduser);

        if ($lastrowid > 0) {
            $texto = \model\lexi::get('', 'sys057', $this->src->idproject, $iduser);
            $this->addSystemNote($texto);
        }

        return $lastrowid;
    }

    public function setprojectownerdelete($iduser) {
        if (!$this->isprojectowner())
            return false;

        $this->executeSql('DELETE FROM projectowner WHERE iduser = ?', (int) $iduser);
    }

    public function isDataShared($actionname) {
        $shared = $this->getRecords('SELECT setname FROM sharedata');
        return count(\model\utils::filter($shared, \model\utils::format('$v->setname === "{0}"', $actionname))) > 0;
    }

    public function getSharedProjectModule($modulename, $idproject) {
        return $this->getRecord('SELECT setname,deleted,createon,idsharedataproj,inactiveon,requirerefresh FROM sharedataproj WHERE setname = ? AND idproject = ?', (string) $modulename, (int) $idproject);
    }

    public function getSharedProjectModuleData($idproject, $modulename) {
        return $this->getRecord('SELECT sharedataproj.deleted,sharedataproj.requirerefresh FROM sharedata LEFT JOIN sharedataproj USING ( setname ) WHERE sharedata.setname = ? AND  ( sharedataproj.idproject = ? OR sharedataproj.idproject IS NULL )', (string) $modulename, (int) $idproject);
    }

    private function _getSharedModule($modulename) {
        return $this->getRecords('SELECT setname,deleted,createon,idproject,idsharedataproj,inactiveon,requirerefresh FROM sharedataproj WHERE setname = ?', (string) $modulename);
    }

    public function getLinkedOwner($modulename) {
        $projlist = [];

//$issharedmodule = $modelshare->isDataShared($modulename);
        $sharedatas = $this->_getSharedModule($modulename);

        foreach ($sharedatas as $sharedata) {
            $project = (new \model\project)->getproject($sharedata->idproject);

            if (!isset($project))
                continue;

            $proj = new \stdClass();
            $proj->title = $project->title;
            $proj->idproject = $sharedata->idproject;
            $proj->idsharedataproj = $sharedata->idsharedataproj;
            $proj->createon = $sharedata->createon;
            $proj->active = false;

            if (!$sharedata->deleted)
                $proj->active = true;

            $projlist[] = $proj;
        }

        return $projlist;
    }

    public function getLinkedModule($modulename) {
        $projlist = [];

        $projects = (new \model\project)->getprojects();
// do not include the current project
        $projects = \model\utils::filter($projects, '$v->idproject !== ' . $this->src->idproject);
        foreach ($projects as $project) {
// check each project for shared data related to: $modulename
            $accesstomodule = (new \model\action(\model\env::src($project->idproject)))->getSharedProjectModule($modulename, $this->src->idproject);
            if (!isset($accesstomodule))
                continue;

            if ($accesstomodule->deleted)
                continue;

            $proj = new \stdClass();
            $proj->title = $project->title;
            $proj->idproject = $project->idproject;
            //is active 
            $proj->active = true;
            $projlist[] = $proj;
        }

        return $projlist;
    }

    public function getLinkedModuleData($modulename) {
        $projlist = [];

        $projects = (new \model\project)->getprojects();
// do not include the current project
        $projects = \model\utils::filter($projects, '$v->idproject !== ' . $this->src->idproject);
        foreach ($projects as $project) {
// check each project for shared data related to: $modulename
            $accesstomodule = (new \model\action(\model\env::src($project->idproject)))->getSharedProjectModuleData($this->src->idproject, $modulename);
            if (!isset($accesstomodule))
                continue;

            if ($accesstomodule->deleted)
                continue;

            $proj = new \stdClass();
            $proj->title = $project->title;
            $proj->idproject = $project->idproject;
            //is active 
            $proj->active = true;
            $projlist[] = $proj;
        }

        return $projlist;
    }

    public function getSharedProjectsModule($modulename) {
        $isshared = false;

        $projects = (new \model\project)->getprojects();
        $datasharedprojects = $this->_getSharedModule($modulename);

        $syncallprojects = false;

        foreach ($datasharedprojects as $datasharedproject) {
            if ($datasharedproject->requirerefresh)
                $syncallprojects = true;

            $isshared = true;
            $project = \model\utils::firstOrDefault($projects, '$v->idproject === ' . $datasharedproject->idproject);

            if (isset($project))
                $datasharedproject->title = $project->title;
        }

        return $datasharedprojects;
    }

    public function deleteSharedModule($idprojectsubscriber, $modulename) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

        $result = $this->getRecord('SELECT idsharedataproj,setname,idproject,createon,deleted,inactiveon,requirerefresh FROM sharedataproj WHERE setname = ? AND idproject = ?', (string) $modulename, (int) $idprojectsubscriber);

        if (!isset($result))
            return;

//        //only delete if flag is 0
        if ($result->deleted)
            return;

        //disable data
        (new \model\action(\model\env::src($idprojectsubscriber)))->inactiveSharedLinkedData($this->src->idproject, $modulename);

        //delete previous subscriptions 
        // deleted allow owner to force not subscription
        $this->executeSql('DELETE FROM sharedataproj WHERE idsharedataproj = ?', (int) $result->idsharedataproj);
    }

    public function updateSharedModule($idprojectsubscriber, $modulename) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

        $project = (new \model\project)->getproject($this->src->idproject);

        $result = $this->getRecord('SELECT idsharedataproj,setname,idproject,createon,deleted,inactiveon,requirerefresh FROM sharedataproj WHERE setname = ? AND idproject = ?', (string) $modulename, (int) $idprojectsubscriber);

        if (isset($result))
            return;

        //add new subscription
        $lastinserted = $this->executeSql('INSERT INTO sharedataproj (deleted, idproject, setname, requirerefresh) VALUES (?, ?, ?, ?)', 0, (int) $idprojectsubscriber, (string) $modulename, 0);

        $note_text = \model\lexi::get('', 'sys040', $project->title, $modulename);
        $this->addSystemNote($note_text);

        return $lastinserted;
    }

    public function linktomodule($modulename, $args) {
//******************************************************
//@TODO warning when module been deactivated
        $this->inactiveSharedLinkedData($this->src->idproject, $modulename);
//******************************************************

        foreach ($args as $param_name => $param_val) {
            if (substr($param_name, 0, 2) === 'h-') {
                $src_idproject = (int) str_replace('h-', '', $param_name);

                $needtodelete = true;

                //check if is active
                foreach ($args as $param_name => $param_val) {
                    if (substr($param_name, 0, 2) !== 'p-')
                        continue;

                    $on_idproject = (int) str_replace('p-', '', $param_name);
                    if ($src_idproject !== $on_idproject)
                        continue;

                    (new \model\action(\model\env::src($src_idproject)))->updateSharedModule($this->src->idproject, $modulename);
                    // update any attached data
                    $this->updateSharedLinkedData($src_idproject, $modulename);

                    $needtodelete = false;
                    break;
                }

                if ($needtodelete) {
                    (new \model\action(\model\env::src($src_idproject)))->deleteSharedModule($this->src->idproject, $modulename);
                }
            }
        }
    }

    public function syncSharedData($modulename) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

//get all records projects associated with module
        $sharedataprojs = $this->_getSharedModule($modulename);

        foreach ($sharedataprojs as $sharedataproj) {
            if ($sharedataproj->requirerefresh && !$sharedataproj->deleted) {
                $this->_updateSharedProjModule($modulename, $sharedataproj->idsharedataproj, 0);

                (new \model\action(\model\env::src($sharedataproj->idproject)))->updateSharedLinkedData(\model\env::session_idproject(), $modulename);
            }
        }
    }

    public function linkmoduleowner($modulename, $isshared, $args) {
        if (!$this->isuserallow(\model\project::ROLE_SHAREDATA, self::class))
            return false;

//ON-OFF sharedata
        $this->_updateSharedData($modulename, $isshared);

//get all records projects associated with module
        $sharedataprojs = $this->_getSharedModule($modulename);

//update access to local project
        if (isset($args)) {
            foreach ($args as $param_name => $param_val) {
                if (substr($param_name, 0, 2) !== 'i-')
                    continue;

                $idsharedataproj = (int) str_replace('i-', '', $param_name);

                //find record
                $sharedataproj = \model\utils::firstOrDefault($sharedataprojs, '$v->idsharedataproj === ' . $idsharedataproj);
                if (!isset($sharedataproj))
                    continue;

                // need to be activated
                if ($sharedataproj->deleted) {
                    $this->_updateSharedProjModule($modulename, $sharedataproj->idsharedataproj, 0);

                    (new \model\action(\model\env::src($sharedataproj->idproject)))->updateSharedLinkedData(\model\env::session_idproject(), $modulename);
                }

                //force to skip delete (last step below)
                $sharedataproj->deleted = true;
            }
        }

        //inactive records
        $recordstoupdate = \model\utils::filter($sharedataprojs, '$v->deleted === false');
        foreach ($recordstoupdate as $recordtoupdate) {
            $this->_updateSharedProjModule($modulename, $recordtoupdate->idsharedataproj, 1);

            (new \model\action(\model\env::src($recordtoupdate->idproject)))->inactiveSharedLinkedData(\model\env::session_idproject(), $modulename);
        }
    }

    private function _updateSharedData($modulename, $isshared) {
        $result = $this->getRecord('SELECT setname FROM sharedata WHERE setname = ?', (string) $modulename);

        $sucess = false;

        //find if exist
        if ($isshared & !isset($result)) {
            $this->executeSql('INSERT INTO sharedata (setname) VALUES (?)', (string) $modulename);
            $sucess = true;
        }
        if (!$isshared & isset($result)) {
            $this->executeSql('DELETE FROM sharedata WHERE setname = ?', (string) $modulename);
            $sucess = true;
        }

        if ($sucess) {
            $texto = \model\lexi::get('', 'sys037', $modulename, \model\utils::formatBooleanToString($isshared));
            $this->addSystemNote($texto);
        }
    }

    private function _updateSharedProjModule($modulename, $idsharedataproj, $deleted) {
        $this->executeSql('UPDATE sharedataproj SET deleted = ?, inactiveon = ? WHERE idsharedataproj = ?', (int) $deleted, \model\utils::forDatabaseDateTime(new \DateTime()), \model\utils::formatBooleanToInt($idsharedataproj));

        $texto = \model\lexi::get('', 'sys041', $modulename, $idsharedataproj, $deleted);
        $this->addSystemNote($texto);
    }

    public function setSharedProjForRefresh($modulename, $isrefresh = false) {
        $this->executeSql('UPDATE sharedataproj SET requirerefresh = ? WHERE setname = ?', $isrefresh ? 1 : 0, (string) $modulename);
    }

    public function updateSharedLinkedData($src_idproject, $modulename) {
        if ($modulename === \model\env::MODULE_PRODUCTS)
            (new \model\ext\market\market($this->src))->updateLinkedProductData($src_idproject, $modulename);
    }

    //clear data from specific project
    public function inactiveSharedLinkedData($src_idproject, $modulename) {
        if ($modulename === \model\env::MODULE_PRODUCTS)
            (new \model\ext\market\market($this->src))->inactiveLinkedProductData($src_idproject);
    }

    public function getFilterSession($idproject) {
//prepare for display
        $assignedusernames = [];

        if (filter_input(INPUT_COOKIE, 'selectedusers' . $idproject) !== null) {
            $selectedusers = filter_input(INPUT_COOKIE, 'selectedusers' . $idproject);
            if (!empty($selectedusers)) {
                $users = explode(',', $selectedusers);
                foreach ($users as $user) {
                    $fields = explode('|', $user);

                    $user = new \stdClass;
                    $user->iduser = (int) $fields[0];
                    $user->idgroup = 0;
                    $user->name = (string) $fields[1];
                    $assignedusernames[] = $user;
                }
            }
        }

        if (filter_input(INPUT_COOKIE, 'selectedgroups' . $idproject) !== null) {
            $selectedgroups = filter_input(INPUT_COOKIE, 'selectedgroups' . $idproject);
            if (!empty($selectedgroups)) {
                $groups = explode(',', $selectedgroups);
                foreach ($groups as $group) {
                    $fields = explode('|', $group);

                    $group = new \stdClass;
                    $group->iduser = 0;
                    $group->idgroup = (int) $fields[0];
                    $group->name = (string) $fields[1];
                    $assignedusernames[] = $group;
                }
            }
        }
        return $assignedusernames;
    }

    public function getSecurityProjectRoles($seccode) {
        $localsecurity = $this->getprojectsecurity();
        $modelproject = new \model\project();

        $projectsecurity = $modelproject->getProjectRoles(\model\env::getIdUser(), $localsecurity, $seccode);
        if (isset($projectsecurity)) {
            $projectsecurity->isrole = $this->isprojectowner();
            // find users
            $projectusers = $modelproject->getprojectusers($this->src->idproject);
            $projectsecurity->projectusers = \model\utils::filter($projectusers, '$v->inactive === false');
            foreach ($projectsecurity->projectusers as $projectuser) {
                $projectuser->idsecrole = 0;
                $projectuser->idrole = 0;

                // find custom user role
                $localusersseccode = \model\utils::filter($projectsecurity->users, '$v->iduser === ' . $projectuser->iduser);
                foreach ($localusersseccode as $localuserseccode) {
                    $projectuser->idsecrole = $localuserseccode->idsecrole;
                    $projectuser->idrole = $localuserseccode->idrole;
                    $projectuser->roledescription = $localuserseccode->rolename;

                    if (!$projectsecurity->isrole)
                        $projectuser->roledescription = $newrole->name;
                }
            }
        }

        return $projectsecurity;
    }

}
