{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-YetiForce-RegistrationForm card">
		<div class="card-body">
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_NAME',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][name]"
							data-validation-engine="validate[required]"
							value="{$company['name']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_INDUSTRY',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<select class="select2 form-control" name="companies[{$company['id']}][industry]"
							data-validation-engine="validate[required]">
						{foreach from=Settings_Companies_Module_Model::getIndustryList() item=ITEM}
							<option value="{$ITEM}"
									{if $company['industry'] === $ITEM}selected="true"{/if}>
								{App\Language::translate($ITEM)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_CITY',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][city]"
							data-validation-engine="validate[required]"
							value="{$company['city']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_COUNTRY',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<select class="select2 form-control" name="companies[{$company['id']}][country]"
							data-validation-engine="validate[required]">
						{foreach from=\App\Fields\Country::getAll() item=ITEM}
							<option value="{$ITEM['name']}"
									{if $company['country'] === $ITEM['name']}selected="true"{/if}>{\App\Language::translateSingleMod($ITEM['name'],'Other.Country')}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_WEBSITE',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][website]"
							value="{$company['website']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right">
					<div class="js-popover-tooltip ml-2 mr-2 d-inline mt-2" data-js="popover"
						 data-content="{\App\Purifier::encodeHtml(App\Language::translateArgs("LBL_EMAIL_NEWSLETTER_INFO", $COMPANIES_MODULE,"<a href=\"https://yetiforce.com/pl/newsletter-info\">{App\Language::translate('LBL_PRIVACY_POLICY', $COMPANIES_MODULE)}</a>"))}">
						<span class="fas fa-info-circle"></span></div>
					<b>{\App\Language::translate('LBL_EMAIL',$COMPANIES_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][email]"
							value="{$company['email']}">
				</div>
			</div>
		</div>
	</div>
{/strip}
