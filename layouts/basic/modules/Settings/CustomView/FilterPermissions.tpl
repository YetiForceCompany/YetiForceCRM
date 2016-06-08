{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign 'MEMBERS' Settings_Groups_Member_Model::getAll()}
	{assign 'MEMBERS_DEFAULT' $MODULE_MODEL->getFilterPermissionsView($CVID, $TYPE)}
	<input type="hidden" id="cvid" value="{$CVID}" />
	<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
	<input type="hidden" id="type" value="{$TYPE}" />
	<div class="modal-header">
		<div class="pull-left">
			<h3 class="modal-title">{vtranslate('LBL_MANAGE_PERMISSIONS', $MODULE_NAME)}</h3>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="modal-body">
		<div class="">
			<div class="form-group">
				<label class="col-xs-12 control-label">
					{vtranslate('LBL_ALL_GROUP_LIST', $MODULE_NAME)}
				</label>
				<div class="col-xs-10">
					<select class="select2 form-control add" id="allGroups" {if $IS_DEFAULT} disabled="disabled"{/if}>
						{foreach from=$MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
							<optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
								{foreach from=$ALL_GROUP_MEMBERS item=MEMBER key=QUALIFIEDID}
								{if is_array($MEMBERS_DEFAULT[$GROUP_LABEL]) && in_array($QUALIFIEDID,$MEMBERS_DEFAULT[$GROUP_LABEL])}{continue}{/if}
								<option value="{$MEMBER->get('id')}"  data-member-type="{$GROUP_LABEL}">{vtranslate($MEMBER->get('name'), $QUALIFIED_MODULE)}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
			<button type="button" class="btn btn-success moveItem" data-source="add" data-target="remove" data-action="add" title="{vtranslate('LBL_ADD_PERMISSIONS', $MODULE_NAME)}" {if $IS_DEFAULT} disabled="disabled"{/if}><span class="glyphicon glyphicon-arrow-down"></span></button>
		</div>
		<div class="form-group">
			<label class="col-xs-12 control-label">
				{vtranslate('LBL_GROUP_MEMBERS', $MODULE_NAME)}
			</label>
			<div class="col-xs-10">
				<select class="select2 form-control remove" id="groups">
					{foreach from=$MEMBERS_DEFAULT key=LABEL item=GROUP}
						<optgroup label="{vtranslate($LABEL, $QUALIFIED_MODULE)}">
							{foreach from=$GROUP item=USER}
								{assign 'MEMBER' $MEMBERS[$LABEL][$USER]}
								{if $MEMBER}
									<option value="{$USER}"  data-member-type="{$LABEL}">{vtranslate($MEMBER->get('name'), $QUALIFIED_MODULE)}</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
			<button type="button" class="btn btn-danger moveItem" data-source="remove" data-target="add" data-action="remove" title="{vtranslate('LBL_RECEIVE_PERMISSION', $MODULE_NAME)}"><span class="glyphicon glyphicon-arrow-up"></span></button>
		</div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
</div>
{/strip}
