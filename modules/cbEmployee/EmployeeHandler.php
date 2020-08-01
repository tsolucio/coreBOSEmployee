<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmployeesPortalLoginDetailsHandler extends VTEventHandler {

	public function handleEvent($eventName, $entityData) {
		global $adb;
		if ($eventName == 'vtiger.entity.aftersave.final') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'cbEmployee') {
				$entityId = $entityData->getId();
				if (strpos($entityId, 'x')>0) {
					$parts = explode('x', $entityId);
					$entityId = $parts[1];
				}
				$email = $entityData->get('work_email');
				if (!empty($email)) {
					if ($entityData->get('portal') == 'on' || $entityData->get('portal') == '1') {
						$result = $adb->pquery('SELECT id FROM vtiger_portalinfo WHERE id=?', array($entityId));
						if ($adb->num_rows($result) == 0) {
							$adb->pquery(
								'INSERT INTO vtiger_portalinfo(id,user_name,user_password,type,isactive) VALUES(?,?,?,?,?)',
								array($entityId, $email, '', 'E', 1)
							);
						} else {
							$adb->pquery('UPDATE vtiger_portalinfo SET user_name=?, isactive=1 WHERE id=?', array($email, $entityId));
						}
					} else {
						$adb->pquery('UPDATE vtiger_portalinfo SET user_name=?, isactive=0 WHERE id=?', array($email, $entityId));
					}
				}
			}
		}
	}
}
?>
