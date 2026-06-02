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
            ['name' => 'Rudi (Sales 3)', 'email' => 'sales3@bluebird.co.id', 'role' => 'sales'],
            ['name' => 'Dedi (Operational)', 'email' => 'ops@bluebird.co.id', 'role' => 'operational'],
            ['name' => 'Rina (Finance)', 'email' => 'finance@bluebird.co.id', 'role' => 'finance'],
        ];
        $userModels = [];
        foreach ($users as $u) {
            $userModels[] = User::create([
                'name' => $u['name'], 'email' => $u['email'],
                'password' => Hash::make('password123'), 'role' => $u['role'], 'is_active' => true,
            ]);
        }
        $gm = $userModels[0]; $sales1 = $userModels[1]; $sales2 = $userModels[2]; $sales3 = $userModels[3];
        $ops = $userModels[4]; $finance = $userModels[5];

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

        // 101 Clients (perusahaan korporat Indonesia)
        $companies = [
            'PT Astra International', 'PT Pertamina', 'PT Bank Mandiri', 'PT Astra Otoparts',
            'PT Astra Honda Motor', 'PT Indosat Ooredoo', 'PT Telekomunikasi Indonesia', 'PT Semen Indonesia',
            'PT Unilever Indonesia', 'PT Indofood Sukses Makmur', 'PT Sinar Mas', 'PT Kalbe Farma',
            'PT Astra Agro Lestari', 'PT Gudang Garam', 'PT Mayora Indah', 'PT Astra Credit',
            'PT Adaro Energy', 'PT Bukit Asam', 'PT Medco Energi', 'PT PGN Tbk',
            'PT Waskita Karya', 'PT Wijaya Karya', 'PT Semen Gresik', 'PT Holcim Indonesia',
            'PT Astra DMS', 'PT Astra Graphia', 'PT Astra Component', 'PT Astra Logistics',
            'PT Astra Property', 'PT Astra Travel Service', 'PT Bank BCA', 'PT Bank BNI',
            'PT Bank BRI', 'PT CIMB Niaga', 'PT Danamon', 'PT Bank Syariah Indonesia',
            'PT PLN', 'PT Pertamina Geothermal', 'PT Pertamina Hulu Energi', 'PT Pertamina Refinery',
            'PT Freeport Indonesia', 'PT Newmont Nusa Tenggara', 'PT Vale Indonesia', 'PT Antam',
            'PT Timah', 'PT Inalum', 'PT Krakatau Steel', 'PT Pupuk Indonesia',
            'PT Petrokimia Gresik', 'PT Pupuk Kujang', 'PT Pupuk Sriwijaya', 'PT Semen Padang',
            'PT Semen Tonasa', 'PT Indocement', 'PT LafargeHolcim', 'PT Martina Berto',
            'PT Mustika Ratu', 'PT Sari Ayu', 'PT Tempo Scan', 'PT Soho Global Health',
            'PT Dexa Medica', 'PT Meprofarm', 'PT Kimia Farma', 'PT Biofarma',
            'PT Darya Varia', 'PT Taisho', 'PT Bayer Indonesia', 'PT Pfizer Indonesia',
            'PT Roche Indonesia', 'PT Novartis Indonesia', 'PT Sanofi', 'PT GSK Indonesia',
            'PT Merck', 'PT Abbott', 'PT Johnson & Johnson', 'PT Nestle Indonesia',
            'PT Coca-Cola Indonesia', 'PT PepsiCo', 'PT Danone', 'PT Frisian Flag',
            'PT Ultra Jaya', 'PT Sariwangi', 'PT Wings', 'PT Lion Air',
            'PT Garuda Indonesia', 'PT AirAsia', 'PT Sriwijaya Air', 'PT Citilink',
            'PT Pelni', 'PT ASDP', 'PT Jasa Marga', 'PT Transjakarta',
            'PT KAI', 'PT MRT Jakarta', 'PT LRT Jakarta', 'PT Angkasa Pura',
            'PT Telkomsel', 'PT XL Axiata', 'PT Smartfren', 'PT Tri',
            'PT IndiHome', 'PT Biznet', 'PT First Media', 'PT MyRepublic',
            'PT Gojek', 'PT Grab Indonesia', 'PT Shopee', 'PT Tokopedia',
            'PT Bukalapak', 'PT Traveloka', 'PT Tiket.com', 'PT OVO',
            'PT Dana', 'PT LinkAja', 'PT SeaBank', 'PT Bank Jago',
        ];
        $industries = ['Otomotif','Energi','Keuangan','Manufaktur','Telekomunikasi','Konstruksi','FMCG','Properti','Logistik','Pertambangan','Teknologi','Transportasi','Farmasi','Retail'];
        $clientModels = [];
        for ($i = 0; $i < 101; $i++) {
            $clientModels[] = Client::create([
                'company_name' => $companies[$i],
                'pic_name' => 'PIC ' . explode(' ', $companies[$i])[1],
                'phone' => '0812' . rand(10000000, 99999999),
                'email' => strtolower(str_replace(' ', '', explode(' ', $companies[$i])[1])) . '@company.co.id',
                'industry' => $industries[$i % 14],
                'tier' => ['platinum','gold','silver','bronze'][$i % 4],
                'status' => ['active','prospect','inactive'][$i % 3],
                'assigned_sales_id' => ($i < 34) ? $sales1->id : (($i < 68) ? $sales2->id : $sales3->id),
                'address' => 'Jl. Sudirman No. ' . ($i + 1) . ', Jakarta',
            ]);
        }

        // 30 Drivers
        $driverModels = [];
        $driverNames = ['Agus','Bambang','Cahyo','Dani','Eko','Faisal','Gunawan','Hadi','Irfan','Joko','Kurniawan','Lukman','Mulyadi','Narto','Oscar','Pandu','Qori','Rudi','Sandi','Teguh','Umar','Vino','Wahyu','Yusuf','Zainal','Ade','Bimo','Cakra','Dika','Ega'];
        foreach ($driverNames as $i => $name) {
            $driverModels[] = Driver::create([
                'name' => $name, 'phone' => '0813' . rand(10000000, 99999999),
                'license_number' => 'SIM-A-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => ['available','on_duty','off'][$i % 3],
            ]);
        }

        // 59 Vehicles (10 model Golden Bird)
        $vehicleModels = [];
        $fleetData = [
            ['brand' => 'veloz', 'model' => 'Toyota Veloz', 'capacity' => 6, 'tier' => 'standard', 'count' => 15],
            ['brand' => 'innova_zenix', 'model' => 'Toyota Innova Zenix', 'capacity' => 8, 'tier' => 'business', 'count' => 8],
            ['brand' => 'innova_reborn', 'model' => 'Toyota Innova Reborn', 'capacity' => 8, 'tier' => 'business', 'count' => 6],
            ['brand' => 'byd_m6', 'model' => 'BYD M6', 'capacity' => 7, 'tier' => 'premium', 'count' => 5],
            ['brand' => 'alphard', 'model' => 'Toyota Alphard', 'capacity' => 7, 'tier' => 'luxury', 'count' => 7],
            ['brand' => 'vellfire', 'model' => 'Toyota Vellfire', 'capacity' => 7, 'tier' => 'luxury', 'count' => 6],
            ['brand' => 'denza_d9', 'model' => 'Denza D9', 'capacity' => 7, 'tier' => 'luxury', 'count' => 4],
            ['brand' => 'bmw_i7', 'model' => 'BMW i7', 'capacity' => 5, 'tier' => 'executive', 'count' => 3],
            ['brand' => 'bmw_ix', 'model' => 'BMW iX', 'capacity' => 5, 'tier' => 'executive', 'count' => 2],
            ['brand' => 'genesis_g80', 'model' => 'Genesis G80', 'capacity' => 5, 'tier' => 'executive', 'count' => 3],
        ];
        $plateNum = 1001;
        foreach ($fleetData as $fleet) {
            for ($i = 0; $i < $fleet['count']; $i++) {
                $vehicleModels[] = Vehicle::create([
                    'plate_number' => 'B ' . $plateNum . ' GB',
                    'brand' => $fleet['brand'],
                    'model' => $fleet['model'],
                    'capacity' => $fleet['capacity'],
                    'year' => rand(2022, 2026),
                    'tier' => $fleet['tier'],
                    'status' => ['available','on_trip','maintenance','inactive'][$i % 4],
                    'pool_id' => $pools[$i % 5]->id,
                ]);
                $plateNum++;
            }
        }

        // 500 Bookings (2 tahun)
        $bookingModels = [];
        $statuses = ['pending','confirmed','on_trip','completed','completed','completed'];
        $destinations = ['Bandung','Surabaya','Yogyakarta','Semarang','Malang','Bali','Bogor','Depok','Tangerang','Bekasi','Cirebon','Tasikmalaya','Sukabumi','Purwakarta','Karawang'];
        for ($i = 0; $i < 500; $i++) {
            $daysAgo = rand(1, 730);
            $pickup = now()->subDays($daysAgo)->addHours(rand(6, 10));
            $vehicle = $vehicleModels[$i % 59];
            $client = $clientModels[$i % 101];
            $sales = ($i % 3 === 0) ? $sales1 : (($i % 3 === 1) ? $sales2 : $sales3);
            $driver = $driverModels[$i % 30];
            $price = rand(500, 15000) * 1000;

            $bookingNumber = 'GB-' . $pickup->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $bookingModels[] = Booking::create([
                'booking_number' => $bookingNumber,
                'client_id' => $client->id, 'sales_id' => $sales->id,
                'created_by' => $sales->id, 'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'pickup_datetime' => $pickup, 'dropoff_datetime' => $pickup->addHours(rand(2, 12)),
                'destination' => $destinations[$i % 15],
                'vehicle_type' => $vehicle->brand,
                'price' => $price,
                'status' => $statuses[$i % 6],
            ]);
        }

        // 200 Invoices
        $invoiceModels = [];
        $invoiceStatuses = ['draft','sent','paid','paid','paid','overdue'];
        for ($i = 0; $i < 200; $i++) {
            $booking = $bookingModels[$i % 500];
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

        // 134 Payments
        $paidInvoices = Invoice::where('status', 'paid')->get();
        for ($i = 0; $i < min(134, $paidInvoices->count()); $i++) {
            Payment::create([
                'payment_number' => 'PAY-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'invoice_id' => $paidInvoices[$i]->id,
                'amount' => $paidInvoices[$i]->amount,
                'method' => ['transfer','cash','giro'][$i % 3],
                'payment_date' => $paidInvoices[$i]->paid_at,
            ]);
        }

        // 50 Purchase Orders
        $vendors = ['Toko Sparepart Jaya','Bengkel Maju Motor','PT Oli Nusantara','CV Ban Indonesia','PT AC Mobil Sejahtera'];
        $items = ['Oli mesin 20L','Ban Michelin 205/55R16','Filter udara','Kampas rem','AC compressor','Wiper blade','Aki GS Astra','Lampu LED','Shock absorber','Timing belt'];
        for ($i = 0; $i < 50; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'vendor' => $vendors[$i % 5],
                'item_description' => $items[$i % 10] . ' (qty: ' . rand(5, 50) . ')',
                'amount' => rand(500, 10000) * 1000,
                'status' => ['pending','approved','received'][$i % 3],
            ]);
        }

        // 59 Maintenance Logs
        $types = ['routine','repair','modification'];
        $descs = ['Ganti oli mesin','Service AC','Ganti ban','Tune up','Ganti kampas rem','Cuci detail','Overhaul mesin','Ganti aki','Balance roda','Ganti filter'];
        for ($i = 0; $i < 59; $i++) {
            MaintenanceLog::create([
                'vehicle_id' => $vehicleModels[$i % 59]->id,
                'type' => $types[$i % 3],
                'description' => $descs[$i % 10],
                'cost' => rand(200, 5000) * 1000,
                'vendor' => $vendors[$i % 5],
                'scheduled_date' => now()->subDays(rand(1, 180)),
                'completed_date' => ($i % 3 !== 0) ? now()->subDays(rand(1, 90)) : null,
                'status' => ['completed','completed','scheduled'][$i % 3],
            ]);
        }

        // 100 Meeting Logs
        for ($i = 0; $i < 100; $i++) {
            $sales = ($i < 34) ? $sales1 : (($i < 68) ? $sales2 : $sales3);
            MeetingLog::create([
                'client_id' => $clientModels[$i % 101]->id,
                'sales_id' => $sales->id,
                'meeting_date' => now()->subDays(rand(1, 730))->addHours(rand(9, 15)),
                'notes' => 'Meeting pembahasan kontrak layanan transportasi',
                'outcome' => ['Positif - lanjut negosiasi','Perlu follow-up minggu depan','Client request proposal baru','Deal - kontrak diperpanjang','Pending keputusan manajemen'][$i % 5],
                'follow_up_date' => now()->addDays(rand(1, 30)),
                'status' => ['done','done','pending'][$i % 3],
            ]);
        }
    }
}
