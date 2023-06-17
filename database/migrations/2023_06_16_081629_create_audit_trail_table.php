<?php

/**
 * Author: Emmanuel Paul Mnzava
 * Twitter: @epmnzava
 * Github:https://github.com/dbrax/audit_trails
 * Email: epmnzava@gmail.com
 * 
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditTrailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('event_category')->nullable();
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->string('fullUrl')->nullable();
            $table->string('comment')->nullable();
            $table->integer('userid')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('request_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_trails');
    }
}
