<div class="panel">
	<div class="panel-heading">Liste des dernières synchronisations</div>
	<table class="table">
		<tr>
			<th>État</th>
			<th>Date</th>
			<th>Produits créés</th>
			<th>Produits mis à jours</th>
			<th>Contacts créés</th>
			<th>Contacts mis à jours</th>
		</tr>
		{foreach from=$lastSyncs item=sync}
			<tr>
				<td>
					{if $sync->state == 'DONE'}
						<span class="label label-success">Succès</span>
					{elseif $sync->state == 'FAIL'}
						<span class="label label-danger">Erreur</span>
					{elseif $sync->state == 'PEND'}
						<span class="label label-warning">En cours</span>
					{else}
						<span class="label label-default">Non défini</span>
					{/if}
				</td>
				<td>{$sync->date|date_format:"%d/%m/%Y %H:%M:%S"}</td>
				<td>{$sync->__prodAdd}</td>
				<td>{$sync->__prodEdit}</td>
				<td>{$sync->__custAdd}</td>
				<td>{$sync->__custEdit}</td>
			</tr>
		{/foreach}
	</table>
</div>