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
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
		</div>
	</div>
	<div>
		<input type="hidden" name="module" value="{$FOR_MODULE}" />
		<table style=" width:90%;margin-left: 5%  " cellpadding="10" cellspacing="10" class="searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left">
					<strong>{\App\Language::translate('LBL_IMPORT', $MODULE)} - {\App\Language::translate('LBL_ERROR', $MODULE)}</strong>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents redColor">
						<tr>
							<td class="style1" align="left" colspan="2">
								{$ERROR_MESSAGE}
							</td>
						</tr>
						{if $ERROR_DETAILS neq ''}
							<tr>
								<td class="errorMessage" align="left" colspan="2">
									{\App\Language::translate('ERR_DETAILS_BELOW', $MODULE)}
									<table cellpadding="5" cellspacing="0">
										{foreach key=_TITLE item=_VALUE from=$ERROR_DETAILS}
											<tr>
												<td>{$_TITLE}</td>
												<td>-</td>
												<td>{$_VALUE}</td>
											</tr>
										{/foreach}
									</table>
								</td>
							</tr>
						{/if}
					</table>
				</td>
			</tr>
			<tr>
				<td align="right">
					{if $CUSTOM_ACTIONS neq ''}
						{foreach key=_LABEL item=_ACTION from=$CUSTOM_ACTIONS}
							<button name="{$_LABEL}" onclick="{$_ACTION}" class="create btn btn-danger"><strong>{\App\Language::translate('$_LABEL', $MODULE)}</strong></button>
								{/foreach}
							{/if}
					<button name="goback" onclick="window.history.back()" class="edit btn btn-success"><strong>{\App\Language::translate('LBL_GO_BACK', $MODULE)}</strong></button>
				</td>
			</tr>
		</table>
	</div>
{/strip}
