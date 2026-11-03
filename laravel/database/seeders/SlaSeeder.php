<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * php artisan db:seed --class=SlaSeeder
 * Prints the generated admin password once. Change it after first login.
 */
class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SLA_ADMIN_EMAIL', 'admin@4axiz.com');
        if (! User::where('email', $email)->exists()) {
            $password = Str::password(16, symbols: false);
            User::create(['name' => '4axiz Admin', 'email' => $email, 'password' => Hash::make($password)]);
            $this->command->warn("Admin login: {$email} / {$password}");
        }

        $hrm = Application::firstOrCreate(['code' => 'HRM'], ['name' => 'HR & Payroll']);
        $kg = Client::firstOrCreate(['code' => 'KG'], ['name' => 'Karim Group']);

        Subscription::firstOrCreate(
            ['client_id' => $kg->id, 'application_id' => $hrm->id],
            ['quarterly_amount' => 0]
        );
    }
}
