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
	<!-- tpl-Base-QuickCreate -->
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="modal quickCreateContainer" tabindex="-3" role="dialog">
		<div class="modal-dialog modal-lg modal-full" role="document">
			<div class="modal-content">
				<form class="form-horizontal recordEditView js-form" name="{$FROM_VIEW}" method="post" action="index.php" enctype="multipart/form-data">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="fromView" value="{$FROM_VIEW}" />
					<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}" />
					<input type="hidden" class="js-change-value-event" value="{\App\EventHandler::getVarsByType(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE, $MODULE_NAME, [$RECORD, $FROM_VIEW])}" />
					{if !empty($IS_RELATION_OPERATION) && !empty($SOURCE_MODULE) && !empty($SOURCE_RECORD)}
						<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
						<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
						<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
					{/if}
					{if !empty($LIST_FILTER_FIELDS)}
						<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
					{/if}
					{foreach key=INPUT_NAME item=INPUT_VALUE from=$HIDDEN_INPUT}
						<input type="hidden" name="{$INPUT_NAME}" value='{\App\Purifier::encodeHtml($INPUT_VALUE)}' />
					{/foreach}
					{if !empty($SOURCE_RELATED_FIELD)}
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$SOURCE_RELATED_FIELD}
							<div class="d-none fieldValue source-related-fields" data-field="{$FIELD_NAME}">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
							</div>
						{/foreach}
					{/if}
					<div class="modal-header align-items-center form-row d-flex justify-content-between py-2">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									<span class="fas fa-plus mr-1"></span>
									<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)} :</strong>
									<strong class="text-uppercase"><span class="yfm-{$MODULE} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE)}</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							{if \App\Privilege::isPermitted($MODULE_NAME, 'RecordCollector') && !empty($QUICKCREATE_LINKS['EDIT_VIEW_RECORD_COLLECTOR'])}
								{include file=\App\Layout::getTemplatePath('Edit/RecordCollectors.tpl', $MODULE) SHOW_BTN_LABEL=1 RECORD_COLLECTOR=$QUICKCREATE_LINKS['EDIT_VIEW_RECORD_COLLECTOR']}
							{/if}
							{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
							{if !empty($QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER'])}
								{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
									{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader' CLASS='display-block-md' TABINDEX=Vtiger_Field_Model::$tabIndexLastSeq}
								{/foreach}
							{/if}
							<button class="btn btn-success mr-1" type="submit" tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" title="{\App\Language::translate('LBL_SAVE', $MODULE)}">
								<strong><span class="fas fa-check"></span></strong>
							</button>
							<button class="cancelLink btn btn-danger" tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					<div class="quickCreateContent">
						<div class="modal-body m-0">
							{if $LAYOUT === 'blocks'}
								{include file=\App\Layout::getTemplatePath('EditBlocks.tpl') RECORD_STRUCTURE=$RECORD_STRUCTURE}
							{else}
								<div class="border-0 px-1 mx-auto m-0">
									<div class="px-0 m-0 form-row d-flex justify-content-center">
										{assign var=COUNTER value=0}
										{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
											{if ($FIELD_NAME === 'time_start' || $FIELD_NAME === 'time_end') && ($MODULE_NAME === 'OSSTimeControl' || $MODULE_NAME === 'Reservations')}{continue}{/if}
											{if $COUNTER eq 2}
											</div>
											<div class="col-12 form-row d-flex justify-content-center px-0 m-0">
												{assign var=COUNTER value=1}
											{else}
												{assign var=COUNTER value=$COUNTER+1}
											{/if}
											<div class="col-md-6 py-2 form-row d-flex justify-content-center px-0 m-0 {$WIDTHTYPE} ">
												<div class="fieldLabel col-lg-12 col-xl-3 pl-0 text-lg-left text-xl-right u-text-ellipsis">
													{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
													<label class="text-right muted small font-weight-bold">
														{if $FIELD_MODEL->isMandatory() eq true}
															<span class="redColor">*</span>
														{/if}
														{if $HELPINFO_LABEL}
															<a href="#" class="js-help-info float-right u-cursor-pointer"
																title=""
																data-placement="top"
																data-content="{$HELPINFO_LABEL}"
																data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}">
																<span class="fas fa-info-circle"></span>
															</a>
														{/if}
														{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}
													</label>
												</div>
												<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1">
													{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
												</div>
											</div>
										{/foreach}
										{if $COUNTER eq 1}
											<div class="col-md-6 form-row align-items-center p-1 {$WIDTHTYPE} px-0"></div>
										{/if}
									</div>
								</div>
							{/if}
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-QuickCreate -->
{/strip}
