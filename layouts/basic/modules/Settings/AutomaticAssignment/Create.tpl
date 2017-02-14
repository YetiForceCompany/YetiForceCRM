{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{if isset($WIZARD_BASE)}
		<form class="form-horizontal" action="{$MODULE_MODEL->getEditViewUrl()}" id="createForm">
			<div class="modal-header">
				<div class="pull-left">
					<h3 class="modal-title">{\App\Language::translate('LBL_CREATE_RECORD', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="modal-body">
				<div class="">
					<div class="verticalBottomSpacing">
						<label class="control-label">
							{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}<span class="redColor"> *</span>
						</label>
						<select class="select2 form-control sourceModule" name="tabid" id="supportedModules">
							<option value="">{\App\Language::translate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
							{foreach item=SUPPORTED_MODULE key=TAB_ID from=$SUPPORTED_MODULES}
								<option value="{$TAB_ID}">{\App\Language::translate($SUPPORTED_MODULE->getName(), $SUPPORTED_MODULE->getName())}</option>
							{/foreach}
						</select>
					</div>
					<div class="fieldList">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success hide submitButton">{\App\Language::translate('BTN_NEXT', $QUALIFIED_MODULE)}</button>
				<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('BTN_CLOSE', $QUALIFIED_MODULE)}</button>
			</div>
		</form>
	{else}
		<label class="control-label">
			{\App\Language::translate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}<span class="redColor"> *</span>
		</label>
		<div class="controls">
			<select class="select2 form-control" name="field" id="supportedFields">
				{foreach key=BLOCK_NAME item=FIELDS from=$SUPPORTED_FIELDS}
					<optgroup label="{\App\Language::translate($BLOCK_NAME, $SELECTED_MODULE)}">
						{foreach key=FIELD_NAME item=FIELD_OBJECT from=$FIELDS name=fieldsLoop}
							<option value="{$FIELD_NAME}">{\App\Language::translate($FIELD_OBJECT->getFieldLabel(),$SELECTED_MODULE)}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	{/if}

{/strip}
