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
	<form class="form-horizontal" name="step4" method="post" action="Install.php">
		<input type="hidden" name="mode" value="Step5" />
		<input type="hidden" name="lang" value="{$LANG}" />
		<div class="row main-container">
			<div class="inner-container">
				<h4>{vtranslate('LBL_SYSTEM_CONFIGURATION', 'Install')} </h4>
				<hr>
				<div class="row hide" id="errorMessage"></div>
				<div class="row">
					<div class="col-md-6">
						<table class="config-table input-table">
							<thead>
								<tr><th colspan="2">{vtranslate('LBL_DATABASE_INFORMATION', 'Install')}</th></tr>
							</thead>
							<tbody>
								<tr>
									<td>{vtranslate('LBL_DATABASE_TYPE', 'Install')}<span class="no">*</span></td>
									<td>{vtranslate('MySQL', 'Install')}<input type="hidden" value="mysql" name="db_type"></td>
								</tr>
								<tr>
									<td>{vtranslate('LBL_HOST_NAME', 'Install')}<span class="no">*</span></td>
									<td><input type="text" class="form-control" value="{$DB_HOSTNAME}" name="db_hostname"></td>
								</tr>
								<tr>
									<td>{vtranslate('LBL_HOST_PORT', 'Install')}<span class="no">*</span></td>
									<td><input type="text" class="form-control" value="3306" name="db_port"></td>
								</tr>
								<tr>
									<td>{vtranslate('LBL_USERNAME', 'Install')}<span class="no">*</span></td>
									<td><input type="text" class="form-control" value="{$DB_USERNAME}" name="db_username"></td>
								</tr>
								<tr>
									<td>{vtranslate('LBL_PASSWORD','Install')}</td>
									<td><input type="password" class="form-control" value="{$DB_PASSWORD}" name="db_password"></td>
								</tr>
								<tr>
									<td>{vtranslate('LBL_DB_NAME', 'Install')}<span class="no">*</span></td>
									<td><input type="text" class="form-control" value="{$DB_NAME}" name="db_name"></td>
								</tr>
								<tr>
									<td colspan="2"><input type="checkbox" name="create_db"/><div class="chkbox"></div><label for="checkbox-1">{vtranslate('LBL_CREATE_NEW_DB','Install')}</label></td>
								</tr>
								<tr class="hide" id="root_user"><td>{vtranslate('LBL_ROOT_USERNAME', 'Install')}<span class="no">*</span></td>
									<td><input type="text" class="form-control" value="" name="db_root_username"></td>
								</tr>
								<tr class="hide" id="root_password"><td>{vtranslate('LBL_ROOT_PASSWORD', 'Install')}</td>
									<td><input type="password" class="form-control" value="" name="db_root_password"></td>
								</tr>
								<!--tr><td colspan="2"><input type="checkbox" checked name="populate"/><div class="chkbox"></div><label for="checkbox-1"> Populate database with demo data</label></td-->
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-6">
						<table class="config-table input-table">
							<thead>
								<tr><th colspan="2">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th></tr>
							</thead>
							<tbody>
								<tr><td>{vtranslate('LBL_CURRENCIES','Install')}<span class="no">*</span></td>
									<td><select name="currency_name" class="select2" style="width:220px;">
											{foreach key=CURRENCY_NAME item=CURRENCY_INFO from=$CURRENCIES}
												<option value="{$CURRENCY_NAME}" {if $CURRENCY_NAME eq 'Euro'} selected {/if}>{$CURRENCY_NAME} ({$CURRENCY_INFO.1})</option>
											{/foreach}
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<table class="config-table input-table">
							<thead><tr><th colspan="2">{vtranslate('LBL_ADMIN_INFORMATION', 'Install')}</th></tr>
							</thead>
							<tbody>
								<tr><td>{vtranslate('LBL_USERNAME', 'Install')}</td>
									<td>admin<input type="hidden" class="form-control" name="{$ADMIN_NAME}" value="admin" /></td>
								</tr>
								<tr><td>{vtranslate('LBL_PASSWORD', 'Install')}<span class="no">*</span></td>
									<td><input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="password" /></td>
								</tr>
								<tr><td>{vtranslate('LBL_RETYPE_PASSWORD', 'Install')} <span class="no">*</span></td>
									<td><input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="retype_password" />
										<span id="passwordError" class="no"></span></td>
								</tr>
								<tr><td>{vtranslate('First Name', 'Install')}</td>
									<td><input type="text" value="" class="form-control" name="firstname" /></td>
								</tr>
								<tr><td>
										{vtranslate('Last Name', 'Install')} <span class="no">*</span>
									</td><td>
										<input type="text" class="form-control" value="{$ADMIN_LASTNAME}" name="lastname" />
									</td>
								</tr>
								<tr>
									<td>
										{vtranslate('LBL_EMAIL','Install')} <span class="no">*</span>
									</td><td>
										<input type="text" class="form-control" value="{$ADMIN_EMAIL}" name="admin_email">
									</td>
								</tr>
								<tr>
									<td>
										{vtranslate('LBL_DATE_FORMAT','Install')} <span class="no">*</span>
									</td>
									<td><select class="select2 form-control" style="width:220px;" name="dateformat">
											<option>yyyy-mm-dd</option>
											<option>dd-mm-yyyy</option>
											<option>mm-dd-yyyy</option>
											<option>yyyy.mm.dd</option>
											<option>dd.mm.yyyy</option>
											<option>mm.dd.yyyy</option>
											<option>yyyy/mm/dd</option>
											<option>dd/mm/yyyy</option>
											<option>mm/dd/yyyy</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										{vtranslate('LBL_TIME_ZONE','Install')} <span class="no">*</span>
									</td>
									<td><select class="select2 form-control" name="timezone">
											{foreach item=TIMEZONE from=$TIMEZONES}
												<option value="{$TIMEZONE}" {if $TIMEZONE eq 'Europe/London'}selected{/if}>{vtranslate($TIMEZONE, 'Users')}</option>
											{/foreach}
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="button-container">
							<a class="btn btn-sm btn-default" href="Install.php" >{vtranslate('LBL_BACK', 'Install')}</a>
							<input type="button" class="btn btn-sm btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step5"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
{/strip}
