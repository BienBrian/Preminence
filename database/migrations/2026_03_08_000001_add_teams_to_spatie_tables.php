<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1: Retrofit Spatie Permission tables for teams (per-tenant role isolation).
 *
 * Fully idempotent — each block is guarded by hasColumn() so it is safe to
 * re-run if a previous attempt partially applied.
 *
 * Uses raw ALTER TABLE statements to avoid Laravel Schema Builder's FK/PK
 * constraint name assumptions which differ across MySQL versions and Spatie releases.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. roles table ────────────────────────────────────────────────────
        if (!Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->index('tenant_id', 'roles_tenant_id_index');
            });

            // Drop original unique — try both common Spatie index names, silently
            foreach (['roles_name_guard_name_unique', 'roles_tenant_name_guard_unique'] as $idx) {
                try {
                    DB::statement("ALTER TABLE `roles` DROP INDEX `{$idx}`");
                } catch (\Exception $e) {}
            }

            try {
                DB::statement("ALTER TABLE `roles` ADD UNIQUE KEY `roles_tenant_name_guard_unique` (`tenant_id`, `name`, `guard_name`)");
            } catch (\Exception $e) {}
        }
        // Always backfill
        DB::table('roles')->whereNull('tenant_id')->update(['tenant_id' => 1]);

        // ── 2. model_has_permissions ──────────────────────────────────────────
        if (!Schema::hasColumn('model_has_permissions', 'tenant_id')) {
            // Drop FK that references permissions.id (blocks PK drop)
            DB::statement('ALTER TABLE `model_has_permissions` DROP FOREIGN KEY `model_has_permissions_permission_id_foreign`');
            // Drop primary key
            DB::statement('ALTER TABLE `model_has_permissions` DROP PRIMARY KEY');
            // Add tenant_id column
            DB::statement('ALTER TABLE `model_has_permissions` ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER `permission_id`');
            // Add new composite PK
            DB::statement('ALTER TABLE `model_has_permissions` ADD PRIMARY KEY (`tenant_id`, `permission_id`, `model_id`, `model_type`(255))');
            // Add regular index on tenant_id
            DB::statement('ALTER TABLE `model_has_permissions` ADD INDEX `model_has_permissions_tenant_id_index` (`tenant_id`)');
            // Restore FK
            DB::statement('ALTER TABLE `model_has_permissions` ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE');

            DB::table('model_has_permissions')->whereNull('tenant_id')->update(['tenant_id' => 1]);
        }

        // ── 3. model_has_roles ────────────────────────────────────────────────
        if (!Schema::hasColumn('model_has_roles', 'tenant_id')) {
            // Check for FK on role_id
            $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME='model_has_roles' AND TABLE_SCHEMA=DATABASE()
                AND REFERENCED_TABLE_NAME='roles'");
            foreach ($fks as $fk) {
                try { DB::statement("ALTER TABLE `model_has_roles` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`"); } catch (\Exception $e) {}
            }

            DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY');
            DB::statement('ALTER TABLE `model_has_roles` ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER `role_id`');
            DB::statement('ALTER TABLE `model_has_roles` ADD PRIMARY KEY (`tenant_id`, `role_id`, `model_id`, `model_type`(255))');
            DB::statement('ALTER TABLE `model_has_roles` ADD INDEX `model_has_roles_tenant_id_index` (`tenant_id`)');

            // Restore FK
            foreach ($fks as $fk) {
                try {
                    DB::statement("ALTER TABLE `model_has_roles` ADD CONSTRAINT `{$fk->CONSTRAINT_NAME}` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE");
                } catch (\Exception $e) {}
            }

            DB::table('model_has_roles')->whereNull('tenant_id')->update(['tenant_id' => 1]);
        }

        // Clear Spatie permission cache so the new schema is used
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // model_has_roles
        if (Schema::hasColumn('model_has_roles', 'tenant_id')) {
            DB::statement('ALTER TABLE `model_has_roles` DROP PRIMARY KEY');
            try { DB::statement('ALTER TABLE `model_has_roles` DROP INDEX `model_has_roles_tenant_id_index`'); } catch (\Exception $e) {}
            DB::statement('ALTER TABLE `model_has_roles` DROP COLUMN `tenant_id`');
            DB::statement('ALTER TABLE `model_has_roles` ADD PRIMARY KEY (`role_id`, `model_id`, `model_type`(255))');
        }

        // model_has_permissions
        if (Schema::hasColumn('model_has_permissions', 'tenant_id')) {
            DB::statement('ALTER TABLE `model_has_permissions` DROP FOREIGN KEY `model_has_permissions_permission_id_foreign`');
            DB::statement('ALTER TABLE `model_has_permissions` DROP PRIMARY KEY');
            try { DB::statement('ALTER TABLE `model_has_permissions` DROP INDEX `model_has_permissions_tenant_id_index`'); } catch (\Exception $e) {}
            DB::statement('ALTER TABLE `model_has_permissions` DROP COLUMN `tenant_id`');
            DB::statement('ALTER TABLE `model_has_permissions` ADD PRIMARY KEY (`permission_id`, `model_id`, `model_type`(255))');
            DB::statement('ALTER TABLE `model_has_permissions` ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE');
        }

        // roles
        if (Schema::hasColumn('roles', 'tenant_id')) {
            try { DB::statement('ALTER TABLE `roles` DROP INDEX `roles_tenant_name_guard_unique`'); } catch (\Exception $e) {}
            try { DB::statement('ALTER TABLE `roles` DROP INDEX `roles_tenant_id_index`'); } catch (\Exception $e) {}
            DB::statement('ALTER TABLE `roles` DROP COLUMN `tenant_id`');
            try { DB::statement("ALTER TABLE `roles` ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)"); } catch (\Exception $e) {}
        }
    }
};
