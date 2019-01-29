<?php

namespace model;

class project extends \model\dbconnect {

    public function __construct() {
        $this->src = \model\env::src(0);
        parent::__construct(\model\env::CONFIG_CORE);
    }

    const ROLE_TEAMUSERS = '005';
    const ROLE_PROJECT = '010';
    const ROLE_SHAREDATA = '065';

    public function isUserActiveProject($idproject) {
        $row = $this->getRecord('SELECT iduser FROM projectuser WHERE iduser = ? AND idproject = ? AND inactive = ?', \model\env::getIdUser(), (int) $idproject, 0);
        return isset($row);
    }

    public function getuseridrole($idproject) {
        $result = $this->getRecord('SELECT idrole FROM projectuser WHERE idproject = ? AND iduser = ? AND inactive = ?', (int) $idproject, \model\env::getIdUser(), 0);
        if (isset($result->idrole))
            return $result->idrole;

        return 99;
    }

    public function getUserSession($idproject) {
        $this->src->idproject = $idproject;

        $result = new \stdClass;
        $result->isrole = (new \model\action($this->src))->isprojectowner();
        $result->roles = $this->getRoles();
        $result->projectusers = $this->getprojectusers($idproject);
        $result->projectinvitations = $this->getinvitationsbyProject($idproject);

        foreach ($result->projectusers as $projectuser) {
            $projectuser->rolename = $this->getRole($projectuser->idrole)->name ?? '';
        }
        return $result;
    }

    public function getprojectbyuser($idproject, $iduser) {
        $this->src->idproject = $idproject;

        $result = new \stdClass;
        $result->isrole = (new \model\action($this->src))->isprojectowner();

        $result->roles = $this->getRoles();
        $result->projectuser = $this->getRecord('SELECT projectuser.iduser,projectuser.inactive,projectuser.idrole,projectuser.idaccess,user.name,user.email FROM projectuser JOIN user USING ( iduser ) WHERE projectuser.idproject = ? AND projectuser.iduser = ?', (int) $idproject, (int) $iduser);

        $result->noeditaccessid = false;
        if (($iduser === \model\env::getIdUser()) & $result->isrole)
            $result->noeditaccessid = true;

        return $result;
    }

    public function getuserrole($idproject) {
        return $this->getRecord('SELECT idrole FROM projectuser WHERE idproject = ? AND iduser = ?', (int) $idproject, \model\env::getIdUser());
    }

    public function getinvitation($idprojectinv) {
        return $this->getRecord('SELECT idrole,idproject,iduser,usernameinvited,useremailinvited FROM projectinvitation WHERE idprojectinv = ?', (int) $idprojectinv);
    }

    public function hasMultipleUsers($idproject) {
        $result = $this->getRecord('SELECT count(*) AS result FROM project JOIN projectuser USING ( idproject ) JOIN user USING ( iduser ) WHERE project.idproject = ? AND projectuser.inactive = ?', (int) $idproject, 0);
        return ($result->result ?? 0) > 1;
    }

    public function getactiveusersinproject($idproject) {
        // get user access level
        $projuser = $this->getprojectuser($idproject, \model\env::getIdUser());
        $idaccess = $projuser->idaccess ?? 3;

        // get everyone
        $results = $this->getRecords('SELECT projectuser.iduser,projectuser.idaccess,user.name,user.email FROM project JOIN projectuser USING ( idproject ) JOIN user USING ( iduser ) WHERE project.idproject = ? AND project.deleted = ? AND projectuser.inactive = ?', (int) $idproject, 0, 0);

        // filter internal users and me
        if ($idaccess === 2) {
            $results = [];
            $results1 = \model\utils::filter($results, '$v->iduser === ' . \model\env::getIdUser());
            foreach ($results1 as $result) {
                $results[] = $result;
            }
            $results2 = \model\utils::filter($results, '$v->idaccess === 1');
            foreach ($results2 as $result) {
                $results[] = $result;
            }
        }

        // filter only me
        if ($idaccess === 3)
            $results = \model\utils::filter($results, '$v->iduser === ' . \model\env::getIdUser());

        foreach ($results as $user) {
            $user->selected = false;
        }

        // add user if empty 
        if (count($results) === 0) {
            $user = new \stdClass();
            $user->iduser = \model\env::getIdUser();
            $user->idaccess = 1;
            $user->name = \model\env::getUserName();
            $user->email = \model\env::getUserEmail();
            $user->selected = true;

            $results[] = $user;
        }

        foreach ($results as $result) {
            unset($result->idaccess);
        }

        return $results;
    }

