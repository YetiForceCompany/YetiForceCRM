{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<tr>
		<td>
			<select class="form-control js-users {if $SELECT}select2{/if}" data-js="value">
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
			<select class="form-control js-locks {if $SELECT}select2{/if}" data-js="value" multiple="">
				{foreach item=LOCKT key=ID from=$LOCKS_TYPE}
					<option value="{$ID}" {if in_array($ID, $LOCK)}selected{/if}>
						{\App\Language::translate($LOCKT, $QUALIFIED_MODULE)}
					</option>
				{/foreach}
			</select>
		</td>
		<td class="text-center">
			<button title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" type="button" class="btn btn-danger js-delete-item" data-js="click">
				<span class="fas fa-trash-alt"></span>
			</button>
		</td>
	</tr>
{/strip}
