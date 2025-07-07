<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $tableName = "reimbursements_logs";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('content', 500);
            $table->uuid('reimbursement_id')->index();
            $table->bigInteger('reimbursement_status_id', false, true)->index();
            $table->bigInteger('user_id', false, true)->index();

            // assign foreign key
            $table->foreign('reimbursement_id')->references('id')->on('reimbursements')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('reimbursement_status_id')->references('id')->on('reimbursements_statuses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // altering index and foreign keys
        Schema::table($this->tableName, function(Blueprint $table) {
            $table->dropForeign("{$this->tableName}_reimbursement_id_foreign");
            $table->dropForeign("{$this->tableName}_reimbursement_status_id_foreign");
            $table->dropForeign("{$this->tableName}_user_id_foreign");
        });

        Schema::dropIfExists($this->tableName);
    }
};