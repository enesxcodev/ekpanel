# Görev Günlüğü

Bu dosya modül keşfi, planlama, uygulama, kaynak staging ve kullanıcı onaylı release aşamalarının tek yetkili durum kaynağıdır. Otomasyon ve kullanıcı tarafından başlatılan her modül işi aynı günlüğe kaydedilir. Otomasyon keşfe başlamadan önce bu dosyayı okur.

## Güncel kilit ve cooldown

- `active_lock`: `none` veya etkin görev kimliği. Yeni modül işi/discovery, değer `none` değilse başlamaz.
- `last_module_completion_at`: son modül işinin tamamlanma zamanı (ISO 8601, yerel Europe/Istanbul offset'iyle). Release açıkça istenmişse ilgili release doğrulaması ve push tamamlanmadan bu değer son completion sayılmaz.
- `last_module_task_id`: yukarıdaki completion zamanına ait görev.
- `baseline_discovery_completed_at`: ilk tam katalog taramasının tamamlanma zamanı. Boşsa sonraki uygun discovery tam baseline taramasıdır.
- `last_discovery_completion_at`: en son discovery run'ının bitiş zamanı. Sonraki run'lar yeni adayları arar; özet/planlarda bulunan adayı yinelemez.

Kilit sahibi işi başlatmadan önce günlüğe kaydeder, `active_lock` alanını görev kimliğine ayarlar ve her aşama değişiminde `updated_at` değerini yeniler. Başarıda `status: completed`, `lock: released`, `completed_at` kaydedilir ve global kilit `none` yapılır. Engellenmiş/askıda ama devam etmesi beklenen işte kilit tutulur. Stale kilidi otomasyon kendiliğinden temizlemez; görev sahibi işi tamamlayıp veya açıkça iptal edip günlüğü günceller.

Yeni discovery, `active_lock: none` olduktan ve son modül görevinin `completed_at` zamanından en az iki saat geçtikten sonra başlayabilir. `last_module_completion_at` release aşaması tamamlanırsa o completion zamanına taşınır; release talep edilmediyse uygulama, test/build ve kaynak staging tamamlandığı zamandır. Discovery kayıtları modül completion saatini sıfırlamaz.

## Kayıt şablonu

Her discovery veya modül görevi için yeni bir kayıt ekle. Durum ve aşama değiştikçe aynı kaydı güncelle; geçmiş tamamlanmış kayıtları silme.

```markdown
### TASK-YYYYMMDD-HHMM-kisa-ad
- origin: automated-discovery | user-started
- task_type: discovery | planning | implementation | release
- module: <modül adı, discovery için candidate-scan>
- status: in_progress | waiting_approval | blocked | completed | cancelled
- lock: held | released
- started_at: YYYY-MM-DDTHH:mm:ss+03:00
- updated_at: YYYY-MM-DDTHH:mm:ss+03:00
- completed_at: <ISO 8601 veya boş>
- relevant_tests: <en dar ilgili komut/yol ve sonuç; henüz çalışmadıysa pending; dokümantasyon işi için n/a>
- build_results: <gerekli build/asset kontrolü ve sonuç veya n/a>
- source_staging: <pending | completed | blocked | n/a> — <tam hedef yol ve kopya zamanı>
- release_requested: <no | yes> — <yes ise açık talep zamanı ve kısa bağlam>
- release_package: <not_requested | pending | complete | blocked> — <ZIP yolu, sürüm, SHA-256>
- source_push: <not_requested | pending | complete | blocked> — <repo, branch, commit>
- github_release: <not_requested | pending | complete | blocked> — <tag ve asset URL>
- catalog_update: <not_requested | pending | complete | blocked> — <catalog path, id/version/url/hash>
- catalog_push: <not_requested | pending | complete | blocked> — <repo, branch, commit>
- remote_verification: <not_requested | pending | passed | failed> — <kontrol edilen URL/sonuç>
- notes: <aday kararı, engel veya kısa tamamlanma özeti>
- catalog_snapshot: <catalog.json SHA-256 veya last-write; discovery için>
- modules_reviewed: <tam baseline için modül adları; sonraki taramalar için değişen/ilgili modüller>
- rules_skills_reviewed: <incelenen ilgili rules/skills yolları veya kısa kayıt>
```

## Aktif kayıt

- `active_lock`: `TASK-20261021-1912-store-implementation`
- `last_module_completion_at`: `none`
- `last_module_task_id`: `none`
- `baseline_discovery_completed_at`: `2026-10-21T18:50:05+03:00`
- `last_discovery_completion_at`: `2026-10-21T18:50:05+03:00`
- `last_discovery_task_id`: `TASK-20261021-1848-full-baseline`

## Kayıtlar

### TASK-20261021-1912-store-implementation
- origin: user-started
- task_type: implementation
- module: Store
- status: in_progress
- lock: held
- started_at: 2026-10-21T19:12:19+03:00
- updated_at: 2026-10-21T19:35:00+03:00
- completed_at:
- relevant_tests: pending
- build_results: pending
- source_staging: pending — D:\localhost\ekAgentModules\Store
- release_requested: no
- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- notes: Store uygulaması başlatıldı. Store kendi satılabilir ürün kayıtlarının sahibi olacak; Products zorunlu bağımlılığı, ilişki ve örtük çift yönlü senkronizasyon eklenmeyecek. Gerçek ödeme sağlayıcısı ve taşıyıcı entegrasyonları Onay Gerektiren İşler kaydına göre Bekliyor durumunda kalacak. Kullanıcı kararları: varsayılan para birimi TL/TRY; tutarlar ölçeklenebilir biçimde kuruş tamsayısı ve üç harfli para koduyla saklanacak. Verginin fiyata dahil olması ve misafir checkout Store Settings bölümünden ayarlanacak; Store kapalıyken Mağaza ayar sekmesi görünmeyecek. Store ayar seeder'ı, etkinlik görünürlüğü feature testi, temel ürün CRUD formu, temel sepet ve yerel sipariş servisleri eklendi; ayar testi geçti. Varyant/medya yönetimi, kategori/sipariş paneli, ziyaretçi ekranları ve geniş kapsam test/build henüz sürüyor.
- catalog_snapshot: 8537e58d3ce0e2ae03cb5e6db7922a3ba046064e835500bad0ddeeaf5e005c29
- modules_reviewed: Products, Blog, Users
- rules_skills_reviewed: AGENTS.md, .ai/rules/index.md, moduller.md, mimari-kod-yapisi.md, yetkilendirme.md, admin-arayuz.md, frontend.md, frontend-blade.md, settings.md, .ai/workflows/modul-gelistirme.md, .claude/skills/theme-integration/SKILL.md, .claude/skills/testing-best-practices/SKILL.md, Modules/Plans/Store/Store - Yeni.md

### TASK-20261021-1848-full-baseline
- origin: user-started
- task_type: discovery
- module: full-catalog-baseline
- status: completed
- lock: released
- started_at: 2026-10-21T18:48:01+03:00
- updated_at: 2026-10-21T18:50:05+03:00
- completed_at: 2026-10-21T18:50:05+03:00
- relevant_tests: n/a — dokümantasyon ve keşif kaydı
- build_results: n/a
- source_staging: n/a
- release_requested: no
- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- notes: İlk tam baseline tamamlandı. Products kurallarının ticaret kapsamını açıkça dışlaması nedeniyle Store doğrulandı; diğer modüllerden yeni aday kanıt eşiğini geçmedi. Aday ayrıntıları README.md'de, imzalar ve değerlendirmeler Keşif Durumu.json dosyasında.
- catalog_snapshot: 8537e58d3ce0e2ae03cb5e6db7922a3ba046064e835500bad0ddeeaf5e005c29
- modules_reviewed: Menus, Blog, Brand, Documents, Faqs, Galeriler, Pages, Projects, Sector, Services, Slider, Team, Testimonials, Popup, Products, Users (16)
- rules_skills_reviewed: 33 rules/skills dosyası; ayrıntılı yollar ve SHA-256 imzaları Modules/Plans/Keşif Durumu.json içinde.
