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
	<!-- tpl-Base-Dashboards-MiniListWizard -->
	{if $WIZARD_STEP eq 'step1'}
		<div id="minilistWizardContainer" class='modelContainer modal fade' tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
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
							<input type="hidden" name="module" value="{$MODULE_NAME}"/>
							<input type="hidden" name="action" value="MassSave"/>
							<table class="table table-bordered">
								<tbody>
								<tr>
									<td class="fieldLabel alignMiddle textAlignCenter"
										nowrap>{App\Language::translate('LBL_WIDGET_NAME','Home')}</td>
									<td class="fieldValue position-relative">
										<input type="text" class="form-control" name="widgetTitle" value=""
											   data-validation-engine="validate[required]">
									</td>
								</tr>
								<tr>
									<td class="fieldLabel alignMiddle textAlignCenter"
										nowrap>{App\Language::translate('LBL_SELECT_MODULE')}</td>
									<td class="fieldValue">
										<select class="form-control select2" name="module">
											<option></option>
											{foreach from=$MODULES item=MODULE_MODEL key=MODULE_THIS_NAME}
												<option value="{$MODULE_MODEL['name']}">{App\Language::translate($MODULE_MODEL['name'], $MODULE_MODEL['name'])}</option>
											{/foreach}
										</select>
									</td>
								</tr>
								<tr>
									<td class="fieldLabel alignMiddle textAlignCenter"
										nowrap>{App\Language::translate('LBL_FILTER')}</td>
									<td class="fieldValue">
										<select class="form-control" name="filterid">
											<option></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="fieldLabel alignMiddle textAlignCenter"
										nowrap>{App\Language::translate('LBL_FIELDS')}</td>
									<td class="fieldValue">
										<select class="form-control" name="fields" size="2" multiple="true"
												data-validation-engine="validate[required]">
											<option></option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="fieldLabel alignMiddle textAlignCenter"
										nowrap>{App\Language::translate('LBL_FILTER_FIELD')}</td>
									<td class="fieldValue">
										<select class="form-control" name="filter_fields">
											<option></option>
										</select>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL' MODULE=$MODULE_NAME}
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
