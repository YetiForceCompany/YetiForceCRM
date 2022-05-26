{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-Delete -->
	<div class="modal-body js-modal-body pb-0" data-js="container">
		<form class="form-horizontal validateForm" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="remove" />
			<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}" />
			<input type="hidden" name="primaryKeyId" value="{$ITEM_MODEL->getId()}" />
			<div class="form-group row align-items-center">
				<div class="col-md-3 col-form-label text-right">{\App\Language::translate('LBL_REPLACE_IT_WITH', $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
				<div class="col-md-9 controls">
					<select id="replaceValue" name="replace_value" class="select2 form-control" data-validation-engine="validate[required]">
						{foreach from=$EDITABLE_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
							{if $PICKLIST_VALUE_KEY != $ITEM_MODEL->getId()}
								<option value="{$PICKLIST_VALUE_KEY}">{\App\Language::translate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
							{/if}
						{/foreach}
						{foreach from=$NON_EDITABLE_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
							{if $PICKLIST_VALUE_KEY != $ITEM_MODEL->getId()}
								<option value="{$PICKLIST_VALUE_KEY}">{\App\Language::translate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			{if $NON_EDITABLE_VALUES}
				<div class="form-group row align-items-center">
					<div class="col-md-3 col-form-label text-right">{\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUES',$QUALIFIED_MODULE)}</div>
					<div class="col-md-9 controls nonEditableValuesDiv">
						<ul class="nonEditablePicklistValues list-unstyled">
							{foreach from=$NON_EDITABLE_VALUES key=NON_EDITABLE_VALUE_KEY item=NON_EDITABLE_VALUE}
								<li>{\App\Language::translate($NON_EDITABLE_VALUE,$SOURCE_MODULE)}</li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}
		</form>
	</div>
	<!-- /tpl-Settings-Picklist-Delete -->
{/strip}
