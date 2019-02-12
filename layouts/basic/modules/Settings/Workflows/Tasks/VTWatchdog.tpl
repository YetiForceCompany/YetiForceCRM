{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="row padding-bottom1per">
		<span class="col-md-3">{\App\Language::translate('LBL_SELECT_ACTION_TYPE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<select class="select2 form-control" name="type" data-validation-engine="validate[required]">
				{foreach from=\App\Fields\Picklist::getValuesName('notification_type') key=KEY item=ITEM}
					<option {if $TASK_OBJECT->type eq $ITEM}selected="selected"{/if}
							value="{$ITEM}">{\App\Language::translate($ITEM, $TASK_OBJECT->srcWatchdogModule)}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-3">{\App\Language::translate('LBL_SELECT_RECIPIENTS', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<select class="select2 form-control" name="recipients" data-validation-engine="validate[required]">
				<option {if $TASK_OBJECT->recipients eq 'watchdog'}selected="selected"{/if} value="watchdog">
					{\App\Language::translate('LBL_WATCHING_USERS', $QUALIFIED_MODULE)}
				</option>
				<option {if $TASK_OBJECT->recipients eq 'owner'}selected="selected"{/if} value="owner">
					{\App\Language::translate('LBL_OWNER_REKORD', $QUALIFIED_MODULE)}
				</option>
				{foreach from=\App\PrivilegeUtil::getMembers() key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
					<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
						{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
							<option class="{$MEMBER['type']}" value="{$MEMBER_ID}"
									{if $TASK_OBJECT->recipients eq $MEMBER_ID}selected="selected"{/if}>{\App\Language::translate($MEMBER['name'])}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="row padding-bottom1per checkbox">
		<span class="col-md-3">{\App\Language::translate('LBL_SKIP_CURRENT_USER', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<input name="skipCurrentUser" type="checkbox" value="1" {if $TASK_OBJECT->skipCurrentUser}checked{/if}>
		</div>
	</div>
	<hr/>
	<div class="row">
		{include file=\App\Layout::getTemplatePath('VariablePanel.tpl') SELECTED_MODULE=$SOURCE_MODULE PARSER_TYPE='mail' GRAY=true}
	</div>
	<hr/>
	<div class="row padding-bottom1per">
		<span class="col-md-3">{\App\Language::translate('LBL_TITLE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<input name="title" class="form-control" type="text" value="{$TASK_OBJECT->title}">
		</div>
	</div>
	<div class="row padding-bottom1per">
		<span class="col-md-3">{\App\Language::translate('LBL_MESSAGE', $QUALIFIED_MODULE)}</span>
		<div class="col-md-9">
			<textarea class="form-control messageContent" name="message" rows="3">
				{if $TASK_OBJECT->message}
					{$TASK_OBJECT->message}
				{else}

				{/if} 
			</textarea>
		</div>
	</div>
{/strip}	
