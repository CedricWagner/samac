{if $order_ddw.ddw_order_date neq ""}
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-truck"></i> Delivery Dates Wizard
		</div>
		<strong>Delivery Date:</strong> {$order_ddw.ddw_order_date|escape:'htmlall':'UTF-8'}<br>
		<strong>Time Slot:</strong> {$order_ddw.ddw_order_time|escape:'htmlall':'UTF-8'}
	</div>
{/if}