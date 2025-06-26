<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lifetime = config('session.lifetime', 120);
        $cutoff = time() - ($lifetime * 60);
        
        $deleted = DB::table('sessions')
            ->where('last_activity', '<', $cutoff)
            ->delete();
            
        $this->info("Cleaned up {$deleted} expired sessions.");
        
        return Command::SUCCESS;
    }
}
