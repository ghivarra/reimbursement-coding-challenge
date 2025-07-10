# Reimbursement Coding Challenge
Aplikasi pengajuan reimbursement simpel dengan menggunakan Laravel 12 &amp; VueJS 3

## Dev Dependency
- PHP minimal versi 8.3
- NodeJS versi LTS
- Database MySQL atau MariaDB

## Cara Instalasi
- Download atau clone repository
- Pindah pada root folder/aplikasi
- Jalankan command `bash install.sh`
- Sesuaikan konfigurasi file `.env` terutama database
- Jalankan command `bash migrate.sh`
- Jalankan command `composer run dev` maka aplikasi akan bisa diakses pada `http://localhost:8000` atau `http://127.0.0.1:8000`

## Keperluan Demo

Untuk keperluan demo saya menyiapkan 4 user dengan role berbeda-beda dengan nama yang tentunya saya karang sendiri *(tidak nyata)*.

1. User Superadmin - Ghivarra
    - email: gsenandika@gmail.com
    - password: user12345

2. User Admin - Bayu Aji
    - email: bayu.aji@ghivarra.com
    - password: User*12345

3. User Employee - Seno Susilo
    - email: seno.susilo@ghivarra.com
    - password: User*12345

4. User Manajer - Angga Restu
    - email: angga@ghivarra.com
    - password: User*12345

## Postman Collection & Documentation

Postman Collection bisa didownload pada folder dokumentasi atau [di sini](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/Code%20Challenge%20-%20Reimbursement.postman_collection.json)

## Arsitektur Database

![Full Database](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/all-tables.png)

Setidaknya arsitektur terbagi menjadi dua, yakni bagian **Role Management** dan **Proses Bisnis** yang mengacu pada flow reimbursment.

## Arsitektur Role Management

![Role Management](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/role-management-tables.png)

Di sini saya menggunakan tabel **role_module_list** dan **role_menu_list** agar memudahkan dan memanfaatkan sifat relasional database SQL secara seutuhnya. Sehingga meminimalisir adanya penyimpanan data berbentuk teks. 

Di mana **role_module_list** adalah tabel yang berisi list modul yang boleh diakses oleh suatu role.

Dan **role_menu_list** adalah tabel yang berisi list menu yang muncul dan boleh diakses oleh suatu role.

Hal ini dilakukan untuk mengurangi 'hard code' atau kode keras pada logic aplikasi sehingga membuat aplikasi lebih dinamis dan mudah dikembangkan.

## Arsitektur Proses Bisnis

![Proses Bisnis](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/reimbursement-tables.png)

Di sini saya menggunakan beberapa tabel yang dipisahkan salah satunya adalah untuk catatan/log **reimbursements_logs** dan status **reimbursements_statuses**

Seperti dijelaskan sebelumnya, pemisahan tabel ini sejalan dengan sifat relasional SQL yang mengutamakan pemisahan data dan merelasikan antar tabel ketimbang disatukan dalam satu tabel besar.

Sekali lagi, hal ini juga dilakukan sesuai prinsip untuk mengurangi 'hard code' atau kode keras pada logic aplikasi. Khususnya karena masing-masing status memiliki ID dan template sendiri untuk generate log/catatan.

## Flow Proses Bisnis

![Flow Probis](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/flow-proses-bisnis.png)

Saya menerapkan flow yang tidak begitu rumit. Jadi user masuk ke halaman login dan melakukan otentikasi yang kemudian dilakukan validasi apakah otentikasinya valid/tidak valid.

Lalu kemudian user yang berhasil login akan diidentifikasi role-nya sebagai admin, employee, atau manajer.

 - Apabila sebagai **Admin**, maka dia hanya bisa memantau proses bisnis aplikasi.
 - Apabila sebagai **Employee**, maka dia bisa melakukan proses bisnis dari awal sampai akhir dari mulai membuat/merevisi pengajuan, lalu menghapus pengajuan.
 - Apabila sebagai **Manager**, maka dia hanya bisa melakukan persetujuan ditolak/diterima/dikembalikannya pengajuan dari Employee dan memantau proses bisnis yang masuk ke akunnya.
 
## Flow Data dari Backend ke Frontend dan Sebaliknya

![Flow Data BE-FE](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/flow-backend-frontend.png)

Saya menerapkan flow arsitektur modern dengan meminimalisir adanya request yang dilakukan user selain tampilan atau view menggunakan perpindahan halaman. Alias AJAX-heavy website menggunakan Vue JS 3 SSR (Server-Side Rendering) yang didukung oleh Laravel 12.

Hal ini memudahkan saya fokus ke tampilan tanpa perlu banyak menerapkan logic rumit pada controller dan memindahkan kerumitan di controller ke frontend. Selain memindahkan kerumitan, tentunya memindahkan beban kalkulasi/processing juga kepada frontend.

## Flow Data Input Reimbursement

![Flow Input Data](https://github.com/ghivarra/reimbursement-coding-challenge/blob/main/Dokumentasi/gambar/flow-input-data.png)

Untuk proses/teknik input dari pengajuan reimbursement itu sendiri, saya menerapkan proses mengirim menggunakan XHR atau AJAX ke Backend yang kemudian sebagian datanya disimpan di database oleh Backend. 

Namun ada beberapa data yang perlu menggunakan fitur Scheduler atau Queue, yakni:

1. **Generate Nomor Pengajuan Reimbursement**

Karena dalam penerapan nyatanya reimbursement ini dekat dengan administrasi, maka saya memutuskan ada yang namanya nomor menyerupai nomor surat. Karena admin sangat suka sekali dengan excelnya terutama apabila untuk nomor yang berurutan dan nomor di depannya pun berguna untuk menghitung jumlah pengajuan di bulan terkait. 

Contohnya 00001/TRNX/REIMBURSE/VII/2025 di mana 00001 adalah nomor urutan di bulan tersebut yang kembali, TRNX adalah kode Kategori reimbursement, REIMBURSE adalah kode reimbursement, VII adalah kode bulan, dan 2025 adalah tahun.

Nah, untuk nomor yang berurutan tentunya perlu dihindari yang namanya *racing condition* dan kondisi balapan tersebut bisa dihindari dengan menerapkan fitur Queue atau Scheduler.

2. **Kirim Notifikasi Email ke Manager**

Ini adalah hal yang umum diterapkan pada aplikasi, yakni menggunakan queue untuk mengirim email. Selain memberikan kesan tanpa menunggu dan realtime bagi pengguna saat input data, hal ini juga meringankan beban proses server

## Fitur lainnya

- UUID pada id pengajuan reimbursement untuk menghindari 'guess URI attack' yang tentunya sebetulnya sudah dimitigasi dengan manajemen role user, tapi untuk menambah keamanan, dan estetika URL.

- Catatan menggunakan template pada masing-masing status. Serta secara dinamis bisa berubah alias tidak 'hard code'.

- Admin bisa merestorasi file yang dihapus oleh Employee.

- Ada halaman CRUD Kategori Reimbursement yang hanya bisa diakses oleh Admin.

- Fitur Ubah Kata Sandi untuk user.

- Fitur Dark/Light Mode.