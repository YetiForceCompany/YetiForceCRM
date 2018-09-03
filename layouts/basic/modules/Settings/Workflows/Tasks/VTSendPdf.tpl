{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
						<option {if $TASK_OBJECT->pdfTemplate eq $item->getId()}selected{/if}
								value="{$item->getId()}">{$item->getName()}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select id="task_timefields" name="smtp" class="select2 form-control" data-select="allowClear"
						data-placeholder="{\App\Language::translate('LBL_DEFAULT')}">
					<optgroup class="p-0">
						<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
					</optgroup>
					{foreach from=App\Mail::getAll() item=ITEM key=ID}
						<option value="{$ID}" {if $TASK_OBJECT->smtp == $ID}selected{/if}>{$ITEM['name']}
							({$ITEM['host']})
						</option>
					{/foreach}
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
					{foreach from=App\Mail::getTempleteList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
						<option {if $TASK_OBJECT->mailTemplate eq $item['id']}selected=""{/if}
								value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row pb-3">
			<span class="col-md-4"></span>
			<span class="col-md-4">
				<label><input type="checkbox" class="align-text-bottom" value="true" name="emailoptout"
							  {if $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
			</span>
		</div>
		<div class="row pb-3">
			<span class="col-md-4 col-form-label text-right">{\App\Language::translate('Select e-mail address', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="select2 form-control" name="email"
						data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}"
						multiple="multiple"
						data-validation-engine="validate[required]">
					{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
					{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('email')}
						<optgroup label="{$BLOCK_NAME}">
							{foreach item=ITEM from=$FIELDS}
								<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}"
										{if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
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
											{if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					{/foreach}
				</select>
			</div>
		</div>
	</div>
{/strip}
