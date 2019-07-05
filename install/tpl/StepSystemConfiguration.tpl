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
	<div class="tpl-install-tpl-StepSystemConfiguration container px-2 px-sm-3">
		<main class="main-container">
			<div class="inner-container">
				<form class="" name="step{$STEP_NUMBER}" method="post" action="Install.php">
					<input type="hidden" name="mode" value="{$NEXT_STEP}">
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
								<caption class="sr-only">
									{\App\Language::translate('LBL_DATABASE_INFORMATION', 'Install')}
								</caption>
								<thead>
								<tr>
									<th colspan="2">{\App\Language::translate('LBL_DATABASE_INFORMATION', 'Install')}</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>
										<label for="db-type">{\App\Language::translate('LBL_DATABASE_TYPE', 'Install')}
											<span class="no">*</span></label>
									</td>
									<td>{\App\Language::translate('MySQL', 'Install')}
										<input id="db-type" type="hidden" value="mysql" name="db_type">
									</td>
								</tr>
								<tr>
									<td><label for="db-hostname">{\App\Language::translate('LBL_HOST_NAME', 'Install')}
											<span
													class="no">*</span></label></td>
									<td class="position-relative">
										<input id="db-hostname" type="text"
											   class="form-control validate[required]" value="{$DB_HOSTNAME}"
											   name="db_server">
									</td>
								</tr>
								<tr>
									<td><label for="db-port">{\App\Language::translate('LBL_HOST_PORT', 'Install')}<span
													class="no">*</span></label></td>
									<td class="position-relative">
										<input id="db-port" type="text" class="form-control validate[required]" value="3306" name="db_port">
									</td>
								</tr>
								<tr>
									<td><label for="db-username">{\App\Language::translate('LBL_USERNAME', 'Install')}
											<span
													class="no">*</span></label></td>
									<td class="position-relative">
										<input id="db-username" type="text" class="form-control validate[required]" value="{$DB_USERNAME}"
											   name="db_username">
									</td>
								</tr>
								<tr>
									<td>
										<label for="db-password">{\App\Language::translate('LBL_PASSWORD','Install')}</label>
									</td>
									<td class="position-relative">
										<input id="db-password" type="password" class="form-control" value="{$DB_PASSWORD}"
											   name="db_password">
									</td>
								</tr>
								<tr>
									<td><label for="db-name">{\App\Language::translate('LBL_DB_NAME', 'Install')}<span
													class="no">*</span></label></td>
									<td class="position-relative">
										<input id="db-name" type="text" class="form-control validate[required]" value="{$DB_NAME}"
											   name="db_name">
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						<div class="col-lg-6">
							<table class="config-table input-table">
								<caption
										class="sr-only">{\App\Language::translate('LBL_SYSTEM_INFORMATION', 'Install')}</caption>
								<thead>
								<tr>
									<th colspan="2">{\App\Language::translate('LBL_SYSTEM_INFORMATION','Install')}</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td>
										<label for="currency-name">{\App\Language::translate('LBL_CURRENCIES','Install')}
											<span class="no">*</span></label></td>
									<td>
										<select id="currency-name" name="currency_name" class="select2 "
												style="width:220px;">
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
									<caption
											class="sr-only">{\App\Language::translate('LBL_ADMIN_INFORMATION', 'Install')}</caption>
									<thead>
									<tr>
										<th colspan="2">{\App\Language::translate('LBL_ADMIN_INFORMATION', 'Install')}</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>
											<label for="user-name">{\App\Language::translate('LBL_USERNAME', 'Install')}</label>
										</td>
										<td class="position-relative">
											<input id="user-name" type="text"
												   class="form-control validate[required,funcCall[Install_Index_Js.checkUsername]]"
												   value="{$ADMIN_NAME}" name="user_name">
										</td>
									</tr>
									<tr>
										<td><label for="password">{\App\Language::translate('LBL_PASSWORD', 'Install')}
												<span
														class="no">*</span></label>
										</td>
										<td class="position-relative"><input type="password"
																			 class="form-control validate[required]"
																			 value="{$ADMIN_PASSWORD}" name="password"
																			 id="password"></td>
									</tr>
									<tr>
										<td>
											<label for="retype-password">{\App\Language::translate('LBL_RETYPE_PASSWORD', 'Install')}
												<span
														class="no">*</span></label></td>
										<td class="position-relative">
											<input type="password" class="form-control validate[required]"
												   value="{$ADMIN_PASSWORD}" name="retype_password" id="retype-password">
											<span id="passwordError" class="no"></span>
										</td>
									</tr>
									<tr>
										<td>
											<label for="first-name">{\App\Language::translate('LBL_FIRST_NAME', 'Install')}</label>
										</td>
										<td><input id="first-name" type="text" class="form-control"
												   value="{$ADMIN_FIRSTNAME}"
												   name="firstname">
										</td>
									</tr>
									<tr>
										<td><label for="last-name">{\App\Language::translate('LBL_LAST_NAME', 'Install')}
												<span
														class="no">*</span></label>
										</td>
										<td>
											<input id="last-name" type="text" class="form-control" value="{$ADMIN_LASTNAME}"
												   name="lastname">
										</td>
									</tr>
									<tr>
										<td><label for="admin-email">{\App\Language::translate('LBL_EMAIL','Install')}
												<span
														class="no">*</span></label></td>
										<td class="position-relative">
											<input id="admin-email" type="text"
												   class="form-control validate[required,custom[email]]"
												   value="{$ADMIN_EMAIL}" name="admin_email"></td>
									</tr>
									<tr>
										<td>
											<label for="date-format">{\App\Language::translate('LBL_DATE_FORMAT','Install')}
												<span
														class="no">*</span></label>
										</td>
										<td>
											<select class="select2 form-control" id="date-format" style="width:220px;"
													name="dateformat">
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
										<td><label for="time-zone">{\App\Language::translate('LBL_TIME_ZONE','Install')}
												<span class="no">*</span></label>
										</td>
										<td>
											<select class="select2 form-control" id="time-zone" name="default_timezone">
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
							<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
								<div class="text-center w-100">
									<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php"
									   role="button">
										<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
										{App\Language::translate('LBL_BACK', 'Install')}
									</a>
									<button type="submit" class="btn btn-lg c-btn-block-xs-down btn-primary">
										{App\Language::translate('LBL_NEXT', 'Install')}
										<span class="fas fa-lg fa-arrow-circle-right ml-2"></span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
{/strip}
