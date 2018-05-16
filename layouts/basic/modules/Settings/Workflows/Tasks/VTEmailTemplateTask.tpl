{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div id="VtVTEmailTemplateTaskContainer">
		<div class="">
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select id="task_timefields" name="smtp" class="select2 form-control" data-validation-engine="validate[required]" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
						<option value="">{\App\Language::translate('LBL_DEFAULT')}</option>
						{foreach from=App\Mail::getAll() item=ITEM key=ID}
							<option value="{$ID}" {if $TASK_OBJECT->smtp == $ID}selected{/if}>{$ITEM['name']}({$ITEM['host']})</option>
						{/foreach}	
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="template" data-validation-engine="validate[required]">
						<option value="">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						{foreach from=App\Mail::getTempleteList($SOURCE_MODULE,'PLL_RECORD') key=key item=item}
							<option {if $TASK_OBJECT->template eq $item['id']}selected=""{/if} value="{$item['id']}">{\App\Language::translate($item['name'], $QUALIFIED_MODULE)}</option>
						{/foreach}	
					</select>
				</div>
			</div>
			<div class="row pb-3">
				<span class="col-md-4"></span>
				<span class="col-md-4">
					<label><input type="checkbox" class="align-text-bottom" value="true" name="emailoptout" {if $TASK_OBJECT->emailoptout}checked{/if}>&nbsp;{\App\Language::translate('LBL_CHECK_EMAIL_OPTOUT', $QUALIFIED_MODULE)}</label>
				</span>
			</div>
			<div class="row pb-3">
				{assign var=EMAIL value=settype($TASK_OBJECT->email, 'array')}
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('Select e-mail address', $QUALIFIED_MODULE)}</span>
				<div class="col-md-4">
					<select class="select2 form-control" name="email" data-placeholder="{\App\Language::translate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" multiple  data-validation-engine="validate[required]">
						<option value="none"></option>
						{assign var=TEXT_PARSER value=App\TextParser::getInstance($SOURCE_MODULE)}
						{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable('email')}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$FIELDS}
									<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}" {if $TASK_OBJECT->email && in_array($ITEM['var_value'],$TASK_OBJECT->email)}selected=""{/if}>
										{$ITEM['label']}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
						{foreach item=FIELDS from=$TEXT_PARSER->getRelatedVariable('email')}
							{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$FIELDS}
								<optgroup label="{$BLOCK_NAME}">
									{foreach item=ITEM from=$RELATED_FIELDS}
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
			<div class="row pb-3">
				<span class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_BCC')}</span>
				<div class="col-md-4">
					<input class="form-control" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="copy_email" value="{$TASK_OBJECT->copy_email}">
				</div>
			</div>
		</div>
	</div>	
{/strip}	
