<?php
/**
 * @author Arkadiusz Bisaga <abisaga@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license Commercial
 * @version 1.0
 * @package epesi-utils
 * @subpackage genericbrowser
 */
if (!isset($_POST['id']) || !isset($_POST['tab']) || !isset($_POST['mode']) || !isset($_POST['element']) || !isset($_POST['cid']))
	die('Invalid request');

define('JS_OUTPUT',1);
define('CID',$_POST['cid']); 
require_once('../../../include.php');
ModuleManager::load_modules();

if (!Acl::is_user()) die('Unauthorized access');

$id = json_decode($_POST['id']);
$element = json_decode($_POST['element']);
$tab = json_decode($_POST['tab']);
$mode = json_decode($_POST['mode']);

if (!is_numeric($id) || !is_string($element)) 
	die('Invalid request');

$form = ModuleManager::new_instance('Libs_QuickForm', null, 'grid_form');
$rb = ModuleManager::new_instance('Utils_RecordBrowser', null, 'grid_rb');
$form->construct();
$rb->construct($tab);
$rb->init();
$record = Utils_RecordBrowserCommon::get_record($tab, $id);
	
if ($mode=='submit') {
	$value = json_decode($_POST['value']);
	Utils_RecordBrowserCommon::update_record($tab, $id, array($element=>$value));
	$record[$element] = $value;

	$rb->view_fields_permission = $rb->get_access('view', $record);
	$rb->prepare_view_entry_details($record, 'view', $id, $form, array($element=>true));
	$html = $form->toArray();
	print('$("grid_value_field_'.$element.'_'.$id.'").innerHTML = \''.Epesi::escapeJS($html['elements'][2]['html']).'\';');
	return;
}
$rb->view_fields_permission = $rb->get_access('edit', $record);
$rb->prepare_view_entry_details($record, 'edit', $id, $form, array($element=>true));
$html = $form->toArray();
print('$("grid_form_field_'.$element.'_'.$id.'").innerHTML = \''.Epesi::escapeJS($html['elements'][2]['html']).'\';');
print('Event.observe("'.$element.'","keydown", function(ev){if(ev.keyCode==13)grid_submit_field("'.$element.'",'.$id.',"'.$tab.'");});');
?>