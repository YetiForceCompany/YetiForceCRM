{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($TEXT_PARSER)}
		{assign var=TEXT_PARSER value=\App\TextParser::getInstance($SELECTED_MODULE)}
	{/if}
	{if $PARSER_TYPE}
		{assign var=TEXT_PARSER value=$TEXT_PARSER->setType($PARSER_TYPE)}
	{/if}
	{if $SELECTED_MODULE && App\Module::getEntityInfo($SELECTED_MODULE)}
		<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
			<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_MODULE_FIELDS','Other.TextParser')}</label>
			<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
				<div class="input-group">
					<select class="select2 form-control" id="recordVariable" data-width="style">
						{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable()}
							<optgroup label="{$BLOCK_NAME}">
								{foreach item=ITEM from=$FIELDS}
									<option value="{$ITEM['var_value']}"
											data-label="{$ITEM['var_label']}">{\App\Language::translate($ITEM['label'], $SELECTED_MODULE)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<div class="input-group-append">
						<button type="button" class="btn btn-primary clipboard" data-copy-target="#recordVariable"
								title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
							<span class="fas fa-copy"></span>
						</button>
						<button type="button" class="btn btn-success clipboard" data-copy-target="#recordVariable"
								data-copy-type="label"
								title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}  - {\App\Language::translate('LBL_COPY_LABEL','Other.TextParser')}">
							<span class="fas fa-copy"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
		{assign var=RELATED_VARIABLE value=$TEXT_PARSER->getRelatedVariable()}
		{if $RELATED_VARIABLE}
			<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
				<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_DEPENDENT_MODULE_FIELDS','Other.TextParser')}</label>
				<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
					<div class="input-group">
						<select class="select2 form-control" id="relatedVariable" data-width="style">
							{foreach item=FIELDS from=$RELATED_VARIABLE}
								{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$FIELDS}
									<optgroup label="{$BLOCK_NAME}">
										{foreach item=ITEM from=$RELATED_FIELDS}
											<option value="{$ITEM['var_value']}"
													data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							{/foreach}
						</select>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary clipboard" data-copy-target="#relatedVariable"
									title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
								<span class="fas fa-copy"></span>
							</button>
							<button type="button" class="btn btn-success clipboard" data-copy-target="#relatedVariable"
									data-copy-type="label"
									title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}  - {\App\Language::translate('LBL_COPY_LABEL','Other.TextParser')}">
								<span class="fas fa-copy"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{assign var=SOURCE_VARIABLE value=$TEXT_PARSER->getSourceVariable()}
		{if $SOURCE_VARIABLE}
			<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
				<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_SOURCE_MODULE_FIELDS','Other.TextParser')}</label>
				<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
					<div class="input-group">
						<select class="select2" id="sourceVariable" data-width="style">
							{foreach item=BLOCKS key=SOURCE_MODULE from=$SOURCE_VARIABLE}
								{if $SOURCE_MODULE == 'LBL_ENTITY_VARIABLES'}
									<optgroup label="{\App\Language::translate($SOURCE_MODULE, 'Other.TextParser')}">
										{foreach item=ITEM from=$BLOCKS}
											<option value="{$ITEM['var_value']}"
													data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
										{/foreach}
									</optgroup>
								{else}
									{assign var=SOURCE_LABEL value=\App\Language::translate(\App\Language::getSingularModuleName($SOURCE_MODULE), $SOURCE_MODULE)}
									{foreach item=FIELDS key=BLOCK_NAME from=$BLOCKS}
										<optgroup
												label="{$SOURCE_LABEL} - {\App\Language::translate($BLOCK_NAME, $SOURCE_MODULE)}">
											{foreach item=ITEM from=$FIELDS}
												<option value="{$ITEM['var_value']}"
														data-label="{$ITEM['var_label']}">{$SOURCE_LABEL}
													: {$ITEM['label']}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								{/if}
							{/foreach}
						</select>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary clipboard" data-copy-target="#sourceVariable"
									title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
								<span class="fas fa-copy"></span>
							</button>
							<button type="button" class="btn btn-success clipboard" data-copy-target="#sourceVariable"
									data-copy-type="label"
									title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}  - {\App\Language::translate('LBL_COPY_LABEL','Other.TextParser')}">
								<span class="fas fa-copy"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		{/if}
		{assign var=RELATED_LISTS value=$TEXT_PARSER->getRelatedListVariable()}
		{if $RELATED_LISTS}
			<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
				<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_RELATED_RECORDS_LIST','Other.TextParser')}</label>
				<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
					<div class="input-group">
						<select class="select2 form-control" id="relatedLists" data-width="style">
							{foreach item=MODULE_LIST from=$RELATED_LISTS}
								<option value="{$MODULE_LIST['key']}">{$MODULE_LIST['label']}</option>
							{/foreach}
						</select>
						<div class="input-group-append">
							<button type="button" class="btn btn-primary clipboard" data-copy-target="#relatedLists"
									title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
								<span class="fas fa-copy"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		{/if}
	{/if}
	{assign var=BASE_LISTS value=$TEXT_PARSER->getBaseListVariable()}
	{if $BASE_LISTS}
		<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
			<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_RECORDS_LIST')}</label>
			<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
				<div class="input-group">
					<select class="select2 form-control" id="recordList" data-width="style">
						{foreach item=MODULE_LIST from=$BASE_LISTS}
							<option value="{$MODULE_LIST['key']}">{$MODULE_LIST['label']}</option>
						{/foreach}
					</select>
					<div class="input-group-append">
						<button type="button" class="btn btn-primary clipboard" data-copy-target="#recordList"
								title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
							<span class="fas fa-copy"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	{/if}
	<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
		<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_ADDITIONAL_VARIABLES','Other.TextParser')}</label>
		<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
			<div class="input-group">
				<select class="select2 form-control" id="generalVariable" data-container-class-css="form-control"
						data-width="style">
					{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getGeneralVariable()}
						<optgroup label="{\App\Language::translate($BLOCK_NAME, 'Other.TextParser')}">
							{foreach item=LABEL key=VARIABLE from=$FIELDS}
								<option value="{$VARIABLE}">{$LABEL}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				<div class="input-group-append">
					<button type="button" class="btn btn-primary clipboard" data-copy-target="#generalVariable"
							title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}">
						<span class="fas fa-copy"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
		<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_ORGANIZATION','Other.TextParser')}</label>
		<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
			<div class="input-group">
				<select class="select2 form-control js-company-list" id="companyList"
						data-container-class-css="form-control"
						data-width="style" data-js="val">
					{foreach item=DATA key=KEY from=\App\MultiCompany::getAll()}
						<option value="{$DATA['multicompanyid']}">{$DATA['company_name']}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
	<div class="col-sm-6 fieldRow row form-group align-items-center my-1">
		<label class="my-0 col-lg-12 col-xl-3 fieldLabel text-lg-left text-xl-right">{\App\Language::translate('LBL_COMPANY_VARIABLES','Other.TextParser')}</label>
		<div class="medium w-100  col-lg-12 col-xl-9  fieldValue">
			<div class="input-group">
				<select class="select2 form-control js-company-variable" id="companyVariable"
						data-container-class-css="form-control"
						data-width="style" data-js="container">
					{foreach item=BLOCK_OBJ key=BLOCK_NAME from=\Vtiger_Module_Model::getInstance('MultiCompany')->getBlocks()}
						<optgroup label="{\App\Language::translate($BLOCK_NAME, 'MultiCompany')}">
							{foreach item=FIELD from=$BLOCK_OBJ->getFields()}
								<option value="$(organization : 1|{$FIELD->getFieldName()})$"
										data-value-template="$(organization :
									__X__|{$FIELD->getFieldName()})$">{\App\Language::translate($FIELD->getFieldLabel(), 'MultiCompany')}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				<div class="input-group-append">
					<button type="button" class="btn btn-primary clipboard" data-copy-target="#companyVariable"
							title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}">
						<span class="fas fa-copy"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
