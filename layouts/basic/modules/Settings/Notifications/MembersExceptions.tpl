{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<form id="exceptionsView" class="form-horizontal">
		<input type="hidden" id="srcModule" name="srcModule" value="{$SRC_MODULE}" />
		<input type="hidden" id="member" name="member" value={$MEMBER} />
		<input type="hidden" id="mode" name="mode" value="exceptions" />
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 class="modal-title">{vtranslate('LBL_EXCEPTIONS', $QUALIFIED_MODULE)}</h3>
			<div class="clearfix"></div>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<div class="col-xs-12">
					<label class="control-label">
						{vtranslate('LBL_SELECT_USER', $QUALIFIED_MODULE)}
					</label>
					<select id="exceptions" class="select2 form-control"  multiple="true" name="exceptions[]">
						{foreach from=\App\PrivilegeUtil::getUserByMember($MEMBER) item=USER_ID}
							<option value="{$USER_ID}" {if in_array($USER_ID, $MEMBERS)}selected{/if}>{\App\Fields\Owner::getUserLabel($USER_ID)}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
{/strip}
