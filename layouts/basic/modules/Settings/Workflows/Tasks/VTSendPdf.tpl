{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=MAIL_TPL value=OSSMailTemplates_Record_Model::getTempleteList($SOURCE_MODULE)}
	<div class="well" id="VtVTEmailTemplateTaskContainer">
		<div class="row">
			<span class="col-md-4">{vtranslate('LBL_PDF_TEMPLATE', $QUALIFIED_MODULE)}</span>
			<div class="col-md-6 padding-bottom1per">
				<select class="chzn-select form-control" name="pdf_tpl" data-validation-engine="validate[required]">
					<option value="none">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
					{foreach from=Vtiger_PDF_Model::getTemplatesByModule($SOURCE_MODULE) item=item}
						<option {if $TASK_OBJECT->pdf_tpl eq $item->getId()}selected{/if} value="{$item->getId()}">{$item->getName()}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row">
			<span class="col-md-4">{vtranslate('EmailTempleteList', $QUALIFIED_MODULE)}</span>
			<div class="col-md-6 padding-bottom1per">
				<select class="chzn-select form-control" name="email_tpl"  data-validation-engine="validate[required]">
					<option value="none">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
					{foreach from=$MAIL_TPL key=key item=item}
						<option {if $TASK_OBJECT->email_tpl eq $item.ossmailtemplatesid}selected{/if} value="{$item.ossmailtemplatesid}">{vtranslate($item.name, $QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-4">{vtranslate('Select e-mail address', $QUALIFIED_MODULE)}</span>
			<div class="col-md-4">
				<select class="chzn-select form-control" name="email_fld" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" data-validation-engine="validate[required]">
					<option value=""></option>
					{assign var=RECORD_STRUCTURE_TYPE value=$RECORD_STRUCTURE_MODEL->getStructure('email')}
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE_TYPE }
						<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
								{assign var=KEYVAL value=$FIELD_MODEL->get(selectOption)}
								{if $KEYVAL neq ''} 
									<option value="{$KEYVAL}" {if $TASK_OBJECT->email_fld eq $KEYVAL}selected=""{/if}>
										{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')} 
											({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
										{else}
											{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
										{/if}
									</option>
								{/if}
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
	</div>

{/strip}
