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
	<input type="hidden" id="supportedImageFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}' />
	<div class="padding-left1per">
		<div class="row-fluid widget_header">
			<div class="span8">
				<h3>{vtranslate('LBL_COMPANY_DETAILS', $QUALIFIED_MODULE)}</h3>
				{if $DESCRIPTION}<span style="font-size:12px;color: black;"> - &nbsp;{vtranslate({$DESCRIPTION}, $QUALIFIED_MODULE)}</span>{/if}
			</div>
			<div class="span3">
				<button id="addCustomField" class="btn  pull-right" type="button">
					<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<div class="span1">
			
			<button id="updateCompanyDetails" class="btn pull-right">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
			</div>
		</div>
		<hr>
		{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
		<div  id="CompanyDetailsContainer" class="{if !empty($ERROR_MESSAGE)}hide{/if}">
			<div class="row-fluid">
				<table class="table table-bordered">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="{$WIDTHTYPE}">
								<div class="companyLogo" style="max-width: 250px; max-height: 200px;">
									<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" />
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<br>
				<table class="table table-bordered">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
							{if $FIELD neq 'logoname' && $FIELD neq 'logo' }
								<tr>
									<td class="{$WIDTHTYPE}" style="width:25%"><label class="pull-right">{{{vtranslate($FIELD,$QUALIFIED_MODULE)}|ucfirst}|replace:'_':' '}</label></td>
									<td class="{$WIDTHTYPE}">
										{if $FIELD eq 'address'} {$MODULE_MODEL->get($FIELD)|nl2br} {else} {$MODULE_MODEL->get($FIELD)} {/if}
									</td>
								</tr>
							{/if}
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>

	<form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}"  id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="Vtiger" />
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="CompanyDetailsSave" />
		<table class="table table-bordered">
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td >
						<div class="companyLogo" style="max-width: 250px; max-height: 200px;">
							<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" />
						</div>
					</td>
					<td>
						<div>
							<input type="file" name="logo" id="logoFile" />&nbsp;&nbsp;
							<span class="alert alert-info">
								{vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
							</span>
							{if !empty($ERROR_MESSAGE)}
								<br><br><div class="marginLeftZero span9 alert alert-error">
									{vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
								</div>
							{/if}
						</div>
					</td>
				</tr>
			</tbody>
		</table>	
		<br><br>	
		<table class="table table-bordered" >
			<thead>
				<tr class="blockHeader">
					<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
		{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
			{if $FIELD neq 'logoname' && $FIELD neq 'logo' }
				<tr>
					<td style="width:25%">
						<div class="control-group">
							<div class=" pull-right">
								{vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'organizationname'}<span class="redColor">*</span>{/if}
							</div>
						</div>
					</td>
					<td>	
						<div class="">
							{if $FIELD eq 'address'}
								<textarea name="{$FIELD}" style="width: 40%">{$MODULE_MODEL->get($FIELD)}</textarea>
							{else}
								<input type="text" {if $FIELD eq 'organizationname'} data-validation-engine="validate[required]" {/if} class="input-xlarge" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
							{/if}
						</div>
					</td>	
				</tr>
			{/if}
		{/foreach}
			</tbody>
		</table>
		{include file="ModalFooter.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</form>

	<div class="modal addCustomFieldModal hide">
		<div class="modal-header contentsBackground">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h3>
		</div>
		<form class="form-horizontal addCustomBlockForm" method="post" action="index.php" >
			<div class="modal-body">
				<div class="control-group">
					<input type="hidden" name="module" value="Vtiger" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="CompanyDetailsFieldSave" />
					<div class="control-group">
						<div class="control-label">{vtranslate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</div>
						<div class="controls">
							<input type="text" name="field name" id="fieldName" />
						</div>
					</div>
				</div>
			
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>

{/strip}