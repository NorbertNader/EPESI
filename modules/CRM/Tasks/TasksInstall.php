<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-crm
 * @subpackage tasks
 */

defined("_VALID_ACCESS") || die('Direct access forbidden');

class CRM_TasksInstall extends ModuleInstall {

	public function install() {
		Base_LangCommon::install_translations($this->get_type());
		Base_ThemeCommon::install_default_theme('CRM/Tasks');
		$fields = array(
			array('name'=>'Title', 				'type'=>'text', 'required'=>true, 'param'=>'255', 'extra'=>false, 'visible'=>true, 'display_callback'=>array('CRM_TasksCommon','display_title')),

			array('name'=>'Description', 		'type'=>'long text', 'extra'=>false, 'param'=>'255', 'visible'=>false),

			array('name'=>'Employees', 			'type'=>'crm_contact', 'param'=>array('field_type'=>'multiselect', 'crits'=>array('CRM_TasksCommon','employees_crits'), 'format'=>array('CRM_ContactsCommon','contact_format_no_company')), 'display_callback'=>array('CRM_TasksCommon','display_employees'), 'required'=>true, 'extra'=>false, 'visible'=>true, 'filter'=>true),
			array('name'=>'Customers', 			'type'=>'crm_company_contact', 'param'=>array('field_type'=>'multiselect', 'crits'=>array('CRM_TasksCommon','customers_crits')), 'extra'=>false, 'visible'=>true),

			array('name'=>'Status',				'type'=>'commondata', 'required'=>true, 'visible'=>true, 'filter'=>true, 'param'=>array('order_by_key'=>true,'CRM/Status'), 'extra'=>false, 'visible'=>true, 'display_callback'=>array('CRM_TasksCommon','display_status')),
			array('name'=>'Priority', 			'type'=>'commondata', 'required'=>true, 'visible'=>true, 'param'=>array('order_by_key'=>true,'CRM/Priority'), 'extra'=>false, 'filter'=>true),
			array('name'=>'Permission', 		'type'=>'commondata', 'required'=>true, 'param'=>array('order_by_key'=>true,'CRM/Access'), 'extra'=>false),

			array('name'=>'Longterm',			'type'=>'checkbox', 'extra'=>false, 'filter'=>true, 'visible'=>true),

//			array('name'=>'Is Deadline',		'type'=>'checkbox', 'extra'=>false, 'QFfield_callback'=>array('CRM_TasksCommon','QFfield_is_deadline')),			
			array('name'=>'Deadline',			'type'=>'date', 'extra'=>false, 'visible'=>true)
		);
		Utils_RecordBrowserCommon::install_new_recordset('task', $fields);
		Utils_RecordBrowserCommon::register_processing_callback('task', array('CRM_TasksCommon', 'submit_task'));
		Utils_RecordBrowserCommon::set_icon('task', Base_ThemeCommon::get_template_filename('CRM/Tasks', 'icon.png'));
		Utils_RecordBrowserCommon::set_recent('task', 5);
		Utils_RecordBrowserCommon::set_caption('task', 'Tasks');
		Utils_RecordBrowserCommon::enable_watchdog('task', array('CRM_TasksCommon','watchdog_label'));
// ************ addons ************** //
		Utils_AttachmentCommon::new_addon('task');
		Utils_RecordBrowserCommon::new_addon('task', 'CRM/Tasks', 'messanger_addon', 'Alerts');
// ************ other ************** //
		CRM_CalendarCommon::new_event_handler('Tasks', array('CRM_TasksCommon', 'crm_calendar_handler'));
		Utils_BBCodeCommon::new_bbcode('task', 'CRM_TasksCommon', 'task_bbcode');
        CRM_RoundcubeCommon::new_addon('task');

		if (ModuleManager::is_installed('Premium_SalesOpportunity')>=0)
			Utils_RecordBrowserCommon::new_record_field('task', 'Opportunity', 'select', true, false, 'premium_salesopportunity::Opportunity Name;Premium_SalesOpportunityCommon::crm_opportunity_reference_crits', '', false);

		Utils_RecordBrowserCommon::add_access('task', 'view', 'EMPLOYEE', array('(!permission'=>2, '|employees'=>'USER'));
		Utils_RecordBrowserCommon::add_access('task', 'add', 'EMPLOYEE');
		Utils_RecordBrowserCommon::add_access('task', 'edit', 'EMPLOYEE', array('(permission'=>0, '|employees'=>'USER', '|customers'=>'USER'));
		Utils_RecordBrowserCommon::add_access('task', 'delete', 'EMPLOYEE', array(':Created_by'=>'USER_ID'));
		Utils_RecordBrowserCommon::add_access('task', 'delete', array('EMPLOYEE','ACCESS:manager'));

		return true;
	}

	public function uninstall() {
		CRM_CalendarCommon::delete_event_handler('Tasks');
        CRM_RoundcubeCommon::delete_addon('task');
		Utils_AttachmentCommon::delete_addon('task');
		Base_ThemeCommon::uninstall_default_theme('CRM/Tasks');
		Utils_RecordBrowserCommon::unregister_processing_callback('task', array('CRM_TasksCommon', 'submit_task'));
		Utils_RecordBrowserCommon::uninstall_recordset('task');
		return true;
	}

	public function version() {
		return array("1.0");
	}

	public function requires($v) {
		return array(
			array('name'=>'Utils/RecordBrowser', 'version'=>0),
			array('name'=>'Utils/Attachment', 'version'=>0),
			array('name'=>'CRM/Common', 'version'=>0),
			array('name'=>'CRM/Acl', 'version'=>0),
			array('name'=>'CRM/Roundcube', 'version'=>0),
			array('name'=>'CRM/Contacts', 'version'=>0),
			array('name'=>'CRM/Calendar', 'version'=>0),
			array('name'=>'Base/Lang', 'version'=>0),
			array('name'=>'Base/Acl', 'version'=>0),
			array('name'=>'Utils/ChainedSelect', 'version'=>0),
			array('name'=>'Data/Countries', 'version'=>0),
			array('name'=>'CRM/Filters','version'=>0),
			array('name'=>'Libs/QuickForm','version'=>0),
			array('name'=>'Base/Theme','version'=>0));
	}

	public static function info() {
		return array('Author'=>'<a href="mailto:abisaga@telaxus.com">Arkadiusz Bisaga</a> (<a href="http://www.telaxus.com">Telaxus LLC</a>)', 'License'=>'TL', 'Description'=>'Module for organising todo list.');
	}

	public static function simple_setup() {
		return true;
	}
}

?>
