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
            $('.mr-manufacture_date').html(data.date)
	});
});
</script>

