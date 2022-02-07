{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-Tabs-DetailViewLayout -->
	{assign var=FIELD_TYPE_INFO value=$SELECTED_MODULE_MODEL->getAddFieldTypeInfo()}
	{assign var=IS_SORTABLE value=$SELECTED_MODULE_MODEL->isSortableAllowed()}
	{assign var=IS_BLOCK_SORTABLE value=$SELECTED_MODULE_MODEL->isBlockSortableAllowed()}
	{assign var=ALL_BLOCK_LABELS value=[]}
	{if $IS_SORTABLE}
		<div class="btn-toolbar" id="layoutEditorButtons">
			<button class="btn btn-success addButton addCustomBlock">
				<span class="fas fa-plus mr-2"></span>
				{App\Language::translate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}
			</button>
			<button class="btn btn-success saveFieldSequence ml-3 d-none">
				<span class="fas fa-check mr-2"></span>
				{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}
			</button>
			<button class="btn btn-default ml-3 js-inactive-fields-btn">
				<span class="fas fa-ban mr-2"></span>
				{App\Language::translate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}
			</button>
		</div>
	{/if}
	<div class="moduleBlocks">
		{assign var=FIEL_TYPE_LABEL value=Settings_LayoutEditor_Field_Model::$fieldTypeLabel}
		{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
			{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
			{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
			{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_LABEL_KEY}
			<div id="block_{$BLOCK_ID}"
				class="editFieldsTable block_{$BLOCK_ID} mb-2 border1px {if $IS_BLOCK_SORTABLE} blockSortable{/if} js-block-container"
				data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}"
				style="border-radius: 4px;background: white;" data-js="container">
				<div class="layoutBlockHeader d-flex flex-wrap justify-content-between m-0 p-1 pt-1 w-100">
					<div class="blockLabel u-white-space-nowrap">
						{if $IS_BLOCK_SORTABLE}
							<img class="align-middle" src="{\App\Layout::getImagePath('drag.png')}" alt="" /> &nbsp;&nbsp;
						{/if}
						<strong class="align-middle js-block-label" title="{$BLOCK_LABEL_KEY}" data-js="container">
							{if !empty($BLOCK_MODEL->get('icon'))}<span class="{$BLOCK_MODEL->get('icon')} mr-2"></span>{/if}
							{App\Language::translate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}
						</strong>
					</div>
					<div class="btn-toolbar pl-1" role="toolbar">
						{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
							<div class="btn-group btn-group-sm u-h-fit mr-1 mt-1">
								<button class="btn btn-success addCustomField" type="button">
									<span class="fas fa-plus u-mr-5px"></span><strong>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
								</button>
							</div>
							<div class="btn-group btn-group-sm u-h-fit mr-1 mt-1">
								<button class="btn btn-warning js-add-system-field" type="button" data-js="click">
									<span class="fas fa-plus-circle u-mr-5px"></span><strong>{App\Language::translate('BTN_ADD_SYSTEM_FIELD', $QUALIFIED_MODULE)}</strong>
								</button>
							</div>
						{/if}
						<div class="btn-group btn-group-sm btn-group-toggle mt-1" data-toggle="buttons">
							<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if $BLOCK_MODEL->isHidden()} active{/if}" data-visible="0"
								data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
								<input type="radio" name="options" id="options-option1" autocomplete="off" {if $BLOCK_MODEL->isHidden()} checked{/if}>
								<span class="fas fa-fw mr-1 fa-eye-slash"></span>
								<span class="c-btn-collapsible__text">{App\Language::translate('LBL_ALWAYS_HIDE', $QUALIFIED_MODULE)}</span>
							</label>
							<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if !$BLOCK_MODEL->isHidden() && !$BLOCK_MODEL->isDynamic()} active{/if}" data-visible="1"
								data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
								<input type="radio" name="options" id="options-option2" autocomplete="off" {if !$BLOCK_MODEL->isHidden() && !$BLOCK_MODEL->isDynamic()} checked{/if}>
								<span class="fas fa-fw mr-1  fa-eye"></span>
								<span class="c-btn-collapsible__text">{App\Language::translate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}</span>
							</label>
							<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if $BLOCK_MODEL->isDynamic()} active{/if}" data-visible="2"
								data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
								<input type="radio" name="options" id="options-option3" autocomplete="off" {if $BLOCK_MODEL->isDynamic()} checked{/if}>
								<span class="fas fa-fw mr-1  fa-atom"></span>
								<span class="c-btn-collapsible__text">{App\Language::translate('LBL_DYNAMIC_SHOW', $QUALIFIED_MODULE)}</span>
							</label>
						</div>
						{if $BLOCK_MODEL->isCustomized()}
							<div class="btn-group btn-group-sm ml-1 mt-1 u-h-fit" role="group">
								<button class="js-delete-custom-block-btn c-btn-collapsible btn btn-danger js-popover-tooltip" data-js="click">
									<span class="fas fa-trash-alt mr-1"></span>
									<span class="c-btn-collapsible__text">{App\Language::translate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</span>
								</button>
							</div>
						{/if}
					</div>
				</div>
				<div class="blockFieldsList blockFieldsSortable row m-0 p-1 u-min-height-50">
					{for $LOOP=0 to 1}
						<ul name="{if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}sortable{$LOOP+1}{/if}"
							class="sortTableUl js-sort-table{$LOOP+1} connectedSortable col-md-6 mb-1" data-js="container">
							{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
								{if $smarty.foreach.fieldlist.index % 2 eq $LOOP}
									<li>
										<div class="opacity editFields ml-0 border1px" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
											<div class="px-2 py-1 d-flex">
												{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
												<div class="col-12 pr-0 fieldContainer" style="word-wrap: break-word;">
													{if $FIELD_MODEL->isEditable()}
														<a class="mr-3">
															<img src="{\App\Layout::getImagePath('drag.png')}" border="0" alt="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
														</a>
													{/if}
													<span class="fieldLabel">
														{assign var=ICON value=$FIELD_MODEL->getIcon()}
														{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
														{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
														{if $IS_MANDATORY}
															<span class="redColor">*</span>
														{/if}
														<span class="ml-3 badge badge-secondary d-none d-sm-inline-block">{$FIELD_MODEL->getName()}</span>
														{if isset($FIEL_TYPE_LABEL[$FIELD_MODEL->getUIType()])}
															<span class="ml-3 badge badge-info d-none d-sm-inline-block">{App\Language::translate($FIEL_TYPE_LABEL[$FIELD_MODEL->getUIType()], $QUALIFIED_MODULE)}</span>
														{/if}
													</span>
													<span class="float-right actions">
														<input type="hidden" value="{$FIELD_MODEL->getName()}" id="relatedFieldValue{$FIELD_MODEL->get('id')}" />
														{if $FIELD_MODEL->isEditable()}
															<button class="btn btn-success btn-xs editFieldDetails ml-1" title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}">
																<span class="yfi yfi-full-editing-view"></span>
															</button>
														{/if}
														<button class="btn btn-primary btn-xs copyFieldLabel ml-1" data-target="relatedFieldValue{$FIELD_MODEL->get('id')}" title="{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}">
															<span class="fas fa-copy"></span>
														</button>
														{if $FIELD_MODEL->isEditable() && !$FIELD_MODEL->isActiveOptionDisabled()}
															<button class="btn btn-default btn-xs js-disable-field ml-1" title="{App\Language::translate('LBL_DISABLE_FIELD', $QUALIFIED_MODULE)}">
																<span class="fas fa-ban"></span>
															</button>
														{/if}
														{if $FIELD_MODEL->isCustomField() eq 'true'}
															<button class="btn btn-danger btn-xs deleteCustomField ml-1" data-field-id="{$FIELD_MODEL->get('id')}" title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}">
																<span class="fas fa-trash-alt"></span>
															</button>
														{/if}
														<button class="btn btn-info btn-xs js-context-help ml-1" data-js="click" data-field-id="{$FIELD_MODEL->get('id')}"
															data-url="index.php?module=LayoutEditor&parent=Settings&view=HelpInfo&field={$FIELD_MODEL->get('id')}&source={$SELECTED_MODULE_NAME}" title="{App\Language::translate('LBL_CONTEXT_HELP', $QUALIFIED_MODULE)}">
															<span class="fas fa-info-circle"></span>
														</button>
													</span>
												</div>
											</div>
										</div>
									</li>
								{/if}
							{/foreach}
						</ul>
					{/for}
				</div>
			</div>
		{/foreach}
	</div>
	<input type="hidden" class="inActiveFieldsArray" value='{\App\Purifier::encodeHtml(\App\Json::encode($IN_ACTIVE_FIELDS))}' />
	{include file=\App\Layout::getTemplatePath('NewCustomBlock.tpl', $QUALIFIED_MODULE)}
	{include file=\App\Layout::getTemplatePath('AddBlockModal.tpl', $QUALIFIED_MODULE)}
	{include file=\App\Layout::getTemplatePath('CreateFieldModal.tpl', $QUALIFIED_MODULE)}
	{include file=\App\Layout::getTemplatePath('InactiveFieldModal.tpl', $QUALIFIED_MODULE)}
	<!-- /tpl-Settings-LayoutEditor-Tabs-DetailViewLayout -->
{/strip}
