<?php
/*************************************************************************************************
 * Copyright 2020 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS Customizations.
* Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
* file except in compliance with the License. You can redistribute it and/or modify it
* under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
* granted by the License. coreBOS distributed by JPL TSolucio S.L. is distributed in
* the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
* applicable law or agreed to in writing, software distributed under the License is
* distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing
* permissions and limitations under the License. You may obtain a copy of the License
* at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
*************************************************************************************************/
require_once 'include/events/include.inc';

class activatePortalFieldsEmployee extends cbupdaterWorker {

	public function applyChange() {
		global $adb;
		if ($this->hasError()) {
			$this->sendError();
		}
		if ($this->isApplied()) {
			$this->sendMsg('Changeset '.get_class($this).' already applied!');
		} else {
			global $adb;
			$result = $adb->pquery('SELECT operationid FROM vtiger_ws_operation WHERE name=?', array('loginPortal'));
			if ($result) {
				$operationid = $adb->query_result($result, 0, 'operationid');
				if (isset($operationid)) {
					$chkrs = $adb->pquery('SELECT 1 FROM vtiger_ws_operation_parameters WHERE operationid=? and name=?', array($operationid, 'entity'));
					if ($chkrs && $adb->num_rows($chkrs)==0) {
						$this->ExecuteQuery("INSERT INTO vtiger_ws_operation_parameters (operationid, name, type, sequence) VALUES ($operationid, 'entity', 'String', 3);");
					}
				}
			}
			// enlarge password field
			$this->ExecuteQuery(
				'ALTER TABLE `vtiger_portalinfo` CHANGE `user_password` `user_password` VARCHAR(12550) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;',
				array()
			);
			// enlarge password field
			$this->ExecuteQuery(
				'ALTER TABLE vtiger_portalinfo DROP FOREIGN KEY fk_1_vtiger_portalinfo;',
				array()
			);
			// add new fields
			$fieldLayout=array(
				'cbEmployee' => array(
					'LBL_EMPLOYEE_PORTAL_INFORMATION' => array(
						'portal' => array(
							'label' => 'portal',
							'columntype'=>'varchar(3)',
							'typeofdata'=>'V~O',
							'uitype'=>'56',
							'displaytype'=>'1',
						),
						'support_start_date' => array(
							'label' => 'support_start_date',
							'columntype'=>'date',
							'typeofdata'=>'D~O',
							'uitype'=>'5',
							'displaytype'=>'1',
						),
						'template_language' => array(
							'label' => 'template_language',
							'columntype'=>'varchar(6)',
							'typeofdata'=>'V~O',
							'uitype'=>'15',
							'displaytype'=>'1',
							'vals' => array('de','en','es','fr','hu','it','nl','pt','ro'),
						),
						'support_end_date' => array(
							'label' => 'support_end_date',
							'columntype'=>'date',
							'typeofdata'=>'D~O',
							'uitype'=>'5',
							'displaytype'=>'1',
						),
						'portalpasswordtype' => array(
							'label' => 'portalpasswordtype',
							'columntype'=>'varchar(26)',
							'typeofdata'=>'V~O',
							'uitype'=>'16',
							'displaytype'=>'1',
							'vals' => array(
								'sha512',
								'sha256',
								'md5',
								'plaintext',
							)
						),
						'portalloginuser' => array(
							'label' => 'portalloginuser',
							'columntype'=>'int(11)',
							'typeofdata'=>'I~O',
							'uitype'=>'77',
							'displaytype'=>'1',
						),
					),
				),
			);
			$this->massCreateFields($fieldLayout);
			// activate widget
			$module = Vtiger_Module::getInstance('cbEmployee');
			if ($module) {
				$module->addLink('DETAILVIEWWIDGET', 'PortalUserPasswordManagement', 'module=Contacts&action=ContactsAjax&file=PortalUserPasswordManagement&recordid=$RECORD$');
			}
			$em = new VTEventsManager($adb);
			$em->registerHandler('vtiger.entity.aftersave.final', 'modules/cbEmployee/EmployeeHandler.php', 'EmployeesPortalLoginDetailsHandler');
			$this->sendMsg('Changeset '.get_class($this).' applied!');
			$this->markApplied();
		}
		$this->finishExecution();
	}

	public function undoChange() {
		global $adb;
		if ($this->isBlocked()) {
			return true;
		}
		if ($this->hasError()) {
			$this->sendError();
		}
		if ($this->isSystemUpdate()) {
			$this->sendMsg('Changeset '.get_class($this).' is a system update, it cannot be undone!');
		} else {
			if ($this->isApplied()) {
				$fieldLayout=array(
					'Contacts' => array(
						'portal',
						'support_start_date',
						'template_language',
						'support_end_date',
						'portalpasswordtype',
						'portalloginuser',
					),
				);
				$this->massHideFields($fieldLayout);
				$module = Vtiger_Module::getInstance('cbEmployee');
				if ($module) {
					$module->deleteLink('DETAILVIEWWIDGET', 'PortalUserPasswordManagement', 'module=Contacts&action=ContactsAjax&file=PortalUserPasswordManagement&recordid=$RECORD$');
				}
				$em = new VTEventsManager($adb);
				$em->unregisterHandler('EmployeesPortalLoginDetailsHandler');
				$this->sendMsg('Changeset '.get_class($this).' undone!');
				$this->markUndone();
			} else {
				$this->sendMsg('Changeset '.get_class($this).' not applied!');
			}
		}
		$this->finishExecution();
	}
}