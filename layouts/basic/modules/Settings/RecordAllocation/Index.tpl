{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="">
		<div class="alert alert-danger fade in">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			{vtranslate('LBL_SORTING_SETTINGS_WORNING', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="">
		<button class="btn btn-success addPanel" type="button"></span> {vtranslate('LBL_ADD_PANEL_TO_MODULE',$QUALIFIED_MODULE)}</button>
	</div>
	<br>
	{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers('Public')}
	<div class="panelsContainer">
	{foreach from=$USERS_GROUPS_LIST key=MODULE_ID item=DATA name=allData}
		{assign 'INDEX' $smarty.foreach.allData.iteration}
		{include file='AddPanel.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
	{/foreach}
	</div>
	<div id="myModal" class="modal fade">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</h4>
					</div>
					<div class="modal-body">
						<select id="modulesList" class="modules form-control" name="modules" data-validation-engine="validate[required]">
							{foreach from=Vtiger_Module_Model::getAll([0],[],true) key=TABID item=MODULE_MODEL}
								<option value="{$TABID}" {if 0 }selected="true"{/if}>{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}</option>
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
