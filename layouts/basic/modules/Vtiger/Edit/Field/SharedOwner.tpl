{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-SharedOwner -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getUIType() eq '120'}
		{assign var=OWNER_FIELD value=\App\Fields\Owner::getInstance($MODULE_NAME)}
		{assign var=ALL_ACTIVEUSER_LIST value=$OWNER_FIELD->getAccessibleUsers('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=$OWNER_FIELD->getAccessibleGroups('',$FIELD_MODEL->getFieldDataType())}
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		{assign var=SHOW_FAVORITE_OWNERS value=App\Config::module('Users','FAVORITE_OWNERS') && $CURRENT_USER_ID === \App\User::getCurrentUserRealId()}
		{if $FIELD_VALUE neq '' }
			{assign var=FIELD_VALUE value=vtlib\Functions::getArrayFromValue($FIELD_VALUE)}
			{assign var=NOT_DISPLAY_LIST value=array_diff_key(array_flip($FIELD_VALUE), $ALL_ACTIVEUSER_LIST, $ALL_ACTIVEGROUP_LIST)}
		{else}
			{assign var=NOT_DISPLAY_LIST value=[]}
			{assign var=FIELD_VALUE value=[]}
		{/if}
		{function OPTGRUOP BLOCK_NAME='' OWNERS=[] ACTIVE='inactive'}
			{if $OWNERS}
				<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$OWNERS}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}"
							{foreach item=ELEMENT from=$FIELD_VALUE}
								{if $ELEMENT eq $OWNER_ID } selected {/if}
							{/foreach}
							data-userId="{$CURRENT_USER_ID}"
							{if $SHOW_FAVORITE_OWNERS}
								data-url="index.php?module={$MODULE_NAME}&action=Fields&mode=changeFavoriteOwner&fieldName={$FIELD_NAME}&owner={$OWNER_ID}" data-icon-active="fas fa-star" data-icon-inactive="far fa-star"
								data-state="{$ACTIVE}" data-template="<span class='c-option-template--state-icons'>{$OWNER_NAME}<span class='js-select-option-actions o-filter-actions noWrap float-right'><span data-js='click|class:icons' class='mx-1 js-select-option-event{if $ACTIVE == 'active'} fas fa-star{else} far fa-star{/if}'></span></span></span>" {/if}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/function}
			<div>
				<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="" />
				<select class="select2 form-control {if !empty($NOT_DISPLAY_LIST)}hideSelected{/if} {$FIELD_NAME}"
					title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" tabindex="{$FIELD_MODEL->getTabIndex()}"
					data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					data-name="{$FIELD_NAME}" name="{$FIELD_NAME}[]" data-fieldinfo='{$FIELD_INFO}'
					multiple="multiple" {if !empty($SPECIAL_VALIDATOR)} data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if}
					{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
						data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$FIELD_NAME}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
					{elseif App\Config::module('Users','FAVORITE_OWNERS')}
						data-select-cb="registerIconsEvents" data-template-result="prependDataTemplate" data-template-selection="prependDataTemplate"
					{/if}>
					{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
						{foreach item=USER from=$FIELD_VALUE}
							{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($USER)}
							<option value="{$USER}" data-picklistvalue="{$OWNER_NAME}" selected="selected">
								{\App\Purifier::encodeHtml($OWNER_NAME)}
							</option>
						{/foreach}
					{else}
						{if App\Config::module('Users','FAVORITE_OWNERS')}
							{assign var=FAVORITE_OWNERS value=$OWNER_FIELD->getFavorites($FIELD_MODEL->getFieldDataType())}
							{if $FAVORITE_OWNERS}
								{assign var=FAVORITE_OWNERS value=array_intersect_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS) + array_intersect_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
								{assign var=ALL_ACTIVEUSER_LIST value=array_diff_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS)}
								{assign var=ALL_ACTIVEGROUP_LIST value=array_diff_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
								{OPTGRUOP BLOCK_NAME='LBL_FAVORITE_OWNERS' OWNERS=$FAVORITE_OWNERS ACTIVE='active'}
							{/if}
						{/if}
						{OPTGRUOP BLOCK_NAME='LBL_USERS' OWNERS=$ALL_ACTIVEUSER_LIST}
						{OPTGRUOP BLOCK_NAME='LBL_GROUPS' OWNERS=$ALL_ACTIVEGROUP_LIST}
						{if !empty($NOT_DISPLAY_LIST)}
							{foreach from=$NOT_DISPLAY_LIST key=OWNER_ID item=OWNER_NAME}
								<option value="{$OWNER_ID}"
									{if in_array(\App\Purifier::encodeHtml($OWNER_NAME), $FIELD_VALUE)}selected="selected" {/if}
									disabled="disabled" class="d-none">{\App\Purifier::encodeHtml($OWNER_NAME)}</option>
							{/foreach}
						{/if}
					{/if}
				</select>
			</div>
		{/if}
		<!-- /tpl-Base-Edit-Field-SharedOwner -->
	{/strip}
