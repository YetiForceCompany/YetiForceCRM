{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTEmailTemplateTaskContainer">
		<div class="row">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4 pb-3">
				<select class="select2 form-control" name="pdfTemplate" data-validation-engine="validate[required]"
					data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}"
					data-select="allowClear">
					<optgroup class="p-0">
						<option value="none">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</option>
					</optgroup>
					{foreach from=Vtiger_PDF_Model::getTemplatesByModule($SOURCE_MODULE) item=item}
						<option {if isset($TASK_OBJECT->pdfTemplate) && $TASK_OBJECT->pdfTemplate eq $item->getId()}selected="selected" {/if}
							value="{$item->getId()}">{$item->getName()}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				{assign var=SMTP_DEFAULT value=App\Mail::getDefaultSmtp()}
				<select id="smtp_{\App\Layout::getUniqueId()}" name="smtp" class="select2 form-control" data-select="allowClear">
					<optgroup class="p-0">
						{if !empty($SMTP_DEFAULT)}
							<option value="{$SMTP_DEFAULT}" {if isset($TASK_OBJECT->smtp) && $TASK_OBJECT->smtp eq $SMTP_DEFAULT}selected{/if}>{\App\Language::translate('LBL_DEFAULT')} </option>
						{else}
							<option>{\App\Language::translate('LBL_SELECT_SMTP',$QUALIFIED_MODULE)}</option>
						{/if}
						{foreach from=App\Mail::getSmtpServers(true) item=ITEM key=ID}
							<option value="{$ID}" {if isset($TASK_OBJECT->smtp) && $TASK_OBJECT->smtp == $ID}selected{/if}>{\App\Purifier::encodeHtml($ITEM['name'])}
								{if !empty($ITEM['host'])} ({\App\Purifier::encodeHtml($ITEM['host'])}){/if}
							</option>
						{/foreach}
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="select2 form-control" name="mailTemplate" data-validation-engine='validate[required]'
					data-select="allowClear"
					data-placeholder="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
					</optgroup>
					{foreach from=App\Mail::getTemplateList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
						<option {if isset($TASK_OBJECT->mailTemplate) && $TASK_OBJECT->mailTemplate eq $item['id']}selected="" {/if}
							value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4"></span>
			<span class="col-md-4">
				<label>
					<input type="checkbox" class="align-text-bottom" value="true" name="emailoptout"
						{if isset($TASK_OBJECT->emailoptout) && $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}
				</label>
			</span>
		</div>
		{if !empty($TASK_OBJECT->email) }
			{if  is_array($TASK_OBJECT->email)}
				{assign var=EMAIL value=implode(',', $TASK_OBJECT->email)}
			{else}
				{assign var=EMAIL value=$TASK_OBJECT->email}
			{/if}
		{/if}
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('Select e-mail address',$QUALIFIED_MODULE)}<span
					class="redColor">*</span></span>
			<div class="col-md-4">
				<div class="col-md-12 px-0 row m-0">
					<div class="col px-0 mr-1">
						<input data-validation-engine='validate[required]' name="email" class="fields form-control"
							type="text" value="{if !empty($EMAIL)}{\App\Purifier::encodeHtml($EMAIL)}{/if}" />
					</div>
					<div class="input-group col px-0">
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
							{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRelatedLevelVariable('email')}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=ITEM from=$RELATED_FIELDS}
										<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}">
											{$ITEM['label']}
										</option>
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
	</div>
{/strip}
