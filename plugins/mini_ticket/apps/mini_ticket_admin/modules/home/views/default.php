<h1>Admin Ticket List</h1>
<table id="tickets" style="width:80%;">
	<thead>
		<tr><th></th><th>Status<i></i></th><th>User<i></i></th><th>Content<i></i></th><th>Updated<i></i></th><th>Comments<i></i></th></tr>
	</thead>
	<tbody>
		<?php foreach($tickets as $ticket): ?>
		<tr>
			<td><img src="<?= Mini_Ticket::getGravatarImage($ticket['status'], 30); ?>"  alt="" style="margin-right: 5px;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;"/></td>
			<td><?= $ticket['status']; ?></td>
			<td><?= $ticket['first_name']; ?> <?= substr($ticket['last_name'], 0, 1); ?>.</td>
			<td><a href="ticket.php?id=<?= $ticket['id']; ?>"><?= $ticket['subject']; ?></a> - <?= substr($ticket['message'], 0, 200); ?></td>
			<td><?= date("M d", strtotime($ticket['updated_at'])); ?></td>
			<td><a href="ticket.php?id=<?= $ticket['id']; ?>"><i class="icon-comment"></i> <?= $ticket['count']; ?></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>