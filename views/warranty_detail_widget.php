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
	$.getJSON( appUrl + '/module/warranty/report/' + serialNumber, function( data ) {

		// Check if est mfg date is more than 4 years ago and status is blanks, warranty has expired if it is
		if (data.est_mfg_date && data.est_mfg_date != "" ){

			var warranty_mfg_purchase_date = data.est_mfg_date;
			$('.mr-manufacture_date').text(data.est_mfg_date);

		// If mfg date is blank, use purchase date
		} else if (data.purchase_date && data.purchase_date != "" && data.purchase_date != "Unknown"){
			var warranty_mfg_purchase_date = data.purchase_date;

			// Get estimate_manufactured_date using dynamic date
			$.getJSON( appUrl + '/module/warranty/estimate_manufactured_date/' + serialNumber, function( data ) {
				// Make sure we have a valid date
				if (data.date != "1970-01-01"){
					$('.mr-manufacture_date').text(data.date);
				}
			});

		} else {
			var warranty_mfg_purchase_date = false;

			// Get estimate_manufactured_date using dynamic date
			$.getJSON( appUrl + '/module/warranty/estimate_manufactured_date/' + serialNumber, function( data ) {
				// Make sure we have a valid date
				if (data.date != "Unknown" && data.date != "1970-01-01"){
					$('.mr-manufacture_date').text(data.date);
				}
			});
		}

		// Check if est mfg date is more than 4 years ago and status is blanks, warranty has expired if it is
		if (warranty_mfg_purchase_date){
			var purchase_date = new Date(data.purchase_date).getTime();
			var current_date = new Date();
			var four_years_ago = current_date.setFullYear(current_date.getFullYear() - 4);

			if ((purchase_date < four_years_ago) && (data.status == null || data.status == "")){
				data.status = "Expired";
			}
		}

		// Don't show Unknown or matching mfg date
		if (data.purchase_date === data.est_mfg_date){
			$('.mr-purchase_date').text("");
		} else if (data.purchase_date != "Unknown"){
			$('.mr-purchase_date').text(data.purchase_date);
		}

		// Warranty status
		var cls = 'text-danger',
		msg = data.status
		switch (data.status) {
			case 'Supported':
				cls = 'text-success';
				msg = i18n.t("warranty.supported_until", {date:data.end_date});
				break;
			case 'AppleCare':
				cls = 'text-success';
				msg = i18n.t("warranty.supported_with_applecare", {date:data.end_date});
				break;
			case 'Limited Warranty':
				cls = 'text-success';
				msg = i18n.t("warranty.supported_no_applecare", {date:data.end_date});
				break;
			case 'No Applecare':
				cls = 'text-warning';
				msg = i18n.t("warranty.supported_no_applecare", {date:data.end_date});
				break;
			case 'Unregistered serialnumber':
				cls = 'text-warning';
				msg = i18n.t("warranty.unregistered");
				break;
			case 'Virtual Machine':
				cls = 'text-default';
				msg = i18n.t("warranty.virtual_machine");
				break;
			case 'Expired':
				cls = 'text-danger';
				if(data.end_date == null){
					msg = i18n.t("warranty.expired_listing");
				} else {
					msg = i18n.t("warranty.expired", {date:data.end_date});
				}
				break;
			default:
				msg = data.status;
		}
		$('.mr-warranty_status').addClass(cls).text(msg);


	});
});
</script>
