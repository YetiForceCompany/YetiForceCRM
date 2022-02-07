{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="fieldType" value="{$TYPE}" />
	{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('Public')}
	{assign var=ALL_MODULE_LIST value=Vtiger_Module_Model::getAll([0],[],true)}
	<div class="mt-2">
		<div class="alert alert-danger fade show">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			{\App\Language::translate('LBL_SORTING_SETTINGS_WORNING', $QUALIFIED_MODULE)} (
			<a href="index.php?module=Roles&parent=Settings&view=Index">{\App\Language::translate('LBL_GO_TO_PANEL', $QUALIFIED_MODULE)}</a>)
		</div>
	</div>
	<div class="">
		<button class="btn btn-success js-add-panel" data-js="click" type="button"></span> {\App\Language::translate('LBL_ADD_PANEL_TO_MODULE',$QUALIFIED_MODULE)}</button>
	</div>
	<br />
	<div class="js-panels-container" data-js="container">
		{foreach from=$ALL_MODULE_LIST key=MODULE_ID item=MODULE_MODEL name=modules}
			{assign 'INDEX' $smarty.foreach.modules.iteration}
			{assign 'MODULE_NAME' $MODULE_MODEL->getName()}
			{assign var=DATA value=Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($TYPE, $MODULE_NAME)}
			{if $DATA}
				{include file=\App\Layout::getTemplatePath('AddPanel.tpl', $QUALIFIED_MODULE)}
			{/if}
		{/foreach}
	</div>
	<div class="js-modal-add-panel modal fade in" data-js="modal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form>
					<div class="modal-header">
						<h5 class="modal-title">{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<select class="js-modules-list form-control" name="modules" data-validation-engine="validate[required]" data-js="value">
							{foreach from=$ALL_MODULE_LIST key=TABID item=MODULE_MODEL}
								<option value="{$MODULE_MODEL->getName()}">{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
							{/foreach}
						</select>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success saveButton">
							<span class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
						</button>
						<button type="button" class="btn btn-danger dismiss" data-dismiss="modal">
							<span class="fas fa-times mr-1"></span>
							{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