    public function getDefaultCurrency($idproject) {
        return $this->getRecord('SELECT idcurrency FROM project WHERE idproject = ?', (int) $idproject);
    }

    public function getproject($idproject) {
        return $this->getRecord('SELECT idproject,title,description,prefix,ticketseq,createdon,lastmodifiedon,startuppath,startupwidth,ispublic,marketname,remoteurl,idcurrency FROM project WHERE idproject = ? AND deleted = 0', (int) $idproject);
    }

    public function getprojectusers($idproject) {
        return $this->getRecords('SELECT projectuser.iduser,projectuser.idrole,projectuser.inactive,projectuser.idaccess,user.name,user.email,project.lastmodifiedon,projectuser.idrole AS roledescription FROM project JOIN projectuser USING ( idproject ) JOIN user USING ( iduser ) WHERE project.idproject = ? AND project.deleted = ?', (int) $idproject, 0);
    }

    public function getprojectuser($idproject, $iduser) {
        return $this->getRecord('SELECT iduser,inactive,idrole,idaccess FROM projectuser WHERE idproject = ? AND iduser = ?', (int) $idproject, (int) $iduser);
    }

    public function getUserAccessId($idproject, $iduser) {
        $projectuser = $this->getprojectuser($idproject, $iduser);
        if (isset($projectuser->idaccess))
            return $projectuser->idaccess;

        return 0;
    }

    public function getpublicprojects($search, $take) {
        $search = '%' . $search . '%';
        return $this->getRecords('SELECT idproject,title,marketname FROM project WHERE deleted = ? AND ispublic = ? AND ( title LIKE ? OR description LIKE ? OR marketname LIKE ? )  LIMIT ?', 0, 1, \trim((string) $search), \trim((string) $search), \trim((string) $search), (int) $take);
    }

    public function getactiveprojects() {
        return $this->getRecords('SELECT project.idproject,project.title,project.startupwidth,project.ispublic,project.remoteurl,user.idproject AS useridproject FROM project JOIN projectuser USING ( idproject ) JOIN user USING ( iduser ) WHERE iduser = ? AND project.deleted = ? AND projectuser.inactive = ? AND user.idproject <> projectuser.idproject ORDER BY project.title', \model\env::getIdUser(), 0, 0);
    }

    public function getprojects() {
        return $this->getRecords('SELECT project.idproject,project.title,project.startupwidth,project.ispublic,user.idproject AS useridproject FROM project JOIN projectuser USING ( idproject ) JOIN user USING ( iduser ) WHERE iduser = ? AND project.deleted = ? AND projectuser.inactive = ? ORDER BY project.title', \model\env::getIdUser(), 0, 0);
    }

    public function getinvitationsbyProject($idproject) {
        return $this->getRecords('SELECT projectinvitation.idprojectinv,projectinvitation.useremailinvited,projectinvitation.usernameinvited,projectinvitation.iduserinvited,projectinvitation.createdon,user.name AS sender FROM projectinvitation JOIN user USING ( iduser ) WHERE projectinvitation.idproject = ?', (int) $idproject);
    }

    public function searchArchivedProjects($search, $take) {
        $search = \strtolower('%' . $search . '%');
        return $this->getRecords('SELECT project.title,project.idproject,project.marketname,project.lastmodifiedon,projectuser.idrole FROM project JOIN projectuser USING ( idproject ) WHERE  ( project.title LIKE ? OR project.description LIKE ? )  AND project.deleted = ? AND projectuser.iduser = ? LIMIT ?', \trim((string) $search), \trim((string) $search), 1, \model\env::getIdUser(), $take);
    }

