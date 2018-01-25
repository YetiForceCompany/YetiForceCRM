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
	<form class="form-horizontal" name="step3" method="post" action="Install.php">
		<input type="hidden" name="mode" value="step4" />
		<input type="hidden" name="lang" value="{$LANG}" />

		<div class="row main-container">
			<div class="inner-container">
				<div class="pull-right">
					<a class="helpBtn" href="https://yetiforce.com/en/implementer/installation-updates/103-web-server-requirements.html" target="_blank" rel="noreferrer">
						<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
					</a>
				</div>
				<h4>{App\Language::translate('LBL_INSTALL_PREREQUISITES', 'Install')}</h4>
				<hr>
				<div>
					<div class="offset2">
						<div class="pull-right">
							<div class="button-container">
								<a href ="#">
									<input type="button" class="btn btn-default" value="{App\Language::translate('LBL_RECHECK', 'Install')}" id='recheck'/>
								</a>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="offset2">
						<div>
							{assign var="LIBRARY" value=Settings_ConfReport_Module_Model::getLibrary()}
							<table class="config-table table">
								<thead>
									<tr>
										<th><label>{App\Language::translate('LBL_LIBRARY', 'Settings::ConfReport')}</label></th>
										<th><label>{App\Language::translate('LBL_INSTALLED', 'Settings::ConfReport')}</label></th>
										<th><label>{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}</label></th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$LIBRARY key=key item=item}
										<tr {if $item.status == 'LBL_NO'}class="danger"{/if}>
											<td>{App\Language::translate($key, 'Settings::ConfReport')}</td>
											<td>{App\Language::translate($item.status, 'Settings::ConfReport')}</td>
											<td>
												{if $item.mandatory}
													{App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport')}
												{else}
													{App\Language::translate('LBL_OPTIONAL', 'Settings::ConfReport')}
												{/if}
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
							{assign var="STABILITY_CONF" value=Settings_ConfReport_Module_Model::getSecurityConf(true)}
							<br />
							<table class="config-table table">
								<thead>
									<tr>
										<th>{App\Language::translate('LBL_SECURITY_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$STABILITY_CONF key=key item=item}
										<tr {if $item.status}class="danger"{/if}>
											<td>
												<label>{$key}</label>
												{if isset($item.help)}<a href="#" class="popoverTooltip pull-right" data-placement="rigth" data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}"><i class="glyphicon glyphicon-info-sign"></i></a>{/if}
											</td>
											<td><label>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</label></td>
											<td><label>{App\Language::translate($item.current, 'Settings::ConfReport')}</label></td>
										</tr>
									{/foreach}
								</tbody>
							</table>
							{assign var="STABILITY_CONF" value=Settings_ConfReport_Module_Model::getStabilityConf(true)}
							<br />
							<table class="config-table table">
								<thead>
									<tr>
										<th>{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$STABILITY_CONF key=key item=item}
										<tr {if $item.status}class="danger"{/if}>
											<td>
												<label>{$key}</label>
												{if isset($item.help)}<a href="#" class="popoverTooltip pull-right" data-placement="rigth" data-content="{App\Language::translate($item.help, 'Settings::ConfReport')}"><i class="glyphicon glyphicon-info-sign"></i></a>{/if}
											</td>
											<td><label>{App\Language::translate($item.recommended, 'Settings::ConfReport')}</label></td>
											<td><label>{App\Language::translate($item.current, 'Settings::ConfReport')}</label></td>
										</tr>
									{/foreach}
								</tbody>
							</table>
							{if $FAILED_FILE_PERMISSIONS}
								<table class="config-table table">
									<thead>
										<tr class="blockHeader">
											<th colspan="1" class="mediumWidthType">
												<span>{App\Language::translate('LBL_READ_WRITE_ACCESS', 'Install')}</span>
											</th>
											<th colspan="1" class="mediumWidthType">
												<span>{App\Language::translate('LBL_PATH', 'Settings::ConfReport')}</span>
											</th>
											<th colspan="1" class="mediumWidthType">
												<span>{App\Language::translate('LBL_PERMISSION', 'Settings::ConfReport')}</span>
											</th>
										</tr>
									</thead>
									<tbody>
										{foreach from=$FAILED_FILE_PERMISSIONS key=key item=item}
											<tr {if $item.permission eq 'FailedPermission'}class="danger"{/if}>
												<td width="23%"><label class="marginRight5px">{App\Language::translate($key, 'Settings::ConfReport')}</label></td>
												<td width="23%"><label class="marginRight5px">{App\Language::translate($item.path, 'Settings::ConfReport')}</label></td>
												<td width="23%"><label class="marginRight5px">
														{if $item.permission eq 'FailedPermission'}
															{App\Language::translate('LBL_FAILED_PERMISSION', 'Settings::ConfReport')}
														{else}
															{App\Language::translate('LBL_TRUE_PERMISSION', 'Settings::ConfReport')}
														{/if}
													</label></td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							{/if}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="button-container">
						<a class="btn btn-sm btn-default" href="Install.php" >{App\Language::translate('LBL_BACK', 'Install')}</a>
						<input type="button" class="btn btn-sm btn-primary" value="{App\Language::translate('LBL_NEXT', 'Install')}" name="step4"/>
					</div>
				</div>
			</div>
		</div>
	</form>
{/strip}
