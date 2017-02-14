{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div id="VtEmailTaskContainer">
		<div class="">
			<div class="row padding-bottom1per">
				<span class="col-md-12 row">
					<span class="col-md-2 control-label">{vtranslate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
					<div class="col-md-10 paddingLRZero">
						<select id="task_timefields" name="smtp" class="chzn-select form-control " data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option value="">{vtranslate('LBL_DEFAULT')}</option>
							{foreach from=App\Mail::getAll() item=ITEM key=ID}
								<option value="{$ID}" {if $TASK_OBJECT->smtp == $ID}selected{/if}>{$ITEM['name']}({$ITEM['host']})</option>
							{/foreach}	
						</select>
					</div>
				</span>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-12 row">
					<span class="col-md-2"></span>
					<span class="col-md-10">
						<label><input type="checkbox" class="alignTop" value="true" name="emailoptout" {if $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{vtranslate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
					</span>
				</span>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input data-validation-engine='validate[]' name="fromEmail" class="fields form-control" type="text" value="{$TASK_OBJECT->fromEmail}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 paddingLRZero">
						<select id="fromEmailOption" class="chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
							{foreach item=FIELDS key=BLOCK_NAME from=$FROM_EMAIL_FIELD_OPTION}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=LABEL key=VAL from=$FIELDS}
										<option value="{$VAL}">{$LABEL}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
					<div class="col-md-9">
						<input data-validation-engine='validate[required]' name="recepient" class="fields form-control" type="text" value="{$TASK_OBJECT->recepient}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 paddingLRZero">
						<select class="task-fields chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
							{foreach item=FIELDS key=BLOCK_NAME from=$EMAIL_FIELD_OPTION}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=LABEL key=VAL from=$FIELDS}
										<option value="{$VAL}">{$LABEL}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input class="fields form-control" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 paddingLRZero">
						<select class="task-fields chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}" >
							<option></option>
							{foreach item=FIELDS key=BLOCK_NAME from=$EMAIL_FIELD_OPTION}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=LABEL key=VAL from=$FIELDS}
										<option value="{$VAL}">{$LABEL}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input class="fields form-control" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 paddingLRZero">
						<select class="task-fields chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
							{foreach item=FIELDS key=BLOCK_NAME from=$EMAIL_FIELD_OPTION}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=LABEL key=VAL from=$FIELDS}
										<option value="{$VAL}">{$LABEL}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
				<span class="col-md-8 row">
					<span class="col-md-3">&nbsp;</span>
					<span class="col-md-9">
						<a class="btn btn-default {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
						<a class="btn btn-default {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
					</span>
				</span>
			</div>
			<hr/>
			<div class="row">
				{include file='VariablePanel.tpl'|@vtemplate_path SELECTED_MODULE=$SOURCE_MODULE PARSER_TYPE='mail' GRAY=true}
			</div>
			<hr/>
			<div class="row padding-bottom1per">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
					<div class="col-md-9">
						<input data-validation-engine='validate[required]' name="subject" class="fields form-control" type="text" name="subject" value="{$TASK_OBJECT->subject|escape}" id="subject" spellcheck="true"/>
					</div>
				</span>
			</div>
			<div class="padding-bottom1per">
				<textarea id="content" class="form-control" name="content">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>
	</div>	
{/strip}	
