<?php $this->view('partials/head')?>

<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <h3>Update warranty status</h3>
            <p>Update warranty status of the clients</p>
            <div class="form-group">
                <button id="update-status" class="btn btn-default">Update</button>
            </div>

            <div class="alert alert-success hidden" role="alert" id="update-result"></div>
        </div>

        <div class="col-lg-6">
            <h3>Update warranty information</h3>
            <?php if($result):?>
                
                <div class="alert alert-success alert-dismissible" role="alert">Updated entries: <?=$result['updated']?></div>
                <div class="alert alert-info alert-dismissible" role="alert">CSV entries: <?=$result['csv_entries']?></div>
                <div class="alert alert-warning alert-dismissible" role="alert">Invalid entries: <?=$result['invalid']?></div>
            
            <?php endif?>

            <p class="help-block">CSV file format (header required): 
                        <pre>
"serial_number","purchase_date","end_date"
"3X6RHPJ3P7QM","2016-06-09","2020-06-09"
"CLJW1VCQMD6N","2020-04-14","2024-04-14"
"8WSF8O4BHDNK","2019-10-18","2023-10-18"</pre>
                    </p>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?php echo getCSRF();?>">
                <div class="form-group">
                    <label for="csv">Select csv file</label>
                    <input type="file" name="csv" id="csv" accept=".csv">
                </div>
                <button type="submit" class="btn btn-default">Upload</button>
            </form>
        </div>
    </div>
</div>

<script>
    $('#update-status').click(function (e) {
          $(this).attr('disabled', true);
          var $btn = $(this);

          function done () {
            $btn.attr('disabled', false);
            $('#update-result').removeClass('hidden');
          }

          $.getJSON(appUrl + '/module/warranty/update_status', function (data) {
            done();
            $('#update-result').text('Updated: ' + data['updated'] + ' clients');
          });
    });
</script>

<?php $this->view('partials/foot'); ?>