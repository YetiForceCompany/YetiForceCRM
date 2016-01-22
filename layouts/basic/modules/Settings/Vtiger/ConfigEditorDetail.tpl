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
	<div class="" id="ConfigEditorDetails">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="widget_header row">
			<div class="col-md-8">
			    {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{vtranslate('LBL_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4">
				<div class="pull-right">
					<button class="btn btn-success editButton" data-url='{$MODEL->getEditViewUrl()}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"><strong>{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</div>
		</div>
		<hr>
		<div class="contents">
			<table class="table tableRWD table-bordered table-condensed themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE}">
							<span class="alignMiddle">{vtranslate('LBL_CONFIG_FILE', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{assign var=FIELD_DATA value=$MODEL->getViewableData()}
					{foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
						<tr><td width="30%" class="{$WIDTHTYPE} textAlignRight"><label class="muted marginRight10px">{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}</label></td>
							<td style="border-left: none;" class="{$WIDTHTYPE}">
								<span>{if $FIELD_NAME == 'default_module'}
										{vtranslate($FIELD_DATA[$FIELD_NAME], $FIELD_DATA[$FIELD_NAME])}
									{else if $FIELD_DETAILS['fieldType'] == 'checkbox'}
										{if vtranslate($FIELD_DATA[$FIELD_NAME]) == 'true'}
											{vtranslate(LBL_YES)}
										{else}
											{vtranslate(LBL_NO)}
										{/if}
									{elseif $FIELD_DETAILS['fieldType'] == 'picklist'}
										{assign var=PICKLIST value=$MODEL->getPicklistValues($FIELD_NAME)}
										{$PICKLIST[$FIELD_DATA[$FIELD_NAME]]}
									{else}
										{$FIELD_DATA[$FIELD_NAME]}
									{/if}
									{if $FIELD_NAME == 'upload_maxsize'}&nbsp;{vtranslate('LBL_MB', $QUALIFIED_MODULE)}{/if}</span>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
