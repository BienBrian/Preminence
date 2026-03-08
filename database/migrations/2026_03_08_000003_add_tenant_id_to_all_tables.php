<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2: Add tenant_id to ALL remaining tenant-scoped tables.
 *
 * Every table gets:
 *   - unsignedBigInteger('tenant_id') DEFAULT 1  (after 'id')
 *   - FK to tenants.id  ON DELETE CASCADE
 *   - index on tenant_id for query performance
 *
 * Existing rows are implicitly assigned to tenant 1 (the default value).
 *
 * Tables are processed in dependency order (no FK conflicts).
 */
return new class extends Migration
{
    /**
     * Tables that need tenant_id added.
     * Format: 'table_name' => 'after_column'
     * If after_column is 'id', tenant_id goes right after id.
     */
    private array $tables = [
        // ── People & Contacts ─────────────────────────────────────────────────
        'contacts'           => 'id',
        'scontacts'          => 'id',
        'emergency_contact'  => 'id',
        'church'             => 'id',
        'families'           => 'id',
        'professions'        => 'id',
        'education'          => 'id',
        'profiles'           => 'id',
        'profile_categories' => 'id',

        // ── Finance ───────────────────────────────────────────────────────────
        'funds'                => 'id',
        'sources'              => 'id',
        'pledges'              => 'id',
        'pledge_sms'           => 'id',
        'assets'               => 'id',
        'donations'            => 'id',
        'budget'               => 'id',
        'budget_items'         => 'id',
        'activities'           => 'id',
        'summaries'            => 'id',
        'summaries_operations' => 'id',
        'ModeOfPayment'        => 'id',

        // ── Communication ─────────────────────────────────────────────────────
        'sms'            => 'id',
        'sms_recipients' => 'id',
        'emails'         => 'id',
        'email_recipients' => 'id',
        'schedules'      => 'id',
        'twilios'        => 'id',
        'pending_sms'    => 'id',

        // ── Events ────────────────────────────────────────────────────────────
        'events'            => 'id',
        'notices'           => 'id',
        'seminars'          => 'id',
        'attendance'        => 'id',
        'attendance_groups' => 'id',
        'registration'      => 'id',

        // ── Spiritual & Content ───────────────────────────────────────────────
        'sermons'        => 'id',
        'prayers'        => 'id',
        'testimonials'   => 'id',
        'articles'       => 'id',
        'article_categories' => 'id',
        'article_tags'   => 'id',
        'galleries'      => 'id',
        'home_pages'     => 'id',
        'pastorsmessage' => 'id',
        'orderofservice' => 'id',
        'weeklyverse'    => 'id',

        // ── People Groups ─────────────────────────────────────────────────────
        'communities'   => 'id',
        'departments'   => 'id',
        'people'        => 'id',
        'people_members'=> 'id',
        'participants'  => 'id',
        'groups'        => 'id',
        'pastors'       => 'id',

        // ── Media ─────────────────────────────────────────────────────────────
        'media_folders' => 'id',
        'media_files'   => 'id',

        // ── Shop ──────────────────────────────────────────────────────────────
        'products'  => 'id',
        'purchases' => 'id',

        // ── Tags & Settings ───────────────────────────────────────────────────
        'tags'                   => 'id',
        'tag_user'               => 'id',
        'birthday_settings'      => 'id',
        'mpesa_message_settings' => 'id',
        'sunday_school_classes'  => 'id',
        'residences'             => 'id',

        // ── Mpesa ─────────────────────────────────────────────────────────────
        'mpesa_transactions'   => 'id',
        'mpesa_phones'         => 'id',
        'missing_mpesa_phones' => 'id',

        // ── Discipleship ──────────────────────────────────────────────────────
        'discipleship_tracks'      => 'id',
        'discipleship_steps'       => 'id',
        'discipleship_enrollments' => 'id',
        'discipleship_progress'    => 'id',
        'mentorships'              => 'id',
        'mentorship_sessions'      => 'id',
        'recovery_journals'        => 'id',

        // ── Prayer Requests ───────────────────────────────────────────────────
        'prayer_requests'      => 'id',
        'prayer_request_notes' => 'id',
        'prayer_tags'          => 'id',
        'prayer_request_tag'   => 'id',

        // ── Invitations ───────────────────────────────────────────────────────
        'invitations' => 'id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $afterColumn) {
            // Skip tables that already have tenant_id (e.g. added in earlier migrations)
            if (Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            // Skip tables that don't exist (safety check)
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            // Skip tables that don't have the 'after' column (e.g., pivot tables without id)
            if (!Schema::hasColumn($table, $afterColumn)) {
                // For tables without 'id', add tenant_id as the first column
                $afterColumn = null;
            }

            // 1. Add the column
            Schema::table($table, function (Blueprint $blueprint) use ($afterColumn) {
                if ($afterColumn) {
                    $blueprint->unsignedBigInteger('tenant_id')
                        ->default(1)
                        ->after($afterColumn);
                } else {
                    $blueprint->unsignedBigInteger('tenant_id')
                        ->default(1)
                        ->first();
                }
            });

            // 2. Add FK + index in a separate statement
            //    (some DB engines require the column to exist first)
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $safeIndex = substr('idx_' . $table . '_tenant', 0, 64);
                $blueprint->index('tenant_id', $safeIndex);
                $blueprint->foreign('tenant_id', substr('fk_' . $table . '_tenant', 0, 64))
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys($this->tables)) as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                try { $blueprint->dropForeign(substr('fk_' . $table . '_tenant', 0, 64)); } catch (\Exception $e) {}
                try { $blueprint->dropIndex(substr('idx_' . $table . '_tenant', 0, 64)); } catch (\Exception $e) {}
                $blueprint->dropColumn('tenant_id');
            });
        }
    }
};
