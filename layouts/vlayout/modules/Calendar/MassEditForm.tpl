{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div id="massEditContainer contentsBackground" class='modelContainer'>
	<div class="modal-header">
        <button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader">{vtranslate('LBL_CHANGE_OWNER', $MODULE)}</h3>
	</div>
	<form class="form-horizontal calendarMassEdit" id="massEdit" name="MassEdit" method="post" action="index.php">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
		{/if}
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="MassSave" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value='{ZEND_JSON::encode($EXCLUDED_IDS)}'>
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
        <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
        
        {$massEditFields = ["assigned_user_id"=>$MASS_EDIT_FIELD_DETAILS.assigned_user_id]}
        <input type="hidden" id="massEditFieldsNameList" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($massEditFields))}' />
        
		<div class="controlElements padding20px">
			<div class="row-fluid">
				{assign var=FIELD_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()->getField('assigned_user_id')}
				<span class="span3">
					{vtranslate($FIELD_MODEL->get('label'),$MODULE)}
				</span>
				<span class="">
				</span>
				<span class="span9 offset2">
				<input type="hidden" name="assigned_user_id_mass_edit_check" value="on"/>
					{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$FIELD_MODEL}
				</span>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}