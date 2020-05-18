<?php

use munkireport\models\MRModel as Eloquent;

class Warranty_model extends Eloquent
{
    protected $table = 'warranty';

    protected $fillable = [
      'serial_number',
      'purchase_date',
      'end_date',
      'status',
    ];
}
