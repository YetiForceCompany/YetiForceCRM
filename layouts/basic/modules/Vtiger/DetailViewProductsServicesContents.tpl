{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{* Summary View Products Widget*}
	<div class="tpl-DetailViewProductsServicesContents">
		{if isset($RELATIONS['Products'])}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_products hideActionImages"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_PRODUCTS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Products" />
							<div class="col-10 col-sm-10 col-md-9 margin0px">
								<h5
									class="mb-0">{\App\Language::translate('Interested products',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									{if !$RELATIONS['Products']->isTreeRelation()}
										{assign var=VIEW value='RecordsList'}
									{else}
										{assign var=VIEW value='TreeCategoryModal'}
									{/if}
									<button class="btn btn-light {if !$RELATIONS['Products']->isTreeRelation()}js-widget-products-services{else}showModal{/if}" type="button"
										data-modalid="ProductsModal"
										data-url="index.php?module=Products&view={$VIEW}&src_module={$MODULE_NAME}&src_record={$RECORDID}&multi_select=true">
										<span class="fas fa-search-plus"
											title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('Products',$MODULE_NAME)}</h6>
									</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS['Products']->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedTree"
						data-name="LBL_RELATED_PRODUCTS">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
									<tr>
										<th class="py-0 border-0">
											<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES', $MODULE_NAME)}</h6>
										</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							data-js="container|value">
							<input type="hidden" name="relatedModule" value="Products" />
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OutsourcedProducts Widget*}
		{if isset($RELATIONS['OutsourcedProducts'])}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_assets"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_OP">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
							<div class="col-10 col-sm-10 col-md-9 margin0px">
								<h5
									class="mb-0">{\App\Language::translate('LBL_RELATED_OP',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									{if !$RELATIONS['OutsourcedProducts']->isTreeRelation()}
										{assign var=VIEW value='RecordsList'}
									{else}
										{assign var=VIEW value='TreeCategoryModal'}
									{/if}
									<button class="btn btn-light {if !$RELATIONS['OutsourcedProducts']->isTreeRelation()}js-widget-products-services{else}showModal{/if}" type="button"
										data-modalid="OutsourcedProductsModal" data-module="OutsourcedProducts"
										data-url="index.php?module=OutsourcedProducts&view={$VIEW}&src_module={$MODULE_NAME}&src_record={$RECORDID}&multi_select=true">
										<span class="fas fa-search-plus"
											title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('Products',$MODULE_NAME)}</h6>
									</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS['OutsourcedProducts']->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedTree"
						data-name="LBL_RELATED_OP">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
									<tr>
										<th class="py-0 border-0">
											<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
										</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							data-js="container|value">
							<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View Assets Widget*}
		{if isset($RELATIONS['Assets'])}
			{assign var=REL_RELATION value=$RELATIONS['Assets']}
			{assign var=CREATE_RECORD_URL value=$REL_RELATION->getCreateViewUrl()}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_assets2"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_ASSETS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Assets" />
							<div class="col-10 col-sm-10 col-md-9 margin0px">
								<h5
									class="mb-0">{\App\Language::translate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								{if {\App\Privilege::isPermitted('Assets', 'CreateView')} }
									<span class="float-right">
										<button class="btn btn-light {if false !== strpos($CREATE_RECORD_URL, 'view=QuickCreateAjax')}createRecordFromFilter{else}createInventoryRecordFromFilter{/if}" type="button" data-url="{$CREATE_RECORD_URL}">
											<span class="fas fa-plus-circle" title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
										</button>
									</span>
								{/if}
							</div>
						</div>
						<hr class="widgetHr">
					</div>

					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
			</div>
		{/if}
		{* Summary View Services Widget Ends Here*}
		{if isset($RELATIONS['Services'])}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_service hideActionImages"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_SERVICES">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Services" />
							<div class="col-10 col-sm-10 col-md-9 margin0px">
								<h5
									class="mb-0">{\App\Language::translate('Interested services',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<span class="float-right">
									{if !$RELATIONS['Services']->isTreeRelation()}
										{assign var=VIEW value='RecordsList'}
									{else}
										{assign var=VIEW value='TreeCategoryModal'}
									{/if}
									<button class="btn btn-light {if !$RELATIONS['Services']->isTreeRelation()}js-widget-products-services{else}showModal{/if}" type="button" data-modalid="ServicesModal"
										data-url="index.php?module=Services&view={$VIEW}&src_module={$MODULE_NAME}&src_record={$RECORDID}&multi_select=true">
										<span class="fas fa-search-plus"
											title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</span>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('Services',$MODULE_NAME)}</h6>
									</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS['Services']->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedTree"
						data-name="LBL_RELATED_SERVICES">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
									<tr>
										<th class="py-0 border-0">
											<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
										</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							data-js="container|value">
							<input type="hidden" name="relatedModule" value="Services" />
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OSSOutsourcedServices Widget Start Here*}
		{if isset($RELATIONS['OSSOutsourcedServices'])}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_service"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_OSSOS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
							<div class="col-10 col-sm-10 col-md-9 margin0px">
								<h5
									class="mb-0">{\App\Language::translate('LBL_RELATED_OSSOS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									{if !$RELATIONS['OSSOutsourcedServices']->isTreeRelation()}
										{assign var=VIEW value='RecordsList'}
									{else}
										{assign var=VIEW value='TreeCategoryModal'}
									{/if}
									<button class="btn btn-light {if !$RELATIONS['OSSOutsourcedServices']->isTreeRelation()}js-widget-products-services{else}showModal{/if}" type="button"
										data-modalid="OSSOutsourcedServicesModal"
										data-module="OSSOutsourcedServices"
										data-url="index.php?module=OSSOutsourcedServices&view={$VIEW}&src_module={$MODULE_NAME}&src_record={$RECORDID}&multi_select=true">
										<span class="fas fa-search-plus"
											title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('Services',$MODULE_NAME)}</h6>
									</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS['OSSOutsourcedServices']->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedTree"
						data-name="LBL_RELATED_OSSOS">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
									<tr>
										<th class="py-0 border-0">
											<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
										</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							data-js="container|value">
							<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{if isset($RELATIONS['OSSSoldServices'])}
			{assign var=REL_RELATION value=$RELATIONS['OSSSoldServices']}
			{assign var=CREATE_RECORD_URL value=$REL_RELATION->getCreateViewUrl()}
			<div class="c-detail-widget js-detail-widget" data-js="container">
				<div class="widgetContainer_service"
					data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					data-name="LBL_RELATED_OSSSS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OSSSoldServices" />
							<div class="col-10 col-sm-10 col-md-9">
								<h5
									class="mb-0">{\App\Language::translate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								{if {\App\Privilege::isPermitted('OSSSoldServices', 'CreateView')} }
									<span class="float-right">
										<button class="btn btn-light {if false !== strpos($CREATE_RECORD_URL, 'view=QuickCreateAjax')}createRecordFromFilter{else}createInventoryRecordFromFilter{/if}" type="button" data-url="{$CREATE_RECORD_URL}">
											<span class="fas fa-plus-circle" title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
										</button>
									</span>
								{/if}
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
