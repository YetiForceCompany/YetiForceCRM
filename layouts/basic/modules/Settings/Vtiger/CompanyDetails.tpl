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
	<input type="hidden" id="supportedImageFormats" value='{\includes\utils\Json::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}' />
	<div class="padding-left1per">
		<div class="row widget_header ">
			<div class="form-group marginbottomZero noSpaces">
				<div class="col-md-8">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					{if $DESCRIPTION}
						<h6>&nbsp;{vtranslate({$DESCRIPTION}, $QUALIFIED_MODULE)}</h6>
					{/if}
				</div>
				<div class="col-md-4 marginbottomZero">
					<div class="pull-right btn-toolbar">
						<button id="addCustomField" class="btn btn-success" type="button">
							<strong>{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
						</button>
						<button id="updateCompanyDetails" class="btn btn-info">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div id="companyDetailsContainer" class="{if !empty($ERROR_MESSAGE)}hide{/if}">
			<table class="table table-bordered">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
						{if $FIELD neq 'height_panellogo' && $FIELD neq 'logoname' && $FIELD neq 'logo' && $FIELD neq 'panellogo' && $FIELD neq 'panellogoname'}
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
				<br>
			<div class='table-responsive'>
				<table class="table table-bordered marginBottom10px">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th colspan="2" class="{$WIDTHTYPE}">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_COMPANY_LOGO_IN_LOGIN',$QUALIFIED_MODULE)}
							&nbsp;&nbsp;&nbsp{vtranslate('LBL_HEIGHT_LOGO',$QUALIFIED_MODULE)}: {$MODULE_MODEL->get('height_panellogo')}px </th>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE} companyLogoContainerSettings">
								<div class="companyLogo">
									<img src="{$MODULE_MODEL->getLogoPath('panellogoname')}" class="alignMiddle"/>
								</div>
							</td>
						</tr>
						<tr>
							<th colspan="2" class="{$WIDTHTYPE}">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_COMPANY_LOGO_IN_SYSTEM',$QUALIFIED_MODULE)}</th>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE} companyLogoContainerSettings">
								<div class="companyLogo">
									<img src="{$MODULE_MODEL->getLogoPath('logoname')}" class="alignMiddle"/>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}"  id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
			<input type="hidden" name="module" value="Vtiger" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="CompanyDetailsSave" />
			<table class="table table-bordered" >
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
						{if $FIELD neq 'height_panellogo' && $FIELD neq 'logoname' && $FIELD neq 'logoname' && $FIELD neq 'logo' && $FIELD neq 'panellogo' && $FIELD neq 'panellogoname'}
							<tr>
								<td style="width:25%">
									<div class=" pull-right">
										{if $FIELD eq 'organizationname'}<span class="redColor">*</span>{/if}{vtranslate($FIELD,$QUALIFIED_MODULE)}
									</div>
								</td>
								<td>	
									<div class="col-md-5">
										{if $FIELD eq 'address'}
											<textarea class="form-control" name="{$FIELD}">{$MODULE_MODEL->get($FIELD)}</textarea>
										{else}
											<input class="form-control" type="text" {if $FIELD eq 'organizationname'} data-validation-engine="validate[required]" {/if} class="input-xlarge" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
										{/if}
									</div>
								</td>	
							</tr>
						{/if}
					{/foreach}
				</tbody>
			</table>
			<br><br>
			<div class='table-responsive'>
				<table class="table table-bordered">
					<thead>
						<tr class="blockHeader">
							<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th colspan="2" class="{$WIDTHTYPE}">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_COMPANY_LOGO_IN_LOGIN',$QUALIFIED_MODULE)}</th>
						</tr>
						<tr>
							<td class="companyLogoContainerSettings" >
								<div class="companyLogo" style="max-width: 250px; max-height: 200px;">
									<img src="{$MODULE_MODEL->getLogoPath('panellogoname')}" class="alignMiddle" />
								</div>
							</td>
							<td>
								<div class='col-xs-12'>
									<div class=''>
										<input type="file" name="panellogo" id="logoFile" />&nbsp;&nbsp;

									</div>
									<div class=" col-xs-12 alert alert-info pull-right">
										{vtranslate('LBL_PANELLOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
									</div>
									<div class='col-xs-12 paddingLRZero'>								    
										<div class='col-md-2 paddingLRZero'>
											{vtranslate('LBL_HEIGHT_LOGO',$QUALIFIED_MODULE)}[px]
										</div>
										<div class='col-md-3 paddingLRZero'>
											<select name='height_panellogo' class='chzn-select form-control'>
												{foreach from=$MODULE_MODEL->getHeights() item=HEIGHT }
													<option value='{$HEIGHT}' {if $HEIGHT eq $MODULE_MODEL->get('height_panellogo')} selected {/if} >{$HEIGHT}px</option>										    
												{/foreach}
											</select>
										</div>
										{if !empty($ERROR_MESSAGE)}
											<br><br><div class="marginLeftZero col-md-9 alert alert-warning">
												{vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
											</div>
										{/if}
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th colspan="2" class="{$WIDTHTYPE}">&nbsp;&nbsp;&nbsp;{vtranslate('LBL_COMPANY_LOGO_IN_SYSTEM',$QUALIFIED_MODULE)}</th>
						</tr>
						<tr>
							<td class="companyLogoContainerSettings">
								<div class="companyLogo" style="max-width: 250px; max-height: 200px;">
									<img src="{$MODULE_MODEL->getLogoPath('logoname')}" class="alignMiddle" />
								</div>
							</td>
							<td>
								<div class='col-xs-12'>
									<input type="file" name="logo" id="panelLogoFile" />&nbsp;&nbsp;
									<div class="col-xs-12 alert alert-info pull-right">
										{vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
									</div>
									{if !empty($ERROR_MESSAGE)}
										<br><br><div class="marginLeftZero col-md-9 alert alert-warning">
											{vtranslate($ERROR_MESSAGE,$QUALIFIED_MODULE)}
										</div>
									{/if}
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			{include file="ModalFooter.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
		</form>
		<div class="addCustomFieldModal modal fade" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h4>
					</div>
					<form class="form-horizontal addCustomBlockForm">
						<div class="modal-body">
							<div class="form-group">
								<div class="col-md-3 control-label" >{vtranslate('LBL_FIELD_NAME',$QUALIFIED_MODULE)}</div>
								<div class="col-md-6 controls">
									<input type="text" name="fieldName" id="fieldName" class="form-control"/>
								</div>
							</div>
						</div>
						{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
					</form>
				</div>
			</div>
		</div>
{/strip}

