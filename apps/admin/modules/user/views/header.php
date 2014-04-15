<link rel="stylesheet" href="/css/datatables-bootstrap3.css">
<script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/js/dataTables.bootstrap3.js"></script>
<script type="text/javascript" src="/js/dataTables.editor.bootstrap3.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	/* Table initialisation */
	$('#user-list').dataTable({
    "sDom": "<'row'<'col-6'f><'col-6'l>r>t<'row'<'col-6'i><'col-6'p>>",
    "sPaginationType": "bootstrap",
    "oLanguage": {
    "sLengthMenu": "Show _MENU_ Rows",
  

    }});

});
</script>