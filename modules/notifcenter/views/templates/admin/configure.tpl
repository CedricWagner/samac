<div class="panel">
	<div class="panel-heading">Gestion des notifications</div>
	<table class="table">
		<tr>
			<th>Date</th>
			<th>Cible</th>
			<th style="width:75%">Contenu</th>
		</tr>
		{foreach from=$notifications item=notif}
			<tr>
				<td>{$notif->date|date_format:"%d/%m/%Y %H:%M:%S"}</td>
				<td>
					{if $notif->level == 'C'}
						Collective
					{else}
						{foreach from=$notif->getTargets() item=target}
							- {$target->__customer->firstname} {$target->__customer->lastname}<br />		
						{/foreach}
					{/if}
				</td>
				<td>{$notif->content|escape:'html'}</td>
			</tr>
		{/foreach}
	</table>
</div>

<script type="text/javascript">
	$(function(){
		$('.form-group .checkbox').parents('.form-group').hide();
		$('#target-all').click(function(){
			$('.form-group .checkbox').parents('.form-group').hide();
		});
		$('#target-ind').click(function(){
			$('.form-group .checkbox').parents('.form-group').show();
		});
	});
</script>