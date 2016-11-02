{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<input type="hidden" id="fieldType" value="{$TYPE}"/>
	{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers('Public')}
	{assign var=ALL_MODULE_LIST value=Vtiger_Module_Model::getAll([0],[],true)}
	<div class="">
		<div class="alert alert-danger fade in">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			{vtranslate('LBL_SORTING_SETTINGS_WORNING', $QUALIFIED_MODULE)} (
			<a href="index.php?module=Roles&parent=Settings&view=Index">{vtranslate('LBL_GO_TO_PANEL', $QUALIFIED_MODULE)}</a>)
		</div>
	</div>
	<div class="">
		<button class="btn btn-success addPanel" type="button"></span> {vtranslate('LBL_ADD_PANEL_TO_MODULE',$QUALIFIED_MODULE)}</button>
	</div>
	<br>
	<div class="panelsContainer">
		{foreach from=$ALL_MODULE_LIST key=MODULE_ID item=MODULE_MODEL name=modules}
			{assign 'INDEX' $smarty.foreach.modules.iteration}
			{assign 'MODULE_NAME' $MODULE_MODEL->getName()}
			{assign var=DATA value=Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($TYPE, $MODULE_NAME)}
			{if $DATA}
				{include file='AddPanel.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			{/if}
		{/foreach}
	</div>
	<div id="myModal" class="modal fade in">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</h4>
					</div>
					<div class="modal-body">
						<select id="modulesList" class="modules form-control" name="modules" data-validation-engine="validate[required]">
							{foreach from=$ALL_MODULE_LIST key=TABID item=MODULE_MODEL}
								<option value="{$MODULE_MODEL->getName()}">{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
							{/foreach}
						</select>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE', $MODULE_NAME)}</button>
						<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
					</div>
				</form>
            </div>
        </div>
    </div>
{/strip}
