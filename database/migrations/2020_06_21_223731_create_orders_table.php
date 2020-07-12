<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {

	public function up()
	{
		Schema::create('orders', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('special_request');
			$table->decimal('total');
			$table->mediumText('notes');
			$table->string('address');
			$table->enum('order_status', array('pending', 'accepted', 'rejected', 'delivered', 'declined'));
			$table->string('reason');
			$table->integer('resturant_id')->unsigned();
			$table->integer('client_id')->unsigned();
			$table->decimal('total_commission');
			$table->float('cost')->nullable();
			$table->float('delivery_cost')->nullable();
			$table->float('net')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('orders');
	}
}