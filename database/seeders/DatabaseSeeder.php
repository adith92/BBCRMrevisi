<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pool;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\MaintenanceLog;
use App\Models\MeetingLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 6 Users
        $users = [
            ['name' => 'Pak Andi (GM)', 'email' => 'gm@bluebird.co.id', 'role' => 'gm'],
            ['name' => 'Budi (Sales 1)', 'email' => 'sales1@bluebird.co.id', 'role' => 'sales'],
            ['name' => 'Sari (Sales 2)', 'email' => 'sales2@bluebird.co.id', 'role' => 'sales'],
            ['name' => 'Dedi (Operational)', 'email' => 'ops@bluebird.co.id', 'role' => 'operational'],
            ['name' => 'Rina (Finance)', 'email' => 'finance@bluebird.co.id', 'role' => 'finance'],
            ['name' => 'Joko (Admin)', 'email' => 'admin@bluebird.co.id', 'role' => 'gm'],
        ];
        $userModels = [];
        foreach ($users as $u) {
            $userModels[] = User::create([
                'name' => $u['name'], 'email' => $u['email'],
                'password' => Hash::make('password123'), 'role' => $u['role'], 'is_active' => true,
            ]);
        }
        $gm = $userModels[0]; $sales1 = $userModels[1]; $sales2 = $userModels[2];
        $ops = $userModels[3]; $finance = $userModels[4];

        // 5 Pools
        $pools = [];
        foreach ([
            ['name' => 'Pool Jakarta Pusat', 'location' => 'Jl. Gatot Subroto No. 10', 'capacity' => 30],
            ['name' => 'Pool Jakarta Selatan', 'location' => 'Jl. TB Simatupang No. 5', 'capacity' => 25],
            ['name' => 'Pool Bandung', 'location' => 'Jl. Asia Afrika No. 20', 'capacity' => 20],
            ['name' => 'Pool Surabaya', 'location' => 'Jl. Pemuda No. 15', 'capacity' => 15],
            ['name' => 'Pool Bali', 'location' => 'Jl. Sunset Road No. 8', 'capacity' => 10],
        ] as $p) {
            $pools[] = Pool::create($p);
        }

        // 30 Clients
        $companies = [
            'PT Astra International', 'PT Pertamina', 'PT Bank Mandiri', 'PT Astra Otoparts',
            'PT Astra Honda Motor', 'PT Indosat Ooredoo', 'PT Telekomunikasi Indonesia', 'PT Semen Indonesia',
            'PT Unilever Indonesia', 'PT Indofood Sukses Makmur', 'PT Sinar Mas', 'PT Kalbe Farma',
            'PT Astra Agro Lestari', 'PT Gudang Garam', 'PT Mayora Indah', 'PT Astra Credit',
            'PT Adaro Energy', 'PT Bukit Asam', 'PT Medco Energi', 'PT PGN Tbk',
            'PT Waskita Karya', 'PT Wijaya Karya', 'PT Semen Gresik', 'PT Holcim Indonesia',
            'PT Astra DMS', 'PT Astra Graphia', 'PT Astra Component', 'PT Astra Logistics',
            'PT Astra Property', 'PT Astra Travel Service',
        ];
        $industries = ['Otomotif','Energi','Keuangan','Manufaktur','Telekomunikasi','Konstruksi','FMCG','Properti','Logistik','Pertambangan'];
        $clientModels = [];
        for ($i = 0; $i < 30; $i++) {
            $clientModels[] = Client::create([
                'company_name' => $companies[$i],
                'pic_name' => 'PIC ' . explode(' ', $companies[$i])[1],
                'phone' => '0812' . rand(10000000, 99999999),
                'email' => strtolower(str_replace(' ', '', explode(' ', $companies[$i])[1])) . '@company.co.id',
                'industry' => $industries[$i % 10],
                'status' => ['active','prospect','inactive'][$i % 3],
                'assigned_sales_id' => ($i < 15) ? $sales1->id : $sales2->id,
                'address' => 'Jl. Sudirman No. ' . ($i + 1) . ', Jakarta',
            ]);
        }

        // 15 Drivers
        $driverModels = [];
        $driverNames = ['Agus','Bambang','Cahyo','Dani','Eko','Faisal','Gunawan','Hadi','Irfan','Joko','Kurniawan','Lukman','Mulyadi','Narto','Oscar'];
        foreach ($driverNames as $i => $name) {
            $driverModels[] = Driver::create([
                'name' => $name, 'phone' => '0813' . rand(10000000, 99999999),
                'license_number' => 'SIM-A-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => ['available','on_duty','off'][$i % 3],
            ]);
        }

        // 20 Vehicles (5 per brand)
        $vehicleModels = [];
        $brands = ['bigbird','goldenbird','cititrans','executive'];
        $models = [
            'bigbird' => ['Mercedes Sprinter','Hino RK','Isuzu NQR','Mitsubishi FE','Hino RK8'],
            'goldenbird' => ['Toyota Alphard','Toyota Camry','Honda Accord','BMW 520i','Mercedes E-Class'],
            'cititrans' => ['Toyota HiAce','Daihatsu Luxio','Suzuki APV','Toyota Avanza','Honda Mobilio'],
            'executive' => ['Toyota Fortuner','Mitsubishi Pajero','Honda CR-V','Mazda CX-5','Nissan X-Trail'],
        ];
        $platePrefix = ['B','D','L','DK'];
        $vi = 0;
        foreach ($brands as $bi => $brand) {
            foreach ($models[$brand] as $model) {
                $vehicleModels[] = Vehicle::create([
                    'plate_number' => $platePrefix[$bi] . ' ' . rand(1000,9999) . ' ' . strtoupper(substr($brand, 0, 2)),
                    'brand' => $brand, 'model' => $model,
                    'capacity' => $brand === 'bigbird' ? 30 : ($brand === 'goldenbird' ? 4 : 7),
                    'year' => rand(2020, 2026),
                    'status' => ['available','on_trip','maintenance','inactive'][$vi % 4],
                    'pool_id' => $pools[$vi % 5]->id,
                ]);
                $vi++;
            }
        }

        // 60 Bookings (spread 12 months)
        $bookingModels = [];
        $statuses = ['pending','confirmed','on_trip','completed','completed','completed'];
        $destinations = ['Bandung','Surabaya','Yogyakarta','Semarang','Malang','Bali','Bogor','Depok','Tangerang','Bekasi'];
        for ($i = 0; $i < 60; $i++) {
            $daysAgo = rand(1, 365);
            $pickup = now()->subDays($daysAgo)->addHours(rand(6, 10));
            $vehicle = $vehicleModels[$i % 20];
            $client = $clientModels[$i % 30];
            $sales = ($i % 2 === 0) ? $sales1 : $sales2;
            $driver = $driverModels[$i % 15];
            $price = rand(500, 5000) * 1000;
            
            $bookingModels[] = Booking::create([
                'booking_number' => 'BK' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id, 'sales_id' => $sales->id,
                'created_by' => $sales->id, 'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'pickup_datetime' => $pickup, 'dropoff_datetime' => $pickup->addHours(rand(2, 12)),
                'destination' => $destinations[$i % 10],
                'vehicle_type' => $vehicle->brand,
                'price' => $price,
                'status' => $statuses[$i % 6],
            ]);
        }

        // 50 Invoices
        $invoiceModels = [];
        $invoiceStatuses = ['draft','sent','paid','paid','paid','overdue'];
        for ($i = 0; $i < 50; $i++) {
            $booking = $bookingModels[$i % 60];
            $status = $invoiceStatuses[$i % 6];
            $invoiceModels[] = Invoice::create([
                'invoice_number' => 'INV-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'booking_id' => $booking->id, 'client_id' => $booking->client_id,
                'amount' => $booking->price,
                'status' => $status,
                'due_date' => $booking->pickup_datetime->addDays(30),
                'paid_at' => $status === 'paid' ? $booking->pickup_datetime->addDays(rand(5, 25)) : null,
            ]);
        }

        // 40 Payments
        $paidInvoices = Invoice::where('status', 'paid')->get();
        for ($i = 0; $i < min(40, $paidInvoices->count()); $i++) {
            Payment::create([
                'payment_number' => 'PAY-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'invoice_id' => $paidInvoices[$i]->id,
                'amount' => $paidInvoices[$i]->amount,
                'method' => ['transfer','cash','giro'][$i % 3],
                'payment_date' => $paidInvoices[$i]->paid_at,
            ]);
        }

        // 15 Purchase Orders
        $vendors = ['Toko Sparepart Jaya','Bengkel Maju Motor','PT Oli Nusantara','CV Ban Indonesia','PT AC Mobil Sejahtera'];
        $items = ['Oli mesin 20L','Ban Michelin 205/55R16','Filter udara','Kampas rem','AC compressor','Wiper blade','Aki GS Astra','Lampu LED','Shock absorber','Timing belt'];
        for ($i = 0; $i < 15; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'vendor' => $vendors[$i % 5],
                'item_description' => $items[$i % 10] . ' (qty: ' . rand(5, 50) . ')',
                'amount' => rand(500, 10000) * 1000,
                'status' => ['pending','approved','received'][$i % 3],
            ]);
        }

        // 20 Maintenance Logs
        $types = ['routine','repair','modification'];
        $descs = ['Ganti oli mesin','Service AC','Ganti ban','Tune up','Ganti kampas rem','Cuci detail','Overhaul mesin','Ganti aki','Balance roda','Ganti filter'];
        for ($i = 0; $i < 20; $i++) {
            MaintenanceLog::create([
                'vehicle_id' => $vehicleModels[$i % 20]->id,
                'type' => $types[$i % 3],
                'description' => $descs[$i % 10],
                'cost' => rand(200, 5000) * 1000,
                'vendor' => $vendors[$i % 5],
                'scheduled_date' => now()->subDays(rand(1, 180)),
                'completed_date' => ($i % 3 !== 0) ? now()->subDays(rand(1, 90)) : null,
                'status' => ['completed','completed','scheduled'][$i % 3],
            ]);
        }

        // 25 Meeting Logs
        for ($i = 0; $i < 25; $i++) {
            $sales = ($i < 13) ? $sales1 : $sales2;
            MeetingLog::create([
                'client_id' => $clientModels[$i % 30]->id,
                'sales_id' => $sales->id,
                'meeting_date' => now()->subDays(rand(1, 120))->addHours(rand(9, 15)),
                'notes' => 'Meeting pembahasan kontrak layanan transportasi',
                'outcome' => ['Positif - lanjut negosiasi','Perlu follow-up minggu depan','Client request proposal baru','Deal - kontrak diperpanjang','Pending keputusan manajemen'][$i % 5],
                'follow_up_date' => now()->addDays(rand(1, 30)),
                'status' => ['done','done','pending'][$i % 3],
            ]);
        }
    }
}
