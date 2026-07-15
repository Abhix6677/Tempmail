<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tmail:setup-admin 
        {--name=Admin : Admin user name}
        {--email=admin@example.com : Admin user email}
        {--password=password : Admin user password}';

    /**
     * The console command description.
     */
    protected $description = 'Enable user registration and create the first admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Step 1: Enable user registration
        $this->info('Enabling user registration...');
        
        $regSetting = Setting::where('key', 'user_registration')->first();
        if ($regSetting) {
            $value = unserialize($regSetting->value);
            $value['enabled'] = true;
            $regSetting->value = serialize($value);
            $regSetting->save();
            $this->info('User registration enabled.');
        } else {
            Setting::create([
                'key' => 'user_registration',
                'value' => serialize(['enabled' => true, 'require_email_verification' => false]),
            ]);
            $this->info('User registration setting created and enabled.');
        }

        // Step 2: Create admin user if none exists
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        if (User::where('email', $email)->exists()) {
            $this->warn("User with email '$email' already exists. Skipping user creation.");
            
            // Ensure existing user has admin role
            $user = User::where('email', $email)->first();
            if ($user->role !== 7) {
                $user->role = 7;
                $user->save();
                $this->info("Updated existing user to admin role (7).");
            }
        } else {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 7,
            ]);
            $this->info("Admin user created: $email (role: 7)");
        }

        $this->info('');
        $this->info('Setup complete! You can now:');
        $this->info("  1. Visit /register to create additional accounts");
        $this->info("  2. Login at /login with: $email / $password");
        $this->info("  3. Access admin panel at /admin");

        return Command::SUCCESS;
    }
}
