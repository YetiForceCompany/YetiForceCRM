{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="form-group row">
		<label class="col-form-label col-md-3"><strong>{\App\Language::translate('LBL_SELECT_PICKLIST_IN',$QUALIFIED_MODULE)}&nbsp;{\App\Language::translate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}</strong></label>
		<div class="col-md-4">
			<select class="select2 form-control js-picklist-field" id="modulePickList" data-allow-clear="true">
				<optgroup>
					{foreach key=PICKLIST_FIELD item=FIELD_MODEL from=$PICKLIST_FIELDS}
						<option value="{$FIELD_MODEL->getName()}" {if !empty($PICKLIST_INTERDEPENDENT[$FIELD_MODEL->getFieldName()]) && count($PICKLIST_INTERDEPENDENT[$FIELD_MODEL->getFieldName()]) > 1} data-confirmation="{\App\Language::translateArgs('LBL_CONFIRM_BEFORE_MODIFY', $QUALIFIED_MODULE, implode(', ',  $PICKLIST_INTERDEPENDENT[$FIELD_MODEL->getFieldName()]))}" {/if}>
							{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
						</option>
					{/foreach}
				</optgroup>
			</select>
		</div>
	</div>
	<!-- /tpl-Settings-Picklist-ModulePickListDetail -->
{/strip}
