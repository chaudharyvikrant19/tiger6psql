<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Home_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function returns the default view for the Home module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel) {
		$db = PearDatabase::getInstance();

		$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');

		$result = $db->pquery('SELECT *, vtiger_crmentity.createdtime AS createdtime, vtiger_crmentity.smownerid AS smownerid,
						crmentity2.crmid AS parentId, crmentity2.setype AS parentModule FROM vtiger_modcomments
						INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
							AND vtiger_crmentity.deleted = 0
						INNER JOIN vtiger_crmentity crmentity2 ON vtiger_modcomments.related_to = crmentity2.crmid
							AND crmentity2.deleted = 0
						 '.$nonAdminAccessQuery.'
						ORDER BY vtiger_crmentity.crmid DESC OFFSET ? LIMIT ?',
				array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$comments = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
			$commentModel->setData($row);
			$time = $commentModel->get('createdtime');
			$comments[$time] = $commentModel;			
		}

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type=false) {
		if(empty($type)) {
			$type = 'all';
		}
		//TODO: need to handle security
		$comments = array();
		if($type == 'all' || $type == 'comments') {
			$comments = $this->getComments($pagingModel);
			if($type == 'comments') {
				return $comments;
			}
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT vtiger_modtracker_basic.*
								FROM vtiger_modtracker_basic
								INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
									AND deleted = 0
								ORDER BY vtiger_modtracker_basic.id DESC OFFSET ? LIMIT ?',
				array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$activites = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['module'], 'DetailView', $row['crmid'])){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($row['crmid'], $row['module']);
				$time = $modTrackerRecorModel->get('changedon');
				$activites[$time] = $modTrackerRecorModel;
			}
		}

		$history = array_merge($activites, $comments);

		foreach($history as $time=>$model) {
			$dateTime[] = $time;
		}

		if(!empty($history)) {
			array_multisort($dateTime,SORT_DESC,SORT_STRING,$history);
			return $history;
		}
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $recordId = false) {
		$db = PearDatabase::getInstance();

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_activity.* FROM vtiger_activity
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE vtiger_crmentity.deleted=0
					AND (vtiger_activity.activitytype NOT IN ('Emails'))
					AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held'))";
		
		if ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$query .= " ORDER BY date_start, time_start OFFSET ? LIMIT ?";

		$result = $db->pquery($query, array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()+1));
		$numOfRows = $db->num_rows($result);

		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
			$model->setId($row['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}
}
