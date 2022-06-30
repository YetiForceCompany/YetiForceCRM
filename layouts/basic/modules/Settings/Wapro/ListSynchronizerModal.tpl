{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Wapro-ListSynchronizerModal -->
	<div class="modal-body pb-0">
		<textarea rows="15" disabled>{$WAPRO_MODEL->getInfo()}</textarea>
		<form>
			{foreach from=$WAPRO_MODEL->getSynchronizers() item=SYNCHRONIZER}
				<div class="form-group form-check">
					<input type="checkbox" class="form-check-input" id="wapro{$SYNCHRONIZER->className}">
					<label class="form-check-label ml-2" for="wapro{$SYNCHRONIZER->className}">{\App\Language::translate($SYNCHRONIZER::NAME, $QUALIFIED_MODULE)}</label>
				</div>
			{/foreach}
		</form>
	</div>
	<!-- /tpl-Settings-Wapro-ListSynchronizerModal -->
{/strip}
