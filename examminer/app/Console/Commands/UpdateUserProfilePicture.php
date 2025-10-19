<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserProfilePicture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-profile-picture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all users to have default profile picture';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('profile_picture')->orWhere('profile_picture', '')->get();
        
        foreach ($users as $user) {
            $user->update(['profile_picture' => 'default-avatar.png']);
            $this->info("Updated profile picture for user: {$user->name}");
        }
        
        $this->info('All users now have default profile pictures!');
    }
}
