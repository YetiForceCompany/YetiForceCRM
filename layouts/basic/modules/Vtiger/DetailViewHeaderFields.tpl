{strip}
{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="col-xs-12 col-sm-12 col-md-4 detailViewHeaderFields">
		{if $CUSTOM_FIELDS_HEADER}
			{foreach from=$CUSTOM_FIELDS_HEADER item=ROW}
				<div class="col-xs-12 marginTB3 paddingLRZero">
					<div class="row col-lg-9 col-md-10 col-xs-12 pull-right paddingLRZero detailViewHeaderFieldsContent">
						<div class="btn btn-default {$ROW['class']} btn-xs col-xs-12" {if $ROW['action']}onclick="{Vtiger_Util_Helper::toSafeHTML($ROW['action'])}"{/if}>
							<div class="detailViewHeaderFieldsName">
								{$ROW['title']}
							</div>
							<div class="detailViewHeaderFieldsValue">
								<span class="badge">
									{$ROW['badge']}
								</span>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		{/if}
		{if $FIELDS_HEADER}
			{foreach from=$FIELDS_HEADER key=LABEL item=VALUE}
				{if !empty($VALUE['value'])}
					<div class="col-xs-12 marginTB3 paddingLRZero">
						<div class="row col-lg-9 col-md-10 col-xs-12 pull-right paddingLRZero detailViewHeaderFieldsContent">
							<div class="btn {$VALUE['class']} btn-xs col-xs-12">
								<div class="detailViewHeaderFieldsName">
									{vtranslate($LABEL, $MODULE_NAME)} 
								</div>
								<div class="detailViewHeaderFieldsValue">
									<span class="badge">
										{$VALUE['value']}
									</span>
								</div>
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		{/if}
	</div>
{/strip}
