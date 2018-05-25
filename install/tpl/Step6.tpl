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
			<form class="" name="step6" method="post" action="Install.php">
				<input type="hidden" name="mode" value="step7">
				<input type="hidden" name="auth_key" value="{$AUTH_KEY}">
				<input type="hidden" name="lang" value="{$LANG}">
				<div class="row">
					<div class="col-12 text-center">
						<h2>{\App\Language::translate('LBL_CONFIGURATION_COMPANY_DETAILS', 'Install')}</h2>
					</div>
				</div>
				<hr>
				<div class="row">
					<p class="col-12">
						{\App\Language::translate('LBL_STEP6_DESCRIPTION', 'Install')}
					</p>
				</div>
				<div class="offset2">
					<div class="table-responsive">
						<table class="config-table input-table">
							<tbody>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_NAME', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td><input type="text" name="company_name" class="form-control" data-validation-engine="validate[required]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_INDUSTRY', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td>
										<select class="select2 form-control" name="company_industry" data-validation-engine="validate[required]">
											<option value="{$ITEM}">{App\Language::translate($ITEM)}</option>
											{foreach from=Install_Utils_Model::getIndustryList() item=ITEM}
											<option value="{$ITEM}">{App\Language::translate($ITEM)}</option>
											{/foreach}
										</select>
									</td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_STREET', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td><input type="text" name="company_street" class="form-control" data-validation-engine="validate[required]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_CITY', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td><input type="text" name="company_city" class="form-control" data-validation-engine="validate[required]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_CODE', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td><input type="text" name="company_code" class="form-control" data-validation-engine="validate[required]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_STATE', 'Settings:Companies')}</td>
									<td><input type="text" name="company_state" class="form-control"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_COUNTRY', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td>
										<select class="select2 form-control" name="company_country" data-validation-engine="validate[required]">
											{foreach from=Install_Utils_Model::getCountryList() item=ITEM}
											<option value="{$ITEM}">{\App\Language::translateSingleMod($ITEM,'Other.Country')}</option>
											{/foreach}
										</select>
									</td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_PHONE', 'Settings:Companies')}</td>
									<td><input type="text" name="company_phone" class="form-control" data-validation-engine="validate[custom[phone]]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_WEBSITE', 'Settings:Companies')}</td>
									<td><input type="text" name="company_website" class="form-control" data-validation-engine="validate[custom[url]]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_EMAIL', 'Settings:Companies')}&nbsp;<span class="no">*</span></td>
									<td><input type="text" name="company_email" class="form-control" data-validation-engine="validate[required,custom[email]]"></td>
								</tr>
								<tr>
									<td class="text-right">{App\Language::translate('LBL_VATID', 'Settings:Companies')}</td>
									<td><input type="text" name="company_vatid" class="form-control"></td>
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
			</form>
		</div>
	</div>
{/strip}
