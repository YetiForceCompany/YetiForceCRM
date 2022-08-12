{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Users-SwitchUsersItem -->
	<tr>
		<td>
			<select class="form-control sufrom {if $SELECT}select2{/if}">
				<optgroup label="{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}">
					{foreach item=ROLE key=ROLEID from=$ROLES}
						<option value="{$ROLEID}" {if !empty($ID) && $ID == $ROLEID}selected{/if}>
							{\App\Language::translate($ROLE->getName(), $QUALIFIED_MODULE)}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}">
					{foreach item=USER key=USERID from=$USERS}
						<option value="{$USERID}" {if !empty($ID) && $ID == $USERID}selected{/if}>
							{$USER->getName()}
						</option>
					{/foreach}
				</optgroup>
			</select>
		</td>
		<td>
			<select class="form-control suto {if $SELECT}select2{/if}" multiple="">
				<optgroup label="{\App\Language::translate('LBL_ROLES', $QUALIFIED_MODULE)}">
					{foreach item=ROLE key=ROLEID from=$ROLES}
						<option value="{$ROLEID}" {if in_array($ROLEID, $SUSERS)}selected{/if}>
							{\App\Language::translate($ROLE->getName(), $QUALIFIED_MODULE)}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}">
					{foreach item=USER key=USERID from=$USERS}
						<option value="{$USERID}" {if in_array($USERID, $SUSERS)}selected{/if}>
							{$USER->getName()}
						</option>
					{/foreach}
				</optgroup>
			</select>
		</td>
		<td class="textAlignCenter">
			<button title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" type="button"
				class="btn btn-sm btn-danger delate">
				<i class="fas fa-trash-alt"></i>
			</button>
		</td>
	</tr>
	<!-- /tpl-Settings-Users-SwitchUsersItem -->
{/strip}
