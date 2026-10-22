---
name: appointments-theme-integration
description: Randevu teması verisini Appointments modülünden eşlemek için kullan.
---

# Appointments

## Ne zaman kullanılır

Tema içinde randevu, rezervasyon, görüşme veya saat seçimi bölümü varsa bu beceriyi kullan. `Appointment` modelinin alanlarını ve modül etkinliğini koddan doğrula.

## Veri sınırı

Ziyaretçi formu müşteri adı, iletişim, hizmet ve başlangıç zamanını kullanabilir. `status` ve `notes` yönetim alanıdır. Temada sabit bir takvim tanıtımı varsa kayıtlı randevularla çakışmadığını varsayma; bu modül ilk sürümde çalışma takvimi üretmez.

## Uygulama

Mevcut tema bileşenleri ve responsive düzen kullanılır. Modül kapalıysa randevu verisi sorgulanmaz. Yeni alan gerektiğinde önce `features.json` ve ortak modül kurallarını değerlendir.
