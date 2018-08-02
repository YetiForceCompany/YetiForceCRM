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
	<div class="col-md-12 pr-0 row">
		<span class="col-md-2">
			<div style="position:relative;display:inline;">
				<img src="{\App\Layout::getImagePath('PBXManager48.png')}" class="summaryImg" alt="{\App\Language::translate($MODULE, $MODULE)}" />
				{if $RECORD->get('direction') eq 'inbound'}
					<img src="modules/PBXManager/resources/images/Incoming.png" style="position:absolute;bottom:4px;right:0;">
				</div>
			{else if $RECORD->get('direction') eq 'outbound'}
				<img src="modules/PBXManager/resources/images/Outgoing.png" style="position:absolute;bottom:4px;right:0;">
				</div>
			{else}
				</div>
			{/if}    
		</span> 
		<span class="col-md-4 margin0px">
			<span class="row">
				<span class="recordLabel pushDown" title="{$RECORD->getName()}">
					{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
					{if $RECORD_STATE !== 'Active'}
						&nbsp;&nbsp;
						{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
						<span class="badge badge-secondary" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};"{/if}>
							{if \App\Record::getState($RECORD->getId()) === 'Trash'}
								{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
							{else}
								{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
							{/if}
						</span>
					{/if}
					{assign var=NAME_FIELDS value=$MODULE_MODEL->getNameFields()}
					{foreach from=$MODULE_MODEL->getNameFields() item=NAME_FIELD }
						{assign var=FIELD_MODEL value=$MODULE_MODEL->getFieldByColumn($NAME_FIELD)}
						{if $FIELD_MODEL && $FIELD_MODEL->getPermissions()}
							{assign var=RECORDID value=$RECORD->get("customer")}

							{if $RECORDID}
								{assign var=MODULE value=$RECORD->get('customertype')}
								{assign var=ENTITY_NAMES value=\App\Fields\Owner::getUserLabel($RECORDID, $MODULE)}
								{assign var=CALLERNAME value=$ENTITY_NAMES[$RECORDID]}
							{else}
								{assign var=CALLERNAME value=$RECORD->getDisplayValue("customernumber")}
							{/if}

							{assign var=CALLER_INFO value=PBXManager_Record_Model::lookUpRelatedWithNumber($RECORD->get('customernumber'))}
							{if $CALLER_INFO.id}
								{assign var=MODULEMODEL value=Vtiger_Module_Model::getInstance($RECORD->get('customertype'))}
								{assign var=FIELDMODEL value=Vtiger_Field_Model::getInstance($CALLER_INFO.fieldname,$MODULEMODEL)}
								{assign var=FIELD_NAME value=$FIELDMODEL->get('label')}
							{/if}

							{if $RECORD->get('direction') eq 'inbound'}
								&nbsp;<strong><span class="{$NAME_FIELD}">
										{\App\Language::translate('LBL_CALL_FROM', $MODULE_MODEL->get('name'))}&nbsp;{$CALLERNAME}
									</span><br /></strong>
								{else}
								&nbsp;<strong><span class="{$NAME_FIELD}">
										{\App\Language::translate('LBL_CALL_TO', $MODULE_MODEL->get('name'))}&nbsp;{$CALLERNAME}
									</span><br /></strong>
								{/if}    
								{if $FIELD_NAME}
								&nbsp;{$FIELD_NAME}:&nbsp;<span class="title_label muted">{$RECORD->getDisplayValue('customernumber')}
								</span>
							{/if}
						{/if}
					{/foreach}
				</span>
			</span>
			<span class="row">
				<span class="muted">
					{\App\Language::translate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
					{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
					{if $SHOWNERS != ''}
						<br />{\App\Language::translate('Share with users',$MODULE_NAME)} {$SHOWNERS}
					{/if}
				</span>
			</span>
		</span>
		{include file=\App\Layout::getTemplatePath('Detail/HeaderFields.tpl', $MODULE_NAME)}
	</div>
{/strip}

