<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Attachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Super Admin ──────────────────────────────────────────────────────
        $admin = User::create([
            'name'              => 'Əli Həsənov',
            'email'             => 'admin@techfix.az',
            'password'          => Hash::make('password'),
            'phone'             => '+994 50 111 00 01',
            'role'              => 'super_admin', // Spatie paketindən asılı olmayan düzgün sütun adı
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // ─── Support Agentlər ─────────────────────────────────────────────────
        $agentsData = [
            ['Nərmin Quliyeva',  'narmin@techfix.az',  '+994 51 222 00 01'],
            ['Tural Məmmədov',   'tural@techfix.az',   '+994 55 333 00 02'],
            ['Sevinc Əliyeva',   'sevinc@techfix.az',  '+994 70 444 00 03'],
        ];

        $agents = [];
        foreach ($agentsData as [$name, $email, $phone]) {
            $agents[] = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make('password'),
                'phone'             => $phone,
                'role'              => 'support_agent',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
        }

        // ─── Müştərilər ───────────────────────────────────────────────────────
        $customersData = [
            ['Kamran Babayev',   'kamran@gmail.com',    '+994 50 555 00 10'],
            ['Laman Hüseynova',  'laman@mail.ru',       '+994 55 666 00 11'],
            ['Rauf Rzayev',      'rauf@yahoo.com',      '+994 70 777 00 12'],
            ['Günel Süleymanova','gunel@outlook.com',   '+994 51 888 00 13'],
            ['Orxan Kərimov',    'orxan@gmail.com',     '+994 50 999 00 14'],
        ];

        $customers = [];
        foreach ($customersData as [$name, $email, $phone]) {
            $customers[] = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make('password'),
                'phone'             => $phone,
                'role'              => 'customer',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
        }

        // ─── Kateqoriyalar ────────────────────────────────────────────────────
        $categoriesData = [
            ['Hardware Xətaları',      'hardware-errors',  '🖥️', '#EF4444', 'Ana plata, RAM, ekran, klaviatura problemləri',          1],
            ['Software / OS',          'software-os',      '💻', '#3B82F6', 'Windows, driver, antivirus, sistem xətaları',             2],
            ['Zəmanət və Geri Ödəniş', 'warranty',          '🛡️', '#10B981', 'Zəmanət tələbləri, geri qaytarma, dəyişdirmə',           3],
            ['Şəbəkə / WiFi',          'network',          '📶', '#8B5CF6', 'İnternet bağlantısı, WiFi adapter, modem problemləri',    4],
            ['Batareya / Şarj',        'battery',          '🔋', '#F59E0B', 'Batareya ömrü, şarj olunmama, şarj cihazı',               5],
            ['Ekran / Display',        'display',          '🖥', '#06B6D4', 'Ekran çatlaması, piksel ölümü, parlaqlıq problemləri',    6],
        ];

        $categories = [];
        foreach ($categoriesData as [$name, $slug, $icon, $color, $desc, $order]) {
            $categories[] = Category::create([
                'name'        => $name,
                'slug'        => $slug,
                'icon'        => $icon,
                'color'       => $color,
                'description' => $desc,
                'sort_order'  => $order,
                'is_active'   => true,
            ]);
        }

        // ─── SLA Konfiqurasiyası ──────────────────────────────────────────────
        $slaConfigs = [
            ['urgent', 2,  24, 'Təcili',  '#EF4444'],
            ['high',   6,  48, 'Yüksək',  '#F97316'],
            ['medium', 12, 72, 'Orta',    '#3B82F6'],
            ['low',    24, 96, 'Aşağı',   '#6B7280'],
        ];
        foreach ($slaConfigs as [$priority, $response, $resolution, $label, $color]) {
            \DB::table('sla_configs')->insert([
                'priority'         => $priority,
                'response_hours'   => $response,
                'resolution_hours' => $resolution,
                'label_az'         => $label,
                'color'            => $color,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // ─── Demo Biletlər ────────────────────────────────────────────────────
        $ticketsData = [
            [
                'Dell XPS 15 laptopum açılmır',
                'Laptop qəfildən söndü və bir daha açılmır. Şarj işığı yanır ama ekran heç nə göstərmir. Zəmanət müddəti hələ davam edir.',
                0, 0, 0, 'urgent', 'in_progress', 1, false
            ],
            [
                'Windows 11 yeniləmə xətası 0x80070002',
                'Windows 11 yeniləməsini quraşdırmağa çalışanda bu xəta kodu ilə rastlaşıram. Bir neçə dəfə cəhd etdim ama olmadı.',
                1, 1, 1, 'high', 'waiting_customer', 2, false
            ],
            [
                'iPhone 14 ekranı çatladı, zəmanət tələbi',
                'Telefonum düşdü və ekran çatladı. Alış tarixindən 8 ay keçib, zəmanət hələ davam edir. Ekranı dəyişdirmək istəyirəm.',
                2, 2, 2, 'medium', 'in_service', 5, false
            ],
            [
                'MacBook Air WiFi adaptoru işləmir',
                'MacBook-un WiFi adapteri görünmür. Sistem ayarlarında WiFi seçimi tamamilə yox olub. Şəbəkə ayarlarını sıfırladım ama olmadı.',
                3, 3, 0, 'high', 'in_progress', 3, true
            ],
            [
                'Lenovo laptop batareyası 30 dəqiqəyə düşür',
                'Yeni aldığım Lenovo ThinkPad-in batareyası 6 ay ərzində çox zəiflədi. Tam şarjdan 30 dəqiqəyə düşür.',
                4, 4, 1, 'medium', 'new', 0, false
            ],
            [
                'Samsung monitoru işıq sızması problemi',
                'Samsung 27 düym monitorda sol alt küncdə işıq sızması var. Açıq rəng fonlarda çox aydın görünür.',
                5, 0, 2, 'low', 'resolved', 10, false
            ],
            [
                'ASUS ROG laptop ana plata problemi',
                'Laptop qəfildən kapasitəsini itirdi. Çox yavaşladı, oyunlarda FPS sıfıra düşür. Diaqnostika lazımdır.',
                0, 1, 0, 'urgent', 'in_service', 4, true
            ],
            [
                'Adobe Photoshop GPU xətası',
                'Photoshop açılarkən GPU sürətlənmə xətası verir. OpenGL/OpenCL dəstəyi yoxdur deyir.',
                1, 2, 1, 'low', 'waiting_agent', 7, false
            ],
        ];

        $ticketNum = 1;
        foreach ($ticketsData as $data) {
            [$title, $desc, $catIdx, $custIdx, $agentIdx, $priority, $status, $daysAgo, $violated] = $data;

            $createdAt   = now()->subDays($daysAgo)->subHours(rand(1, 8));
            $slaDeadline = $createdAt->copy()->addHours(12);

            $ticket = Ticket::create([
                'ticket_number'  => 'TF-' . date('Y') . '-' . str_pad($ticketNum++, 4, '0', STR_PAD_LEFT),
                'title'          => $title,
                'description'    => $desc,
                'category_id'    => $categories[$catIdx]->id,
                'customer_id'    => $customers[$custIdx]->id,
                'assigned_to'    => $agents[$agentIdx]->id,
                'priority'       => $priority,
                'status'         => $status,
                'sla_deadline'   => $slaDeadline,
                'is_sla_violated'=> $violated,
                'sla_violated_at'=> $violated ? $slaDeadline->addMinutes(30) : null,
                'device_model'   => $this->randomDevice(),
                'first_response_at' => $status !== 'new' ? $createdAt->copy()->addHours(rand(1, 3)) : null,
                'resolved_at'    => $status === 'resolved' ? $createdAt->copy()->addDays(2) : null,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt->copy()->addHours(rand(1, 5)),
            ]);

            $this->seedMessages($ticket, $customers[$custIdx], $agents[$agentIdx], $createdAt);
        }

        $this->command->info('✅ TechFix verilənlər bazası rəvan formada dolduruldu!');
        $this->command->table(
            ['Rol', 'E-mail', 'Şifrə'],
            [
                ['Super Admin',    'admin@techfix.az',  'password'],
                ['Support Agent',  'narmin@techfix.az', 'password'],
                ['Support Agent',  'tural@techfix.az',  'password'],
                ['Müştəri',        'kamran@gmail.com',  'password'],
            ]
        );
    }

    private function seedMessages(Ticket $ticket, User $customer, User $agent, Carbon $createdAt): void
    {
        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $customer->id,
            'message'     => $ticket->description,
            'sender_type' => 'customer',
            'created_at'  => $createdAt,
            'updated_at'  => $createdAt,
        ]);

        if ($ticket->status === 'new') return;

        $assignTime = $createdAt->copy()->addMinutes(rand(5, 30));
        TicketMessage::create([
            'ticket_id'        => $ticket->id,
            'user_id'          => $agent->id,
            'message'          => "👤 Bilet avtomatik olaraq **{$agent->name}** agentinə təyin edildi.",
            'sender_type'      => 'system',
            'is_system_message' => true,
            'system_event'     => 'assigned',
            'created_at'       => $assignTime,
            'updated_at'       => $assignTime,
        ]);

        $agentReplyTime = $assignTime->copy()->addMinutes(rand(30, 120));
        $agentReplies = [
            "Salam {$customer->name}, müraciətiniz qəbul edildi. Problemin detallarını araşdırırıq. Tezliklə sizinlə əlaqə saxlayacağıq.",
            "Hörmətli müştərimiz, sizin problemlə tanış oldum. Cihazınızın diaqnostikasını aparacağıq.",
            "Müraciətiniz üçün təşəkkür edirik. Texniki komandamız bu barədə işləyir.",
        ];

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $agent->id,
            'message'     => $agentReplies[array_rand($agentReplies)],
            'sender_type' => 'agent',
            'is_read'     => true,
            'read_at'     => $agentReplyTime->copy()->addMinutes(5),
            'created_at'  => $agentReplyTime,
            'updated_at'  => $agentReplyTime,
        ]);
    }

    private function randomDevice(): string
    {
        $devices = [
            'Dell XPS 15 9530', 'MacBook Air M2', 'Lenovo ThinkPad X1',
            'ASUS ROG Strix G15', 'HP Spectre x360', 'Samsung Galaxy Book3',
            'Acer Swift 5', 'Microsoft Surface Pro 9', 'iPhone 14 Pro',
        ];
        return $devices[array_rand($devices)];
    }
}