<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResturantsTable extends Migration {

	public function up()
	{
		Schema::create('resturants', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('resturant_name');
			$table->string('emai');
			$table->string('delivery_time');
			$table->integer('district_id')->unsigned();
			$table->string('password');
			$table->double('minimum_order');
			$table->double('delivery_charge');
			$table->enum('status', array('open', 'close'));
			$table->string('phone');
			$table->string('whatsapp');
			$table->string('image');
			$table->string('rest_code')->nullable();
			$table->string('api_token')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('resturants');
	}
}