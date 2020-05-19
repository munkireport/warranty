<div class="col-lg-4">
    <h4><i class="fa fa-umbrella"></i> <span data-i18n="warranty.warranty"></span></h4>
    <table>
        <tr>
            <th data-i18n="warranty.coverage"></th><td class="mr-warranty_status"></td>
        </tr>
        <tr>
            <th data-i18n="warranty.est_manufacture_date"></th><td class="mr-manufacture_date"></td>
        </tr>
        <tr>
            <th data-i18n="warranty.est_purchase_date"></th><td class="mr-purchase_date"></td>
        </tr>
    </table>
</div>

<script>
$(document).on('appReady', function(e, lang) {
    // Get estimate_manufactured_date
    $.getJSON( appUrl + '/module/warranty/estimate_manufactured_date/' + serialNumber, function( data ) {
            $('.mr-manufacture_date').text(data.date)
    });

    $.getJSON( appUrl + '/module/warranty/report/' + serialNumber, function( data ) {

        $('.mr-purchase_date').text(data.purchase_date);

    	// Warranty status
        var cls = 'text-danger',
        msg = data.status
		switch (data.status) {
			case 'Supported':
				cls = 'text-success';
				msg = i18n.t("warranty.supported_until", {date:data.end_date});
				break;
			case 'No Applecare':
				cls = 'text-warning';
				msg = i18n.t("warranty.supported_no_applecare", {date:data.end_date});
				break;
			case 'Unregistered serialnumber':
				cls = 'text-warning';
				msg = i18n.t("warranty.unregistered");
				break;
			case 'Expired':
				cls = 'text-danger';
				msg = i18n.t("warranty.expired", {date:data.end_date});
				break;
			default:
				msg = data.status;
		}


        $('.mr-warranty_status').addClass(cls).text(msg);
    });

    

});
</script>

