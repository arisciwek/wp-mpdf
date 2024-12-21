# WP mPDF

WordPress Plugin untuk mengintegrasikan mPDF library ke WordPress. Plugin ini menyediakan integrasi lengkap dengan mPDF versi 8.2.5, memungkinkan Anda untuk menggunakan semua fitur yang tersedia di mPDF library asli.

mPDF adalah library PHP untuk menghasilkan file PDF dari HTML dengan encoding UTF-8.

Versi ini menyertakan [FPDI 2.6.2](https://www.setasign.com/products/fpdi/about/) dari Setasign 
untuk mendukung fungsi import PDF.

[![Latest Stable Version](https://poser.pugx.org/mpdf/mpdf/v/stable)](https://packagist.org/packages/mpdf/mpdf)
[![Total Downloads](https://poser.pugx.org/mpdf/mpdf/downloads)](https://packagist.org/packages/mpdf/mpdf)
[![License](https://poser.pugx.org/mpdf/mpdf/license)](https://packagist.org/packages/mpdf/mpdf)


> ⚠ Jika Anda melihat file ini di halaman GitHub repository mPDF atau di Packagist, perlu diketahui bahwa
> branch default 'development' mungkin berbeda dari rilis stabil terakhir.


## Deskripsi

WP mPDF adalah plugin WordPress yang mengintegrasikan library mPDF secara penuh. Plugin ini dirancang untuk memberikan akses ke semua fitur mPDF dalam lingkungan WordPress, menjadikannya solusi PDF yang paling lengkap untuk WordPress.

### Fitur Utama
- Konversi HTML ke PDF dengan dukungan penuh untuk CSS
- Integrasi dengan WP Document Generator (WP DocGen)
  - Convert dokumen yang di-generate oleh WP DocGen ke PDF
  - Mendukung semua template dari WP DocGen
  - Preservasi styling dan formatting dari dokumen WP DocGen
- Support Unicode untuk multiple language
- Custom header dan footer
- Watermark dan background
- Custom font support
- Table of Contents generation
- Bookmarks
- Dan semua fitur lainnya yang tersedia di mPDF 8.2.5

### Integrasi dengan WP DocGen

Plugin ini terintegrasi secara penuh dengan [WP Document Generator (WP DocGen)
](https://github.com/arisciwek/wp-docgen), memungkinkan Anda untuk:
- Mengkonversi dokumen yang dihasilkan WP DocGen langsung ke format PDF
- Mempertahankan semua formatting dan styling dari dokumen asli
- Menambahkan header, footer, dan watermark khusus pada dokumen yang dikonversi
- Menggunakan semua fitur mPDF untuk meningkatkan hasil konversi dokumen

Untuk menggunakan fitur ini, pastikan Anda telah menginstal dan mengaktifkan WP DocGen di WordPress Anda.

## Requirements

- PHP 7.4 atau lebih tinggi
- WordPress 5.8 atau lebih tinggi
- Write permission pada direktori upload WordPress

## Instalasi

### Instalasi mPDF 
1. Download atau clone repository ini ke direktori `wp-content/plugins/` WordPress Anda
2. **PENTING**: Download mPDF library
   ```bash
   # Buat folder libs/mpdf di dalam direktori plugin
   mkdir -p wp-content/plugins/wp-mpdf/libs/mpdf

   # Download mPDF v8.2.5 dari GitHub
   wget https://github.com/mpdf/mpdf/archive/refs/tags/v8.2.5.zip

   # Extract file ke folder libs/mpdf
   unzip v8.2.5.zip
   cp -r mpdf-8.2.5/* wp-content/plugins/wp-mpdf/libs/mpdf/
   ```
   Atau Anda bisa download manual dari [mPDF GitHub Release v8.2.5](https://github.com/mpdf/mpdf/releases/tag/v8.2.5)


### Instalasi FPDI
----------------

Paket ini sudah menyertakan FPDI 2.6.2. File-file FPDI harus berada di: ```bash libs/mpdf/fpdi/```

Jika direktori tersebut tidak ada, Anda perlu:

1. Download FPDI 2.6.2 dari [FPDI releases](https://github.com/Setasign/FPDI/releases/tag/v2.6.2)
2. Buat direktori `libs/mpdf/fpdi/`
3. Ekstrak file-file FPDI ke dalam direktori tersebut
4. Pastikan struktur berikut ada:

libs/mpdf/
├── fpdi/
│   └── src/
│       └── FpdiTrait.php



Setelah lengkap ktifkan plugin melalui menu 'Plugins' di WordPress

## Struktur Folder

```
wp-mpdf/
├── includes/
│   ├── class-wp-mpdf.php
│   ├── class-wp-mpdf-loader.php
│   └── class-wp-mpdf-activator.php
├── libs/
│   └── mpdf/           # Folder ini di-ignore oleh git, harus diisi manual
│       ├── src/
│       └── ... (file mPDF lainnya)
├── README.md
├── wp-mpdf.php
└── .gitignore
```

## Penggunaan

```php
// Generate PDF dari HTML
$mpdf = wp_mpdf()->generate_pdf($html, [
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 16,
    'margin_bottom' => 16
]);
```

## Credits

Plugin ini menggunakan [mPDF Library](https://github.com/mpdf/mpdf) yang dikembangkan oleh:
- Original Author: Ian Back
- Current Maintainers: Matěj Humpál, Johannes Goslar, dan kontributor lainnya
- License: GNU GPL v2+
- Version: 8.2.5

mPDF adalah proyek open source yang fantastis yang memungkinkan pembuatan PDF dari HTML dengan dukungan CSS yang kuat. Terima kasih kepada semua kontributor mPDF yang telah membuat library ini menjadi sangat powerful.

## License

- Plugin License: GPL v2 atau yang lebih baru
- mPDF Library License: GNU GPL v2+

## Author

- Plugin Author: arisciwek
- Email: arisciwek@gmail.com
- Website: http://example.com

## Changelog

### 1.0.0 - 2024-12-21
- Initial release
- Integrasi penuh dengan mPDF 8.2.5
- Sistem autoloading untuk mPDF library
- Manajemen temporary files
- Support untuk semua fitur mPDF
