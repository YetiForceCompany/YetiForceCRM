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
	<!-- tpl-Import-ImportError -->
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div>
		{if isset($FOR_MODULE)}
			<input type="hidden" name="module" value="{$FOR_MODULE}"/>
		{/if}
		<table class="u-w-90per m-auto searchUIBasic well">
			<tr>
				<td class="font-x-large text-center">
					<h3>
						<strong>
							{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)}
							- {\App\Language::translate('LBL_ERROR', $MODULE_NAME)}
						</strong>
					</h3>
				</td>
			</tr>
			<tr>
				<td class="d-flex justify-content-center">
					<table class="text-center w-100 dvtSelectedCell thickBorder importContents redColor">
						<tr>
							<td class="text-center p-3">
								{$ERROR_MESSAGE}
							</td>
						</tr>
						{if !empty($ERROR_DETAILS)}
							<tr>
								<td class="errorMessage d-flex justify-content-center">
									{\App\Language::translate('ERR_DETAILS_BELOW', $MODULE_NAME)}
									<table class="d-flex justify-content-center">
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
							<button class="create btn btn-danger u-mr-5px btn-sm" name="{$_LABEL}" onclick="{$_ACTION}">
								<strong>{\App\Language::translate($_LABEL, $MODULE_NAME)}</strong>
							</button>
						{/foreach}
					{/if}
					<button class="edit btn btn-success btn-sm" name="goback" onclick="window.history.back()">
						<strong>{\App\Language::translate('LBL_GO_BACK', $MODULE_NAME)}</strong>
					</button>
				</td>
			</tr>
		</table>
	</div>
	<!-- /tpl-Import-ImportError -->
{/strip}
