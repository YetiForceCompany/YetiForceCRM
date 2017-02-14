{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign var=ID value=$RECORD->get('id')}
	{assign var=FIELD_DATA value=$RECORD->getFieldToEditByModal()}
	{assign var=FIELD_TO_EDIT value=$FIELD_DATA['name']}
	{assign var=BASIC_FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_TO_EDIT, $RECORD->getModule())}
	<input type="hidden" class="recordBasic" id="recordBasic" value="{$ID}">
	<input type="hidden" class="moduleBasic" id="moduleBasic" value="{$RECORD->getModuleName()}">
	<input type="hidden" class="hierarchyId" id="hierarchyId" value="{$HIERARCHY_ID}">
	<input type="hidden" class="hierarchyField" id="hierarchyField" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($HIERARCHY_FIELD))}">
	{if $RELATED_EXISTS}
		<input type="hidden" class="relatedRecord" id="relatedRecord" value="{$RELATED_RECORD}">
		<input type="hidden" class="relatedModuleBasic" id="relatedModuleBasic" value="{$RELATED_MODULE_BASIC}">
		<input type="hidden" class="relatedModule" id="relatedModule" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($RELATED_MODULE))}">
	{/if}
	<div class="modal-header">
		<div class="col-xs-8">
			<h3 class="modal-title">
				{if $RECORD->get('serviceid')}
					{\App\Record::getLabel($RECORD->get('serviceid'))}
					{if $RECORD->get('osssoldservices_renew')}<span class="marginLeft10 font-small label label-info">{vtranslate($RECORD->get('osssoldservices_renew'), $MODULE_NAME)}</span>{/if}
				{else}
					{vtranslate('LBL_CHANGE_VALUE_FOR_FIELD', $MODULE_NAME)}
				{/if}</h3>
		</div>
		<div class="btn-toolbar">
			<div class="pull-right btn-group">
				{if $RECORD->isEditable()}
					<a href="{$RECORD->getEditViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"><span class="glyphicon glyphicon-pencil summaryViewEdit"></span></a>
					{/if}
					{if $RECORD->isViewable()}
					<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}"><span  class="glyphicon glyphicon-th-list summaryViewEdit"></span></a>
					{/if}
				<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
			</div>
			{if $RECORD->isViewable()}
				{assign var=IS_EDITABLE_READONLY value=$BASIC_FIELD_MODEL->set('isEditableReadOnly', false)}
				{assign var=PICKLIST value=$BASIC_FIELD_MODEL->getPicklistValues()}
				<div class="btn-group fieldButton" data-name="{$FIELD_TO_EDIT}">
					<button type="button" class="btn btn-danger dropdown-toggle{if $BASIC_FIELD_MODEL->isEditableReadOnly()} disabled{/if}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						{vtranslate($BASIC_FIELD_MODEL->get('label'),$MODULE_NAME)} <span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach  key=KEY item=ITEM from=$PICKLIST}
							{if in_array($KEY, $RESTRICTS_ITEM) || $KEY eq $RECORD->get($FIELD_TO_EDIT)} {continue} {/if}
							<li><a href="#" class="editState" data-state='{$KEY}' data-id='{$ID}'>{$ITEM}</a></li>
							{/foreach}
					</ul>
				</div>
				{if $RECORD->get($FIELD_TO_EDIT) eq 'PLL_ACCEPTED'}
					{assign var=RENEW_FIELD_MODEL value=Vtiger_Field_Model::getInstance('osssoldservices_renew', $RECORD->getModule())}
					{assign var=IS_EDITABLE_READONLY value=$RENEW_FIELD_MODEL->set('isEditableReadOnly', false)}
					{assign var=PICKLIST value=$RENEW_FIELD_MODEL->getPicklistValues()}
					<div class="btn-group fieldButton" data-name="osssoldservices_renew">
						<button type="button" class="btn btn-primary dropdown-toggle{if $RENEW_FIELD_MODEL->isEditableReadOnly()} disabled{/if}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							{vtranslate($RENEW_FIELD_MODEL->get('label'), $MODULE_NAME)} <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach  key=KEY item=ITEM from=$PICKLIST}
								{if in_array($KEY, $RESTRICTS_ITEM) || $KEY eq $RECORD->get($FIELD_TO_EDIT)} {continue} {/if}
								<li><a href="#" class="editState" data-state='{$KEY}' data-id='{$ID}'>{$ITEM}</a></li>
								{/foreach}
						</ul>
					</div>
				{/if}
			{/if}
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="modal-body row">
		{if $RECORD->isViewable()}
			<div class="form-horizontal col-xs-5">
				{foreach item=FIELD_NAME from=$SHOW_FIELDS}
					{assign var=FIELD_MODEL value=$FIELD_LIST[$FIELD_NAME]}
					{if !$FIELD_MODEL || ($FIELD_MODEL && !$FIELD_MODEL->isViewableInDetailView())}
						{continue}
					{/if}
					{assign var=CONVERT value=false}
					{assign var=VALUE value={include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}}
					<div class="form-group">
						<label class="col-sm-4 control-label">{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)} 
							{if in_array($FIELD_MODEL->get('uitype'),['300','19']) && $VALUE}
								<a href="#" class="helpInfoPopover" title="{vtranslate('LBL_PREVIEW',$MODULE_NAME)}" data-placement="auto right" data-content="{htmlspecialchars($VALUE)}"> <span title="{vtranslate('LBL_PREVIEW',$MODULE_NAME)}" class="glyphicon glyphicon-modal-window"></span> </a>
								{assign var=CONVERT value=true}
							{/if}
							:</label>
						<div class="col-sm-8 textOverflowEllipsis {if $CONVERT}convert{/if}">
							{$VALUE}
						</div>
					</div>
				{/foreach}
				{if $HIERARCHY_ID}
					<div class="form-group hierarchyContainer"></div>
				{/if}
			</div>
			{if $RELATED_EXISTS}
				<div class="relatedRecordsContents col-xs-7">
					<ul class="nav nav-tabs" id="myTab">
						{foreach from=$RELATED_MODULE item=REL_MODULE_NAME name=tabs}
							{assign var=REL_MODULE_NAME_LOWER value=$REL_MODULE_NAME|lower}
							<li class="{if $smarty.foreach.tabs.first}active{/if}"><a data-toggle="tab" href="#{$REL_MODULE_NAME_LOWER}">{vtranslate($REL_MODULE_NAME, $REL_MODULE_NAME)}</a></li>
							{/foreach}
					</ul>
					<div class="tab-content">
						{foreach from=$RELATED_MODULE item=REL_MODULE_NAME name=tabs}
							{assign var=REL_MODULE_NAME_LOWER value=$REL_MODULE_NAME|lower}
							<div id="{$REL_MODULE_NAME_LOWER}" class="tab-pane fade in{if $smarty.foreach.tabs.first} active{/if}">
							</div>
						{/foreach}
					</div>
					<div class="message text-center padding10 hide">
						{vtranslate('LBL_NO_RECORDS', $RECORD->getModuleName())}
					</div>
				</div>
			{/if}
		{/if}
	</div>
{/strip}
