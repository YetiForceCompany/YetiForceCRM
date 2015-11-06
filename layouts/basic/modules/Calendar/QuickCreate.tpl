{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    <div class="modelContainer modal fade" tabindex="-1">
		 <div class="modal-dialog modal-lg">
            <div class="modal-content">
				<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header contentsBackground">
						<div class="pull-left"><h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate('LBL_EVENT_OR_TASK', $MODULE)}</h3></div>

						{assign var="CALENDAR_MODULE_MODEL" value=$QUICK_CREATE_CONTENTS['Calendar']['moduleModel']}
						<div class="quickCreateActions pull-right">
							{if $MODULE_NAME eq 'Calendar'}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateTaskRecordUrl()}
							{else}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateEventRecordUrl()}
							{/if}
							<button class="btn btn-default" id="goToFullForm" type="button" data-edit-view-url="{$EDIT_VIEW_URL}"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							<button class="cancelLink btn btn-warning" type="reset" aria-hidden="true" data-dismiss="modal"
									type="button" title="{vtranslate('LBL_CLOSE')}">&times;</button>
						</div>
						<div class="clearfix"></div>
					</div>
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
			{/if}
			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
			{/if}
			<input type="hidden" name="module" value="{$MODULE}">
			<input type="hidden" name="action" value="SaveAjax">
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
			 <input type="hidden" name="userChangedEndDateTime" value="0" />

			<!-- Random number is used to make specific tab is opened -->
			{assign var="RAND_NUMBER" value=rand()}
			<div class="modal-body tabbable" style="padding:0px">
				<ul class="nav nav-pills" style="margin-bottom:0px;padding-left:5px">
					<li class="active">
						<a href="javascript:void(0);" data-target=".EventsQuikcCreateContents_{$RAND_NUMBER}" data-toggle="tab" data-tab-name="Event">{vtranslate('LBL_EVENT',$MODULE)}</a>
					</li>
					<li class="">
						<a href="javascript:void(0);" data-target=".CalendarQuikcCreateContents_{$RAND_NUMBER} " data-toggle="tab" data-tab-name="Task">{vtranslate('LBL_TASK',$MODULE)}</a>
					</li>
				</ul>
				<div class="tab-content overflowVisible">
					{foreach item=MODULE_DETAILS key=MODULE_NAME from=$QUICK_CREATE_CONTENTS}
					<div class="{$MODULE_NAME}QuikcCreateContents_{$RAND_NUMBER} tab-pane {if $MODULE_NAME eq 'Events'} active in {/if}fade">
						<input type="hidden" name="mode" value="{if $MODULE_NAME eq 'Calendar'}Calendar{else}Events{/if}">
						{assign var="RECORD_STRUCTURE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructureModel']}
						{assign var="RECORD_STRUCTURE" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['recordStructure']}
						{assign var="MODULE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE_NAME]['moduleModel']}
						<div class="quickCreateContent">
							<div style='margin:5px'>
								<table class="massEditTable table table-bordered">
									<tr>
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $COUNTER eq 2}
											</tr><tr>
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<td class="fieldLabel alignMiddle {$WIDTHTYPE}">
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											{if {$isReferenceField} eq "reference"}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}<br />
												{if $refrenceListCount > 1}
													{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
													{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
													{if !empty($REFERENCED_MODULE_STRUCT)}
														{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
													{/if}
													<select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList_{$FIELD_MODEL->get('id')}">
														<optgroup>
															{foreach key=index item=value from=$refrenceList}
																<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
															{/foreach}
														</optgroup>
													</select>
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										</td>
										<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME)}
										</td>
									{/foreach}
									</tr>
								</table>
								<div class="row" style="margin-top: 5px;">
									<div class="col-md-4" id="prev_events">
										<table class="table">
											<tr><th>{vtranslate('TASK_PREV', $MODULE)}</th></tr>
										</table>
									</div>
									<div class="col-md-4" id="cur_events">
										 <table class="table">
											<tr><th>{vtranslate('TASK_CUR', $MODULE)}</th></tr>
										</table>
									</div>
									<div class="col-md-4" id="next_events">
										<table class="table">
											<tr><th>{vtranslate('TASK_NEXT', $MODULE)}</th></tr>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					{/foreach}
				</div>
				</div>
				{if !empty($SOURCE_RELATED_FIELD)}
					{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_VALUE from=$SOURCE_RELATED_FIELD}
						<input type="hidden" name="{$RELATED_FIELD_NAME}" value='{$RELATED_FIELD_VALUE}' />
					{/foreach}
				{/if}
			</form>
		</div>
	</div>
</div>
{/strip}
