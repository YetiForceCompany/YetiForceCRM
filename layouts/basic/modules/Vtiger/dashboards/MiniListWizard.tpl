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
	<!-- tpl-Base-Dashboards-MiniListWizard -->
	{if $WIZARD_STEP eq 'step1'}
		<div id="minilistWizardContainer" class='modelContainer modal fade' tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="massEditHeader">
							<span class="fas fa-filter mr-1"></span>
							{\App\Language::translate('LBL_MINI_LIST','Home')} {\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<form class="form-horizontal" method="post" action="javascript:;">
						<div class="modal-body">
							<input type="hidden" name="module" value="{$MODULE_NAME}" />
							<input type="hidden" name="action" value="MassSave" />
							<table class="table table-bordered">
								<tbody>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter"
											nowrap>{App\Language::translate('LBL_WIDGET_NAME','Home')}</td>
										<td class="fieldValue position-relative">
											<input type="text" class="form-control" name="widgetTitle" value="{$WIDGET_MODEL->getValueForEditView('title')}"
												data-validation-engine="validate[required]" {if $WIDGET_ID} disabled{/if}>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter"
											nowrap>{App\Language::translate('LBL_SELECT_MODULE')}</td>
										<td class="fieldValue">
											{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('module')}
											<select class="form-control select2" name="module" {if $WIDGET_ID} disabled{/if}>
												<option></option>
												{foreach from=$MODULES item=MODULE_MODEL key=MODULE_THIS_NAME}
													<option value="{$MODULE_MODEL['name']}" {if $MODULE_MODEL['name'] === $VALUE_FIELD} selected{/if}>{App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
												{/foreach}
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter"
											nowrap>{App\Language::translate('LBL_FILTER')}</td>
										<td class="fieldValue">
											{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('filterid')}
											<select class="form-control" name="filterid" {if $WIDGET_ID} disabled{/if}>
												<option></option>
												{if $VALUE_FIELD}
													{assign "CV_DETAIL" \App\CustomView::getCVDetails($VALUE_FIELD)}
													<option value="{$VALUE_FIELD}" selected>{App\Language::translate($CV_DETAIL['viewname'])}</option>
												{/if}
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter"
											nowrap>{App\Language::translate('LBL_FIELDS')}</td>
										<td class="fieldValue">
											{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('fields')}
											<select class="form-control{if $WIDGET_ID} select2{/if}" name="fields" size="2" multiple="true"
												data-validation-engine="validate[required]" {if $WIDGET_ID} disabled{/if}>
												{if $VALUE_FIELD}
													{assign "WIDGET_MODULE_MODEL" Vtiger_Module_Model::getInstance($WIDGET_MODEL->getValueForEditView('module'))}
													{foreach from=$VALUE_FIELD item=FIELD_NAME}
														{assign "FIELD_MODEL" $WIDGET_MODULE_MODEL->getFieldByName($FIELD_NAME)}
														{if $FIELD_MODEL}
															<option value="{$FIELD_NAME}" selected>{$FIELD_MODEL->getFullLabelTranslation()}</option>
														{/if}
													{/foreach}
												{else}
													<option></option>
												{/if}
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter" nowrap>
											{App\Language::translate('LBL_FIELD_HREF')}&nbsp;
											<span class="js-popover-tooltip u-cursor-pointer" data-js="popover" data-placement="top" data-content="{\App\Language::translate('LBL_FIELD_HREF_TOOLTIP')}">
												<span class="fas fa-question-circle"></span>
											</span>
										</td>
										<td class="fieldValue">
											{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('fieldHref')}
											<select class="form-control{if $WIDGET_ID} select2{/if}" name="field_href" size="2" {if $WIDGET_ID} disabled{/if}>
												{if $VALUE_FIELD}
													{assign "WIDGET_MODULE_MODEL" Vtiger_Module_Model::getInstance($WIDGET_MODEL->getValueForEditView('module'))}
													{assign "FIELD_MODEL" $WIDGET_MODULE_MODEL->getFieldByName($VALUE_FIELD)}
													{if $FIELD_MODEL}
														<option value="{$VALUE_FIELD}" selected>{$FIELD_MODEL->getFullLabelTranslation()}</option>
													{/if}
												{else}
													<option></option>
												{/if}
											</select>
										</td>
									</tr>
									<tr>
										<td class="fieldLabel alignMiddle textAlignCenter"
											nowrap>{App\Language::translate('LBL_FILTER_FIELD')}</td>
										<td class="fieldValue">
											{assign "VALUE_FIELD" $WIDGET_MODEL->getValueForEditView('filterFields')}
											<select class="form-control{if $WIDGET_ID} select2{/if}" name="filter_fields" {if $WIDGET_ID} disabled{/if}>
												<option></option>
												{if $VALUE_FIELD}
													{assign "FIELD_MODEL" Vtiger_Field_Model::getInstanceFromFieldId($VALUE_FIELD)}
													{if $FIELD_MODEL}
														<option value="{$VALUE_FIELD}" selected>{$FIELD_MODEL->getFullLabelTranslation()}</option>
													{/if}
												{/if}
											</select>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						{assign "BTN_SUCCESS" 'LBL_SAVE'}
						{if $WIDGET_ID}
							{assign "BTN_SUCCESS" ''}
						{/if}
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS=$BTN_SUCCESS BTN_DANGER='LBL_CANCEL' MODULE=$MODULE_NAME}
					</form>
				</div>
			</div>
		</div>
	{elseif $WIZARD_STEP eq 'step2'}
		<option></option>
		{foreach from=$ALLFILTERS item=FILTERS key=FILTERGROUP}
			<optgroup label="{\App\Language::translate($FILTERGROUP,$SELECTED_MODULE)}">
				{foreach from=$FILTERS item=FILTER key=FILTERNAME}
					{if $FILTER->get('setmetrics') eq 1}
						<option value="{$FILTER->getId()}">{\App\Language::translate($FILTER->get('viewname'),$SELECTED_MODULE)}</option>
					{/if}
				{/foreach}
			</optgroup>
		{/foreach}
	{elseif $WIZARD_STEP eq 'step3'}
		<div>
			<select class="form-control" name="fields" size="2" multiple="true">
				<option></option>
				{foreach from=$LIST_VIEW_FIELDS item=FIELD key=FIELD_NAME}
					<option value="{$FIELD_NAME}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
				{/foreach}
			</select>
			<select class="form-control" name="filter_fields">
				<option></option>
				{foreach from=$FIELDS_BY_BLOCK item=FIELDS key=BLOCK_NAME}
					<optgroup label="{\App\Language::translate($BLOCK_NAME,$SELECTED_MODULE)}">
						{foreach from=$FIELDS item=FIELD}
							{if $FIELD->isActiveSearchView()}
								<option value="{$FIELD->getId()}">{\App\Language::translate($FIELD->getFieldLabel(),$SELECTED_MODULE)}</option>
							{/if}
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	{/if}
	<!-- /tpl-Base-Dashboards-MiniListWizard -->
{/strip}