    public function geActiveUsersAllProjects() {
        $projects = $this->getprojects();

        $allusers = [];
        foreach ($projects as $actionproject) {
            $results = (new \model\project)->getactiveusersinproject($actionproject->idproject);
            $allusers = \YaLinqo\Enumerable::from($allusers)
                            ->union($results, '$v->iduser')->toList();
        }
        return $allusers;
    }

    public function getServiceRecordsSys($module, $modulename) {
        if (!isset($module) || !isset($modulename))
            return [];

        $filelocation = \model\utils::format('{0}/config/{1}/*/{2}.json', DATA_PATH, $module, $modulename);
        // check for project custom confg
        if (\model\env::session_idproject() > 0) {
            $filelocationback = \model\utils::format('{0}/attach/{1}/config/{2}/', DATA_PATH, \model\env::session_idproject(), $module);
            if (file_exists($filelocationback))
                $filelocation = \model\utils::format('{0}/attach/{1}/config/{2}/*/{3}.json', DATA_PATH, \model\env::session_idproject(), $module, $modulename);
        }

        return \model\utils::loadRecords($filelocation);
    }

    public function getServiceRecords($idproject, $module, $modulename, $forcedefault = false) {
        if (!isset($module) || !isset($modulename))
            return [];

        $defaultpath = \model\utils::format('{0}/config/{1}/*/', DATA_PATH, $module);
        if ($idproject > 0) {
            $defaultpathback = \model\utils::format('{0}/attach/{1}/config/{2}/', DATA_PATH, $idproject, $module);
            if(file_exists($defaultpathback))
                $defaultpath = \model\utils::format('{0}/attach/{1}/config/{2}/*/', DATA_PATH, $idproject, $module);
        }
        
        $result = $this->getRecord('SELECT idproject,name,template,createdon,lastmodifiedon FROM projectservice WHERE idproject = ? AND name = ?', (int) $idproject, (string) $modulename);
        $servicetemplate = isset($result) ? \trim($result->template) : false;

        if ($forcedefault && $servicetemplate === false)
            return \model\utils::loadRecords(\model\utils::format('{0}{1}.json', $defaultpath, $modulename));

        if ($servicetemplate === false)
            return [];

        return \model\utils::loadRecords($defaultpath . $servicetemplate);
    }

    public function getService($idproject, $modulename) {
        return $this->getRecord('SELECT idproject,name,template,createdon,lastmodifiedon FROM projectservice WHERE idproject = ? AND name = ?', (int) $idproject, (string) $modulename);
    }

    private $langcache;

    public function getLang($idlang) {
        if (isset($idlang)) {
            $idlang = \trim(\strtolower($idlang));
            if (!isset($this->langcache))
                $this->getLangs();

            return \model\utils::firstOrDefault($this->langcache, \model\utils::format('$v->idlang === "{0}"', $idlang));
        }
        return null;
    }

    public function getLangs() {
        if (!isset($this->langcache))
            $this->langcache = $this->getServiceRecordsSys(\model\env::CONFIG_CORE, \model\env::MODULE_LANGUAGE);

        return $this->langcache;
    }

    private $themecache;

    public function getTheme($idtheme) {
        if (isset($idtheme)) {
            if (!isset($this->themecache))
                $this->getThemes();

            return \model\utils::firstOrDefault($this->themecache, '$v->idtheme === ' . $idtheme);
        }
        return null;
    }

    public function getThemes() {
        if (!isset($this->themecache))
            $this->themecache = $this->getServiceRecordsSys(\model\env::CONFIG_CORE, \model\env::MODULE_THEME);

        return $this->themecache;
    }

    private $rolecache;

    public function getRole($idrole) {
        if ($idrole > 0) {
            if (!isset($this->rolecache))
                $this->getRoles();

            return \model\utils::firstOrDefault($this->rolecache, '$v->idrole === ' . $idrole);
        }
        return null;
    }

    public function getRoles() {
        if (!isset($this->rolecache))
            $this->rolecache = $this->getServiceRecordsSys(\model\env::CONFIG_CORE, \model\env::MODULE_ROLE);

        return $this->rolecache;
    }

