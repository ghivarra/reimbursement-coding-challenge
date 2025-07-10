# reimbursement-coding-challenge
Aplikasi pengajuan reimbursement simpel dengan menggunakan Laravel 12 &amp; VueJS 3

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