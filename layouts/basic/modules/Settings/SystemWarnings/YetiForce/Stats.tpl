{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<form class="tpl-Settings-SystemWarnings-YetiForce-Stats form-horizontal row validateForm" method="post"
		  action="index.php">
		<div class="col-md-12">
			<h3>
				{App\Language::translate('LBL_STATS','Settings:SystemWarnings')}
			</h3>
			<p>{App\Language::translate('LBL_STATS_DESC','Settings:SystemWarnings')}</p>
			{assign var=COMPANY value=\App\Company::getInstanceById()}
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked disabled>
					</div>
				</div>
				<input type="text" name="company_name" class="form-control"
					   data-validation-engine="validate[required,funcCall[Vtiger_YetiForceCompanyName_Validator_Js.invokeValidation]]"
					   placeholder="{App\Language::translate('LBL_NAME','Settings:Companies')}"
					   value="{$COMPANY->get('name')}">
			</div>
			<br/>
			<div class="input-group flex-nowrap">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked disabled>
					</div>
				</div>
				<select class="select2 form-control" name="company_industry"
						data-validation-engine="validate[required]">
					{foreach from=Settings_Companies_Module_Model::getIndustryList() item=ITEM}
						<option value="{$ITEM}"
								{if $COMPANY->get('industry') eq $ITEM}selected{/if}>{App\Language::translate($ITEM)}</option>
					{/foreach}
				</select>
			</div>
			<br/>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked disabled>
					</div>
				</div>
				<input type="text" name="company_city" class="form-control" data-validation-engine="validate[required]"
					   placeholder="{App\Language::translate('LBL_CITY','Settings:Companies')}"
					   value="{$COMPANY->get('city')}">
			</div>
			<br/>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked disabled
							   title="{App\Language::translate('LBL_COUNTRY','Settings:Companies')}">
					</div>
				</div>
				<select class="select2 form-control" name="company_country" data-validation-engine="validate[required]">
					{foreach from=\App\Fields\Country::getAll() item=ITEM}
						<option value="{$ITEM['name']}"
								{if $COMPANY->get('country') eq $ITEM['name']}selected{/if}>{App\Language::translate($ITEM['name'],'Other.Country')}</option>
					{/foreach}
				</select>
			</div>
			<br/>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked>
					</div>
				</div>
				<input type="text" name="company_website" class="form-control"
					   placeholder="{App\Language::translate('LBL_WEBSITE','Settings:Companies')}"
					   value="{$COMPANY->get('website')}">
			</div>
			<br/>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text">
						<input type="checkbox" checked>
					</div>
				</div>
				<input type="text" name="company_email" class="form-control"
					   placeholder="{App\Language::translate('LBL_EMAIL','Settings:Companies')}"
					   value="{$COMPANY->get('email')}">
			</div>
			<br/>
			<div class="float-right">
				<button type="button" class="btn btn-success ajaxBtn mr-1">
					<span class="fas fa-check mr-1"></span>
					{App\Language::translate('LBL_SEND','Settings:SystemWarnings')}
				</button>
				<button type="button" class="btn btn-danger cancel">
					<span class="fas fa-ban mr-1"></span>
					{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
				</button>
			</div>
			<div class="clearfix"></div>
		</div>
	</form>
{/strip}
