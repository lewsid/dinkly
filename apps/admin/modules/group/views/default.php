<h3>Groups <button type="button" class="btn btn-primary btn-create-group pull-right">Create Group</button></h3>
<hr>

<table cellpadding="0" cellspacing="0" border="0"  class="table table-striped table-bordered dinkly-datatable" id="user-list">
  <thead>
    <tr>
      <th>Name</th>
      <th>Abbreviation</th>
      <th>Members</th>
      <th class="no-sort">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($groups as $pos => $group): ?>
    <tr class="<?php echo ($pos % 2 == 0) ? 'odd' : 'even'; ?>">
      <td><?php echo $group->getName(); ?></td>
      <td><?php echo $group->getAbbreviation(); ?></td>
      <td><?php echo $group->getMemberCount(); ?></td>
      <td><a href="/admin/group/detail/id/<?php echo $group->getId(); ?>">view</a>
    </tr> 
    <?php endforeach; ?>
  </tbody>
</table>

<script type="text/javascript">
$(document).ready(function() {
  $('.btn-create-group').click(function() { 
    window.location = "/admin/group/new/";
    return true;
  });
}); 
</script>