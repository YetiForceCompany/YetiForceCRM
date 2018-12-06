{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class='verticalScroll'>
	<div class="editViewContainer">
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php"
			  enctype="multipart/form-data">
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input type="hidden" name="picklistDependency"
					   value='{\App\Purifier::encodeHtml($PICKIST_DEPENDENCY_DATASOURCE)}'/>
			{/if}
			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField"
					   value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}'/>
			{/if}
			{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
			{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
			{if $IS_PARENT_EXISTS}
				{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
				<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}"/>
				<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}"/>
			{else}
				<input type="hidden" name="module" value="{$MODULE}"/>
			{/if}
			<input type="hidden" name="action" value="Save"/>
			<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}"/>
			<input name="defaultOtherEventDuration" value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" type="hidden"/>
			{if $MODE === 'duplicate'}
				<input type="hidden" name="_isDuplicateRecord" value="true"/>
				<input type="hidden" name="_duplicateRecord" value="{\App\Request::_get('record')}"/>
			{/if}
			{if $IS_RELATION_OPERATION }
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
				<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}"/>
			{/if}
			<input type="hidden" id="allowedLetters" value="{$allowChars}"/>
			<input type="hidden" id="maxChars" value="{$passLengthMax}"/>
			<input type="hidden" id="minChars" value="{$passLengthMin}"/>
			{foreach from=$RECORD->getModule()->getFieldsByDisplayType(9) item=FIELD key=FIELD_NAME}
				<input type="hidden" name="{$FIELD_NAME}"
					   value="{\App\Purifier::encodeHtml($RECORD->get($FIELD_NAME))}"/>
			{/foreach}
			<div class="widget_header row">
				<div class="col-12">
					{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
					<span class="float-left">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
					</span>
				</div>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
				{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
				{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
				{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
				{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
				{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				{if $BLOCKS_HIDE}
					<div class="c-panel form-row js-toggle-panel row mx-1 mb-3"
						 data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true"{/if}
						 data-label="{$BLOCK_LABEL}">
						<div class="blockHeader c-panel__header align-items-center">
							{if !empty($APIADDRESS_ACTIVE) && ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION')}
								{assign var=APIADDRESFIELD value=TRUE}
							{else}
								{assign var=APIADDRESFIELD value=FALSE}
							{/if}
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
								  data-js="click" data-mode="hide"
								  data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}"
								  data-js="click" data-mode="show"
								  data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h4>{\App\Language::translate($BLOCK_LABEL, $MODULE)}</h4>
						</div>
						<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}"
							 data-js="display">
							<div class="form-row m-0 mt-2">
								{assign var=COUNTER value=0}
								{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '19' || $FIELD_MODEL->getUIType() eq '300'}
								{if $COUNTER eq '1'}
							</div>
							<div class="col-md-12 px-0 m-0">
								{assign var=COUNTER value=0}
								{/if}
								{/if}
								{if $COUNTER eq 2}
							</div>
							<div class="form-row m-0">
								{assign var=COUNTER value=1}
								{else}
								{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="{if $FIELD_MODEL->getUIType() neq "300"} col-sm-6 form-row align-items-center my-1 mx-0 {else} w-100 {/if}">
									{if $FIELD_MODEL->getUIType() neq "300"}
										<div class="col-lg-12 col-xl-3 text-lg-left text-xl-right fieldLabel  {$WIDTHTYPE}">
											<label class="u-text-small-bold m-0 pr-1">
												{if $FIELD_MODEL->isMandatory() eq true}
													<span class="redColor">*</span>
												{/if}
												{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
											</label>
										</div>
									{/if}
									<div class="fieldValue {$WIDTHTYPE} {if $FIELD_MODEL->getUIType() eq '300'} col-md-12 {assign var=COUNTER value=$COUNTER+1} {else} col-lg-12 col-xl-9  {/if}">
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
