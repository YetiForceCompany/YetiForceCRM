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
	<div class="tpl-QuickCreate modelContainer modal quickCreateContainer" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg modal-full" role="document">
			<div class="modal-content">
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					<div class="modal-header col-12 m-0 align-items-center form-row d-flex justify-content-between py-2">
						<div class="col-xl-6 col-12">
							<h5 class="modal-title form-row text-center text-xl-left mb-2 mb-xl-0">
								<span class="col-12">
									<span class="fas fa-plus mr-1"></span>
									<strong class="mr-1">{\App\Language::translate('LBL_QUICK_CREATE', $MODULE)}
										:</strong>
									<strong class="text-uppercase"><span
												class="userIcon-{$MODULE} mx-1"></span>{\App\Language::translate($SINGLE_MODULE, $MODULE)}</strong>
								</span>
							</h5>
						</div>
						<div class="col-xl-6 col-12 text-center text-xl-right">
							{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
							{if !empty($QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER'])}
								{foreach item=LINK from=$QUICKCREATE_LINKS['QUICKCREATE_VIEW_HEADER']}
									{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='quickcreateViewHeader' CLASS='display-block-md'}
								{/foreach}
							{/if}
							<button class="btn btn-success col-12 col-md-1 mb-2 mb-md-0" type="submit"
									title="{\App\Language::translate('LBL_SAVE', $MODULE)}">
								<strong><span class="fas fa-check"></span></strong>
							</button>
							<button class="cancelLink btn btn-danger col-12 col-md-1 ml-0 ml-md-1" aria-hidden="true"
									data-dismiss="modal" type="button" title="{\App\Language::translate('LBL_CLOSE')}">
								<span class="fas fa-times"></span>
							</button>
						</div>
					</div>
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency"
							   value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'/>
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField"
							   value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
					{/if}
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="action" value="SaveAjax"/>
					<div class="quickCreateContent">
						<div class="modal-body m-0">
							<div class="massEditTable border-0 px-1 mx-auto m-0">
								<div class="px-0 m-0 form-row d-flex justify-content-center">
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{if ($FIELD_NAME === 'time_start' || $FIELD_NAME === 'time_end') && ($MODULE === 'OSSTimeControl' || $MODULE === 'Reservations')}{continue}{/if}
									{if $COUNTER eq 2}
								</div>
								<div class="col-12 form-row d-flex justify-content-center px-0 m-0">
									{assign var=COUNTER value=1}
									{else}
									{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="col-md-6 py-2 form-row d-flex justify-content-center px-0 m-0 {$WIDTHTYPE} ">
										<div class="fieldLabel col-lg-12 col-xl-3 pl-0 text-lg-left text-xl-right u-text-ellipsis">
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->getFieldLabel()}
											<label class="text-right muted small font-weight-bold">
												{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}
												{if in_array($VIEW,$HELPINFO) && \App\Language::translate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
													<a href="#" class="js-help-info float-right" title=""
													   data-placement="top"
													   data-content="{\App\Language::translate($HELPINFO_LABEL, 'HelpInfo')}"
													   data-original-title='{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}'>
														<span class="fas fa-info-circle"></span>
													</a>
												{/if}
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
											</label>
										</div>
										<div class="fieldValue col-lg-12 col-xl-9 px-0 px-sm-1">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE) RECORD=null}
										</div>
									</div>
									{/foreach}
									{if $COUNTER eq 1}
										<div class="col-md-6 form-row align-items-center p-1 {$WIDTHTYPE} px-0"></div>
									{/if}
								</div>
							</div>
						</div>
					</div>
					{if !empty($SOURCE_RELATED_FIELD)}
						{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_MODEL from=$SOURCE_RELATED_FIELD}
							<input type="hidden" name="{$RELATED_FIELD_NAME}"
								   value="{\App\Purifier::encodeHtml($RELATED_FIELD_MODEL->get('fieldvalue'))}"
								   data-fieldtype="{$RELATED_FIELD_MODEL->getFieldDataType()}"/>
						{/foreach}
					{/if}
				</form>
			</div>
		</div>
	</div>
{/strip}
