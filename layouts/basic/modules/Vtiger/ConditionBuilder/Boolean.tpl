{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-ConditionBuilder-Boolean">
		<select class="select2 form-control js-condition-builder-value"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
				data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}"
		<optgroup class="p-0">
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
		</optgroup>
		<option value="0"
				title="0" {if trim($FIELD_VALUE) === 0} selected {/if}>{\App\Language::translate('JS_IS_DISABLED')}
		</option>
		<option value="1"
				title="1" {if trim($FIELD_VALUE) === 1} selected {/if}>{\App\Language::translate('JS_IS_ENABLED')}
		</option>
		</select>
	</div>
{/strip}
