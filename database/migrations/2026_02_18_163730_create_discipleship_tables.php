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
        // 1. Tracks (The "Roadmap")
        Schema::create('discipleship_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_public')->default(true); // Publicly visible vs Invite only
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Steps (Milestones/Classes within a track)
        Schema::create('discipleship_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('track_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('content_type')->default('text'); // text, video, pdf, link
            $table->text('content_url')->nullable();
            $table->text('content_body')->nullable(); // For text content
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('track_id')->references('id')->on('discipleship_tracks')->onDelete('cascade');
        });

        // 3. Enrollments (User's journey in a track)
        Schema::create('discipleship_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('track_id');
            $table->string('status')->default('in_progress'); // in_progress, completed, dropped
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('track_id')->references('id')->on('discipleship_tracks')->onDelete('cascade');
        });

        // 4. Progress (Tracking step completion)
        Schema::create('discipleship_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('step_id');
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('enrollment_id')->references('id')->on('discipleship_enrollments')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('discipleship_steps')->onDelete('cascade');
        });

        // 5. Mentorships (Connecting Mentor & Mentee)
        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id');
            $table->unsignedBigInteger('mentee_id');
            $table->string('status')->default('active'); // active, completed, paused
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mentee_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 6. Mentorship Sessions (Log of meetings/check-ins)
        Schema::create('mentorship_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentorship_id');
            $table->text('notes')->nullable(); // Private notes
            $table->timestamp('session_date')->useCurrent();
            $table->unsignedBigInteger('created_by'); // Who logged the session
            $table->timestamps();

            $table->foreign('mentorship_id')->references('id')->on('mentorships')->onDelete('cascade');
        });

        // 7. Recovery Journals (Private user logs)
        Schema::create('recovery_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->text('entry');
            $table->boolean('is_shared_with_mentor')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_journals');
        Schema::dropIfExists('mentorship_sessions');
        Schema::dropIfExists('mentorships');
        Schema::dropIfExists('discipleship_progress');
        Schema::dropIfExists('discipleship_enrollments');
        Schema::dropIfExists('discipleship_steps');
        Schema::dropIfExists('discipleship_tracks');
    }
};
