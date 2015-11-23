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
							{$FROM_EMAIL_FIELD_OPTION}
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
							{$EMAIL_FIELD_OPTION}
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
							{$EMAIL_FIELD_OPTION}
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
							{$EMAIL_FIELD_OPTION}
						</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} hide {/if}">
				<span class="col-md-8 row">
					<span class="col-md-3">&nbsp;</span>
					<span class="col-md-9">
						<a class="cursorPointer {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
						<a class="cursorPointer {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
					</span>
				</span>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
					<div class="col-md-9">
						<input data-validation-engine='validate[required]' name="subject" class="fields form-control" type="text" name="subject" value="{$TASK_OBJECT->subject|escape}" id="subject" spellcheck="true"/>
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 paddingLRZero">
					<select class="task-fields chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
						<option></option>
						{$ALL_FIELD_OPTIONS}
					</select>
					</div>
				</div>
			</div>
			<div class="row padding-bottom1per">
				<span class="col-md-7 row">
					<span class="col-md-3 control-label">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<select id="task-fieldnames" class="chzn-select form-control" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
							{$ALL_FIELD_OPTIONS}
						</select>
					</div>	
				</span>
				<div class="col-md-5">
					<div  class="col-md-3 paddingLRZero control-label addTime">{vtranslate('LBL_ADD_TIME',$QUALIFIED_MODULE)}</div>
					<div class="col-md-9 paddingLRZero">
						<select id="task_timefields" class="chzn-select form-control " data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option></option>
							{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
								<option value="${$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
							{/foreach}	
						</select>
					</div>	
				</div>
			</div>
			<div class="padding-bottom1per">
				<textarea id="content" class="form-control" name="content">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>
	</div>	
{/strip}	
