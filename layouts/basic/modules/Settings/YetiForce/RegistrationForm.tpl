{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="card">
		<div class="card-body">
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_COMPANY_NAME',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][name]"
							data-validation-engine="validate[required]"
							value="{$company['name']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_INDUSTRY',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<select class="select2 form-control" name="companies[{$company['id']}][industry]">
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
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_CITY',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][city]"
							data-validation-engine="validate[required]"
							value="{$company['city']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_COUNTRY',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<select class="select2 form-control" name="companies[{$company['id']}][country]">
						{foreach from=\App\Fields\Country::getAll() item=ITEM}
							<option value="{$ITEM['name']}"
									{if $company['country'] === $ITEM['name']}selected="true"{/if}>{\App\Language::translateSingleMod($ITEM['name'],'Other.Country')}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_WEBSITE',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][website]"
							data-validation-engine="validate[required,custom[url]]"
							value="{$company['website']}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-lg-4 col-form-label text-left text-lg-right"><b>{\App\Language::translate('LBL_EMAIL',$QUALIFIED_MODULE)}</b></label>
				<div class="col-lg-8">
					<input
							class="form-control"
							name="companies[{$company['id']}][email]"
							data-validation-engine="validate[required,custom[email]]"
							value="{$company['email']}">
				</div>
			</div>
		</div>
	</div>
{/strip}