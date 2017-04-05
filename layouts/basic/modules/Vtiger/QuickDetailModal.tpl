{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-body col-md-12">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<div class="quickDetailContent">
			<div class="row">
				<div class="col-md-12">
					<div class="moduleIcon">
						<span class="detailViewIcon userIcon-{$MODULE_NAME}" {if $COLORLISTHANDLERS}style="background-color: {$COLORLISTHANDLERS['background']};color: {$COLORLISTHANDLERS['text']};"{/if}></span>
					</div>
					<div class="paddingLeft5px">
						<h4 class="recordLabel textOverflowEllipsis pushDown marginbottomZero" title='{$RECORD->getName()}'>
							<span class="moduleColor_{$MODULE_NAME}">{$RECORD->getName()}</span>
						</h4>
						{if $MODULE_NAME}
							<div class="paddingLeft5px">
								<span class="muted">
									{vtranslate('Assigned To',$MODULE_NAME)}: {$RECORD->getDisplayValue('assigned_user_id')}
									{assign var=SHOWNERS value=$RECORD->getDisplayValue('shownerid')}
									{if $SHOWNERS != ''}
										<br/>{vtranslate('Share with users',$MODULE_NAME)} {$SHOWNERS}
									{/if}
								</span>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					{foreach key=key item=WIDGET from=$WIDGETS}
						<div class="quickDetailWidget">
							{if $WIDGET['title']}
								<h4>{$WIDGET['title']}</h4>
							{/if}
							<div>{$WIDGET['content']}</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}
