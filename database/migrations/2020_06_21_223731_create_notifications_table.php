<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsTable extends Migration {

	public function up()
	{
		Schema::create('notifications', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('title');
			$table->mediumText('content');
			$table->integer('order_id')->unsigned();
			$table->string('notifiable_type');
			$table->integer('notifiable_id');
			$table->tinyInteger('is_read')->default('0');
		});
	}

	public function down()
	{
		Schema::drop('notifications');
	}
}