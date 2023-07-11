{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTEmailTemplateTaskContainer">
		<div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select id="smtp_{\App\Layout::getUniqueId()}" name="smtp" class="select2 form-control">
						{foreach from=App\Mail::getSmtpServers() item=ITEM key=ID}
							<option value="{$ID}" {if (isset($TASK_OBJECT->smtp) && $TASK_OBJECT->smtp eq $ID) || App\Mail::SMTP_DEFAULT eq $ID}selected{/if}>{\App\Purifier::encodeHtml($ITEM['name'])}
								{if !empty($ITEM['host'])} ({\App\Purifier::encodeHtml($ITEM['host'])}){/if} {if App\Mail::SMTP_DEFAULT eq $ID} - {\App\Language::translate('LBL_DEFAULT')} {/if}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4"></span>
				<span class="col-md-4">
					<label>
						<input type="checkbox" class="align-text-bottom" value="true" name="smtpTemplate"
							{if isset($TASK_OBJECT->smtpTemplate) && $TASK_OBJECT->smtpTemplate}checked{/if}>&nbsp;{\App\Language::translate('LBL_GET_SMTP_FROM_TEMPLATE', $QUALIFIED_MODULE)}
					</label>
				</span>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="template" data-validation-engine="validate[required]"
						data-select="allowClear"
						data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						</optgroup>
						{foreach from=App\Mail::getTemplateList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if isset($TASK_OBJECT->template) && $TASK_OBJECT->template eq $item['id']}selected="" {/if}
								value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4"></span>
				<span class="col-md-4">
					<label><input type="checkbox" class="align-text-bottom" value="true" name="emailoptout"
							{if isset($TASK_OBJECT->emailoptout) && $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
				</span>
			</div>
			<div class="row pb-3">
				{assign var=EMAIL value=settype($TASK_OBJECT->email, 'array')}
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('Select e-mail address', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="email"
						data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
						multiple="multiple">
						{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
						{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('email')}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
										{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected="" {/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
						{foreach item=FIELDS from=$TEXT_PARSER->getRelatedVariable('email')}
							{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$FIELDS}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=ITEM from=$RELATED_FIELDS}
										<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
											{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected="" {/if}>
											{$ITEM['label']}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
						{/foreach}
						{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRelatedLevelVariable('email')}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$RELATED_FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
										{if isset($TASK_OBJECT->email) && $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected="" {/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">
					{\App\Language::translate('LBL_SELECT_RELATIONS_EMAIL_ADDRESS', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1 delay0" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_SELECT_RELATIONS_EMAIL_ADDRESS_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="relations_email" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option value="-">{\App\Language::translate('LBL_NONE')}</option>
						{foreach item=LABEL key=KEY from=$RELATED_RECORDS_EMAIL}
							<option value="{$KEY}" {if isset($TASK_OBJECT->relations_email) && $TASK_OBJECT->relations_email === $KEY}selected="" {/if}>
								{$LABEL}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_TO')}</span>
				<div class="col-md-4">
					<div class="input-group mb-3">
						<input class="form-control"
							data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
							name="address_emails" value="{if isset($TASK_OBJECT->address_emails)}{$TASK_OBJECT->address_emails}{/if}">
						<div class="input-group-append js-popover-tooltip u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate('LBL_ADDRESS_EMAILS_INFO', $QUALIFIED_MODULE)}">
							<span class="input-group-text">
								<span class="fas fa-info-circle" </span>
								</span>

						</div>
					</div>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_ATTACH_DOCS_FROM', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					{include file=\App\Layout::getTemplatePath('Tasks/AttatchDocumentsFrom.tpl', $QUALIFIED_MODULE)}
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_BCC')}</span>
				<div class="col-md-4">
					<input class="form-control" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="copy_email" value="{if isset($TASK_OBJECT->copy_email)}{$TASK_OBJECT->copy_email}{/if}">
				</div>
			</div>
		</div>
	</div>
{/strip}
