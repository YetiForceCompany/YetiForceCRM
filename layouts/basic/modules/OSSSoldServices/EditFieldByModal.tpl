{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	{assign var=ID value=$RECORD->get('id')}
	{assign var=FIELD_DATA value=$RECORD->getFieldToEditByModal()}
	{assign var=FIELD_TO_EDIT value=$FIELD_DATA['name']}
	{assign var=BASIC_FIELD_MODEL value=Vtiger_Field_Model::getInstance($FIELD_TO_EDIT, $RECORD->getModule())}
	<input type="hidden" class="moduleBasic" id="moduleBasic" value="{$RECORD->getModuleName()}">
	<div class="modal-header">
		<div class="col-xs-10">
			<h3 class="modal-title">{vtranslate('LBL_CHANGE_VALUE_FOR_FIELD', $MODULE_NAME)}</h3>
		</div>
		<div class="pull-right btn-group">
			{if $RECORD->isEditable()}
				<a href="{$RECORD->getEditViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"><span class="glyphicon glyphicon-pencil summaryViewEdit"></span></a>
			{/if}
			{if $RECORD->isViewable()}
				<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}"><span  class="glyphicon glyphicon-th-list summaryViewEdit"></span></a>
			{/if}
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="modal-body">
		{if $RECORD->isViewable()}
			<div class="form-horizontal">
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
			</div>
		{/if}
	</div>
<div class="modal-footer">
	<div class="pull-left">
		<div class="btn-toolbar">
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
	</div>
	<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE_NAME)}</button>
</div>
{/strip}
