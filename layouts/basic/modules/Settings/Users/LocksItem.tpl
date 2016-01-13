{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<tr>
		<td>
			<select class="form-control users {if $SELECT}select2{/if}">
				<optgroup label="{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}">
					{foreach item=ROLE key=ROLEID from=$ROLES}
						<option value="{$ROLEID}" {if $ID == $ROLEID}selected{/if}>
							{vtranslate($ROLE->getName(), $QUALIFIED_MODULE)}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}">
					{foreach item=USER key=USERID from=$USERS}
						<option value="{$USERID}" {if $ID == $USERID}selected{/if}>
							{$USER->getName()}
						</option>
					{/foreach}
				</optgroup>
			</select>
		</td>
		<td>
			<select class="form-control locks {if $SELECT}select2{/if}" multiple="">
					{foreach item=LOCKT key=ID from=$LOCKS_TYPE}
						<option value="{$ID}" {if in_array($ID, $LOCK)}selected{/if}>
							{vtranslate($LOCKT, $QUALIFIED_MODULE)}
						</option>
					{/foreach}
			</select>
		</td>
		<td class="textAlignCenter">
			<button title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" type="button" class="btn btn-default delate">
				<i class="glyphicon glyphicon-trash"></i>
			</button>
		</td>
	</tr>
{/strip}

