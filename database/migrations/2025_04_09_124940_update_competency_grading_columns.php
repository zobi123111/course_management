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
        Schema::table('competency_gradings', function (Blueprint $table) {
            // Remove old general grade column
            $table->dropColumn('competency_grade');

            // Add new competency-specific grade and comment fields
            $table->tinyInteger('kno_grade')->nullable()->after('user_id');
            $table->text('kno_comment')->nullable()->after('kno_grade');

            $table->tinyInteger('pro_grade')->nullable()->after('kno_comment');
            $table->text('pro_comment')->nullable()->after('pro_grade');

            $table->tinyInteger('com_grade')->nullable()->after('pro_comment');
            $table->text('com_comment')->nullable()->after('com_grade');

            $table->tinyInteger('fpa_grade')->nullable()->after('com_comment');
            $table->text('fpa_comment')->nullable()->after('fpa_grade');

            $table->tinyInteger('fpm_grade')->nullable()->after('fpa_comment');
            $table->text('fpm_comment')->nullable()->after('fpm_grade');

            $table->tinyInteger('ltw_grade')->nullable()->after('fpm_comment');
            $table->text('ltw_comment')->nullable()->after('ltw_grade');

            $table->tinyInteger('psd_grade')->nullable()->after('ltw_comment');
            $table->text('psd_comment')->nullable()->after('psd_grade');

            $table->tinyInteger('saw_grade')->nullable()->after('psd_comment');
            $table->text('saw_comment')->nullable()->after('saw_grade');

            $table->tinyInteger('wlm_grade')->nullable()->after('saw_comment');
            $table->text('wlm_comment')->nullable()->after('wlm_grade');
        });
    }

    public function down(): void
    {
        Schema::table('competency_gradings', function (Blueprint $table) {
            // Re-add the old general grade column in its original position
            $table->string('competency_grade', 50)->nullable()->after('user_id');
    
            // Drop the new competency-based grade/comment columns
            $table->dropColumn([
                'kno_grade', 'kno_comment',
                'pro_grade', 'pro_comment',
                'com_grade', 'com_comment',
                'fpa_grade', 'fpa_comment',
                'fpm_grade', 'fpm_comment',
                'ltw_grade', 'ltw_comment',
                'psd_grade', 'psd_comment',
                'saw_grade', 'saw_comment',
                'wlm_grade', 'wlm_comment',
            ]);
        });
    }
};
