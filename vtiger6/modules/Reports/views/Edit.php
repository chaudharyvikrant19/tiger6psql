<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Reports_Edit_View extends Vtiger_Edit_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('step3');
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}

		$record = $request->get('record');
		if ($record) {
			$reportModel = Reports_Record_Model::getCleanInstance($record);
			if (!$reportModel->isEditable()) {
				throw new AppException('LBL_PERMISSION_DENIED');
			}
		}
	}

	public function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
		$viewer = $this->getViewer($request);
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);
		$primaryModule = $reportModel->getPrimaryModule();
		$primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
		if ($primaryModuleModel) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
			$permission = $userPrivilegesModel->hasModulePermission($primaryModuleModel->getId());

			if (!$permission) {
				$viewer->assign('MODULE', $primaryModule);
				$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
				$viewer->view('OperationNotPermitted.tpl', $primaryModule);
				exit;
			}
		}

		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule());
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			exit;
		}
		$this->step1($request);
	}

	function step1(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);
		if(!$reportModel->has('folderid')){
			$reportModel->set('folderid',$request->get('folder'));
		}
		$data = $request->getAll();
		foreach ($data as $name => $value) {
			$reportModel->set($name, $value);
		}
		if (!empty($record)) {
			$viewer->assign('MODE', 'edit');
		} else {
			$viewer->assign('MODE', '');
		}

		$reportModuleModel = $reportModel->getModule();
		$reportFolderModels = $reportModuleModel->getFolders();

		$relatedModules = $reportModel->getReportRelatedModules();



		foreach ($relatedModules as $primaryModule => $relatedModuleList) {
			$translatedRelatedModules = array();

			foreach($relatedModuleList as $relatedModuleName) {
				$translatedRelatedModules[$relatedModuleName] = vtranslate($relatedModuleName, $relatedModuleName);
			}
			$relatedModules[$primaryModule] = $translatedRelatedModules;
		}

		$viewer->assign('RELATED_MODULES', $relatedModules);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('REPORT_FOLDERS', $reportFolderModels);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);

		$viewer->view('Step1.tpl', $moduleName);
	}

	function step2(Vtiger_request $request) {


		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);
		if(!empty($record)) {
			$viewer->assign('SELECTED_FIELDS', $reportModel->getSelectedFields());
			$viewer->assign('SELECTED_SORT_FIELDS', $reportModel->getSelectedSortFields());
			$viewer->assign('SELECTED_CALCULATION_FIELDS', $reportModel->getSelectedCalculationFields());
		}

		$data = $request->getAll();
		foreach ($data as $name => $value) {
			$reportModel->set($name, $value);
		}
		$primaryModule = $request->get('primary_module');
		$secondaryModules = $request->get('secondary_modules');

		$reportModel->setPrimaryModule($primaryModule);
		if(!empty($secondaryModules)){
			$secondaryModules = implode(':', $secondaryModules);
			$reportModel->setSecondaryModule($secondaryModules);

			$secondaryModules = explode(':',$secondaryModules);
		}

		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('PRIMARY_MODULE',$primaryModule);
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFields());
		$viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFields());

		$viewer->assign('CALCULATION_FIELDS', $reportModel->getCalculationFields());
		$viewer->assign('MODULE', $moduleName);

		$viewer->view('step2.tpl', $moduleName);
	}

	function step3(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);
		if (!empty($record)) {
			$viewer->assign('SELECTED_STANDARD_FILTER_FIELDS', $reportModel->getSelectedStandardFilter());
			$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		}
		$data = $request->getAll();
		foreach ($data as $name => $value) {
			$reportModel->set($name, $value);
		}
		$primaryModule = $request->get('primary_module');
		$secondaryModules = $request->get('secondary_modules');
		$reportModel->setPrimaryModule($primaryModule);
		if(!empty($secondaryModules)){
			$secondaryModules = implode(':', $secondaryModules);
			$reportModel->setSecondaryModule($secondaryModules);

			$secondaryModules = explode(':',$secondaryModules);
		}

		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('PRIMARY_MODULE',$primaryModule);
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $reportModel->getPrimaryModuleRecordStructure());
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $reportModel->getSecondaryModuleRecordStructure());
		$viewer->assign('DATE_FILTERS', Vtiger_Field_Model::getDateFilterTypes());
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Vtiger_Field_Model::getAdvancedFilterOpsByFieldType());
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('step3.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.Edit1",
			"modules.$moduleName.resources.Edit2",
			"modules.$moduleName.resources.Edit3"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}