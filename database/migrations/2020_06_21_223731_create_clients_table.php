<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientsTable extends Migration {

	public function up()
	{
		Schema::create('clients', function(Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->string('username');
			$table->string('password');
			$table->string('email');
			$table->string('phone');
			$table->string('image');
			$table->integer('district_id')->unsigned();
			$table->string('reset_code')->nullable();
			$table->string('apt_token')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('clients');
	}
}