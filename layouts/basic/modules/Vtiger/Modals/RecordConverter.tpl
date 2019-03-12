{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-Modals-RecordConverter modal-body js-modal-body" data-js="container">
		<form class="form-horizontal">
			<input type="hidden" name="module" value="{$MODULE_NAME}"/>
			<input type="hidden" name="action" value="RecordConverter"/>
			<input type="hidden" name="convertId" value="{$SELECTED_CONVERT_TYPE}"/>
			{if $CONVERTERS}
				<div class="form-group form-row">
					<label class="col-sm-7 col-form-label">
						{\App\Language::translate('LBL_SELECT_CONVERT_TYPE', $MODULE)}:
					</label>
					<div class="col-sm-5">
						<select class="select2 form-control js-convert-type" data-js="change">
							{foreach key=KEY item=ITEM from=$CONVERTERS}
								<option value=""></option>
								<option value="{$KEY}"
										{if $KEY eq $SELECTED_CONVERT_TYPE} selected {/if}
								>{$ITEM['name']}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/if}
			<div class="form-group form-row">
				<label class="col-sm-7 col-form-label">
					{\App\Language::translate('LBL_NUMBER_OF_SELECTED_RECORDS', $MODULE)}:
				</label>
				<div class="col-sm-5">
					<div class="form-control-plaintext">{$ALL_RECORDS}</div>
				</div>
			</div>
			<div class="form-group form-row">
				<label class="col-sm-7 col-form-label">
					{\App\Language::translate('LBL_CREATED_RECORDS_AMOUNT', $MODULE)}:
				</label>
				<div class="col-sm-5">
					<div class="form-control-plaintext">{$CREATED_RECORDS}</div>
				</div>
			</div>
			{if $MODULE_WITHOUT_PERMISSIONS}
				<div class="alert alert-warning" role="alert">
					{\App\Language::translate('LBL_MODULES_WITHOUT_PERMISSION_TO_CREATE', $MODULE)}&nbsp;
					{foreach item=MODULE_NAME from=$MODULE_WITHOUT_PERMISSIONS}
						&nbsp;{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
					{/foreach}
				</div>
			{/if}
		</form>
	</div>
{/strip}
