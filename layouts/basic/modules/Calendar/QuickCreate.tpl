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
				<form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header">
						<div class="pull-left">
							<h3 class="modal-title quickCreateTitle">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}:&nbsp;<p class="textTransform"><b> {\App\Language::translate('LBL_EVENT_OR_TASK', $MODULE)}</b></p></h3>
						</div>
						{assign var="CALENDAR_MODULE_MODEL" value=$QUICK_CREATE_CONTENTS['Calendar']['moduleModel']}
						<div class="quickCreateActions pull-right pullRight">
							{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader'}
								&nbsp;&nbsp;
							{/foreach}
							{if $MODULE_NAME eq 'Calendar'}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateTaskRecordUrl()}
							{else}
								{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateEventRecordUrl()}
							{/if}
							<button class="btn btn-default goToFullFormOne" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{\App\Language::translate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>&nbsp;
							<button class="btn btn-success" type="submit" title="{\App\Language::translate('LBL_SAVE', $MODULE)}"><strong><span class="fas fa-check"></span></strong></button>
							<button class="cancelLink btn btn-warning" type="reset" aria-hidden="true" data-dismiss="modal"	type="button" title="{\App\Language::translate('LBL_CLOSE')}"><span class="fas fa-times"></span></button>
						</div>
						<div class="clearfix"></div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
					<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
					<input type="hidden" name="userChangedEndDateTime" value="0" />
					<!-- Random number is used to make specific tab is opened -->
					{assign var="RAND_NUMBER" value=rand()}
					<div class="modal-body row no-margin tabbable" >
						<ul class="nav nav-pills">
							<li class="active">
								<a href="javascript:void(0);" data-target=".EventsQuikcCreateContents_{$RAND_NUMBER}" data-toggle="tab" data-tab-name="Event">{\App\Language::translate('LBL_EVENT',$MODULE)}</a>
							</li>
							<li class="">
								<a href="javascript:void(0);" data-target=".CalendarQuikcCreateContents_{$RAND_NUMBER} " data-toggle="tab" data-tab-name="Task">{\App\Language::translate('LBL_TASK',$MODULE)}</a>
							</li>
						</ul>
						<div class="tab-content overflowVisible">
							{foreach item=MODULE_DETAILS key=MODULE_NAME from=$QUICK_CREATE_CONTENTS}
								<div class="{$MODULE_NAME}QuikcCreateContents_{$RAND_NUMBER} tab-pane {if $MODULE_NAME eq 'Events'} active in {/if}fade">
									<input type="hidden" name="mode" value="{if $MODULE_NAME eq 'Calendar'}calendar{else}events{/if}" />
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
															{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
															<label class="muted pull-left-xs pull-right-sm pull-right-lg">
																{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
																{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
																	<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="auto top" data-content="{htmlspecialchars(\App\Language::translate($MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel(), 'HelpInfo'))}" data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'><span class="fas fa-info-circle"></span></a>
																	{/if}
																	{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
															</label>
														</div>
														<div class="fieldValue col-xs-12 col-sm-7 " >
															{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
														</div>
													</div>
												{/foreach}
											</div>
										</div>
										<div class="pull-right marginTB10">
											<button class="btn btn-primary saveAndComplete" type="button">{\App\Language::translate('LBL_SAVE_AND_CLOSE', $MODULE)}</button>
											<button class="btn btn-success" type="submit" title="{\App\Language::translate('LBL_SAVE', $MODULE)}"><strong><span class="fas fa-check"></span></strong></button>
										</div>
										{if AppConfig::module($MODULE, 'SHOW_DAYS_QUICKCREATE')}
											<div class="row noSpaces col-xs-12 eventsTable"></div>
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
							<input type="hidden" name="{$RELATED_FIELD_NAME}" value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}" data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}" />
						{/foreach}
					{/if}
				</form>
			</div>
		</div>
	</div>
{/strip}
