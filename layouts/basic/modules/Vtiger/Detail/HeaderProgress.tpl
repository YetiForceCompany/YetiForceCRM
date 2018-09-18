{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if isset($FIELDS_HEADER['progress'])}
		{foreach from=$FIELDS_HEADER['progress'] key=NAME item=FIELD_MODEL}
			{if !$RECORD->isEmpty($NAME)}
				{\App\DebugerEx::log(Users_Record_Model::getCurrentUserModel()->get('roleid'))}
				{assign var=PICKLIST_VALUES value=\App\Fields\Picklist::getValues($NAME)}
				{assign var=PICKLIST_VALUES_BY_ROLE value=\App\Fields\Picklist::getRoleBasedPicklistValues($NAME, Users_Record_Model::getCurrentUserModel()->get('roleid'))}
				<div class="tpl-Base-Detail-HeaderProgress c-arrows px-3 w-100">
					<ul class="c-arrows__container js-header-progress-bar" data-picklist-name="{$NAME}"
						data-js="container">
						{assign var=ARROW_CLASS value="before"}
						{foreach from=$PICKLIST_VALUES item=VALUE_DATA name=picklistValues}
							<li
									class="c-arrows__item{if $smarty.foreach.picklistValues.first}first{/if} {if $VALUE_DATA['picklistValue'] eq $RECORD->get($NAME)}active{assign var=ARROW_CLASS value="after"}{else}{$ARROW_CLASS}{/if}"
									data-picklist-value="{$VALUE_DATA['picklistValue']}"
									data-js="confirm|click"
									data-access="{if in_array($VALUE_DATA['picklistValue'], $PICKLIST_VALUES_BY_ROLE)}1{else}0{/if}">
								<a class="c-arrows__link">
									<span class="c-arrows__text">{$FIELD_MODEL->getDisplayValue($VALUE_DATA['picklistValue'], false, false, true)}</span>
									{if !empty($VALUE_DATA['description'])}
										<span class="c-arrows__text ml-1 u-mr-minus-8px js-popover-tooltip"
											  data-js="popover"
											  data-trigger="hover focus"
											  data-content="{\App\Purifier::encodeHtml($VALUE_DATA['description'])}">
											<span class="fas fa-info-circle"></span>
										</span>
									{/if}
								</a>
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		{/foreach}
	{/if}
{/strip}
