{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-SMSNotifier-Create -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="validateForm">
			<div class="form-group form-row">
				<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
					{\App\Language::translate('FL_PROVIDER', $QUALIFIED_MODULE)}
					<span class="redColor">*</span>
				</label>
				<div class="col-md-8 fieldValue">
					<select id="provider" tabindex="0" title="{\App\Language::translate('FL_PROVIDER', $QUALIFIED_MODULE)}" class="select2 form-control" name="provider" data-validation-engine="validate[required]">
						{foreach from=$PROVIDERS item=PROVIDER name=fields}
							<option value="{\App\Purifier::encodeHtml($PROVIDER->getEditViewUrl())}">{$PROVIDER->getName()}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-SMSNotifier-Create -->
{/strip}
