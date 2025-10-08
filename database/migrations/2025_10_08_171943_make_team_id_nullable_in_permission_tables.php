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
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable()->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable()->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable(false)->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable(false)->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable(false)->change();
        });
    }
};