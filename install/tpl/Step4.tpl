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
	<div class="main-container">
		<div class="inner-container">
			<form class="" name="step5" method="post" action="Install.php">
				<input type="hidden" name="mode" value="step5">
				<input type="hidden" name="auth_key" value="{$AUTH_KEY}">
				<input type="hidden" name="lang" value="{$LANG}">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{\App\Language::translate('LBL_CONFIRM_CONFIGURATION_SETTINGS', 'Install')}</h2>
					</div>
				</div>
				<hr>
				<div class="row">
					<p class="col-12">
						{\App\Language::translate('LBL_STEP5_DESCRIPTION', 'Install')}
					</p>
				</div>
				{if $DB_CONNECTION_INFO['flag'] neq true}
					<div class="offset2 row" id="errorMessage">
						<div class="col-md-12">
							<div class="alert alert-danger">
								{$DB_CONNECTION_INFO['error_msg']}<br>
								{$DB_CONNECTION_INFO['error_msg_info']}
							</div>
						</div>
					</div>
				{/if}
				<div class="offset2 ">
					<div class="table-responsive">
						<table class="config-table input-table">
							<thead>
							<tr>
								<th colspan="2">{\App\Language::translate('LBL_DATABASE_INFORMATION','Install')}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>{\App\Language::translate('LBL_DATABASE_TYPE','Install')}</td>
								<td>{\App\Language::translate('MySQL','Install')}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_HOST_NAME','Install')}</td>
								<td>{$INFORMATION['db_hostname']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_HOST_PORT','Install')}</td>
								<td>{$INFORMATION['db_port']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_DB_NAME','Install')}</td>
								<td>{$INFORMATION['db_name']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_USERNAME','Install')}</td>
								<td>{$INFORMATION['db_username']}</td>
							</tr>
							</tbody>
						</table>
					</div>
					<div class="table-responsive">
						<table class="config-table input-table">
							<thead>
							<tr>
								<th colspan="2">{\App\Language::translate('LBL_SYSTEM_INFORMATION','Install')}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>{\App\Language::translate('LBL_URL','Install')}</td>
								<td><a href="#">{$SITE_URL}</a></td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_CURRENCY','Install')}</td>
								<td>{$INFORMATION['currency_name']}</td>
							</tr>
							</tbody>
						</table>
					</div>
					<div class="table-responsive">
						<table class="config-table input-table">
							<thead>
							<tr>
								<th colspan="2">{\App\Language::translate('LBL_ADMIN_USER_INFORMATION','Install')}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>{\App\Language::translate('First Name','Install')}</td>
								<td>{$INFORMATION['firstname']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('Last Name','Install')}</td>
								<td>{$INFORMATION['lastname']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_USERNAME','Install')}</td>
								<td>{$INFORMATION['user_name']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_EMAIL','Install')}</td>
								<td>{$INFORMATION['admin_email']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_TIME_ZONE','Install')}</td>
								<td>{$INFORMATION['timezone']}</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_DATE_FORMAT','Install')}</td>
								<td>{$INFORMATION['dateformat']}</td>
							</tr>
							</tbody>
						</table>
					</div>
					<div class="form-buttom-nav fixed-bottom button-container p-1">
						<div class="text-center">
							<a class="btn c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="#"
									{if $DB_CONNECTION_INFO['flag'] eq true} disabled="disabled"{else} onclick="window.history.back()"{/if}>
								<span class="fas fa-arrow-circle-left mr-1"></span>
								{App\Language::translate('LBL_BACK', 'Install')}
							</a>
							{if $DB_CONNECTION_INFO['flag'] eq true}
								<button type="submit" role="button" class="btn c-btn-block-xs-down btn-primary">
									<span class="fas fa-arrow-circle-right mr-1"></span>
									{App\Language::translate('LBL_NEXT', 'Install')}
								</button>
							{/if}
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
