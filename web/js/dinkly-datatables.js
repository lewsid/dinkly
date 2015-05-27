$(document).ready(function() {
    $('.dinkly-datatable').DataTable();
});

$.extend( true, $.fn.dataTable.defaults, {
    "Dom": "<'row'<'col-6'><'col-6'l><'pull-right' f>r>t<'row'<'col-6'i><'col-6'<'pull-right' p>>>",
    "PaginationType": "bootstrap",
    "Language": {
        "LengthMenu": "Show _MENU_ Rows",
                "Search": ""
    },
    "ColumnDefs": [{
        "Sortable" : false,
        "Targets" : [ "no-sort" ]
    }]
});

$(function(){
    $('.dinkly-datatable').each(function(){
        var datatable = $(this);
        // SEARCH - Add the placeholder for Search and Turn this into in-line formcontrol
        var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
        search_input.attr('placeholder', 'Search')
        search_input.addClass('form-control input-sm')
        search_input.css('width', '250px')
 
        // SEARCH CLEAR - Use an Icon
        var clear_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] a');
        clear_input.html('<i class="icon-remove-circle icon-large"></i>')
        clear_input.css('margin-left', '5px')
 
        // LENGTH - Inline-Form control
        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
        length_sel.addClass('form-control input-sm')
        length_sel.css('width', '75px')
 
        // LENGTH - Info adjust location
        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_info]');
        length_sel.css('margin-top', '18px')
    });
});