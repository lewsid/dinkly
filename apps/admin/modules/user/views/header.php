<link rel="stylesheet" href="/css/datatables-bootstrap.css">
<script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/js/dataTables.bootstrap.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	/* Table initialisation */
	$('#user-list').dataTable();

});
$.extend( true, $.fn.dataTable.defaults, {
    "sDom": "<'row'<'col-6'><'col-6'l><'pull-right' f>r>t<'row'<'col-6'i><'col-6'<'pull-right' p>>>",
    "sPaginationType": "bootstrap",
    "oLanguage": {
        "sLengthMenu": "Show _MENU_ Rows",
                "sSearch": ""
    }
} );
$(function(){
    $('#user-list').each(function(){
        var datatable = $(this);
        // SEARCH - Add the placeholder for Search and Turn this into in-line formcontrol
        var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
        search_input.attr('placeholder', 'Search')
        search_input.addClass('form-control input-small')
        search_input.css('width', '250px')

        
        
       // search_input.css('position', 'absolute')
        //search_input.css('right', '100px')
 
        // SEARCH CLEAR - Use an Icon
        var clear_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] a');
        clear_input.html('<i class="icon-remove-circle icon-large"></i>')
        clear_input.css('margin-left', '5px')

 
        // LENGTH - Inline-Form control
        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
        length_sel.addClass('form-control input-small')
        length_sel.css('width', '75px')
 
        // LENGTH - Info adjust location
        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_info]');
        length_sel.css('margin-top', '18px')
    });


});
</script>