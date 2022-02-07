{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Components-ColorPickerModal-->
	<div class="modal-body">
		<form class="form-horizontal">
			<div class="form-group form-row">
				<div class="col-12 d-flex align-items-center flex-wrap">
					<span class="col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_COLOR', $MODULE)}</span>
					<span class="ml-auto mr-2">{\App\Language::translate('LBL_PREVIOUS_COLOR', $MODULE)}</span>
					<span class="c-circle d-inline-flex" style="background-color: #{$COLOR}"></span>
				</div>
				<div class=" col-12 controls">
					<input type="hidden" class="selectedColor" value="{$COLOR}" />
					<div class="js-color-picker" data-js="color-picker">
					</div>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Components-ColorPickerModal-->
{/strip}
