<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');
        $teamForeignKey = config('permission.column_names.team_foreign_key', 'team_id');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('permission_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeignKey) {
            $table->bigIncrements('role_id');
            if ($teams) {
                $table->unsignedBigInteger($teamForeignKey)->nullable();
                $table->index($teamForeignKey, 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            if ($teams) {
                $table->unique([$teamForeignKey, 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $teamForeignKey) {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']); // permission_id
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']); // model_id

            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('permission_id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            if ($teams) {
                $table->unsignedBigInteger($teamForeignKey);
                $table->index($teamForeignKey, 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$teamForeignKey, $columnNames['permission_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_primary'
                );
            } else {
                $table->primary(
                    [$columnNames['permission_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_primary'
                );
            }
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams, $teamForeignKey) {
            $table->unsignedBigInteger($columnNames['role_pivot_key']); // role_id
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']); // model_id

            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($columnNames['role_pivot_key'])
                ->references('role_id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            if ($teams) {
                $table->unsignedBigInteger($teamForeignKey);
                $table->index($teamForeignKey, 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$teamForeignKey, $columnNames['role_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_primary'
                );
            } else {
                $table->primary(
                    [$columnNames['role_pivot_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_primary'
                );
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']); // permission_id
            $table->unsignedBigInteger($columnNames['role_pivot_key']); // role_id

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('permission_id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($columnNames['role_pivot_key'])
                ->references('role_id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$columnNames['permission_pivot_key'], $columnNames['role_pivot_key']], 'role_has_permissions_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found, cannot drop tables.');
        }

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
