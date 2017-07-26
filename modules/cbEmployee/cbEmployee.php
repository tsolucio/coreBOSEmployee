<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class cbEmployee extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'vtiger_cbemployee';
	var $table_index= 'cbemployeeid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;
	var $HasDirectImageField = false;
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_cbemployeecf', 'cbemployeeid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_cbemployee', 'vtiger_cbemployeecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity'    => 'crmid',
		'vtiger_cbemployee'   => 'cbemployeeid',
		'vtiger_cbemployeecf' => 'cbemployeeid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbemployeeno'=> Array('cbemployee'=> 'cbemployeeno'),
		'nombre'      => Array('cbemployee'=> 'nombre'),
		'altaempresa' => Array('cbemployee'=> 'altaempresa'),
		'mobile_phone'=> Array('cbemployee'=> 'mobile_phone'),
		'work_phone'  => Array('cbemployee'=> 'work_phone'),
		'work_email'  => Array('cbemployee'=> 'work_email'),
		'Assigned To' => Array('crmentity' =>'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'cbemployeeno'=> 'cbemployeeno',
		'nombre'      => 'nombre',
		'altaempresa' => 'altaempresa',
		'mobile_phone'=> 'mobile_phone',
		'work_phone'  => 'work_phone',
		'work_email'  => 'work_email',
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'nombre';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbemployeeno'=> Array('cbemployee'=> 'cbemployeeno'),
		'nombre'      => Array('cbemployee'=> 'nombre'),
		'altaempresa'  => Array('cbemployee'=> 'altaempresa'),
		'nss'         => Array('cbemployee'=> 'nss'),
		'nif'         => Array('cbemployee'=> 'nif'),
		'work_email'  => Array('cbemployee'=> 'work_email'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'cbemployeeno'=> 'cbemployeeno',
		'nombre'      => 'nombre',
		'altaempresa' => 'altaempresa',
		'nss'         => 'nss',
		'nif'         => 'nif',
		'work_email'  => 'work_email',
	);

	// For Popup window record selection
	var $popup_fields = Array('nombre');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'nombre';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'nombre';

	// Required Information for enabling Import feature
	var $required_fields = Array('nombre'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'nombre';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('nombre');

	function save_module($module) {
		global $adb;
		$query = "update vtiger_cbemployee set age=DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birthday, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birthday, '00-%m-%d')) where cbemployeeid={$this->id}";
		$adb->query($query);
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id,$module);
		}
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
			$this->setModuleSeqNumber('configure', $modulename, $modulename.'-', '0000001');
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
			global $adb;
			$adb->query('ALTER TABLE vtiger_cbemployee CHANGE `birthdate` `birthday` DATE NULL DEFAULT NULL');
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }
}
?>
