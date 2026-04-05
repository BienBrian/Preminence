<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for tenant-scoped queries.
 *
 * Covers: pledges, donations, sms_recipients, mpesa_transactions,
 *         sources, people, people_members, schedules.
 *
 * All indexes are conditional on column/index existence so this
 * migration is safe to re-run or apply on top of partial schemas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── pledges ──────────────────────────────────────────────────────────
        if (Schema::hasTable('pledges')) {
            Schema::table('pledges', function (Blueprint $table) {
                // Tenant-scoped status filter (reports overview)
                if (!$this->indexExists('pledges', 'idx_pledges_tenant_status')) {
                    $table->index(['tenant_id', 'status'], 'idx_pledges_tenant_status');
                }
                // Tenant + user lookup (individual member giving)
                if (!$this->indexExists('pledges', 'idx_pledges_tenant_user')) {
                    $table->index(['tenant_id', 'user_id'], 'idx_pledges_tenant_user');
                }
            });
        }

        // ── donations ────────────────────────────────────────────────────────
        if (Schema::hasTable('donations')) {
            Schema::table('donations', function (Blueprint $table) {
                if (!$this->indexExists('donations', 'idx_donations_tenant')) {
                    $table->index(['tenant_id'], 'idx_donations_tenant');
                }
                if (!$this->indexExists('donations', 'idx_donations_tenant_user')) {
                    $table->index(['tenant_id', 'user_id'], 'idx_donations_tenant_user');
                }
            });
        }

        // ── sms_recipients ───────────────────────────────────────────────────
        if (Schema::hasTable('sms_recipients')) {
            Schema::table('sms_recipients', function (Blueprint $table) {
                // JOIN on sms_id when reading a message's recipients
                if (!$this->indexExists('sms_recipients', 'idx_sms_recipients_sms_id')) {
                    $table->index(['tenant_id', 'sms_id'], 'idx_sms_recipients_sms_id');
                }
                // Per-user message history
                if (!$this->indexExists('sms_recipients', 'idx_sms_recipients_user')) {
                    $table->index(['tenant_id', 'recipients'], 'idx_sms_recipients_user');
                }
            });
        }

        // ── mpesa_transactions ───────────────────────────────────────────────
        if (Schema::hasTable('mpesa_transactions')) {
            Schema::table('mpesa_transactions', function (Blueprint $table) {
                // Date-range queries (DataTable default: last 90 days)
                if (!$this->indexExists('mpesa_transactions', 'idx_mpesa_tenant_created')) {
                    $table->index(['tenant_id', 'created_at'], 'idx_mpesa_tenant_created');
                }
                // BillRefNumber filter (reference type filter)
                if (!$this->indexExists('mpesa_transactions', 'idx_mpesa_tenant_billref')) {
                    $table->index(['tenant_id', 'BillRefNumber'], 'idx_mpesa_tenant_billref');
                }
                // MSISDN lookup (phone matching on callback)
                if (!$this->indexExists('mpesa_transactions', 'idx_mpesa_msisdn')) {
                    $table->index(['MSISDN'], 'idx_mpesa_msisdn');
                }
            });
        }

        // ── sources ──────────────────────────────────────────────────────────
        if (Schema::hasTable('sources')) {
            Schema::table('sources', function (Blueprint $table) {
                // ftype filter (0=collection, 1=expense) used in reports & HomeController
                if (!$this->indexExists('sources', 'idx_sources_tenant_ftype')) {
                    $table->index(['tenant_id', 'ftype'], 'idx_sources_tenant_ftype');
                }
            });
        }

        // ── people ───────────────────────────────────────────────────────────
        if (Schema::hasTable('people')) {
            Schema::table('people', function (Blueprint $table) {
                if (!$this->indexExists('people', 'idx_people_tenant')) {
                    $table->index(['tenant_id'], 'idx_people_tenant');
                }
            });
        }

        // ── people_members ───────────────────────────────────────────────────
        if (Schema::hasTable('people_members')) {
            Schema::table('people_members', function (Blueprint $table) {
                // Group membership lookup (SMS group send)
                if (!$this->indexExists('people_members', 'idx_people_members_group')) {
                    $table->index(['tenant_id', 'people_id', 'status'], 'idx_people_members_group');
                }
            });
        }

        // ── schedules ────────────────────────────────────────────────────────
        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                // Scheduled SMS queue scan (type=0, status=0)
                if (!$this->indexExists('schedules', 'idx_schedules_tenant_type')) {
                    $table->index(['tenant_id', 'type', 'status'], 'idx_schedules_tenant_type');
                }
                // Schedule time ordering
                if (!$this->indexExists('schedules', 'idx_schedules_schedule')) {
                    $table->index(['tenant_id', 'schedule'], 'idx_schedules_schedule');
                }
            });
        }

        // ── funds ────────────────────────────────────────────────────────────
        // (tenant_id, created_at) already added in phase1 migration.
        // Add source index for JOIN queries in reports.
        if (Schema::hasTable('funds')) {
            Schema::table('funds', function (Blueprint $table) {
                if (!$this->indexExists('funds', 'idx_funds_tenant_source')) {
                    $table->index(['tenant_id', 'source'], 'idx_funds_tenant_source');
                }
                if (!$this->indexExists('funds', 'idx_funds_tenant_user')) {
                    $table->index(['tenant_id', 'user_id'], 'idx_funds_tenant_user');
                }
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'pledges'           => ['idx_pledges_tenant_status', 'idx_pledges_tenant_user'],
            'donations'         => ['idx_donations_tenant', 'idx_donations_tenant_user'],
            'sms_recipients'    => ['idx_sms_recipients_sms_id', 'idx_sms_recipients_user'],
            'mpesa_transactions'=> ['idx_mpesa_tenant_created', 'idx_mpesa_tenant_billref', 'idx_mpesa_msisdn'],
            'sources'           => ['idx_sources_tenant_ftype'],
            'people'            => ['idx_people_tenant'],
            'people_members'    => ['idx_people_members_group'],
            'schedules'         => ['idx_schedules_tenant_type', 'idx_schedules_schedule'],
            'funds'             => ['idx_funds_tenant_source', 'idx_funds_tenant_user'],
        ];

        foreach ($drops as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($indexes) {
                    foreach ($indexes as $index) {
                        try { $t->dropIndex($index); } catch (\Exception $e) {}
                    }
                });
            }
        }
    }

    /** Check if an index already exists to make the migration idempotent. */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }
};
