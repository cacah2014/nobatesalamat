<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Doctor;
use App\Models\SiteSetting;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- تنظیمات سایت (قابل ویرایش از پنل) ----------
        if (SiteSetting::count() === 0) {
            $settings = [
                ['site_name', 'نوبت سلامت', 'نام وب‌سایت', 'general'],
                ['support_phone', '۰۲۱-۹۱۰۹۹۰۹۰', 'شماره پشتیبانی', 'general'],
                ['hero_badge', 'همراه مطمئن سلامتی شما', 'نشان بالای صفحه', 'homepage'],
                ['hero_title', 'پزشک مناسب، نزدیک‌تر از همیشه', 'عنوان اصلی', 'homepage'],
                ['hero_description', 'از میان پزشکان معتبر جست‌وجو کنید، نظرات بیماران را بخوانید و نوبت خود را در چند دقیقه قطعی کنید.', 'توضیح اصلی', 'homepage'],
                ['stats_doctors', '+۲,۴۰۰', 'تعداد پزشکان', 'homepage'],
                ['stats_appointments', '+۱۸۰ هزار', 'تعداد نوبت‌ها', 'homepage'],
                ['stats_satisfaction', '۹۶٪', 'رضایت کاربران', 'homepage'],
                ['footer_description', 'سامانه هوشمند انتخاب پزشک و دریافت نوبت آنلاین؛ همیشه و همه‌جا کنار شما.', 'توضیح فوتر', 'footer'],
                ['instagram', 'nobatesalamat', 'اینستاگرام', 'social'],
                ['address', 'تهران، میدان ونک، خیابان ملاصدرا', 'نشانی دفتر', 'general'],
            ];

            foreach ($settings as [$key, $value, $label, $group]) {
                SiteSetting::create(compact('key', 'value', 'label', 'group'));
            }
        }

        // ---------- تخصص‌ها ----------
        if (Specialty::count() === 0) {
            $items = [
                ['قلب و عروق', 'cardiology', 'heart', 'rose', 'فشار خون، آریتمی و سلامت قلب'],
                ['پوست و مو', 'dermatology', 'sparkles', 'amber', 'درمان پوست، مو و زیبایی'],
                ['روان‌شناسی', 'psychology', 'brain', 'violet', 'مشاوره فردی و سلامت روان'],
                ['زنان و زایمان', 'gynecology', 'flower', 'pink', 'سلامت بانوان و بارداری'],
                ['کودکان', 'pediatrics', 'baby', 'sky', 'رشد و سلامت کودک'],
                ['ارتوپدی', 'orthopedics', 'bone', 'blue', 'مفاصل، ستون فقرات و آسیب‌ها'],
                ['چشم‌پزشکی', 'ophthalmology', 'eye', 'cyan', 'بینایی و بیماری‌های چشم'],
                ['داخلی', 'internal', 'stethoscope', 'teal', 'تشخیص و درمان بیماری‌های داخلی'],
            ];

            foreach ($items as $i => [$title, $slug, $icon, $color, $description]) {
                Specialty::create(compact('title', 'slug', 'icon', 'color', 'description') + [
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // ---------- پزشکان نمونه ----------
        if (Doctor::count() === 0) {
            $bySlug = Specialty::pluck('id', 'slug');

            $doctors = [
                ['دکتر آرمان فرهمند', 'dr-arman-farahmand', 'cardiology', 'متخصص قلب و عروق و فشار خون', 'تهران', 'تهران، سعادت‌آباد، میدان کاج', 4.9, 286, 285000, 18, 'امروز، ساعت ۱۸:۳۰', 'https://images.pexels.com/photos/37870065/pexels-photo-37870065.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620', true, 'عضو انجمن قلب ایران با بیش از ۱۸ سال تجربه در تشخیص و درمان بیماری‌های قلبی.', '۱۲۸۴۵۶'],
                ['دکتر نگار آذری', 'dr-negar-azari', 'dermatology', 'متخصص پوست، مو و زیبایی', 'تهران', 'تهران، پاسداران، بوستان هفتم', 4.8, 194, 320000, 12, 'فردا، ساعت ۱۰:۰۰', 'https://images.pexels.com/photos/6749765/pexels-photo-6749765.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620', false, 'پزشک متخصص پوست و مو با رویکرد درمانی مبتنی بر شواهد و تجربه درمان موفق مراجعان.', '۱۵۳۲۱۰'],
                ['دکتر سارا نیک‌پی', 'dr-sara-nikpey', 'psychology', 'روان‌شناس بالینی و مشاور خانواده', 'شیراز', 'شیراز، بلوار چمران، مجتمع پزشکی چمران', 4.9, 321, 260000, 14, 'امروز، ساعت ۲۰:۰۰', 'https://images.pexels.com/photos/32115905/pexels-photo-32115905.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620', true, 'روان‌شناس بالینی، درمانگر اضطراب و مشاور خانواده با جلسات حضوری و آنلاین.', 'ر-۸۶۳۱'],
                ['دکتر فرزاد نادری', 'dr-farzad-naderi', 'orthopedics', 'جراح و متخصص ارتوپدی', 'اصفهان', 'اصفهان، چهارباغ بالا، مجتمع سپهر', 4.7, 168, 295000, 16, 'شنبه، ساعت ۱۶:۱۵', 'https://images.pexels.com/photos/6129573/pexels-photo-6129573.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=800&w=620', false, 'جراح ارتوپدی با تمرکز بر درمان آسیب‌های ورزشی، زانو و ستون فقرات.', '۱۱۷۹۰۲'],
            ];

            foreach ($doctors as [$fullName, $slug, $specialtySlug, $title, $city, $address, $rating, $reviewsCount, $visitPrice, $experienceYears, $nextAvailable, $imageUrl, $isOnline, $about, $medicalCode]) {
                Doctor::create([
                    'full_name' => $fullName,
                    'slug' => $slug,
                    'specialty_id' => $bySlug[$specialtySlug],
                    'title' => $title,
                    'city' => $city,
                    'address' => $address,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'visit_price' => $visitPrice,
                    'experience_years' => $experienceYears,
                    'next_available' => $nextAvailable,
                    'image_url' => $imageUrl,
                    'is_online' => $isOnline,
                    'is_active' => true,
                    'about' => $about,
                    'medical_code' => $medicalCode,
                ]);
            }
        }

        // ---------- مقالات ----------
        if (Article::count() === 0) {
            Article::insert([
                ['title' => 'چطور پزشک مناسب خود را انتخاب کنیم؟', 'slug' => 'choose-right-doctor', 'excerpt' => '۵ معیار ساده برای انتخاب آگاهانه پزشک و داشتن یک تجربه درمانی بهتر.', 'image_url' => 'https://images.pexels.com/photos/5452201/pexels-photo-5452201.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900', 'read_time' => 6, 'is_published' => true, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'راهنمای چکاپ‌های ضروری سالانه', 'slug' => 'annual-checkups', 'excerpt' => 'آزمایش‌ها و بررسی‌هایی که متناسب با سن بهتر است هر سال انجام شوند.', 'image_url' => 'https://images.pexels.com/photos/4226256/pexels-photo-4226256.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900', 'read_time' => 8, 'is_published' => true, 'created_at' => now(), 'updated_at' => now()],
                ['title' => 'ویزیت آنلاین برای چه مواقعی مناسب است؟', 'slug' => 'online-visit-guide', 'excerpt' => 'چه مشکلاتی را می‌توان با مشاوره آنلاین پیگیری کرد و چه زمانی باید حضوری مراجعه کنیم؟', 'image_url' => 'https://images.pexels.com/photos/7195310/pexels-photo-7195310.jpeg?auto=compress&cs=tinysrgb&fit=crop&h=520&w=900', 'read_time' => 5, 'is_published' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // ---------- مدیر ارشد ----------
        // شماره(های) ADMIN_PHONE با نقش super_admin ساخته می‌شوند.
        foreach (admin_phones() as $adminPhone) {
            User::firstOrCreate(
                ['phone' => $adminPhone],
                ['full_name' => 'مدیر ارشد نوبت سلامت', 'role' => User::ROLE_SUPER, 'status' => 'active', 'permissions' => []]
            );
        }
    }
}
