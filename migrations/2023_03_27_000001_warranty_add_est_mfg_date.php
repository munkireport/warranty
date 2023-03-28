<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class WarrantyAddEstMfgDate extends Migration
{
    private $tableName = 'warranty';

    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->string('est_mfg_date')->nullable();
            $table->boolean('icloud_logged_in')->nullable();
            
            $table->index('est_mfg_date');
            $table->index('icloud_logged_in');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('est_mfg_date');           
            $table->dropColumn('icloud_logged_in');           
        });
    }
}
