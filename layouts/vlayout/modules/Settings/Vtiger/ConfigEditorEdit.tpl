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
<div class="container-fluid">
	<div class="contents">
		<form id="ConfigEditorForm" class="form-horizontal" data-detail-url="{$MODEL->getDetailViewUrl()}" method="POST">
			<div class="widget_header row-fluid">
				<div class="span8"><h3>{vtranslate('LBL_CONFIG_EDITOR', $QUALIFIED_MODULE)}</h3>&nbsp;{vtranslate('LBL_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}</div>
				<div class="span4 btn-toolbar">
					<div class="pull-right">
						<button class="btn btn-success saveButton" type="submit" title="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<a type="reset" class="cancelLink" title="{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					</div>
				</div>
			</div>
			<hr>
			{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
			{assign var=FIELD_VALIDATION  value=['HELPDESK_SUPPORT_EMAIL_ID' => ['name'=>'Email'],
												'upload_maxsize' => ['name' => 'NumberRange5'],
												'history_max_viewed' => ['name' => 'NumberRange5'],
												'listview_max_textlength' => ['name' => 'NumberRange100'],
												'list_max_entries_per_page' => ['name' => 'NumberRange100']]}
			<table class="table table-bordered table-condensed themeTableColor">
				<thead>
					<tr class="blockHeader"><th colspan="2" class="{$WIDTHTYPE}">{vtranslate('LBL_CONFIG_FILE', $QUALIFIED_MODULE)}</th></tr>
				</thead>
				<tbody>
					{assign var=FIELD_DATA value=$MODEL->getViewableData()}
					{foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
						<tr><td width="30%" class="{$WIDTHTYPE}"><label class="muted pull-right marginRight10px">{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}</label></td>
							<td style="border-left: none;" class="row-fluid {$WIDTHTYPE}">
								{if $FIELD_DETAILS['fieldType'] == 'picklist'}
									<span class="span3">
									<select class="select2 row-fluid" name="{$FIELD_NAME}">
										{foreach key=optionName item=optionLabel from=$MODEL->getPicklistValues($FIELD_NAME)}
											{if $FIELD_NAME != 'default_module'}
												<option {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{vtranslate($optionLabel, $QUALIFIED_MODULE)}</option>
											{else}
												<option value="{$optionName}" {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{vtranslate($optionLabel, $optionLabel)}</option>
											{/if}
										{/foreach}
									</select>
									</span>
								{else if $FIELD_NAME == 'USE_RTE'}
									<input type="hidden" name="{$FIELD_NAME}" value="false" />
									<input type="checkbox" name="{$FIELD_NAME}" value="true" {if $FIELD_DATA[$FIELD_NAME] == 'true'} checked {/if} />
								{else}
									<input type="text" name="{$FIELD_NAME}" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {if $FIELD_VALIDATION[$FIELD_NAME]} data-validator={Zend_Json::encode([$FIELD_VALIDATION[$FIELD_NAME]])} {/if} value="{$FIELD_DATA[$FIELD_NAME]}" />
									{if $FIELD_NAME == 'upload_maxsize'}&nbsp;{vtranslate('LBL_MB', $QUALIFIED_MODULE)}{/if}
								{/if}</td></tr>
					{/foreach}
				</tbody>
			</table>
		</form>
	</div>
</div>
{/strip}