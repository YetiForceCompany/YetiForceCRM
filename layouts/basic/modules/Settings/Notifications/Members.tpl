{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<form class="form-horizontal" id="modalMembersView">
		<input type="hidden" id="srcModule" name="srcModule" value="{$SRC_MODULE}" />
		<input type="hidden" id="isToAdd" name="isToAdd" value={$IS_TO_ADD} />
		<input type="hidden" id="mode" name="mode" value="addOrRemoveMembers" />
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 class="modal-title">{vtranslate('LBL_ADD_MEMBERS', $QUALIFIED_MODULE)}</h3>
			<div class="clearfix"></div>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<div class="col-xs-12">
					<label class="control-label">
						{vtranslate('LBL_SELECT_MEMBERS', $QUALIFIED_MODULE)}
					</label>
					<select id="members" class="select2 form-control"  multiple="true" name="members[]">
						{foreach from=\App\PrivilegeUtil::getMembers() key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{vtranslate($GROUP_LABEL)}">
								{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
									{if !in_array($MEMBER_ID, $RESTRICT_MEMBERS)}
										<option class="{$MEMBER['type']}" value="{$MEMBER_ID}">{vtranslate($MEMBER['name'])}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
{/strip}
