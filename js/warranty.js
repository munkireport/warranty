warrantyStatusFormat = function(col, row){
    // Format OS Version
    var cell = $('td:eq('+col+')', row),
        status = cell.text();
        cls = status == 'Expired' ? 'danger' : (status == 'Supported' ? 'success' : 'warning');
    cell.html(mr.label(status, cls))
}