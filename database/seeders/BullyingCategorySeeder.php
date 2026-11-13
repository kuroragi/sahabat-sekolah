<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BullyingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Kategori Perundungan Komprehensif
        $categories = [
            [
                'code' => 'PHYSICAL',
                'name' => 'Perundungan Fisik',
                'description' => 'Tindakan kekerasan fisik, kontak fisik tanpa persetujuan, atau intimidasi tubuh.',
                'status' => true,
            ],
            [
                'code' => 'VERBAL',
                'name' => 'Perundungan Verbal',
                'description' => 'Ucapan, julukan merendahkan, makian, atau ancaman lisan yang melukai psikologis.',
                'status' => true,
            ],
            [
                'code' => 'EXTORTION',
                'name' => 'Pemalakan & Pengompasan',
                'description' => 'Aksi meminta uang, makanan, atau barang secara paksa disertai ancaman atau tekanan.',
                'status' => true,
            ],
            [
                'code' => 'SOCIAL',
                'name' => 'Perundungan Relasional & Sosial',
                'description' => 'Pengucilan terencana, penyebaran rumor palsu, dan sabotase hubungan pertemanan.',
                'status' => true,
            ],
            [
                'code' => 'CYBER',
                'name' => 'Cyberbullying (Perundungan Daring)',
                'description' => 'Intimidasi, teror siber, peretasan akun, atau perundungan via media sosial / aplikasi pesan.',
                'status' => true,
            ],
            [
                'code' => 'SEXUAL',
                'name' => 'Perundungan Bernuansa Seksual',
                'description' => 'Pelecehan fisik, verbal, atau visual bernuansa seksual yang merendahkan martabat peserta didik.',
                'status' => true,
            ],
            [
                'code' => 'DISCRIMINATION',
                'name' => 'Perundungan Diskriminatif (SARA & Fisik)',
                'description' => 'Pengejekan atau perlakuan diskriminatif berbasis suku, agama, ras, disabilitas, atau kondisi ekonomi.',
                'status' => true,
            ],
            [
                'code' => 'OTHER',
                'name' => 'Bentuk Lainnya',
                'description' => 'Tindakan perundungan atau kekerasan di sekolah yang belum tercakup pada kategori di atas.',
                'status' => true,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('bullying_categories')->updateOrInsert(
                ['code' => $cat['code']],
                array_merge($cat, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $catIds = DB::table('bullying_categories')->pluck('id', 'code');

        // 2. Subkategori Lengkap beserta Default Risk Level & Risk Score yang Proper
        $subcategories = [
            // --- PERUNDUNGAN FISIK ---
            [
                'category_id' => $catIds['PHYSICAL'],
                'code' => 'PUSH_TRIP',
                'name' => 'Mendorong, menjegal, atau menabrak sengaja',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 35,
            ],
            [
                'category_id' => $catIds['PHYSICAL'],
                'code' => 'HIT_KICK',
                'name' => 'Memukul, menampar, atau menendang',
                'default_risk_level' => 'HIGH',
                'risk_score' => 65,
            ],
            [
                'category_id' => $catIds['PHYSICAL'],
                'code' => 'MOBBING_PHYSICAL',
                'name' => 'Pengeroyokan / pemukulan berkelompok',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 85,
            ],
            [
                'category_id' => $catIds['PHYSICAL'],
                'code' => 'WEAPON_OBJECT',
                'name' => 'Penyerangan dengan benda tajam / benda tumpul berbahaya',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 95,
            ],
            [
                'category_id' => $catIds['PHYSICAL'],
                'code' => 'CONFINEMENT',
                'name' => 'Penyekapan di toilet / kelas / gudang sekolah',
                'default_risk_level' => 'HIGH',
                'risk_score' => 70,
            ],

            // --- PERUNDUNGAN VERBAL ---
            [
                'category_id' => $catIds['VERBAL'],
                'code' => 'MOCKERY_NICKNAME',
                'name' => 'Hinaan atau julukan merendahkan (nama orang tua/kondisi)',
                'default_risk_level' => 'LOW',
                'risk_score' => 20,
            ],
            [
                'category_id' => $catIds['VERBAL'],
                'code' => 'PUBLIC_HUMILIATION',
                'name' => 'Mempermalukan dan membentak di depan umum',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 35,
            ],
            [
                'category_id' => $catIds['VERBAL'],
                'code' => 'VERBAL_THREAT',
                'name' => 'Ancaman kekerasan atau intimidasi verbal',
                'default_risk_level' => 'HIGH',
                'risk_score' => 60,
            ],
            [
                'category_id' => $catIds['VERBAL'],
                'code' => 'SLANDER_DEFAMATION',
                'name' => 'Fitnah atau tuduhan keji tanpa bukti',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 40,
            ],

            // --- PEMALAKAN & PENGOMPASAN ---
            [
                'category_id' => $catIds['EXTORTION'],
                'code' => 'EXTORT_MONEY',
                'name' => 'Pengompasan / pemalakan uang jajan harian',
                'default_risk_level' => 'HIGH',
                'risk_score' => 60,
            ],
            [
                'category_id' => $catIds['EXTORTION'],
                'code' => 'FORCED_TASK',
                'name' => 'Pemaksaan mengerjakan PR / tugas / piket pelaku',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 35,
            ],
            [
                'category_id' => $catIds['EXTORTION'],
                'code' => 'TAKING_PROPERTY',
                'name' => 'Penyitaan paksa / perusakan barang pribadi korban',
                'default_risk_level' => 'HIGH',
                'risk_score' => 65,
            ],
            [
                'category_id' => $catIds['EXTORTION'],
                'code' => 'EXTORT_THREAT',
                'name' => 'Pemerasan rutin dengan ancaman teror kekerasan',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 80,
            ],

            // --- PERUNDUNGAN SOSIAL & RELASIONAL ---
            [
                'category_id' => $catIds['SOCIAL'],
                'code' => 'EXCLUSION',
                'name' => 'Pengucilan / boikot sosial dari pergaulan dan tugas kelompok',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 35,
            ],
            [
                'category_id' => $catIds['SOCIAL'],
                'code' => 'RUMOR_SPREADING',
                'name' => 'Penyebaran gosip / rumor yang merusak nama baik',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 40,
            ],
            [
                'category_id' => $catIds['SOCIAL'],
                'code' => 'INSTIGATION',
                'name' => 'Menghasut teman sekelas untuk menjauhi / memusuhi korban',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 45,
            ],

            // --- CYBERBULLYING ---
            [
                'category_id' => $catIds['CYBER'],
                'code' => 'CYBER_HARASSMENT',
                'name' => 'Komentar kebencian / spam teror di akun medsos korban',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 45,
            ],
            [
                'category_id' => $catIds['CYBER'],
                'code' => 'DOXXING_LEAK',
                'name' => 'Penyebaran data pribadi / rahasia korban (Doxxing)',
                'default_risk_level' => 'HIGH',
                'risk_score' => 65,
            ],
            [
                'category_id' => $catIds['CYBER'],
                'code' => 'CYBER_IMPERSONATION',
                'name' => 'Pembuatan akun palsu untuk merusak reputasi korban',
                'default_risk_level' => 'HIGH',
                'risk_score' => 60,
            ],
            [
                'category_id' => $catIds['CYBER'],
                'code' => 'PHOTO_SHAMING',
                'name' => 'Penyebaran foto/video editan yang mempermalukan korban',
                'default_risk_level' => 'HIGH',
                'risk_score' => 70,
            ],
            [
                'category_id' => $catIds['CYBER'],
                'code' => 'CYBER_DEATH_THREAT',
                'name' => 'Ancaman pembunuhan / kekerasan ekstrem via media digital',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 90,
            ],

            // --- PERUNDUNGAN BERNUANSA SEKSUAL ---
            [
                'category_id' => $catIds['SEXUAL'],
                'code' => 'CATCALL_VERBAL_SEXUAL',
                'name' => 'Catcalling / lelucon / komentar bernuansa seksual',
                'default_risk_level' => 'HIGH',
                'risk_score' => 60,
            ],
            [
                'category_id' => $catIds['SEXUAL'],
                'code' => 'NON_CONSENSUAL_TOUCH',
                'name' => 'Sentuhan fisik pada area pribadi tanpa persetujuan',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 85,
            ],
            [
                'category_id' => $catIds['SEXUAL'],
                'code' => 'SEXUAL_EXPLOITATION',
                'name' => 'Pemaksaan mengirim foto/video tidak senonoh (KSBO)',
                'default_risk_level' => 'CRITICAL',
                'risk_score' => 95,
            ],

            // --- DISKRIMINASI & SARA ---
            [
                'category_id' => $catIds['DISCRIMINATION'],
                'code' => 'SARA_MOCKERY',
                'name' => 'Pelecehan / penghinaan terhadap suku, agama, atau ras',
                'default_risk_level' => 'HIGH',
                'risk_score' => 65,
            ],
            [
                'category_id' => $catIds['DISCRIMINATION'],
                'code' => 'ABILITY_MOCKERY',
                'name' => 'Perundungan terhadap siswa berkebutuhan khusus / disabilitas',
                'default_risk_level' => 'HIGH',
                'risk_score' => 70,
            ],
            [
                'category_id' => $catIds['DISCRIMINATION'],
                'code' => 'ECONOMIC_SHAMING',
                'name' => 'Mengejek latar belakang ekonomi / kondisi keluarga',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 35,
            ],

            // --- BENTUK LAINNYA ---
            [
                'category_id' => $catIds['OTHER'],
                'code' => 'GENERAL_OTHER',
                'name' => 'Bentuk perundungan lainnya yang belum tertera',
                'default_risk_level' => 'MEDIUM',
                'risk_score' => 30,
            ],
        ];

        foreach ($subcategories as $sub) {
            DB::table('bullying_subcategories')->updateOrInsert(
                [
                    'category_id' => $sub['category_id'],
                    'code' => $sub['code'],
                ],
                array_merge($sub, [
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}

