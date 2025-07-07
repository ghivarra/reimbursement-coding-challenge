<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $tableName = "roles_modules_lists";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->bigInteger('roles_id', false, true)->index();
            $table->bigInteger('modules_id', false, true)->index();
            $table->foreign('roles_id')->references('id')->on('roles')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('modules_id')->references('id')->on('modules')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // altering index and foreign keys
        Schema::table($this->tableName, function(Blueprint $table) {
            $table->dropForeign("{$this->tableName}_roles_id_foreign");
            $table->dropForeign("{$this->tableName}_modules_id_foreign");
        });

        Schema::dropIfExists($this->tableName);
    }
};
