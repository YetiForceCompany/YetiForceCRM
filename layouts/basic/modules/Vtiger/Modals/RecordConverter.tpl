{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Modals-RecordConverter modal-body js-modal-body" data-js="container">
		<form class="form-horizontal js-form-converter">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="action" value="RecordConverter" />
			<input type="hidden" name="sourceView" value="{$SOURCE_VIEW}" />
			<div class="form-group form-row mb-0">
				<label class="col-form-label">
					{\App\Language::translate('LBL_SELECT_CONVERT_TYPE', $MODULE_NAME)}:
				</label>
				<div class="col-sm-6">
					<select name="convertId" class="select2 form-control js-convert-type" data-js="change" data-validation-engine="validate[required]">
						{foreach key=KEY item=ITEM from=$CONVERTERS}
							<option value="{$KEY}" {if $KEY eq $SELECTED_CONVERT_TYPE} selected {/if}>{$ITEM['name']}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</form>
	</div>
{/strip}
