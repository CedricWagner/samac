<div class="panel">
	<div class="panel-heading"><i class="icon-truck"></i>&nbsp;Détails de livraison</div>
	<table class="table">
		<thead>
			<tr>
				<th>Produit</th>
				<th>Quantité</th>
				<th>Date de livraison</th>
				<th>Code de tracking</th>
				<th>Bon de commande</th>
				<th>Bon de livraison</th>
				<th>Facture</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$lstOrderExtra item=orderExtra}
			<tr>
				<td>
					{$orderExtra->product->name[1]}	
				</td>
				<td>
					{$orderExtra->product_quantity}	
				</td>
				<td>
					{$orderExtra->delivery_date|date_format:"%d/%m/%Y %H:%M:%S"}	
				</td>
				<td>
					{$orderExtra->tracking_code}	
				</td>
				<td>
					<a class="btn btn-default">{$orderExtra->ws_num_order}.pdf</a>	
				</td>
				<td>
					<a class="btn btn-default">{$orderExtra->ws_num_delivery}.pdf</a>	
				</td>
				<td>
					<a class="btn btn-default">{$orderExtra->ws_num_invoice}.pdf</a>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>