{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=ENTRIES value=$TASK_OBJECT->getAutoAssignEntries($WORKFLOW_MODEL->get('module_name'))}
	<div class="row">
		<label class="col-md-4 control-label">{\App\Language::translate('LBL_SELECT_TEMPLATE', $QUALIFIED_MODULE)}</label>
		<div class="col-md-5">
			<select class="chzn-select form-control" name="template" data-validation-engine='validate[required]'>
				<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
				{foreach from=$ENTRIES key=KEY item=ITEM}
					<option  value="{$KEY}">{\App\Language::translate($ITEM->getDisplayValue('field'), $ITEM->getSourceModuleName())}</option>
				{/foreach}	
			</select>
		</div>
	</div>
{/strip}	
