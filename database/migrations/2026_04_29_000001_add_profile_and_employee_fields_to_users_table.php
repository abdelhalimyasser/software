<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string('first_name')->nullable()->after('name');
			$table->string('last_name')->nullable()->after('first_name');
			$table->date('birth_date')->nullable()->after('last_name');
			$table->string('phone_number', 15)->nullable()->unique()->after('email');
			$table->string('role')->nullable()->after('password');
			$table->string('emp_id')->nullable()->unique()->after('role');
			$table->string('profile_picture_path')->nullable()->after('emp_id');
			$table->string('resume_path')->nullable()->after('profile_picture_path');
			$table->string('docs_path')->nullable()->after('resume_path');
			$table->json('skills')->nullable()->after('docs_path');
			$table->unsignedTinyInteger('experience_years')->nullable()->after('skills');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (config('database.default') !== 'sqlite') {
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn([
					'first_name',
					'last_name',
					'birth_date',
					'phone_number',
					'role',
					'emp_id',
					'profile_picture_path',
					'resume_path',
					'docs_path',
					'skills',
					'experience_years',
				]);
			});
		}
	}
};


