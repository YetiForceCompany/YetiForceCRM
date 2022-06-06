{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-md-3 d-flex mb-2 mb-md-0">
		<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
		<div class="w-100">
			<select name="sourceModule"
				title="{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}"
				class="select2 form-control ml-0">
				{foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
					{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
					<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE} selected {/if}>
						{\App\Language::translate($MODULE_MODEL->get('label'), $MODULE_NAME)}
					</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="col-md-3 d-flex mb-2 mb-md-0">
		<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_SOURCE_FIELD', $QUALIFIED_MODULE)}</label>
		<div class="w-100">
			<select id="sourceField" name="sourceField" class="select2 form-control"
				data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"
				title="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
				<option value=''></option>
				{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
					<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('source_field') eq $FIELD_NAME} selected {/if}>{\App\Language::translate($FIELD_LABEL, $SELECTED_MODULE)}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="col-md-3 d-flex mb-2 mb-md-0">
		<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_SECOND_FIELD', $QUALIFIED_MODULE)}</label>
		<div class="w-100">
			<select id="secondField" name="secondField" class="select2 form-control"
				data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"
				title="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
				<option value=''></option>
				{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
					<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('second_field') eq $FIELD_NAME} selected {/if}>{\App\Language::translate($FIELD_LABEL, $SELECTED_MODULE)}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="col-md-3 d-flex mb-2 mb-md-0">
		{if isset($THIRD_FIELD)}
			<label class="muted u-text-small-bold u-white-space-nowrap mr-2 my-auto">{\App\Language::translate('LBL_THIRD_FIELD', $QUALIFIED_MODULE)}</label>
			<div class="w-100">
				<select id="thirdField" name="thirdField" class="select2 form-control"
					data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}"
					title="{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
					<option value=''></option>
					{foreach key=FIELD_NAME item=FIELD_LABEL from=$PICKLIST_FIELDS}
						<option value="{$FIELD_NAME}" {if $RECORD_MODEL->get('third_field') eq $FIELD_NAME} selected {/if}>{\App\Language::translate($FIELD_LABEL, $SELECTED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		{else}
			<button type="button" class="btn btn-sm btn-success js-add-next-level-field" data-js="click">{\App\Language::translate('LBL_ADD_NEXT_LEVEL_FIELD', $QUALIFIED_MODULE)}</button>
		{/if}
	</div>
{/strip}
