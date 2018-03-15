{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class='verticalScroll'>
		<div class="editViewContainer">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
				{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
					<input type="hidden" name="picklistDependency" value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}' />
				{/if}
				{if !empty($MAPPING_RELATED_FIELD)}
					<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
				{/if}
				{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
				{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
				{if $IS_PARENT_EXISTS}
					{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
					<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
					<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
				{else}
					<input type="hidden" name="module" value="{$MODULE}" />
				{/if}
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
				<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
				<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
				{if $IS_RELATION_OPERATION }
					<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
					<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
					<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
				{/if}
				<input type="hidden" id="allowedLetters" value="{$allowChars}" />
				<input type="hidden" id="maxChars" value="{$passLengthMax}" />
				<input type="hidden" id="minChars" value="{$passLengthMin}" />
				{foreach from=$RECORD->getModule()->getFieldsByDisplayType(9) item=FIELD key=FIELD_NAME}
					<input type="hidden" name="{$FIELD_NAME}" value="{\App\Purifier::encodeHtml($RECORD->get($FIELD_NAME))}" />
				{/foreach}
				<div class="widget_header row">
					<div class="col-12">
						{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
						<span class="float-left">
							{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
						</span>
					</div>
				</div>

				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
				{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
				{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
				{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
				{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
				{if $BLOCKS_HIDE}
					<div class="card js-toggle-panel row mx-0 blockContainer mt-2" data-label="{$BLOCK_LABEL}">
						<div class="row blockHeader card-header mx-0">
							{if $APIADDRESS_ACTIVE eq true && ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION')}
								{assign var=APIADDRESFIELD value=TRUE}
							{else}
								{assign var=APIADDRESFIELD value=FALSE}
							{/if}

							<span class="cursorPointer blockToggle fas fa-angle-right {if !($IS_HIDDEN)}d-none{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="cursorPointer blockToggle fas fa-angle-down {if ($IS_HIDDEN)}d-none{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h4>{\App\Language::translate($BLOCK_LABEL, $MODULE)}</h4>
						</div>
						<div class="col-md-12 px-0 card-body blockContent {if $IS_HIDDEN}d-none{/if}">
							<div class="col-md-12 px-0 form-row">
								{assign var=COUNTER value=0}
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
									{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '19' || $FIELD_MODEL->getUIType() eq '300'}
										{if $COUNTER eq '1'}
										</div>
										<div class="col-md-12 px-0">
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
									</div>
									<div class="col-md-12 px-0 form-row">
										{assign var=COUNTER value=1}
									{else}
										{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<div class="{if $FIELD_MODEL->getUIType() neq "300"} col-sm-6 form-row {else} w-100 {/if}">
										{if $FIELD_MODEL->getUIType() neq "300"}
											<div class="col-md-3 fieldLabel p-2 {$WIDTHTYPE}">
												<label class="muted small font-weight-bold float-right mr-3">
													{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
													{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
												</label>
											</div>
										{/if}
										<div class="fieldValue {$WIDTHTYPE} {if $FIELD_MODEL->getUIType() eq '300'} col-md-12 {assign var=COUNTER value=$COUNTER+1} {else} col-md-9  {/if}">
											<div class="form-row">
												<div class="col-md-12">
													{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
												</div>
											</div>
										</div>
									</div>
									{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->getUIType() neq "19" and $FIELD_MODEL->getUIType() neq "20" and $FIELD_MODEL->getUIType() neq "30" and $FIELD_MODEL->getUIType() neq '300'}
									</div>
									<div class="col-md-12 px-0">
									{/if}
								{/foreach}
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		{/strip}
