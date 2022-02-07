{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form class="tpl-Settings-Notifications-Members form-horizontal" id="modalMembersView">
		<input type="hidden" id="srcModule" name="srcModule" value="{$SRC_MODULE}" />
		<input type="hidden" id="isToAdd" name="isToAdd" value="{$IS_TO_ADD}" />
		<input type="hidden" id="mode" name="mode" value="addOrRemoveMembers" />
		<div class="modal-header">
			<h5 class="modal-title"><span
					class="fa fa-plus u-mr-5px mt-2"></span>{\App\Language::translate('LBL_ADD_MEMBERS', $QUALIFIED_MODULE)}
			</h5>
			<button type="button" class="close" data-dismiss="modal"
				aria-label="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<div class="col-12">
					<label class="col-form-label">
						{\App\Language::translate('LBL_SELECT_MEMBERS', $QUALIFIED_MODULE)}
					</label>
					<select id="members" class="select2 form-control" multiple="true" name="members[]">
						{foreach from=\App\PrivilegeUtil::getMembers() key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
								{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
									{if !in_array($MEMBER_ID, $RESTRICT_MEMBERS)}
										<option class="{$MEMBER['type']}"
											value="{$MEMBER_ID}">{\App\Language::translate($MEMBER['name'])}</option>
									{/if}
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
{/strip}
