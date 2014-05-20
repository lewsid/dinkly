<?php if($all_permissions != array()): ?>
	<table cellpadding="0" cellspacing="0" border="0"  class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody id="permissions-table-rows">
			<?php foreach($all_permissions as $pos => $perm): ?>
				<tr class="<?php echo ($pos % 2 == 0) ? 'odd' : 'even'; ?>">
					<td><?php echo $perm->getName(); ?></td>
					<td><?php echo $perm->getDescription(); ?></td>
					<td>
						<button type="button" data-toggle="modal" data-id="<?php echo $perm->getId(); ?>" class="btn btn-link btn-delete-permission">delete</button>
						<!-- <a hrefclass="delete-permission" data-id="<?php echo $perm->getId(); ?>">delete</a></td> -->
					</td>
				</tr> 
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	There are no permissions to display
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {
	$('.btn-delete-permission').click(function() {
		$tr = $(this).parents('tr');
		$id = $(this).attr('data-id');

		$.ajax({
			type: "POST",
			url: "/admin/group/delete_permission/",
			data: { 
				permission_id: $id
			},
			success: function(response) {
				if(response == 'success') {
					$tr.fadeOut('slow', function() {
						$(this).remove();
					});
				}
			}
		});
	});
});
</script>