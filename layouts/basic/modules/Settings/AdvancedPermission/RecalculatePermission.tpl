{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header row no-margin">
		<div class="col-xs-12 paddingLRZero">
			<div class="col-xs-8 paddingLRZero">
				<h4>{vtranslate('LBL_RECALCULATE_PERMISSION_TITLE', $MODULE)}</h4>
			</div>
			<div class="pull-right">
				<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
			</div>
		</div>
	</div>
	<form class="sendByAjax">
		<input type="hidden" name="action" value="RecalculatePermission">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="parent" value="Settings">
		<div class="modal-body row">
			<div class="col-xs-12">
				<div class="col-xs-12 paddingLRZero marginBottom10px">
					<div class="alert alert-info">
						{vtranslate('LBL_RECALCULATE_CRON_INFO', $MODULE)}
					</div>
				</div>
				<div class="col-xs-12 paddingLRZero marginBottom10px">
					<b>{vtranslate('LBL_MODULES_LIST', $MODULE)}</b>
					<select class="select2" name="moduleName">
						{foreach from=$LIST_MODULES key=TABID item=MODULE_INFO}
							<option value="{$MODULE_INFO['name']}">{vtranslate($MODULE_INFO['name'], $MODULE_INFO['name'])}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
{/strip}
