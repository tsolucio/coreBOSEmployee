<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';

class cbEmployee extends CRMEntity {
	public $table_name = 'vtiger_cbemployee';
	public $table_index= 'cbemployeeid';
	public $column_fields = array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	public $HasDirectImageField = false;
	public $moduleIcon = array('library' => 'standard', 'containerClass' => 'slds-icon_container slds-icon-standard-account', 'class' => 'slds-icon', 'icon'=>'contact');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = array('vtiger_cbemployeecf', 'cbemployeeid');
	// related_tables variable should define the association (relation) between dependent tables
	// FORMAT: related_tablename => array(related_tablename_column[, base_tablename, base_tablename_column[, related_module]] )
	// Here base_tablename_column should establish relation with related_tablename_column
	// NOTE: If base_tablename and base_tablename_column are not specified, it will default to modules (table_name, related_tablename_column)
	// Uncomment the line below to support custom field columns on related lists
	// public $related_tables = array('vtiger_MODULE_NAME_LOWERCASEcf' => array('MODULE_NAME_LOWERCASEid', 'vtiger_MODULE_NAME_LOWERCASE', 'MODULE_NAME_LOWERCASEid', 'MODULE_NAME_LOWERCASE'));

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = array('vtiger_crmentity', 'vtiger_cbemployee', 'vtiger_cbemployeecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_cbemployee'   => 'cbemployeeid',
		'vtiger_cbemployeecf' => 'cbemployeeid',
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = array (
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbemployeeno'=> array('cbemployee'=> 'cbemployeeno'),
		'nombre'      => array('cbemployee'=> 'nombre'),
		'altaempresa' => array('cbemployee'=> 'altaempresa'),
		'mobile_phone'=> array('cbemployee'=> 'mobile_phone'),
		'work_phone'  => array('cbemployee'=> 'work_phone'),
		'work_email'  => array('cbemployee'=> 'work_email'),
		'Assigned To' => array('crmentity' =>'smownerid')
	);
	public $list_fields_name = array(
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
	public $list_link_field = 'nombre';

	// For Popup listview and UI type support
	public $search_fields = array(
		/* Format: Field Label => Array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbemployeeno'=> array('cbemployee'=> 'cbemployeeno'),
		'nombre'      => array('cbemployee'=> 'nombre'),
		'altaempresa'  => array('cbemployee'=> 'altaempresa'),
		'nss'         => array('cbemployee'=> 'nss'),
		'nif'         => array('cbemployee'=> 'nif'),
		'work_email'  => array('cbemployee'=> 'work_email'),
	);
	public $search_fields_name = array(
		/* Format: Field Label => fieldname */
		'cbemployeeno'=> 'cbemployeeno',
		'nombre'      => 'nombre',
		'altaempresa' => 'altaempresa',
		'nss'         => 'nss',
		'nif'         => 'nif',
		'work_email'  => 'work_email',
	);

	// For Popup window record selection
	public $popup_fields = array('nombre');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	public $sortby_fields = array();

	// For Alphabetical search
	public $def_basicsearch_col = 'nombre';

	// Column value to use on detail view record text display
	public $def_detailview_recname = 'nombre';

	// Required Information for enabling Import feature
	public $required_fields = array('nombre'=>1);

	// Callback function list during Importing
	public $special_functions = array('set_import_assigned_user');

	public $default_order_by = 'nombre';
	public $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = array('nombre');

	public function save_module($module) {
		global $adb;
		$query = "update vtiger_cbemployee set age=DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birthday, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birthday, '00-%m-%d')) where cbemployeeid={$this->id}";
		$adb->query($query);
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id, $module);
		}
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string Module name
	 * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type) {
		if ($event_type == 'module.postinstall') {
			// Handle post installation actions
			$this->setModuleSeqNumber('configure', $modulename, $modulename.'-', '0000001');
		} elseif ($event_type == 'module.disabled') {
			// Handle actions when this module is disabled.
		} elseif ($event_type == 'module.enabled') {
			// Handle actions when this module is enabled.
		} elseif ($event_type == 'module.preuninstall') {
			// Handle actions when this module is about to be deleted.
		} elseif ($event_type == 'module.preupdate') {
			// Handle actions before this module is updated.
		} elseif ($event_type == 'module.postupdate') {
			// Handle actions after this module is updated.
			global $adb;
			$adb->query('ALTER TABLE vtiger_cbemployee CHANGE `birthdate` `birthday` DATE NULL DEFAULT NULL');
		}
	}
}
?>
