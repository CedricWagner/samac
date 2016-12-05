{if isset($ps_version) && ($ps_version < '1.6')}
	<br/>
	<fieldset>
	<legend><img src="../img/admin/delivery.gif">{l s='Delivery Dates Wizard' mod='deliverydateswizard'}</legend>
{else}
	<div class="col-lg-12">
	<div class="panel">
	<h3>
		<i class="icon-truck"></i>
		{l s='Delivery Dates Wizard' mod='deliverydateswizard'}
	</h3>
{/if}
<table style="width: 100%">
	<tr>
		<td width="10%">
			{l s='Delivery Date:' mod='deliverydateswizard'}
		</td>
		<td>
			<div class="input-group fixed-width-xl">
				<input id="ddw_order_date" type="text" data-hex="true" class="datepicker" name="ddw_order_date" value="{$order_ddw.ddw_order_date|escape:'htmlall':'UTF-8'}">
				<div class="input-group-addon">
					<i class="icon-calendar-o"></i>
				</div>
			</div>
		</td>
	</tr>
	<tr><td height="5"></td></tr>
	<tr>
		<td>
			{l s='Delivery Time:' mod='deliverydateswizard'}
		</td>
		<td>
			<div class="input-group fixed-width-xl">
				<input type="text" name="ddw_order_time" id="ddw_order_time" value="{$order_ddw.ddw_order_time|escape:'htmlall':'UTF-8'}" class="">
			</div>
		</td>
	</tr>
	<tr><td height="10"></td></tr>
	<tr>
		<td></td>
		<td align="left">
			<button type="submit" id="submitDDW" class="btn btn-primary pull-left" name="submitDDW">
				{l s='Update' mod='deliverydateswizard'}
			</button>
		</td>
	</tr>
</table>
{if !(isset($ps_version) && ($ps_version < '1.6'))}
	</div>
	</div>
{else}
	</fieldset>
{/if}


<script>
	$(document).ready(function() {

		baseDir = '{$base_url|escape:'htmlall':'UTF-8'}';

		$("#submitDDW").click(function() {
			$.ajax({
				type: 'POST',
				url: baseDir + 'modules/deliverydateswizard/ajax.php?action=update_ddw_order_detail&rand='+new Date().getTime(),
				cache: false,
				data: {
					ddw_order_date: $("input#ddw_order_date").val(),
					ddw_order_time: $("input#ddw_order_time").val(),
					id_order: {$smarty.get.id_order|escape:'htmlall':'UTF-8'},
				},
				success: function (jsonData) {
					alert('saved');
				}
			});
		});

	});
</script>

