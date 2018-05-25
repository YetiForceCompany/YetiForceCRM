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
			<form class="" name="step4" method="post" action="Install.php">
				<input type="hidden" name="mode" value="step4">
				<input type="hidden" name="lang" value="{$LANG}">
				<input type="hidden" id="not_allowed_logins"
					   value="{\App\Purifier::encodeHtml(\App\Json::encode($USERNAME_BLACKLIST))}">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{\App\Language::translate('LBL_SYSTEM_CONFIGURATION', 'Install')}</h2>
					</div>
				</div>
				<hr>
				<div class="row">
					<p class="col-12">
						{\App\Language::translate('LBL_STEP4_DESCRIPTION', 'Install')}
					</p>
				</div>
				<div class="row d-none" id="errorMessage"></div>
				<div class="row">
					<div class="col-lg-6 table-responsive">
						<table class="config-table input-table">
							<thead>
							<tr>
								<th colspan="2">{\App\Language::translate('LBL_DATABASE_INFORMATION', 'Install')}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>{\App\Language::translate('LBL_DATABASE_TYPE', 'Install')}<span class="no">*</span>
								</td>
								<td>{\App\Language::translate('MySQL', 'Install')}<input type="hidden" value="mysql"
																						 name="db_type"></td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_HOST_NAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" class="form-control" value="{$DB_HOSTNAME}" name="db_hostname">
								</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_HOST_PORT', 'Install')}<span class="no">*</span></td>
								<td><input type="text" class="form-control" value="3306" name="db_port"></td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_USERNAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" class="form-control" value="{$DB_USERNAME}" name="db_username">
								</td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_PASSWORD','Install')}</td>
								<td><input type="password" class="form-control" value="{$DB_PASSWORD}"
										   name="db_password"></td>
							</tr>
							<tr>
								<td>{\App\Language::translate('LBL_DB_NAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" class="form-control" value="{$DB_NAME}" name="db_name"></td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="checkbox" name="create_db">
									<div class="chkbox"></div>
									<label for="checkbox-1">{\App\Language::translate('LBL_CREATE_NEW_DB','Install')}</label>
								</td>
							</tr>
							<tr class="d-none" id="root_user">
								<td>{\App\Language::translate('LBL_ROOT_USERNAME', 'Install')}<span class="no">*</span>
								</td>
								<td><input type="text" class="form-control" value="" name="db_root_username"></td>
							</tr>
							<tr class="d-none" id="root_password">
								<td>{\App\Language::translate('LBL_ROOT_PASSWORD', 'Install')}</td>
								<td><input type="password" class="form-control" value="" name="db_root_password"></td>
							</tr>
							<!--tr><td colspan="2"><input type="checkbox" checked name="populate"><div class="chkbox"></div><label for="checkbox-1"> Populate database with demo data</label></td></tr-->
							</tbody>
						</table>
					</div>
					<div class="col-lg-6">
						<table class="config-table input-table">
							<thead>
							<tr>
								<th colspan="2">{\App\Language::translate('LBL_SYSTEM_INFORMATION','Install')}</th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td>{\App\Language::translate('LBL_CURRENCIES','Install')}<span class="no">*</span></td>
								<td>
									<select name="currency_name" class="select2 " style="width:220px;">
										{foreach key=CURRENCY_NAME item=CURRENCY_INFO from=$CURRENCIES}
											<option value="{$CURRENCY_NAME}" {if $CURRENCY_NAME eq 'Euro'} selected {/if}>{$CURRENCY_NAME}
												({$CURRENCY_INFO.1})
											</option>
										{/foreach}
									</select>
								</td>
							</tr>
							</tbody>
						</table>
						<div class="table-responsive">
							<table class="config-table input-table">
								<thead>
								<tr>
									<th colspan="2">{\App\Language::translate('LBL_ADMIN_INFORMATION', 'Install')}</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>{\App\Language::translate('LBL_USERNAME', 'Install')}</td>
									<td><input type="text"
											   class="form-control validate[required,funcCall[Install_Index_Js.checkUsername]]"
											   value="{$ADMIN_NAME}" name="user_name"></td>
								</tr>
								<tr>
									<td>{\App\Language::translate('LBL_PASSWORD', 'Install')}<span class="no">*</span>
									</td>
									<td><input type="password" class="form-control validate[required]"
											   value="{$ADMIN_PASSWORD}" name="password" id="password"></td>
								</tr>
								<tr>
									<td>{\App\Language::translate('LBL_RETYPE_PASSWORD', 'Install')} <span
												class="no">*</span></td>
									<td>
										<input type="password" class="form-control validate[required]"
											   value="{$ADMIN_PASSWORD}" name="retype_password" id="retype_password">
										<span id="passwordError" class="no"></span>
									</td>
								</tr>
								<tr>
									<td>{\App\Language::translate('First Name', 'Install')}</td>
									<td><input type="text" class="form-control" value="{$ADMIN_FIRSTNAME}"
											   name="firstname">
									</td>
								</tr>
								<tr>
									<td>{\App\Language::translate('Last Name', 'Install')} <span class="no">*</span>
									</td>
									<td><input type="text" class="form-control" value="{$ADMIN_LASTNAME}"
											   name="lastname">
									</td>
								</tr>
								<tr>
									<td>{\App\Language::translate('LBL_EMAIL','Install')} <span class="no">*</span></td>
									<td><input type="text" class="form-control validate[required,custom[email]]"
											   value="{$ADMIN_EMAIL}" name="admin_email"></td>
								</tr>
								<tr>
									<td>{\App\Language::translate('LBL_DATE_FORMAT','Install')} <span
												class="no">*</span>
									</td>
									<td>
										<select class="select2 form-control" style="width:220px;" name="dateformat">
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
									<td>{\App\Language::translate('LBL_TIME_ZONE','Install')} <span class="no">*</span>
									</td>
									<td>
										<select class="select2 form-control" name="timezone">
											{foreach item=TIMEZONE from=$TIMEZONES}
												<option value="{$TIMEZONE}"
														{if $TIMEZONE eq 'Europe/London'}selected{/if}>{\App\Language::translate($TIMEZONE, 'Users')}</option>
											{/foreach}
										</select>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						<div class="form-buttom-nav fixed-bottom button-container p-1">
							<div class="text-center">
								<a class="btn c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php">
									<span class="fas fa-arrow-circle-left mr-1"></span>
									{App\Language::translate('LBL_BACK', 'Install')}
								</a>
								<button type="submit" role="button" class="btn c-btn-block-xs-down btn-primary">
									<span class="fas fa-arrow-circle-right mr-1"></span>
									{App\Language::translate('LBL_NEXT', 'Install')}
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
