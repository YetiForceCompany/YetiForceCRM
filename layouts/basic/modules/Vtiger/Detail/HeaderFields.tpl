{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	{if $FIELDS_HEADER || $CUSTOM_FIELDS_HEADER}
		<div class="tpl-Detail-HeaderFields ml-md-2 pr-md-2 u-min-w-md-30 w-100">
			{if $CUSTOM_FIELDS_HEADER}
				{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
					<div class="badge badge-info d-flex flex-nowrap align-items-center justify-content-center my-1 js-popover-tooltip" data-ellipsis="true"
						 data-content="{$ROW['title']} {$ROW['badge']}" data-toggle="popover" data-js="tooltip"
						 {if $ROW['action']}onclick="{\App\Purifier::encodeHtml($ROW['action'])}"{/if}
					>
						<div class="js-popover-text">
							<span class="mr-1">{$ROW['title']}</span>
							{$ROW['badge']}
						</div>
						<span class="fas fa-info-circle fa-sm js-popover-icon d-none"></span>
					</div>
				{/foreach}
			{/if}
			{if $FIELDS_HEADER}
				{foreach from=$FIELDS_HEADER key=LABEL item=VALUE}
					{if !empty($VALUE['value'])}
						<div class="badge badge-info d-flex flex-nowrap align-items-center justify-content-center mt-1 js-popover-tooltip" data-ellipsis="true"
							 data-content="{$VALUE['value']}" data-toggle="popover" data-js="tooltip">
							<div class="js-popover-text">
								<span class="mr-1">
									{\App\Language::translate($LABEL, $MODULE_NAME)}:
								</span>
								{$VALUE['value']}
							</div>
							<span class="fas fa-info-circle fa-sm js-popover-icon d-none"></span>
						</div>
					{/if}
				{/foreach}
			{/if}
		</div>
	{/if}
{/strip}
