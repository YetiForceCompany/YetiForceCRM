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
	<form class="form-horizontal" name="step5" method="post" action="Install.php">
		<input type="hidden" name="mode" value="Step6" />
		<input type="hidden" name="auth_key" value="{$AUTH_KEY}" />
		<input type="hidden" name="lang" value="{$LANG}" />
		<div class="row main-container">
			<div class="inner-container">
				<h4>{vtranslate('LBL_CONFIRM_CONFIGURATION_SETTINGS','Install')}</h4>
				<hr>
				{if $DB_CONNECTION_INFO['flag'] neq true}
					<div class="offset2 row" id="errorMessage">
						<div class="col-md-12">
							<div class="alert alert-danger">
								{$DB_CONNECTION_INFO['error_msg']}<br/>
								{$DB_CONNECTION_INFO['error_msg_info']}
							</div>
						</div>
					</div>
				{/if}
				<div class="offset2 ">
					<table class="config-table input-table">
						<thead>
							<tr>
								<th colspan="2">{vtranslate('LBL_DATABASE_INFORMATION','Install')}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									{vtranslate('LBL_DATABASE_TYPE','Install')}
								</td>
								<td>
									{vtranslate('MySQL','Install')}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_HOST_NAME','Install')}
								</td><td>
									{$INFORMATION['db_hostname']}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_HOST_PORT','Install')}
								</td><td>
									{$INFORMATION['db_port']}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_DB_NAME','Install')}
								</td><td>
									{$INFORMATION['db_name']}
								</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr>
								<th colspan="2">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									{vtranslate('LBL_URL','Install')}
								</td>
								<td>
									<a href="#">{$SITE_URL}</a>
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_CURRENCY','Install')}
								</td>
								<td>
									{$INFORMATION['currency_name']}
								</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr>
								<th colspan="2">{vtranslate('LBL_ADMIN_USER_INFORMATION','Install')}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									{vtranslate('LBL_USERNAME','Install')}
								</td>
								<td>
									{$INFORMATION['admin']}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_EMAIL','Install')}
								</td><td>
									{$INFORMATION['admin_email']}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_TIME_ZONE','Install')}
								</td>
								<td>
									{$INFORMATION['timezone']}
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_DATE_FORMAT','Install')}
								</td>
								<td>
									{$INFORMATION['dateformat']}
								</td>
							</tr>
						</tbody>
					</table>
					<div class="row">
						<div class="col-md-12">
							<div class="button-container">
								<input type="button" class="btn btn-sm btn-default" value="{vtranslate('LBL_BACK','Install')}" {if $DB_CONNECTION_INFO['flag'] eq true} disabled= "disabled"{else} onclick="window.history.back()"{/if} />
								{if $DB_CONNECTION_INFO['flag'] eq true}
									<input type="button" class="btn btn-sm btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step6"/>
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
{/strip}
