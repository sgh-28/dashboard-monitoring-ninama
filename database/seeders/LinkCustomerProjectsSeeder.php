<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;

class LinkCustomerProjectsSeeder extends Seeder
{
    public function run(): void
    {
        // Link PT. Digital Kreatif
        $customer = User::where('email', 'customer@ninama.com')->first();
        if ($customer) {
            Project::where('client_name', 'PT. Digital Kreatif')
                ->update(['customer_id' => $customer->id]);
        }

        // Link Startup Maju Terus
        $customer2 = User::where('email', 'startup@maju.com')->first();
        if ($customer2) {
            Project::where('client_name', 'Startup Maju Terus')
                ->update(['customer_id' => $customer2->id]);
        }
    }
}