{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-SocialMedia-Twitter-Index">
		<div class="contents">
			<div class="alert alert-info">
				<h5 class="alert-heading">{\App\Language::translate('LBL_TWITTER', $QUALIFIED_MODULE)}</h5>
				{\App\Language::translate('LBL_TWITTER_DESC',$QUALIFIED_MODULE)}
			</div>
		</div>
		<div>
			<form class="js-social-media-twitter__form" method="post" data-js="submit">
				<input type="hidden" name="parent" value="Settings">
				<input type="hidden" name="module" value="{$MODULE}">
				<input type="hidden" name="action" value="SaveAjax">
				<input type="hidden" name="mode" value="Twitter">
				<div class="tab-pane active">
					<div class="form-group row">
						<label for="methods"
							   class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_API_KEY', $QUALIFIED_MODULE)}</label>
						<div class="col-12 col-lg-4">
							<input type="text" name="api_key" value=""/>
						</div>
					</div>
					<div class="form-group row">
						<label for="methods"
							   class="col-12 col-lg-2 col-form-label">{\App\Language::translate('LBL_ARCHIVING_RECORDS_NUMBER_OF_DAYS', $QUALIFIED_MODULE)}</label>
						<div class="col-12 col-lg-4">
							<input type="text" name="archiving_records_number_of_days"
								   value="{$CONFIG_TWITTER->get('archiving_records_number_of_days', '365')}"
							/>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
