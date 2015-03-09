{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<style>
.badge1 {
   position:relative;
}
.badge1[data-badge]:after {
   content:attr(data-badge);
   position:absolute;
   top:-5px;
   font-size:.9em;
   background:green;
   color:white;
   width:25px;height:25px;
   text-align:center;
   line-height:25px;
   border-radius:50%;
   box-shadow:0 0 1px #333;
}
</style>
{strip}
	<div class="contentsDiv" >
		<div id="paymentsIn" style="display:none">{$JSON}</div>
		<div style="padding:20px">	  
        <h3 class="span8 ">{vtranslate('Summary', $MODULENAME)}	</h3>
		</div>
		<div class="container" style="margin-top:20px">
				<div class="row " style="padding:20px" >
					<div  style="text-align:center">
						<b>{vtranslate('Liczba transakcji:', $MODULENAME)}</b>&nbsp&nbsp&nbsp
					
						<span class="badge1" data-badge="{$COUNT}"></span>
					</div>
				</div>
		{for $FREQUENCY = 0 to 1}
			{if $FREQUENCY lt $COUNT}
				<div class="row well" >
					<div class="span12 " >
						<div style="padding-bottom:10px">
							<span class="label label-info" >
								{vtranslate('Import', $MODULENAME)} {$FREQUENCY+1}
							</span>
						</div>
							{if $PAYMENTSIN[$FREQUENCY].details.contName neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px">
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Kontrahent', $MODULENAME)}</div>
									</div>
									<div class="span8 " style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.contName}
									</div>
								</div>	
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].details.countAddress neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px">
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Kontrahent address', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.countAddress}
									</div>
								</div>	
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].amount neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px" >
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Amount', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].amount}
									</div>
								</div>	
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].details.currancy neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px" >
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Currancy operation', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.currancy}
									</div>
								</div>	
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].details.currancyAmount neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px" >
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Amount operation', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.currancyAmount}
									</div>
								</div>	
							{/if}
							{*
							{if $PAYMENTSIN[$FREQUENCY].third_letter_currency_code neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px">	
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Currency', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].third_letter_currency_code}
									</div>
								</div>
							{/if}
							*}
							{if $PAYMENTSIN[$FREQUENCY].details.contAccount neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px">	
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Account', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.contAccount}
									</div>
								</div>
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].date neq ''}		
								<div class="row-fluid" style="padding:5px; padding-left:20px">	
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Date', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].date}
									</div>
								</div>
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].accountDate neq ''}		
								<div class="row-fluid" style="padding:5px; padding-left:20px">	
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('DateK', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].accountDate}
									</div>
								</div>
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].details.dateLoad neq ''}		
								<div class="row-fluid" style="padding:5px; padding-left:20px">	
									<div class="span2 " >
										<div style="padding-top:1px;">{vtranslate('DateLoad', $MODULENAME)}</div>
									</div>
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.dateLoad}
									</div>
								</div>
							{/if}
							{if $PAYMENTSIN[$FREQUENCY].details.title neq ''}	
								<div class="row-fluid" style="padding:5px; padding-left:20px">
									<div class="span2 label" >
										<div style="padding-top:1px;">{vtranslate('Title', $MODULENAME)}</div>
									</div> 
									<div class="span8" style="padding-top:2px;">
										{$PAYMENTSIN[$FREQUENCY].details.title}
									</div>
								</div>	
							{/if}
					</div>
				</div>	
			{/if}
		{/for}
		{*
			{if $COUNT gt '2'}
				<div class="row" >
					<div class="span12" style="padding:2px; text-align:center"><b>.</b></div></div>		
				<div class="row" >
					<div class="span12" style="padding:2px; text-align:center"><b>.</b></div></div>	
				<div class="row" >
					<div class="span12" style="padding:2px; text-align:center"><b>.</b></div></div>
			{/if}
		*}
		<div class="pull-right" >
						<button class="btn addButton" id="createRecordButton" onclick="generateRecords();" data-dismiss="modal" aria-hidden="true" >{vtranslate('Create records', $MODULENAME)}</button>
						<a href="index.php?module=PaymentsIn&view=List" id="go" class="btn addButton hide">{vtranslate('Go to Payments', $MODULENAME)}</a>&nbsp
                        <a href="index.php?module=PaymentsIn&view=PaymentsImport#" class="btn">{vtranslate('Back', $MODULENAME)}</a>
        </div>
    </div>
</div>
{/strip}

{literal}
<script>
function generateRecords() {
	var area = jQuery('.contentsDiv').html();
 var paymentsIn= jQuery('#paymentsIn').text();

/* var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					var progressIndicatorElement = jQuery.progressIndicator({
						'message' : deleteMessage,
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					})
					*/
//console.log(jsonTab);  
//
	//var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : true}});
	//var element = jQuery('<div></div>');
//	var detailContainer = jQuery('div.contentsDiv');
	/*element.progressIndicator({
		'position':'html',
		'blockInfo' : {
			 'enabled' : true,
			 'elementToBlock' : detailContainer
		}
	});
//*/

            var params = {};
            params.async = false;
            params.dataType = 'json';
            params.data = { 
                'module' : "PaymentsIn",
                'action' : "GenerateRecords",
				'paymentsIn' : paymentsIn
			}
    //var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html','blockInfo' : {'enabled' : false}});

	
	
    AppConnector.request(params).then(
//
        function(data) {
		//	progressIndicatorElement.progressIndicator({'mode' : 'hide'	}),
		//	jQuery('.contentsDiv').html(area);
            var result = data.result;
            
            if ( result.success === true ) {
                var parametry = {
                    text: result.return,
                    type: 'success'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
				jQuery('#createRecordButton').hide();
				jQuery('#go').show();
            }
            else {
                var parametry = {
                    text: result.return,
                    type: 'error'
                };
                Vtiger_Helper_Js.showPnotify(parametry);
            }
        },
        function(data,err){
            var parametry = {
                text: app.vtranslate('JS_ERROR_CONNECTING'),
                type: 'error'
            };
            Vtiger_Helper_Js.showPnotify(parametry);
        }
    );
	//

	/*	var params = {};
		params['module'] = 'PaymentsIn';
		params['view'] = 'step1';

		AppConnector.request(params).then(
			function(data) {
		jQuery('.contentsDiv').html(data);
			}
		);*/

    //  progressIndicatorElement.progressIndicator({'mode': 'hide'});  
  //
    return false;
}

</script>
{/literal}
