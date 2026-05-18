<div align="center">

# ⚡ TechFix Kürdəmir

### İT Xidmət Mərkəzləri üçün Ağıllı Dəstək İdarəetmə Sistemi

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)

<br/>

> **TechFix Kürdəmir** — İT servis mərkəzləri və texniki dəstək şirkətləri üçün nəzərdə tutulmuş,
> müştəri müraciətlərinin qəbulu, prioritetləşdirilməsi və intellektual idarə edilməsi sistemidir.

</div>

---

## 📚 Akademik Kontekst

Bu layihə **Kompüter Elmləri** ixtisası üzrə **Proqramlaşdırma Texnologiyaları** fənni çərçivəsində kurs işi kimi hazırlanmışdır.

**Tələbə:** İbrahim Rəhimov
**Fənn:** Proqramlaşdırma Texnologiyaları

### 🎯 Tapşırığın tələbləri

| # | Tələb | Status |
|---|-------|--------|
| 1 | Müraciətlərin yaradılması və idarə edilməsi | ✅ |
| 2 | Prioritet və status sistemi | ✅ |
| 3 | Agent (ekspert) təyini | ✅ |
| 4 | Müştəri-ekspert cavablaşması | ✅ |
| 5 | SLA xidmət müddəti anlayışı | ✅ |
| 6 | Fayl əlavə etmə | ✅ |
| 7 | Admin üçün kateqoriya idarəetməsi | ✅ |
| 8 | Hesabat paneli | ✅ |

---

## 🎯 Layihə haqqında

> *"Texniki dəstək şirkətlərində müraciətlər e-poçt və ya telefon ilə qəbul edilir — bu isə idarəetməni çətinləşdirir, SLA-nı pozur və müştəri məmnuniyyətini azaldır."*

**TechFix Kürdəmir** həmin prosesi avtomatlaşdırır, şəffaf edir və ölçülə bilən hala gətirir.

---

## ✨ Əsas xüsusiyyətlər

### 🎫 Müraciət İdarəetməsi
- Müraciət yaratma, izləmə, bağlama
- Prioritet — `Aşağı` `Orta` `Yüksək` `Təcili`
- Status axını — `Yeni → Baxılır → Müştəri Cavab Gözləyir → Həll Olundu`
- Fayl əlavəsi (JPG, PNG, PDF — maks. 5MB)
- Avtomatik müraciət nömrəsi (`TF-2026-XXXX`)

### 🤖 Auto-Assignment
- Müraciət ekspertlər arasında iş yüküne görə avtomatik bölünür
- Manual təyin (Admin tərəfindən)

### ⏱️ SLA Tracking
| Prioritet | Müddət |
|-----------|--------|
| 🔴 Təcili | 2 saat |
| 🟠 Yüksək | 6 saat |
| 🟡 Orta | 12 saat |
| 🔵 Aşağı | 24 saat |

### 💬 Cavablaşma sistemi
- Müştəri ↔ Ekspert mesajlaşması
- Sistem bildirişləri (status dəyişiklikləri)
- AJAX ilə real-vaxt mesaj göndərmə

### 📊 Admin Panel
- KPI kartları, müraciət trendi, kateqoriya paylanması
- Ekspert performans analizi
- Kateqoriya CRUD idarəetməsi
- İstifadəçi və rol idarəetməsi (Spatie Permission)

### 👤 Müştəri Paneli
- Müştəri yalnız öz müraciətlərini görür
- Filtr, axtarış, yeni müraciət

---

## 🛠️ Texnologiyalar

| Qat | Texnologiya |
|-----|-------------|
| **Backend** | Laravel 12, PHP 8.2 |
| **Frontend** | Tailwind CSS 3, Alpine.js 3, Chart.js 4 |
| **Verilənlər bazası** | MySQL 8 |
| **Auth & Roles** | Laravel Breeze + Spatie Permission |
| **Real-time** | AJAX (Fetch API) |
| **Fayl saxlama** | Laravel Storage (public disk) |
| **UI** | Glassmorphism, Dark Mode |

---

## 🚀 Quraşdırma

```bash
# 1. Klonlayın
git clone https://github.com/irahimov/techfix-kurdemir.git
cd techfix-kurdemir

# 2. Asılılıqlar
composer install
npm install

# 3. Mühit
cp .env.example .env
php artisan key:generate

# 4. .env faylında DB məlumatlarını yazın
DB_DATABASE=techfix_kurdemir
DB_USERNAME=root
DB_PASSWORD=your_password

# 5. Migrasiya
php artisan migrate --seed

# 6. Storage
php artisan storage:link

# 7. Server
php artisan serve
npm run dev
```

### 🔑 Default hesablar

| Rol | E-poçt | Şifrə |
|-----|--------|-------|
| Super Admin | admin@techfix.az | password |
| Mütəxəssis | agent@techfix.az | password |
| Müştəri | customer@techfix.az | password |

---

## 👥 İstifadəçi rolları

```
Super Admin
├── Bütün müraciətlərə baxış
├── Ekspert təyini
├── Kateqoriya idarəetməsi
└── Hesabat paneli

Support Agent
├── Öz müraciətlərinə baxış
├── Status yeniləmə
└── Müştəri ilə cavablaşma

Customer
├── Müraciət yaratma
├── Öz müraciətlərini izləmə
└── Cavab göndərmə
```

---

## 🏗️ Struktur

```
techfix-kurdemir/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php
│   │   ├── TicketController.php
│   │   └── Auth/
│   ├── Models/
│   │   ├── Ticket.php
│   │   ├── TicketMessage.php
│   │   ├── Category.php
│   │   └── User.php
│   └── Services/
│       ├── SlaService.php
│       └── AutoAssignmentService.php
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── admin/
│   └── tickets/
└── routes/web.php
```

---

## 👨‍💻 Müəllif

<div align="center">

**İbrahim Rəhimov**

*Computer Science Student | Laravel Developer | ML/DL Enthusiast*

[![GitHub](https://img.shields.io/badge/GitHub-irahimov-181717?style=for-the-badge&logo=github)](https://github.com/irahimov)
[![Profile](https://img.shields.io/badge/Profile-github.com/irahimov-0969DA?style=for-the-badge&logo=github)](https://github.com/irahimov)

*🇦🇿 Kürdəmir, Azərbaycan*

</div>

---

<div align="center">

*Proqramlaşdırma Texnologiyaları fənni üzrə kurs işi · Made with ❤️ in Azerbaijan*

</div>

---

## 📄 Lisenziya

Bu layihə **MIT License** altında lisenziyalanmışdır — istədiyiniz kimi istifadə edə, dəyişdirə və paylaşa bilərsiniz.

```
MIT License — Copyright (c) 2026 İbrahim Rəhimov
```

---

> **📍 Niyə "Kürdəmir"?**
> Çünki dostum Kürdəmirlidir. Başqa səbəb yoxdur. İT infrastrukturu, SLA analizi, auto-assignment alqoritmləri — bunların hamısı var. Amma əsl səbəb budur. 🫡
