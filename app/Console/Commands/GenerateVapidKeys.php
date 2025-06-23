<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapid:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for web push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating VAPID keys...');

        $keys = VAPID::createVapidKeys();

        $this->info('VAPID keys generated successfully!');
        $this->newLine();
        $this->info('Add these to your .env file:');
        $this->newLine();
        $this->line('VAPID_SUBJECT=mailto:your-email@example.com');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->info('Make sure to update the VAPID_SUBJECT with your actual email address.');

        return Command::SUCCESS;
    }
}
