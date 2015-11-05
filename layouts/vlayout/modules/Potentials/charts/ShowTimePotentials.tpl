{if count($DATA2)}
<div style="width: 40%; margin: auto; text-align: center; float: left">
    {vtranslate('LBL_TOTAL_TIME')}<br/>
    {vtranslate('LBL_SUMMARY')}<br/>
			<canvas id="sTP" height="350" width="400"></canvas>
</div>                        
<script>
	var barChartData = {
		labels : [{foreach from=$DATA2 item=record key=key name=dataloop}"{strip_tags($record[1])}"{if !$smarty.foreach.dataloop.last},{/if}{/foreach}],                	
		datasets : [
			{
				fillColor : "rgba(220,220,220,0.5)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(220,220,220,0.75)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [{foreach from=$DATA2 item=record key=key name=dataloop2}"{$record[0]}"{if !$smarty.foreach.dataloop2.last},{/if}{/foreach}]
			},		
		]
	};
	$(document).ready(function(){
        
		var ctx = document.getElementById("sTP").getContext("2d");
		window.myBar = new Chart(ctx).Bar(barChartData);
	});
</script>
{/if}