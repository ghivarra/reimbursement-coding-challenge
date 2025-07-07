<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $tableName = "reimbursements";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('file', 255);
            $table->integer('amount', false, true)->default(0);
            $table->date('date');
            $table->bigInteger('user_id', false, true)->index();
            $table->bigInteger('reimbursement_status_id', false, true)->index();
            $table->bigInteger('reimbursement_category_id', false, true)->index();
            $table->timestamps();
            $table->softDeletes();

            // assign foreign key
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('reimbursement_status_id')->references('id')->on('reimbursements_statuses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('reimbursement_category_id')->references('id')->on('reimbursements_categories')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // altering index and foreign keys
        Schema::table($this->tableName, function(Blueprint $table) {
            $table->dropForeign("{$this->tableName}_user_id_foreign");
            $table->dropForeign("{$this->tableName}_reimbursement_status_id_foreign");
            $table->dropForeign("{$this->tableName}_reimbursement_category_id_foreign");
        });

        Schema::dropIfExists($this->tableName);
    }
};