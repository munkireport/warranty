warrantyStatusFormat = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();

    colvar = colvar == "Supported" ? i18n.t('supported')+'</span>' :
    colvar = colvar == "AppleCare" ? i18n.t('warranty.supported_with_applecare_listing') :
    colvar = colvar == "Limited Warranty" ? i18n.t('warranty.supported_no_applecare_listing') :
    // colvar = colvar == "Virtual Machine" ? i18n.t('warranty.virtual_machine') :
    colvar = colvar == "Expired" ? i18n.t('warranty.expired_listing') :
    colvar = colvar == "Unknown" ? "" :
    (colvar === "Can't lookup warranty" ? "" : colvar)

    // Format coverage status
    if (colvar == "Expired" || colvar == i18n.t('warranty.expired_listing')){
        col.html('<span class="label label-danger">'+colvar+'</span>');
    } else if (colvar == "Supported" || colvar == i18n.t('supported') || colvar == "AppleCare" || colvar == i18n.t('warranty.supported_with_applecare_listing') || colvar == "Limited Warranty" || colvar == i18n.t('warranty.supported_no_applecare_listing')){
        col.html('<span class="label label-success">'+colvar+'</span>');
    } else if (colvar == "Virtual Machine"){
        col.html('<span class="label label-info">Virtual Machine</span>');
    } else {
        col.text(colvar);
    }
}

var warrantyCheckStatus = function(colNumber, row){
    var cell = $('td:eq('+colNumber+')', row);
    var date = cell.text();
    var warranty_status_col = $('td:eq('+(colNumber-3)+')', row);
    var warranty_status = warranty_status_col.text();

    if (date && date != "" && date != "Unknown"){
        cell.html('<span title="'+date+'">'+moment(date).fromNow()+'</span>');

        // Check if warranty has expired
        var end_date = new Date(date).getTime();
        var current_date = new Date().getTime();

        if (warranty_status == "Virtual Machine"){
            // If Virtual Machine
            warranty_status_col.html('<span class="label label-info">'+warranty_status+'</span>');
            cell.text("");
        } else if (warranty_status == "Expired" || warranty_status == i18n.t('warranty.expired_listing')) {
            // If Expired
            warranty_status_col.html('<span class="label label-danger">'+warranty_status+'</span>');
        } else if (end_date < current_date) {
            // Format warranty status
            warranty_status_col.html('<span class="label label-danger">'+i18n.t('warranty.expired_listing')+'</span>');
        } else if (end_date >= current_date){
            // Format warranty status
            var warranty_status_col = $('td:eq('+(colNumber-3)+')', row);
            warranty_status_col.html('<span class="label label-success">'+warranty_status+'</span>');
        }

    } else {
        cell.text("");
    }
}

var warrantyPurchasedDateToMoment = function(col, row){
    var cell = $('td:eq('+col+')', row);
    var date = cell.text();
    if (date && date != "" && date != "Unknown"){
        cell.html('<span title="'+moment(date).fromNow()+'">'+date+'</span>');
        // cell.html('<span title="'+date+'">'+moment(date).fromNow()+'</span>');
    } else {
        cell.text("");
    }
}

var warrantyDateToMoment = function(col, row){
    var cell = $('td:eq('+col+')', row)
    var date = cell.text();
    var purchase_date_col = $('td:eq('+(col-1)+')', row);
    // var purchase_date = purchase_date_col.find("span").attr("title");
    var purchase_date = purchase_date_col.text();

    // If purchase date and mfg date are the same, blank purchase date
    if (date && purchase_date && purchase_date === date){
        purchase_date_col.text("");
    }

    // Check if we have a mfg date, if not check if we have a purchase date and use that
    if (date && date != "" && date != null && date != "Unknown"){
        var warranty_date = date;
        cell.html('<span title="'+date+'">'+moment(date).fromNow()+'</span>');
    } else if (purchase_date && purchase_date != "" && purchase_date != null && purchase_date != "Unknown"){
        var warranty_date = purchase_date;
        cell.text("");
    } else {
        var warranty_date = false;
        cell.text("");
    }

    // Process warranty expired from est mfg or purchase date
    if (warranty_date){

        var warranty_status_col = $('td:eq('+(col-2)+')', row);
        var warranty_status = warranty_status_col.text();

        var est_mfg_date = new Date(warranty_date).getTime();
        var current_date = new Date();
        var four_years_ago = current_date.setFullYear(current_date.getFullYear() - 4);

        // Check if est mfg date is more than 4 years ago and status is blanks, warranty has expired if it is
        if ((est_mfg_date < four_years_ago) && warranty_status != "Virtual Machine"){
            warranty_status_col.html('<span class="label label-danger">'+i18n.t('warranty.expired_listing')+'</span>');
        }
    }
}

var warranty_icloud_signed_in = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();

    colvar = colvar == "1" ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
    (colvar === "0" ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : colvar)
    col.html(colvar)
}