    public function getProjectRoles($iduser, $localsecurity, $seccode = null) {
        $roles = $this->getRoles();
// get project specific security
        foreach ($roles as $role) {
            if (!isset($role->secs))
                continue;

            foreach ($role->secs as $sec) {
                // get only security code:  seccode        
                $sec->idrole = $role->idrole;
                $sec->rolename = $role->name;
                $sec->users = [];

// overide master security from project
                $localprojsecurity = \model\utils::filter($localsecurity, \model\utils::format('$v->seccode === "{0}"', $sec->seccode));
                foreach ($localprojsecurity as $row) {
                    $newrole = \model\utils::firstOrDefault($roles, '$v->idrole === ' . $row->idrole);
                    if (isset($seccode)) {
                        $user = new \stdClass();
                        $user->iduser = $row->iduser;
                        $user->idrole = $row->idrole;
                        $user->rolename = $newrole->name ?? '';
                        $sec->users[] = $user;
                    }
                    //add users security if onnly seccode needed
                    if (($row->iduser === 0 || $row->iduser === $iduser) & $sec->idrole !== $row->idrole) {
                        //get new info role info
                        $sec->localidrole = $row->idrole;
                        $sec->localrolename = $newrole->name ?? '';
                    }
                }

                // stop if onnly seccode needed
                // @TODO improve search speed in nested data
                if (isset($seccode) && $seccode === $sec->seccode)
                    break;
            }
        }

        // get only security code:  seccode        
        if (isset($seccode)) {
            foreach ($roles as $role) {
                if (isset($role->secs)) {
                    $security = \model\utils::firstOrDefault($role->secs, \model\utils::format('$v->seccode === "{0}"', $seccode));
                    if (isset($security))
                        return $security;
                }
            }
            return null;
        }

        return $roles;
    }

    public function getSecurityLevels() {
        $roles = $this->getRoles();

        $seclevels = [];
        foreach ($roles as $role) {
            if (!isset($role->secs))
                continue;

            foreach ($role->secs as $sec) {
                // get only security code:  seccode        
                $seclevel = new \stdClass();
                $seclevel->idrole = $role->idrole;
                $seclevel->seccode = $sec->seccode;
                $seclevel->idaccess = 1;

                $seclevels[] = $seclevel;
            }
        }

        return $seclevels;
    }

    public function joinproject($idproject) {

        if (!(new \model\user)->isActive())
            return false;

        $user = $this->getRecord('SELECT name FROM user WHERE iduser = ?', \model\env::getIdUser());
        if (!isset($user))
            return false;

        $result = $this->getRecord('SELECT idproject FROM projectuser WHERE idproject = ? AND iduser = ?', (int) $idproject, \model\env::getIdUser());
        if (isset($result))
            return false;

        //  register user under project
        $lastrowid = $this->executeSql('INSERT INTO projectuser (idproject, idrole, iduser, idaccess) VALUES (?, ?, ?, ?)', (int) $idproject, 4, \model\env::getIdUser(), 3);
        if ($lastrowid == 0)
            return false;

        $texto = \model\lexi::get('', 'sys065', $user->name);
        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote($texto);

        return $lastrowid;
    }

    public function insertproject($newproj) {
        // ignore package when web link is provided
        if (!empty($newproj->startuppath))
            $newproj->startuppath = "";

        //register project
        $lastrowid = $this->executeSql('INSERT INTO project (title, description, prefix, ticketseq, startuppath, startupwidth, ispublic, marketname, deleted, remoteurl, idcurrency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', trim((string) $newproj->title), trim((string) $newproj->description), trim((string) $newproj->prefix), trim((int) $newproj->ticketseq), trim((string) $newproj->startuppath), (int) $newproj->startupwidth, \model\utils::formatBooleanToInt($newproj->ispublic), trim((string) $newproj->marketname), 0, trim((string) $newproj->remoteurl), trim((string) $newproj->idcurrency));

//        //register user default
        $this->executeSql('INSERT INTO projectuser (idproject, iduser, idrole) VALUES (?, ?, ?)', (int) $lastrowid, \model\env::getIdUser(), 1);

        //create owner
        $this->src->idproject = $lastrowid;
        (new \model\action($this->src))->setprojectowner(\model\env::getIdUser());

        return $lastrowid;
    }

