{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row padding-bottom1per">
		<span class="col-md-3">{vtranslate('LBL_SELECT_ACTION_TYPE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<select class="chzn-select form-control" name="type" data-validation-engine="validate[required]">
				{foreach from=$TASK_OBJECT->getAllTypes() key=KEY item=ITEM}
					<option {if $TASK_OBJECT->type eq $KEY}selected{/if} value="{$KEY}">{vtranslate($ITEM['name'], $QUALIFIED_MODULE)}</option>
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
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ASSIGNED_TO[vtranslate('LBL_USERS')]}
						<option value="{$OWNER_ID}" {if $TASK_OBJECT->recipients eq $OWNER_ID}selected{/if}>
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			</select>
		</div>
	</div>
	<div class="row padding-bottom1per checkbox">
		<span class="col-md-3">{vtranslate('LBL_SKIP_CURRENT_USER', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<input name="skipCurrentUser" type="checkbox" value="1" {if $TASK_OBJECT->skipCurrentUser}checked{/if}>
		</div>
	</div>
	<hr />
	<div class="row padding-bottom1per">
		<span class="col-md-3">{vtranslate('LBL_TITLE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<input name="title" class="form-control" type="text" value="{$TASK_OBJECT->title}">
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-3"> </span>
		<div class="col-md-9">
			{assign var=FIELDS value=$MODULE_MODEL->getFields()}
			<select class="chzn-select form-control variables" onchange="$('.messageContent').val($('.messageContent').val() + ' ' + $(this).val())">
				<option value="">{vtranslate('LBL_SELECT_VARIABLES', $QUALIFIED_MODULE)}</option>
				<optgroup label="{vtranslate('LBL_VALUE_FROM_FIELD', $QUALIFIED_MODULE)}">
					{foreach key=FIELD_NAME item=FIELD from=$FIELDS}
						<option value="${$FIELD_NAME}$">{vtranslate($FIELD->getFieldLabel(),$SOURCE_MODULE)}</option>
					{/foreach}
				</optgroup>
				<optgroup label="{vtranslate('LBL_FIELDS_LABELS', $QUALIFIED_MODULE)}">
					{foreach key=FIELD_NAME item=FIELD from=$FIELDS}
						<option value="%{$FIELD_NAME}%">{vtranslate($FIELD->getFieldLabel(),$SOURCE_MODULE)}</option>
					{/foreach}
				</optgroup>
			</select>
		</div>
		<span class="col-md-3">{vtranslate('LBL_MESSAGE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			{assign var=POPOVER value=vtranslate('LBL_MESSAGE_INFO', $QUALIFIED_MODULE)}
			{foreach from=Vtiger_TextParser_Helper::getFunctionVariables() key=KEY item=ITEM}
				{assign var=POPOVER value=$POPOVER|cat:'<br><strong>'|cat:$ITEM|cat:'</strong> - '|cat:vtranslate($KEY, $QUALIFIED_MODULE)}
			{/foreach}
			<div class="input-group popoverTooltip" data-content="{Vtiger_Util_Helper::toSafeHTML($POPOVER)}" data-placement="right">
				<textarea class="form-control messageContent" name="message" rows="3" aria-describedby="messageaddon">
					{if $TASK_OBJECT->message}
						{$TASK_OBJECT->message}
					{else} 
						 
					{/if} 
				</textarea>
				<span class="input-group-addon" id="messageaddon">
					<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
				</span>
			</div>
		</div>
	</div>
{/strip}	
