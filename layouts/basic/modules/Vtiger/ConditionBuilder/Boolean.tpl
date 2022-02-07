{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Boolean">
		<select class="js-boolean-field select2 form-control js-condition-builder-value"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}"
			data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}"
			<optgroup class="p-0">
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
			</optgroup>
			<option value="0" title="0" {if $VALUE eq 0} selected {/if}>{\App\Language::translate('LBL_NO')}
			</option>
			<option value="1" title="1" {if $VALUE eq 1} selected {/if}>{\App\Language::translate('LBL_YES')}
			</option>
		</select>
	</div>
{/strip}
