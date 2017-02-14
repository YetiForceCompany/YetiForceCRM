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
		 <div class="modal-dialog modal-full">
            <div class="modal-content">
				<input type="hidden" name="showCompanies" value="{AppConfig::module($MODULE, 'SHOW_COMPANIES_IN_QUICKCREATE')}" >
				<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header">
						<div class="pull-left"><h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate('LBL_EVENT_OR_TASK', $MODULE)}</h3></div>

						{assign var="CALENDAR_MODULE_MODEL" value=$QUICK_CREATE_CONTENTS['Calendar']['moduleModel']}
						<div class="quickCreateActions pull-right">
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='quickcreateViewHeader'}
								&nbsp;&nbsp;
							{/foreach}
							{if $MODULE_NAME eq 'Calendar'}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateTaskRecordUrl()}
							{else}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateEventRecordUrl()}
							{/if}
							<button class="btn btn-default" id="goToFullForm" type="button" data-edit-view-url="{$EDIT_VIEW_URL}"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;
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
			<input type="hidden" id="hiddenDays" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode(AppConfig::module('Calendar', 'HIDDEN_DAYS_IN_CALENDAR_VIEW')))}" />

			<!-- Random number is used to make specific tab is opened -->
			{assign var="RAND_NUMBER" value=rand()}
			<div class="modal-body row no-margin tabbable" >
				<ul class="nav nav-pills">
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
								<div class="massEditTable row no-margin">
									<div class="col-xs-12 paddingLRZero fieldRow">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $COUNTER eq 2}
											</div>
											<div class="col-xs-12 paddingLRZero fieldRow">
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero">
											<div class="fieldLabel col-xs-12 col-sm-5 ">
												{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
												{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
												<label class="muted pull-left-xs pull-right-sm pull-right-lg">
													{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
													{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
														<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="auto top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><span class="glyphicon glyphicon-info-sign"></span></a>
													{/if}
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												</label>
											</div>
											<div class="fieldValue col-xs-12 col-sm-7 " >
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME)}
											</div>
										</div>
									{/foreach}
									</div>
								</div>
								<div class="pull-right marginTB10">
									<button class="btn btn-primary saveAndComplete" type="button">{vtranslate('LBL_SAVE_AND_CLOSE', $MODULE)}</button> 
									<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
								</div>
								{if AppConfig::module($MODULE, 'SHOW_DAYS_QUICKCREATE')}
									<div class="row noSpaces col-xs-12 eventsTable" style="margin-top: 5px;">
										<div class="width1per7 paddingLRZero" id="threeDaysAgo">
											<table class="table">
												<tr>
													<th class="padding5">
														<button type="button" class="btn btn-xs btn-primary previousDayBtn"><</button>
													</th>
													<th class="text-center taskPrevThreeDaysAgo">
														{Vtiger_Date_UIType::getDisplayDateValue($THREEDAYSAGO)}
													</th>
												</tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="twoDaysAgo">
											<table class="table">
												<tr><th class="text-center taskPrevTwoDaysAgo">{Vtiger_Date_UIType::getDisplayDateValue($TWODAYSAGO)}</th></tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="oneDaysAgo">
											<table class="table">
												<tr><th class="text-center taskPrevOneDayAgo">{Vtiger_Date_UIType::getDisplayDateValue($ONEDAYAGO)}</th></tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="cur_events">
											 <table class="table">
												<tr><th class="text-center taskCur">{Vtiger_Date_UIType::getDisplayDateValue($CURRENTDATE)}</th></tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="oneDaysLater">
											<table class="table">
												<tr><th class="text-center taskNextOneDayLater">{Vtiger_Date_UIType::getDisplayDateValue($ONEDAYLATER)}</th></tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="twoDaysLater">
											<table class="table">
												<tr><th class="text-center taskNextTwoDaysLater">{Vtiger_Date_UIType::getDisplayDateValue($TWODAYLATER)}</th></tr>
											</table>
										</div>
										<div class="width1per7 paddingLRZero" id="threeDaysLater">
											<table class="table">
												<tr>
													<th class="text-center taskNextThreeDaysLater">
														{Vtiger_Date_UIType::getDisplayDateValue($THREEDAYSLATER)}
													</th>
													<th class="padding5">
														<button type="button" class="btn btn-xs btn-primary nextDayBtn">></button>
													</th>
												</tr>
											</table>
										</div>
									</div>
								{/if}
						</div>
					</div>
					{/foreach}
					{if $COUNTER eq 1}
						<div class="col-xs-12 col-md-6 fieldsLabelValue {$WIDTHTYPE} paddingLRZero"></div>
					{/if}
				</div>
				</div>
				{if !empty($SOURCE_RELATED_FIELD)}
					{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
						<input type="hidden" name="{$RELATED_FIELD_NAME}" value="{$RELATED_FIELD_MODEL->get('fieldvalue')}" data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}" />
					{/foreach}
				{/if}
			</form>
		</div>
	</div>
</div>
{/strip}
