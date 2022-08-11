{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div id="VtEmailTaskContainer tpl-Settings-Workflows-Tasks-VTEmailTask">
		<div class="">
			<div class="form-row pb-3">
				<span class="col-md-12 form-row">
					<span class="col-md-2 col-form-label">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
					<div class="col-md-10 px-0">
						<select id="smtp_{\App\Layout::getUniqueId()}" name="smtp" class="select2 form-control "
							data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
							<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
							{foreach from=App\Mail::getAll() item=ITEM key=ID}
								<option value="{$ID}"
									{if !empty($TASK_OBJECT->smtp) && $TASK_OBJECT->smtp == $ID}selected{/if}>{$ITEM['name']}
									({$ITEM['host']})</option>
							{/foreach}
						</select>
					</div>
				</span>
			</div>
			<div class="form-row pb-3">
				<span class="col-md-12 form-row">
					<span class="col-md-2"></span>
					<span class="col-md-10 form-row d-flex align-items-center">
						<div class="col-md-3 mr-2 mb-0">
							{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}
						</div>
						<input type="checkbox" class="alignTop" value="true" name="emailoptout"
							{if !empty($TASK_OBJECT->emailoptout)} checked {/if}>&nbsp;
					</span>
				</span>
			</div>
			<div class="form-row pb-3">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_FROM', $QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input data-validation-engine='validate[]' name="fromEmail" class="fields form-control"
							type="text" value="{if !empty($TASK_OBJECT->fromEmail)}{$TASK_OBJECT->fromEmail}{/if}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 px-0">
						<div class="input-group">
							<select id="fromEmailOption" class="select2 form-control"
								data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								<option></option>
								{foreach item=FIELDS key=BLOCK_NAME from=$FROM_EMAIL_FIELD_OPTION}
									<optgroup label="{$BLOCK_NAME}">
										{foreach item=LABEL key=VAL from=$FIELDS}
											<option value="{$VAL}">{$LABEL}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary clipboard" data-copy-target="#fromEmailOption"
									title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}">
									<span class="fas fa-copy"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-row pb-3">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_TO',$QUALIFIED_MODULE)}<span
							class="redColor">*</span></span>
					<div class="col-md-9">
						<input data-validation-engine='validate[required]' name="recepient" class="fields form-control"
							type="text" value="{if !empty($TASK_OBJECT->recepient)}{\App\Purifier::encodeHtml($TASK_OBJECT->recepient)}{/if}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 px-0">
						<div class="input-group">
							<select class="task-fields select2 form-control" id="toEmailOption"
								data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								<option></option>
								{foreach item=FIELDS key=BLOCK_NAME from=$EMAIL_FIELD_OPTION}
									<optgroup label="{$BLOCK_NAME}">
										{foreach item=LABEL key=VAL from=$FIELDS}
											<option value="{$VAL}">{$LABEL}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary clipboard" data-copy-target="#toEmailOption"
									title="{\App\Language::translate('BTN_COPY_TO_CLIPBOARD')}">
									<span class="fas fa-copy"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-row pb-3 {if empty($TASK_OBJECT->emailcc)}d-none {/if}" id="ccContainer">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_CC',$QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input class="fields form-control" type="text" name="emailcc"
							value="{if !empty($TASK_OBJECT->emailcc)}{$TASK_OBJECT->emailcc}{/if}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 px-0">
						<select class="task-fields select2 form-control"
							data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
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
			<div class="form-row pb-3 {if empty($TASK_OBJECT->emailbcc)}d-none {/if}" id="bccContainer">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_BCC',$QUALIFIED_MODULE)}</span>
					<div class="col-md-9">
						<input class="fields form-control" type="text" name="emailbcc"
							value="{if !empty($TASK_OBJECT->emailbcc)}{$TASK_OBJECT->emailbcc}{/if}" />
					</div>
				</span>
				<div class="col-md-5">
					<div class="col-md-12 px-0">
						<select class="task-fields select2 form-control"
							data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
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
			<div class="form-row pb-3 {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))} d-none {/if}">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">&nbsp;</span>
					<span class="col-md-9">
						<a class="btn btn-light {if (!empty($TASK_OBJECT->emailcc))}d-none{/if}" role="button"
							id="ccLink">{\App\Language::translate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>&nbsp;&nbsp;
						<a class="btn btn-light {if (!empty($TASK_OBJECT->emailbcc))}d-none{/if}" role="button"
							id="bccLink">{\App\Language::translate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
					</span>
				</span>
			</div>
			<hr />
			<div class="form-row">
				{include file=\App\Layout::getTemplatePath('VariablePanel.tpl') SELECTED_MODULE=$SOURCE_MODULE PARSER_TYPE='mail' GRAY=true}
			</div>
			<hr />
			<div class="form-row pb-3">
				<span class="col-md-7 form-row">
					<span class="col-md-3 col-form-label">{\App\Language::translate('LBL_SUBJECT',$QUALIFIED_MODULE)}
						<span class="redColor">*</span></span>
					<div class="col-md-9">
						<input data-validation-engine='validate[required]' name="subject" class="fields form-control"
							type="text" name="subject"
							value="{if !empty($TASK_OBJECT->subject)}{$TASK_OBJECT->subject|escape}{/if}"
							id="subject"
							spellcheck="true" />
					</div>
				</span>
			</div>
			<div class="pb-3">
				<textarea id="content" class="form-control"
					name="content">{if !empty($TASK_OBJECT->content)}{$TASK_OBJECT->content}{/if}</textarea>
			</div>
		</div>
	</div>
{/strip}
