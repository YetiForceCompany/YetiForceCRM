{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-EditView -->
	<div class="row mb-2 widget_header">
		<div class="col-12 d-flex">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			<a class="js-popover-tooltip my-auto ml-1 ml-lg-2" role="button" data-js="popover"
			   data-content="{App\Language::translate('LBL_COMPANIES_DESCRIPTION', $QUALIFIED_MODULE)}"
			   href="#" data-trigger="focus hover">
				<span class="fas fa-info-circle"></span>
				<span class="sr-only">{App\Language::translate('LBL_COMPANIES_DESCRIPTION', $QUALIFIED_MODULE)}</span>
			</a>
		</div>
	</div>
	<div class="editViewContainer container">
		<form name="EditCompanies" action="index.php" method="post" id="EditView" enctype="multipart/form-data">
			<div class="card mb-2">
				<div class="card-header">
					<span class="adminIcon-company-detlis"
						  aria-hidden="true"></span> {App\Language::translate('LBL_COMPANIES_DESCRIPTION', $QUALIFIED_MODULE)}
				</div>
				<div class="card-body">
					{if $COMPANY_COLUMNS}
						<input type="hidden" name="module" value="Companies">
						<input type="hidden" name="parent" value="Settings"/>
						<input type="hidden" name="action" value="SaveAjax"/>
						<input type="hidden" name="mode" value="updateCompany">
						<input type="hidden" name="record" value="{$RECORD_ID}"/>
						{foreach from=$COMPANY_COLUMNS item=COLUMN}
							{if $COLUMN eq 'industry'}
								<div class="form-group row">
									<label class="col-lg-2 col-form-label text-left text-lg-right">
										{App\Language::translate('LBL_INDUSTRY', $QUALIFIED_MODULE)}
									</label>
									<div class="col-lg-10">
										<select class="select2 form-control" name="industry">
											{foreach from=Settings_Companies_Module_Model::getIndustryList() item=ITEM}
												<option value="{$ITEM}"
														{if $RECORD_MODEL->get('industry') == $ITEM}selected="true"{/if}>
													{App\Language::translate($ITEM)}
												</option>
											{/foreach}
										</select>
									</div>
								</div>
							{elseif $COLUMN eq 'country'}
								<div class="form-group row">
									<label class="col-lg-2 col-form-label text-left text-lg-right">
										{App\Language::translate('LBL_COUNTRY', $QUALIFIED_MODULE)}
									</label>
									<div class="col-lg-10">
										<select class="select2 form-control" name="country">
											{foreach from=\App\Fields\Country::getAll() item=ITEM}
												<option value="{$ITEM['name']}"
														{if $RECORD_MODEL->get('country') == $ITEM['name']}selected="true"{/if}>{\App\Language::translateSingleMod($ITEM['name'],'Other.Country')}</option>
											{/foreach}
										</select>
									</div>
								</div>
							{elseif $COLUMN eq 'type'}
								<div class="form-group row">
									<label class="col-lg-2 col-form-label text-left text-lg-right">
										{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}
									</label>
									<div class="col-lg-10">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-sm btn-outline-primary{if $RECORD_MODEL->get('type')===0} active{/if}"
												   for="option1">
												<input value="1" type="radio" name="type" id="option1"
													   data-validation-engine="validate[required]"
													   autocomplete="off"{if $RECORD_MODEL->get('type')==1} checked{/if}>
												{\App\Language::translate('LBL_TYPE_TARGET_USER',$QUALIFIED_MODULE)}
											</label>
											<label class="btn btn-sm btn-outline-primary{if $RECORD_MODEL->get('type')===1} active{/if}"
												   for="option2">
												<input value="2" type="radio" name="type" id="option2"
													   data-validation-engine="validate[required]"
													   autocomplete="off"{if $RECORD_MODEL->get('type')==2} checked{/if}>
												{\App\Language::translate('LBL_TYPE_INTEGRATOR',$QUALIFIED_MODULE)}
											</label>
											<label class="btn btn-sm btn-outline-primary{if $RECORD_MODEL->get('type')===2} active{/if}"
												   for="option3">
												<input value="3" type="radio" name="type" id="option3"
													   data-validation-engine="validate[required]"
													   autocomplete="off"{if $RECORD_MODEL->get('type')==3} checked{/if}>
												{\App\Language::translate('LBL_TYPE_PROVIDER',$QUALIFIED_MODULE)}
											</label>
										</div>
									</div>
								</div>
							{elseif $COLUMN neq 'logo' && $COLUMN neq 'id'}
								<div class="form-group row">
									<label class="col-lg-2 col-form-label text-left text-lg-right">
										{App\Language::translate('LBL_'|cat:$COLUMN|upper, $QUALIFIED_MODULE)}
									</label>
									<div class="col-lg-10">
										<input class="form-control" name="{$COLUMN}"
											   {if $COLUMN eq 'name' }data-validation-engine="validate[required]"{/if}
											   value="{\App\Purifier::encodeHtml($RECORD_MODEL->get($COLUMN))}">
									</div>
								</div>
							{elseif $COLUMN eq 'logo'}
								<div class="form-group row">
									<div class="col-lg-2">
										{$RECORD_MODEL->getDisplayValue($COLUMN)}
									</div>
									<div class="col-lg-offset-2 col-lg-10">
										<div class="d-block">
											<input type="file" name="{$COLUMN}" id="{$COLUMN}"/>&nbsp;&nbsp;
										</div>
									</div>
								</div>
							{elseif $COLUMN eq 'id' && $RECORD_ID}
								<input type="hidden" name="{$COLUMN}" value="{$RECORD_ID}">
							{/if}
						{/foreach}
					{/if}
				</div>
				<div class="card-footer text-center">
					<button class="btn btn-success mr-1" type="submit">
						<span class="fa fa-check"></span> {App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
					<button class="cancelLink btn btn-warning ml-1" type="reset"
							onclick="javascript:window.history.back();">
						<span class="fa fa-times"></span> {App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Companies-EditView -->
{/strip}
