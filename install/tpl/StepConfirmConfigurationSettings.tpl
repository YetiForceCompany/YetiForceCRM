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
{function SHOW_HELP_TEXT ITEM=[] KEY=''}
		{if empty($ITEM['label'])}{$KEY}{else}{\App\Language::translate('LBL_LABEL_'|cat:$ITEM['label'], 'ConfReport')}{/if}
		{if !$ITEM['status']}
			{assign var="HELP_TEXT" value='LBL_HELP_'|cat:strtoupper(\App\Colors::sanitizeValue($KEY))}
			{assign var="HELP_TEXT_TRANS" value=\App\Language::translateEncodeHtml($HELP_TEXT, 'ConfReport')}
			{if !empty($HELP_TEXT_TRANS) && $HELP_TEXT_TRANS!==$HELP_TEXT }
				<a href="#" class="js-popover-tooltip float-right" data-js="popover"
				   data-trigger="focus hover" data-placement="right"
				   data-content="{$HELP_TEXT_TRANS}">
					<span class="fas fa-info-circle"></span>
				</a>
			{/if}
		{/if}
	{/function}
	<div class="tpl-install-tpl-StepConfirmConfigurationSettings container px-2 px-sm-3">
		<main class="main-container">
			<div class="inner-container">
				<form class="" name="step{$STEP_NUMBER}" method="post" action="Install.php">
					<input type="hidden" name="mode" value="{$NEXT_STEP}">
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
								<caption class="sr-only">{\App\Language::translate('LBL_DATABASE_INFORMATION','Install')}</caption>
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
									<td class="{if !$INFORMATION['db_server']}no{/if}">{\App\Language::translate('LBL_HOST_NAME','Install')}</td>
									<td>{$INFORMATION['db_server']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['db_port']}no{/if}">{\App\Language::translate('LBL_HOST_PORT','Install')}</td>
									<td>{$INFORMATION['db_port']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['db_name']}no{/if}">{\App\Language::translate('LBL_DB_NAME','Install')}</td>
									<td>{$INFORMATION['db_name']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['db_username']}no{/if}">{\App\Language::translate('LBL_USERNAME','Install')}</td>
									<td>{$INFORMATION['db_username']}</td>
								</tr>
								</tbody>
							</table>
						</div>
						<div class="table-responsive">
							<table class="config-table input-table">
								<caption class="sr-only">{\App\Language::translate('LBL_SYSTEM_INFORMATION','Install')}</caption>
								<thead>
								<tr>
									<th colspan="2">{\App\Language::translate('LBL_SYSTEM_INFORMATION','Install')}</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td class="{if !$INFORMATION['site_URL']}no{/if}">{\App\Language::translate('LBL_URL','Install')}</td>
									<td><a href="#">{$INFORMATION['site_URL']}</a></td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['currency_name']}no{/if}">{\App\Language::translate('LBL_CURRENCY','Install')}</td>
									<td>{$INFORMATION['currency_name']}</td>
								</tr>
								</tbody>
							</table>
						</div>
						<div class="table-responsive">
							<table class="config-table input-table">
								<caption class="sr-only">{\App\Language::translate('LBL_ADMIN_USER_INFORMATION','Install')}</caption>
								<thead>
								<tr>
									<th colspan="2">{\App\Language::translate('LBL_ADMIN_USER_INFORMATION','Install')}</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td class="{if !$INFORMATION['firstname']}no{/if}">{\App\Language::translate('LBL_FIRST_NAME','Install')}</td>
									<td>{$INFORMATION['firstname']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['lastname']}no{/if}">{\App\Language::translate('LBL_LAST_NAME','Install')}</td>
									<td>{$INFORMATION['lastname']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['user_name']}no{/if}">{\App\Language::translate('LBL_USERNAME','Install')}</td>
									<td>{$INFORMATION['user_name']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['admin_email']}no{/if}">{\App\Language::translate('LBL_EMAIL','Install')}</td>
									<td>{$INFORMATION['admin_email']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['default_timezone']}no{/if}">{\App\Language::translate('LBL_TIME_ZONE','Install')}</td>
									<td>{$INFORMATION['default_timezone']}</td>
								</tr>
								<tr>
									<td class="{if !$INFORMATION['dateformat']}no{/if}">{\App\Language::translate('LBL_DATE_FORMAT','Install')}</td>
									<td>{$INFORMATION['dateformat']}</td>
								</tr>
								</tbody>
							</table>
						</div>

						<div class="table-responsive">
							{if !empty($CONF_REPORT_RESULT['database'])}
								<br>
								<table class="config-table table u-word-break-all" data-type="database">
									<caption
											class="sr-only">{App\Language::translate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</caption>
									<thead>
									<tr>
										<th>{App\Language::translate('LBL_PARAMETER', 'Install')}</th>
										<th>{App\Language::translate('LBL_REQUIRED_VALUE', 'Install')}</th>
										<th>{App\Language::translate('LBL_PRESENT_VALUE', 'Install')}</th>
									</tr>
									</thead>
									<tbody>
									{foreach from=$CONF_REPORT_RESULT['database'] key=KEY item=ITEM}
										<tr {if !$ITEM['status']}class="table-danger font-weight-bold js-wrong-status"{/if} data-js="length">
											<td>
												{SHOW_HELP_TEXT ITEM=$ITEM KEY=$KEY}
											</td>
											{if isset($ITEM['recommended'])}
												<td>
													{$ITEM['recommended']}
												</td>
												<td>
													{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'ConfReport')}{/if}
												</td>
											{else}
												<td colspan="2">
													{if !empty($ITEM['www'])}{App\Language::translate($ITEM['www'], 'ConfReport')}{/if}
												</td>
											{/if}
										</tr>
									{/foreach}
									</tbody>
								</table>
							{/if}
						</div>

						<div class="form-button-nav fixed-bottom button-container p-1 bg-light">
							<div class="text-center w-100">
								<a class="btn btn-lg c-btn-block-xs-down btn-danger mr-sm-1 mb-1 mb-sm-0" href="Install.php"
								   role="button">
									<span class="fas fa-lg fa-arrow-circle-left mr-2"></span>
									{App\Language::translate('LBL_BACK', 'Install')}
								</a>
								{if !$BREAK_INSTALL}
									<button type="submit" class="btn btn-lg c-btn-block-xs-down btn-primary">
										{App\Language::translate('LBL_NEXT', 'Install')}
										<span class="fas fa-lg fa-arrow-circle-right ml-2"></span>
									</button>
								{/if}
							</div>
						</div>
					</div>
				</form>
			</div>
		</main>
	</div>
{/strip}
