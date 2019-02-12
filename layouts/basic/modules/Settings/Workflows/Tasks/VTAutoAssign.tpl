{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=ENTRIES value=$TASK_OBJECT->getAutoAssignEntries($WORKFLOW_MODEL->get('module_name'))}
	<div class="row">
		<label class="col-md-4 col-form-label">{\App\Language::translate('LBL_SELECT_TEMPLATE', $QUALIFIED_MODULE)}</label>
		<div class="col-md-5">
			<select class="select2 form-control" name="template" data-validation-engine="validate[required]"
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
				<optgroup class="p-0">
					<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
				</optgroup>
				{foreach from=$ENTRIES key=KEY item=ITEM}
					<option value="{$KEY}">{\App\Language::translate($ITEM->getDisplayValue('field'), $ITEM->getSourceModuleName())}</option>
				{/foreach}
			</select>
		</div>
	</div>
{/strip}	
