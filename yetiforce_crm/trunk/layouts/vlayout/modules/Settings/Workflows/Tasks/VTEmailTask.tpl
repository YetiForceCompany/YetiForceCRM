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
		<div class="row-fluid">
			<div class="row-fluid padding-bottom1per">
				<span class="span7 row-fluid">
					<span class="span2">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</span>
					<input data-validation-engine='validate[]' name="fromEmail" class="span9 fields" type="text" value="{$TASK_OBJECT->fromEmail}" />
				</span>
				<span class="span5">
					<select id="fromEmailOption" style="min-width: 300px" class="chzn-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
						<option></option>
						{$FROM_EMAIL_FIELD_OPTION}
					</select>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per">
				<span class="span7 row-fluid">
					<span class="span2">{vtranslate('LBL_TO',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
					<input data-validation-engine='validate[required]' name="recepient" class="span9 fields" type="text" value="{$TASK_OBJECT->recepient}" />
				</span>
				<span class="span5">
					<select style="min-width: 300px" class="task-fields chzn-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
						<option></option>
						{$EMAIL_FIELD_OPTION}
					</select>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per {if empty($TASK_OBJECT->emailcc)}hide {/if}" id="ccContainer">
				<span class="span7 row-fluid">
					<span class="span2">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</span>
					<input class="span9 fields" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}" />
				</span>
				<span class="span5">
					<select class="task-fields" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 300px">
						<option></option>
						{$EMAIL_FIELD_OPTION}
					</select>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per {if empty($TASK_OBJECT->emailbcc)}hide {/if}" id="bccContainer">
				<span class="span7 row-fluid">
					<span class="span2">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</span>
					<input class="span9 fields" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}" />
				</span>
				<span class="span5">
					<select class="task-fields" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}' style="min-width: 300px">
						<option></option>
						{$EMAIL_FIELD_OPTION}
					</select>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
				<span class="span8 row-fluid">
					<span class="span2">&nbsp;</span>
					<span class="span9">
						<a class="cursorPointer {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
						<a class="cursorPointer {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
					</span>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per">
				<span class="span7 row-fluid">
					<span class="span2">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
					<input data-validation-engine='validate[required]' name="subject" class="span9 fields" type="text" name="subject" value="{$TASK_OBJECT->subject}" id="subject" spellcheck="true"/>
				</span>
				<span class="span5">
					<select style="min-width: 300px" class="task-fields chzn-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
						<option></option>
						{$ALL_FIELD_OPTIONS}
					</select>
				</span>
			</div>
			<div class="row-fluid padding-bottom1per">
				<span class="span7 row-fluid">
					<span style="margin-top: 7px" class="span2">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
					<span class="span8">
						<select style="min-width: 250px" id="task-fieldnames" class="chzn-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{$ALL_FIELD_OPTIONS}
						</select>
					</span>	
				</span>
				<span class="span5 row-fluid">
					<span style="margin-top: 7px" class="span3">{vtranslate('LBL_ADD_TIME',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
					<span class="span8">
						<select style="width: 215px" id="task_timefields" class="chzn-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
							<option></option>
							{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
								<option value="${$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
							{/foreach}	
						</select>
					</span>	
				</span>
			</div>
			<div class="row-fluid padding-bottom1per">
				<textarea id="content" name="content">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>
	</div>	
{/strip}	