{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row padding-bottom1per">
		<span class="col-md-3">{vtranslate('LBL_SELECT_ACTION_TYPE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<select class="chzn-select form-control" name="type" data-validation-engine="validate[required]">
				{foreach from=$TASK_OBJECT->getAllTypes() key=KEY item=ITEM}
					<option {if $TASK_OBJECT->type eq $KEY}selected{/if} value="{$KEY}">{$ITEM['name']}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-3">{vtranslate('LBL_SELECT_RECIPIENTS', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<select class="chzn-select form-control" name="recipients" data-validation-engine="validate[required]">
				<option {if $TASK_OBJECT->recipients eq 'watchdog'}selected{/if} value="watchdog">
					{vtranslate('LBL_WATCHING_USERS', $QUALIFIED_MODULE)}
				</option>
			</select>
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-3">{vtranslate('LBL_MESSAGE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			{assign var=POPOVER value=vtranslate('LBL_MESSAGE_INFO', $QUALIFIED_MODULE)}
			{foreach from=Vtiger_TextParser_Helper::getFunctionVariables() key=KEY item=ITEM}
				{assign var=POPOVER value=$POPOVER|cat:'<br><strong>'|cat:$ITEM|cat:'</strong> - '|cat:vtranslate($KEY, $QUALIFIED_MODULE)}
			{/foreach}
			<div class="input-group popoverTooltip" data-content="{Vtiger_Util_Helper::toSafeHTML($POPOVER)}" data-placement="right">
				<input type="text" class="form-control" name="message" value="{$TASK_OBJECT->message}" aria-describedby="messageaddon">
				<span class="input-group-addon" id="messageaddon">
					<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
				</span>
			</div>
		</div>
	</div>
{/strip}	