    public function updateproject($idproject, $updateproj) {
        if (!\model\env::isUserAllow($idproject, self::ROLE_PROJECT))
            return false;

        $this->executeSql('UPDATE project SET title = ?, description = ?, prefix = ?, ticketseq = ?, startuppath = ?, startupwidth = ?, ispublic = ?, marketname = ?, remoteurl = ?, idcurrency = ? WHERE idproject = ?', trim((string) $updateproj->title), trim((string) $updateproj->description), trim((string) $updateproj->prefix), trim((int) $updateproj->ticketseq), trim((string) $updateproj->startuppath), trim((string) $updateproj->startupwidth), \model\utils::formatBooleanToInt($updateproj->ispublic), trim((string) $updateproj->marketname), trim((string) $updateproj->remoteurl), trim((string) $updateproj->idcurrency), (int) $idproject);

        $texto = \model\lexi::get('', 'sys061', $updateproj->title);
        // do not broadcast changes to users, there's no sync while looping and sending messages 

        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote($texto);
    }

    public function deleteproject($idproject, $emailadvicefilename) {
        if (!\model\env::isUserAllow($idproject, self::ROLE_PROJECT))
            return false;

        $project = $this->getproject($idproject);
        if (!isset($project))
            return false;

        $this->executeSql('UPDATE project SET deleted = 1 WHERE idproject = ?', (int) $idproject);

        // send message to project users
        $statusname = \model\lexi::get('g3/project', 'sys024');
        $emailsubject = $statusname . ': ' . $project->title;
// get email string
        $activeusers = $this->getactiveusersinproject($idproject);

        $filename = \model\route::render($emailadvicefilename);
        foreach ($activeusers as $activeuser) {

            $emailstring = array();

            $lines = file($filename);
            foreach ($lines as $line) {
                $line = str_replace('[membername]', $activeuser->name, $line);
                $line = str_replace('[projectname]', $project->title, $line);
                $line = str_replace('[statusname]', $statusname, $line);
                $emailstring[] = $line;
            }

            \model\env::sendMail($activeuser->name, $activeuser->email, $emailsubject, $emailstring);
        }

        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote($statusname);
    }

    public function registerinvitation($idproject, $username, $targetiduser, $memberrole, $emailadvicefilename) {
        if (!\model\env::isUserAllow($idproject, self::ROLE_TEAMUSERS))
            return false;

        if ($targetiduser === 0)
            return false;

        if ($memberrole === 0)
            return false;

        $user = (new \model\user)->getuser($targetiduser);
        if (!isset($user))
            \model\message::render(\model\lexi::get('g3/project', 'sys067'));

        $projecttitle = $this->getproject($idproject)->title;
        $subject = \model\utils::format(\model\lexi::get('g3/project', 'sys071'), $username, $projecttitle);
        $filename = \model\route::render($emailadvicefilename);

        $mailbody = [];
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = str_replace('[membername]', $user->name, $line);
            $line = str_replace('[username]', $username, $line);
            $mailbody[] = str_replace('[projectdescription]', $projecttitle, $line);
        }

        \model\env::sendMail($user->name, $user->email, $subject, $mailbody);

        $this->startTransaction();
        //delete previous invitations
        $this->executeSql('DELETE FROM projectinvitation WHERE idproject = ? AND iduserinvited = ?', (int) $idproject, (int) $targetiduser);
        //register email invitation, this record will remain until user accept or revoke
        $this->executeSql('INSERT INTO projectinvitation (idproject, iduser, iduserinvited, usernameinvited, useremailinvited, idrole, securekey) VALUES (?, ?, ?, ?, ?, ?, ?)', (int) $idproject, $this->src->iduser, (int) $targetiduser, (string) $user->name, (string) $user->email, (int) $memberrole, "");
        $this->endTransaction();

