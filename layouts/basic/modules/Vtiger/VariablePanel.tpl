{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if empty($TEXT_PARSER)}
		{assign var=TEXT_PARSER value=\App\TextParser::getInstance($SELECTED_MODULE)}
	{/if}
	{if $PARSER_TYPE}
		{assign var=TEXT_PARSER value=$TEXT_PARSER->setType($PARSER_TYPE)}
	{/if}
	{if $SELECTED_MODULE && App\Module::getEntityInfo($SELECTED_MODULE)}
		<div class="col-md-6 fieldRow">
			<div class="col-md-3 fieldLabel paddingLeft5px medium {if $GRAY}bc-gray-lighter{/if}">
				<label class="muted">{\App\Language::translate('LBL_MODULE_FIELDS')}</label>
			</div>
			<div class="medium col-md-9 fieldValue">
				<div class="row">
					<div class="input-group">
						<select class="select2 form-control" id="recordVariable">
							{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable()}
								<optgroup label="{\App\Language::translate($BLOCK_NAME, $SELECTED_MODULE)}">
									{foreach item=ITEM from=$FIELDS}
										<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}">{\App\Language::translate($ITEM['label'], $SELECTED_MODULE)}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						<div class="input-group-btn">
							<button type="button" class="btn btn-primary clipboard" data-copy-target="#recordVariable" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')} - {vtranslate('LBL_COPY_VALUE')}">
								<span class="glyphicon glyphicon-copy"></span>
							</button>
							<button type="button" class="btn btn-success clipboard" data-copy-target="#recordVariable" data-copy-type="label" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')}  - {vtranslate('LBL_COPY_LABEL')}">
								<span class="glyphicon glyphicon-copy"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		{assign var=RELETED_VARIABLE value=$TEXT_PARSER->getReletedVariable()}
		{if $RELETED_VARIABLE}
			<div class="col-md-6 fieldRow">
				<div class="col-md-3 fieldLabel paddingLeft5px medium {if $GRAY}bc-gray-lighter{/if}">
					<label class="muted">{\App\Language::translate('LBL_RELETED_MODULE_FIELDS')}</label>
				</div>
				<div class="medium col-md-9 fieldValue">
					<div class="row">
						<div class="input-group">
							<select class="select2" id="relatedVariable">
								{foreach item=FIELDS from=$RELETED_VARIABLE}
									{foreach item=RELETED_FIELDS key=BLOCK_NAME from=$FIELDS}
										<optgroup label="{$BLOCK_NAME}">
											{foreach item=ITEM from=$RELETED_FIELDS}
												<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
											{/foreach}
										</optgroup> 
									{/foreach}
								{/foreach}
							</select>
							<div class="input-group-btn">
								<button type="button" class="btn btn-primary clipboard" data-copy-target="#relatedVariable" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')} - {vtranslate('LBL_COPY_VALUE')}">
									<span class="glyphicon glyphicon-copy"></span>
								</button>
								<button type="button" class="btn btn-success clipboard" data-copy-target="#relatedVariable" data-copy-type="label" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')}  - {vtranslate('LBL_COPY_LABEL')}">
									<span class="glyphicon glyphicon-copy"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{assign var=SOURCE_VARIABLE value=$TEXT_PARSER->getSourceVariable()}
		{if $SOURCE_VARIABLE}
			<div class="col-md-6 fieldRow">
				<div class="col-md-3 fieldLabel paddingLeft5px medium {if $GRAY}bc-gray-lighter{/if}">
					<label class="muted">{\App\Language::translate('LBL_SOURCE_MODULE_FIELDS')}</label>
				</div>
				<div class="medium col-md-9 fieldValue">
					<div class="row">
						<div class="input-group">
							<select class="select2" id="sourceVariable">
								{foreach item=BLOCKS key=SOURCE_MODULE from=$SOURCE_VARIABLE}
									{if $SOURCE_MODULE == 'LBL_ENTITY_VARIABLES'}
										<optgroup label="{\App\Language::translate($SOURCE_MODULE)}">
											{foreach item=ITEM from=$BLOCKS}
												<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
											{/foreach}
										</optgroup> 
									{else}
										{assign var=SOURCE_LABEL value=\App\Language::translate(\App\Language::getSingularModuleName($SOURCE_MODULE), $SOURCE_MODULE)}
										{foreach item=FIELDS key=BLOCK_NAME from=$BLOCKS}
											<optgroup label="{$SOURCE_LABEL} - {\App\Language::translate($BLOCK_NAME, $SOURCE_MODULE)}">
												{foreach item=ITEM from=$FIELDS}
													<option value="{$ITEM['var_value']}" data-label="{$ITEM['var_label']}">{$SOURCE_LABEL}: {$ITEM['label']}</option>
												{/foreach}
											</optgroup> 
										{/foreach}
									{/if}
								{/foreach}
							</select>
							<div class="input-group-btn">
								<button type="button" class="btn btn-primary clipboard" data-copy-target="#sourceVariable" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')} - {vtranslate('LBL_COPY_VALUE')}">
									<span class="glyphicon glyphicon-copy"></span>
								</button>
								<button type="button" class="btn btn-success clipboard" data-copy-target="#sourceVariable" data-copy-type="label" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')}  - {vtranslate('LBL_COPY_LABEL')}">
									<span class="glyphicon glyphicon-copy"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}		
	{/if}
	<div class="col-md-6 fieldRow">
		<div class="col-md-3 fieldLabel paddingLeft5px medium {if $GRAY}bc-gray-lighter{/if}">
			<label class="muted">{\App\Language::translate('LBL_ADDITIONAL_VARIABLES')}</label>
		</div>
		<div class="medium col-md-9 fieldValue">
			<div class="row">
				<div class="input-group">
					<select class="select2" id="generalVariable">
						{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getGeneralVariable()}
							<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
								{foreach item=LABEL key=VARIABLE from=$FIELDS}
									<option value="{$VARIABLE}">{$LABEL}</option>
								{/foreach}
							</optgroup> 
						{/foreach}
					</select>
					<div class="input-group-btn">
						<button type="button" class="btn btn-primary clipboard" data-copy-target="#generalVariable" title="{vtranslate('LBL_COPY_TO_CLIPBOARD')}">
							<span class="glyphicon glyphicon-copy"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
{/strip}
