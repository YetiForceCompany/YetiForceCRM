{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div id="VtVTEmailTemplateTaskContainer">
		<div class="row">
			<span class="col-md-4 control-label">{\App\Language::translate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4 padding-bottom1per">
				<select class="chzn-select form-control" name="pdfTemplate" data-validation-engine="validate[required]">
					<option value="none">{\App\Language::translate('LBL_SELECT_FIELD',$MODULE)}</option>
					{foreach from=Vtiger_PDF_Model::getTemplatesByModule($SOURCE_MODULE) item=item}
						<option {if $TASK_OBJECT->pdfTemplate eq $item->getId()}selected{/if} value="{$item->getId()}">{$item->getName()}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4 control-label">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select id="task_timefields" name="smtp" class="chzn-select form-control " data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
					<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
					{foreach from=App\Mail::getAll() item=ITEM key=ID}
						<option value="{$ID}" {if $TASK_OBJECT->smtp == $ID}selected{/if}>{$ITEM['name']}({$ITEM['host']})</option>
					{/foreach}	
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4 control-label">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="mailTemplate" data-validation-engine='validate[required]'>
					<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
					{foreach from=App\Mail::getTempleteList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
						<option {if $TASK_OBJECT->mailTemplate eq $item['id']}selected=""{/if} value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
					{/foreach}	
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4"></span>
			<span class="col-md-4">
				<label><input type="checkbox" class="alignTop" value="true" name="emailoptout" {if $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4 control-label">{\App\Language::translate('Select e-mail address', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="email" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" multiple  data-validation-engine="validate[required]">
					<option value="none"></option>
					{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
					{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('email')}
						<optgroup label="{\App\Language::translate($BLOCK_NAME, $SOURCE_MODULE)}">
							{foreach item=ITEM from=$FIELDS}
								<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}" {if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
									{\App\Language::translate($ITEM['label'], $SOURCE_MODULE)}
								</option>
							{/foreach}
						</optgroup>
					{/foreach}
					{foreach item=FIELDS from=$TEXT_PARSER->getReletedVariable('email')}
						{foreach item=RELETED_FIELDS key=BLOCK_NAME from=$FIELDS}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$RELETED_FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}" {if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
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
