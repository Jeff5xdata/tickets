<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint
        DB::statement("ALTER TABLE email_accounts DROP CONSTRAINT email_accounts_type_check");
        
        // Add the new check constraint with calendar types
        DB::statement("ALTER TABLE email_accounts ADD CONSTRAINT email_accounts_type_check CHECK (type::text = ANY (ARRAY['imap'::character varying, 'gmail'::character varying, 'outlook'::character varying, 'google-tasks'::character varying, 'google-calendar'::character varying, 'microsoft-calendar'::character varying, 'ical'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new check constraint
        DB::statement("ALTER TABLE email_accounts DROP CONSTRAINT email_accounts_type_check");
        
        // Restore the original check constraint
        DB::statement("ALTER TABLE email_accounts ADD CONSTRAINT email_accounts_type_check CHECK (type::text = ANY (ARRAY['imap'::character varying, 'gmail'::character varying, 'outlook'::character varying, 'google-tasks'::character varying]::text[]))");
    }
}; 