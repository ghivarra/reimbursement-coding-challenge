<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $usersTableName = "users";
    private $passwordResetTokensTableName = "password_reset_tokens";
    private $sessionsTableName = "sessions";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->usersTableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('password');
            $table->bigInteger('role_id', false, true)->index();
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // assign foreign key
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create($this->sessionsTableName, function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('user_id', false, true)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            // assign foreign key
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop foreign keys first then table
        Schema::table($this->sessionsTableName, function (Blueprint $table) {
            $table->dropForeign("{$this->sessionsTableName}_user_id_foreign");
        });

        Schema::dropIfExists($this->sessionsTableName);

        // drop foreign keys first then table
        Schema::table($this->usersTableName, function (Blueprint $table) {
            $table->dropForeign("{$this->usersTableName}_role_id_foreign");
        });

        Schema::dropIfExists($this->usersTableName);
    }
};
