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
		<input type="hidden" name="mode" value="Step4" />
		<input type="hidden" name="lang" value="{$LANG}" />

		<div class="row main-container">
			<div class="inner-container">
				<h4>{vtranslate('LBL_INSTALL_PREREQUISITES', 'Install')}</h4>
				<hr>
				<div>
					<div class="offset2">
						<div class="pull-right">
							<div class="button-container">
								<a href ="#">
									<input type="button" class="btn btn-default" value="{vtranslate('LBL_RECHECK', 'Install')}" id='recheck'/>
								</a>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="offset2">
						<div>
							<table class="config-table table">
								<thead>
									<tr>
										<th><label>{vtranslate('LBL_LIBRARY', 'Settings::ConfReport')}</label></th>
										<th><label>{vtranslate('LBL_INSTALLED', 'Settings::ConfReport')}</label></th>
										<th><label>{vtranslate('LBL_MANDATORY', 'Settings::ConfReport')}</label></th>
									</tr>
								</thead>
								<tbody>
									{foreach from=Settings_ConfReport_Module_Model::getConfigurationLibrary() key=key item=item}
										<tr {if $item.status == 'LBL_NO'}class="danger"{/if}>
											<td>{vtranslate($key, 'Settings::ConfReport')}</td>
											<td>{vtranslate($item.status, 'Settings::ConfReport')}</td>
											<td>
												{if $item.mandatory}
													{vtranslate('LBL_MANDATORY', 'Settings::ConfReport')}
												{else}
													{vtranslate('LBL_OPTIONAL', 'Settings::ConfReport')}
												{/if}
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
							<br>
							<table class="config-table table">
								<thead>
									<tr>
										<th>{vtranslate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
										<th>{vtranslate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{vtranslate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=Settings_ConfReport_Module_Model::getConfigurationValue(true) key=key item=item}
										{if $item.status}
											<tr class="danger">
												<td><label>{$key}</label></td>
												<td><label>{vtranslate($item.prefer, $MODULE)}</label></td>
												<td><label>{vtranslate($item.current, $MODULE)}</label></td>
											</tr>
										{/if}
									{/foreach}
								</tbody>
							</table>
							{if $FAILED_FILE_PERMISSIONS}
								<table class="config-table table">
									<thead>
										<tr class="blockHeader">
											<th colspan="1" class="mediumWidthType">
												<span>{vtranslate('LBL_READ_WRITE_ACCESS', 'Install')}</span>
											</th>
											<th colspan="1" class="mediumWidthType">
												<span>{vtranslate('LBL_PATH', 'Settings::ConfReport')}</span>
											</th> 							
											<th colspan="1" class="mediumWidthType">
												<span>{vtranslate('LBL_PERMISSION', 'Settings::ConfReport')}</span>
											</th>  				
										</tr>
									</thead>
									<tbody>
										{foreach from=$FAILED_FILE_PERMISSIONS key=key item=item}			
											<tr {if $item.permission eq 'FailedPermission'}class="danger"{/if}>
												<td width="23%"><label class="marginRight5px">{vtranslate($key, 'Settings::ConfReport')}</label></td>
												<td width="23%"><label class="marginRight5px">{vtranslate($item.path, 'Settings::ConfReport')}</label></td>
												<td width="23%"><label class="marginRight5px">
														{if $item.permission eq 'FailedPermission'}
															{vtranslate('LBL_FAILED_PERMISSION', 'Settings::ConfReport')}
														{else}
															{vtranslate('LBL_TRUE_PERMISSION', 'Settings::ConfReport')}
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
						<a class="btn btn-sm btn-default" href="Install.php" >{vtranslate('LBL_BACK', 'Install')}</a>
						<input type="button" class="btn btn-sm btn-primary" value="{vtranslate('LBL_NEXT', 'Install')}" name="step4"/>
					</div>
				</div>
			</div>
		</div>
	</form>
{/strip}
