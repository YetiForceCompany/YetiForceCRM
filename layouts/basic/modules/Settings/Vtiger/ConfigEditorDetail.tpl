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
	<div class="tpl-Settings-Vtiger-ConfigEditorDetail" id="ConfigEditorDetails">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_CONFIG_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4">
				<div class="float-right">
					<button class="btn btn-success editButton mt-2" data-url='{$MODEL->getEditViewUrl()}' type="button" title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"><span class="fa fa-edit u-mr-5px"></span><strong>{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</div>
		</div>
		<hr>
		<div class="contents">
			<table class="table tableRWD table-bordered table-sm themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE}">
							<span class="alignMiddle">{\App\Language::translate('LBL_CONFIG_FILE', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{assign var=FIELD_DATA value=$MODEL->getViewableData()}
					{foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
						<tr><td width="30%" class="{$WIDTHTYPE} textAlignRight"><label class="muted marginRight10px">{\App\Language::translate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}</label></td>
							<td style="border-left: none;" class="{$WIDTHTYPE}">
								<span>{if $FIELD_NAME == 'default_module'}
									{\App\Language::translate($FIELD_DATA[$FIELD_NAME], $FIELD_DATA[$FIELD_NAME])}
									{else if $FIELD_DETAILS['fieldType'] == 'checkbox'}
										{if \App\Language::translate($FIELD_DATA[$FIELD_NAME]) == 'true'}
											{\App\Language::translate(LBL_YES)}
										{else}
											{\App\Language::translate(LBL_NO)}
										{/if}
										{elseif $FIELD_DETAILS['fieldType'] == 'picklist'}
											{assign var=PICKLIST value=$MODEL->getPicklistValues($FIELD_NAME)}
											{$PICKLIST[$FIELD_DATA[$FIELD_NAME]]}
											{else}
												{$FIELD_DATA[$FIELD_NAME]}
												{/if}
													{if $FIELD_NAME == 'upload_maxsize'}&nbsp;{\App\Language::translate('LBL_MB', $QUALIFIED_MODULE)}{/if}</span>
											</td>
										</tr>
										{/foreach}
										</tbody>
									</table>
								</div>
							</div>
							{/strip}
