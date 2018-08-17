{strip}
	<div class="row">
		<div class="col-lg-12">&nbsp;</div>
	</div>
	<div class="row">
		<div class="container">
			<div class="jumbotron">
				<h1>{\App\Language::Translate('LBL_MODULE_HEADER',$QUALIFIED_MODULE)}</h1>
				<p>{\App\Language::Translate('LBL_MODULE_DESC',$QUALIFIED_MODULE)}</p>
			</div>
		</div>
	</div>
	<div class="container YetiForceStatusContainer">
		<div class="row">
			<div class="col-lg-2">{\App\Language::Translate('LBL_SERVICE_URL',$QUALIFIED_MODULE)}:</div>
			<div class="col-lg-10">
				<input type="text" class="YetiForceStatusUrlInput" value="{$YF_URL}" size="100%"/>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">&nbsp;</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<table class="table table-hover table-bordered table-striped table-condensed">
					<thead>
					<tr>
						<th>{\App\Language::Translate('LBL_PARAM_NAME',$QUALIFIED_MODULE)}</th>
						<th>{\App\Language::Translate('LBL_PARAM_VAL',$QUALIFIED_MODULE)}</th>
					</tr>
					</thead>
					<tbody>
					{foreach $CURRENT_STATE as $CONF_FLAG}
						<tr>
							<td>{\App\Language::translate($CONF_FLAG['label'],$QUALIFIED_MODULE)}</td>
							<td align="right">
								{if $CONF_FLAG['type'] === 'bool'}
									<div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-secondary{if $CONF_FLAG['value']} active{/if}">
											<input type="radio" name="YF_status_flag[{$CONF_FLAG['name']}]"
												   id="YF_status_flag_{$CONF_FLAG['name']}"
												   class="YetiForceStatusFlagBool"
												   data-flag="{$CONF_FLAG['name']}"
												   autocomplete="off" value="1"{if $CONF_FLAG['value']} checked{/if}>
											{\App\Language::Translate('LBL_PARAM_ENABLED',$QUALIFIED_MODULE)}
										</label>
										<label class="btn btn-secondary{if !$CONF_FLAG['value']} active{/if}">
											<input type="radio" name="YF_status_flag[{$CONF_FLAG['name']}]" value="0"
												   id="YF_status_flag_{$CONF_FLAG['name']}"
												   class="YetiForceStatusFlagBool"
												   data-flag="{$CONF_FLAG['name']}"
												   autocomplete="off"{if !$CONF_FLAG['value']} checked{/if}> {\App\Language::Translate('LBL_PARAM_DISABLED',$QUALIFIED_MODULE)}
										</label>
									</div>
								{else}
									<input type="text" value="{$CONF_FLAG['value']}"/>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}