{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Settings-Vtiger-ConfigEditorEdit">
		<div class="contents">
			<form id="ConfigEditorForm" class="form-horizontal" data-detail-url="{$MODEL->getDetailViewUrl()}" method="POST">
				<div class="row widget_header">
					<div class="col-md-8">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
						{\App\Language::translate('LBL_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}
					</div>
					<div class="col-md-4 btn-toolbar mt-2">
						<div class="float-right">
							<button class="btn btn-success saveButton" type="submit" title="{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}">
								<span class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
							<button type="reset" class="cancelLink btn btn-warning" title="{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}">
								<span class="fas fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
						</div>
					</div>
				</div>
				<hr>
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				{assign var=FIELD_VALIDATION  value=['HELPDESK_SUPPORT_EMAIL_REPLY' => ['name'=>'Email'],
												'upload_maxsize' => ['name' => 'number'],
												'history_max_viewed' => ['name' => 'NumberRange5'],
												'popupType' =>['name' => 'NumberRange2'],
												'title_max_length' => ['name' => 'NumberRange100'],
												'MINIMUM_CRON_FREQUENCY' => ['name' => 'NumberRange100'],
												'href_max_length' => ['name' => 'NumberRange100'],
 												'listview_max_textlength' => ['name' => 'NumberRange100'],
												'list_max_entries_per_page' => ['name' => 'NumberRange100']]}
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
						<tr class="blockHeader"><th colspan="2" class="{$WIDTHTYPE}">{\App\Language::translate('LBL_CONFIG_FILE', $QUALIFIED_MODULE)}</th></tr>
					</thead>
					<tbody>
						{assign var=FIELD_DATA value=$MODEL->getViewableData()}
						{foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
							<tr><td width="30%" class="{$WIDTHTYPE}"><label class="muted float-right marginRight10px">{\App\Language::translate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}</label></td>
								<td style="border-left: none;" class="row {$WIDTHTYPE}">
									{if $FIELD_DETAILS['fieldType'] == 'picklist'}
										<div class="col-md-4">
											<select class="select2 form-control" name="{$FIELD_NAME}">
												{foreach key=optionName item=optionLabel from=$MODEL->getPicklistValues($FIELD_NAME)}
													{if $FIELD_NAME != 'default_module' && $FIELD_NAME != 'defaultLayout' }
														<option {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{\App\Language::translate($optionLabel, $QUALIFIED_MODULE)}</option>
													{else}
														<option value="{$optionName}" {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{\App\Language::translate($optionLabel, $optionLabel)}</option>
													{/if}
												{/foreach}
											</select>
										</div>
									{else if $FIELD_NAME == 'USE_RTE'}
										<div class="col-md-4">
											<input type="hidden" name="{$FIELD_NAME}" value="false" />
											<input type="checkbox" name="{$FIELD_NAME}" value="true" {if $FIELD_DATA[$FIELD_NAME] == 'true'} checked {/if} />
										</div>
									{else if $FIELD_DETAILS['fieldType'] == 'checkbox'}
										<div class="col-md-4">
											<select class="select2 form-control" name="{$FIELD_NAME}">
												<option value="false"  {if $FIELD_DATA[$FIELD_NAME] == 'true'} selected {/if}>{\App\Language::translate(LBL_NO)}</option>
												<option value="true" {if $FIELD_DATA[$FIELD_NAME] == 'true'} selected {/if}>{\App\Language::translate(LBL_YES)}</option>
											</select>
										</div>
									{else}

										{if $FIELD_NAME == 'upload_maxsize'}
											{assign var=MAXUPLOADSIZE value=vtlib\Functions::getMaxUploadSize()}
											<div class="col-md-4">
												<div class="input-group">
													<input type="text" class="form-control" name="{$FIELD_NAME}" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if $FIELD_VALIDATION[$FIELD_NAME]} data-validator={\App\Json::encode([$FIELD_VALIDATION[$FIELD_NAME]])} {/if} value="{$FIELD_DATA[$FIELD_NAME]}" />
													<div class="input-group-addon">{\App\Language::translate('LBL_MB', $QUALIFIED_MODULE)}</div>
												</div>
											</div>
											<label class="col-form-label">
												(upload_max_filesize: {vtlib\Functions::showBytes($MAXUPLOADSIZE)})
											</label>
										{else}
											<div class="col-md-4">
												<input type="text" class="form-control" name="{$FIELD_NAME}" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if $FIELD_VALIDATION[$FIELD_NAME]} data-validator={\App\Json::encode([$FIELD_VALIDATION[$FIELD_NAME]])} {/if} value="{$FIELD_DATA[$FIELD_NAME]}" />
											</div>
										{/if}
									{/if}
								</td></tr>
							{/foreach}
					</tbody>
				</table>
			</form>
		</div>
	</div>
{/strip}