        \model\message::render(\model\utils::format(\model\lexi::get('g3/project', 'sys068'), $user->name, $user->email));
    }

    public function insertprojuserinvitation($idprojectinv) {
        if ($idprojectinv === 0)
            return false;

        // Register users under idaccess = 2, => users are external to the company
        $projectinvitation = $this->getinvitation($idprojectinv);
        if (!isset($projectinvitation))
            return false;

        $result = $this->getRecord('SELECT idproject FROM projectuser WHERE idproject = ? AND iduser = ?', (int) $projectinvitation->idproject, \model\env::getIdUser());

        // if not registered
        if (!isset($result)) {
            $this->executeSql('INSERT INTO projectuser (idproject, idrole, iduser, idaccess) VALUES (?, ?, ?, ?)', (int) $projectinvitation->idproject, (int) $projectinvitation->idrole, \model\env::getIdUser(), 2);
        } else {
            $this->executeSql('UPDATE projectuser SET idrole = ? WHERE idproject = ? AND iduser = ?', (int) $projectinvitation->idrole, (int) $projectinvitation->idproject, \model\env::getIdUser());
        }

        //all good, remove invitation
        $this->executeSql('DELETE FROM projectinvitation WHERE idprojectinv =  ?', (int) $idprojectinv);

        $texto = \model\lexi::get('', 'sys064', $projectinvitation->usernameinvited, $projectinvitation->useremailinvited);

        $this->src->idproject = $projectinvitation->idproject;
        (new \model\action($this->src))->addSystemNote($texto);
    }

    public function removeinvitation($idprojectinv) {
        if (!\model\env::isUserAllow($idprojectinv, self::ROLE_TEAMUSERS))
            return false;

        if ($idprojectinv === 0)
            return false;

        $invitation = $this->getRecord('SELECT idprojectinv,idproject,iduser,iduserinvited,usernameinvited,useremailinvited,idrole,createdon,securekey FROM projectinvitation WHERE projectinvitation.idprojectinv = ?', (int) $idprojectinv);

        $this->executeSql('DELETE FROM projectinvitation WHERE idprojectinv = ?', (int) $idprojectinv);

        $texto = \model\lexi::get('', 'sys063', $idprojectinv, $invitation->iduserinvited);

        $tis->src->idproject = $invitation->idproject;
        (new \model\action($this->src))->addSystemNote($texto);
    }

    public function insertprojectrole($idproject, $targetiduser, $idrole, $inactive, $idaccess) {
        if (!\model\env::isUserAllow($idproject, self::ROLE_TEAMUSERS))
            return false;

        if ($targetiduser === 0)
            return false;

        if ($idrole === 0)
            return false;

        $result = $this->getRecord('SELECT idproject,iduser,idrole,inactive,idaccess,createdon FROM projectuser WHERE idproject = ? AND iduser = ?', (int) $idproject, (int) $targetiduser);

        if (isset($result))
            return false;

        $lastinsertedid = $this->executeSql('INSERT INTO projectuser (idproject,iduser,idrole,inactive,idaccess) VALUES (?, ?, ?, ?, ?)', (int) $idproject, (int) $targetiduser, (int) $idrole, \model\utils::formatBooleanToInt($inactive), (int) $idaccess);

        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys066'));

        return $lastinsertedid;
    }

    public function updateprojectrole($idproject, $targetiduser, $idrole, $inactive, $idaccess) {
        if (!\model\env::isUserAllow($idproject, self::ROLE_TEAMUSERS))
            return false;

        if ($targetiduser === 0)
            return false;

        if ($idrole === 0)
            return false;

        $this->executeSql('UPDATE projectuser SET idrole = ?, inactive = ?, idaccess = ? WHERE idproject = ? AND iduser = ?', (int) $idrole, \model\utils::formatBooleanToInt($inactive), (int) $idaccess, (int) $idproject, (int) $targetiduser);

        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys066'));
    }

    public function restoreproject($idproject, $emailadvicefilename) {
        $result = $this->getRecord('SELECT count(*) AS result FROM project JOIN projectuser USING ( idproject ) WHERE project.deleted = ? AND project.idproject = ? AND projectuser.iduser = ? AND projectuser.idrole = ?', 1, (int) $idproject, \model\env::getIdUser(), 1);
        if (($result->result ?? 0) === 0)
            return false;

        $this->executeSql('UPDATE project SET deleted = 0 WHERE idproject = ?', (int) $idproject);

        $project = $this->getproject($idproject);
        $activeusers = $this->getactiveusersinproject($idproject);

        // send message to project users
        $statusname = \model\lexi::get('g3/project', 'sys114');
        $emailsubject = $statusname . ': ' . $project->title;
// get email string
        $filename = \model\route::render($emailadvicefilename);

        foreach ($activeusers as $activeuser) {
            $emailstring = array();

            $lines = file($filename);
            foreach ($lines as $line) {
                $line = str_replace('[membername]', $activeuser->name, $line);
                $line = str_replace('[projectname]', $project->title, $line);
                $line = str_replace('[statusname]', $statusname, $line);
                $emailstring[] = $line;
            }

            \model\env::sendMail($activeuser->name, $activeuser->email, $emailsubject, $emailstring);
        }

        $this->src->idproject = $idproject;
        (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys069'));
    }

    public function deleteService($idproject, $modulename) {
        $this->src->idproject = $idproject;
        if (!(new \model\action($this->src))->isprojectowner())
            return false;

        $this->executeSql('DELETE FROM projectservice WHERE idproject = ? AND name = ?', (int) $idproject, (string) $modulename);

        (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys042', $modulename));
    }

    public function updateService($idproject, $modulename, $template) {
        $this->src->idproject = $idproject;
        if (!(new \model\action($this->src))->isprojectowner())
            return false;

        $result = $this->getService($idproject, $modulename);
        if (isset($result))
            $this->executeSql('UPDATE projectservice SET template = ? WHERE idproject = ? AND name = ?', (string) $template, (int) $idproject, (string) $modulename);

        if (!isset($result))
            $this->executeSql('INSERT INTO projectservice (idproject, name, template) VALUES (?, ?, ?)', (int) $idproject, (string) $modulename, (string) $template);

        (new \model\action($this->src))->addSystemNote(\model\lexi::get('', 'sys043', $modulename, $template));
    }

    public function setuseridproject($idproject) {
        $this->executeSql('UPDATE user SET idproject = ? WHERE iduser = ? AND idproject = 0 AND deleted = 0', (int) $idproject, \model\env::getIdUser());
    }

    public function getTotalWaitingMessages() {
        $totalUnreadMessages = 0;

        $projects = $this->getprojects();
        foreach ($projects as $project)
            $totalUnreadMessages += (new \model\action(\model\env::src($project->idproject)))->totalUnreadMessages();

        return $totalUnreadMessages;
    }

    public function getProjectsTags() {
        $actions = [];
        foreach ($this->getprojects() as $project) {
            $results = (new \model\action(\model\env::src($project->idproject)))->getSummaryTags();
            foreach ($results as $result) {
                $actions[] = $result;
            }
        }

        return $actions;
    }

    public function getMenuProject($idproject) {
        $result = new \stdClass();

        $result->isrole = \model\env::isUserAllow($idproject, \model\project::ROLE_TEAMUSERS);
        $result->allownewaction = $result->isrole;
        $result->allowsetup = $result->isrole && $idproject !== 0;
        $result->allowmessg = $idproject !== 0;
        $result->allowfilter = true;

        $result->project = $this->getproject($idproject);

        if (isset($result->project)) {
            if (!empty(\trim($result->project->startuppath)))
                $result->allowfilter = false;

            if ($result->allownewaction && !empty(\trim($result->project->startuppath)))
                $result->allownewaction = false;
        }

        $result->assignedusernames = (new \model\action(\model\env::src($idproject)))->getFilterSession($idproject);
        if (count($result->assignedusernames) === 0)
            unset($result->assignedusernames);

//change tab when open user project
        $result->useridproject = $idproject === 0 ? \model\env::getUserIdProject() : $idproject;

        return $result;
    }

}
