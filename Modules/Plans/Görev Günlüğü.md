# Görev Günlüğü

Bu dosya modül keşfi, planlama, uygulama, kaynak staging ve kullanıcı onaylı release aşamalarının tek yetkili durum kaynağıdır. Otomasyon ve kullanıcı tarafından başlatılan her modül işi aynı günlüğe kaydedilir. Otomasyon keşfe başlamadan önce bu dosyayı okur.

## Güncel kilit ve zamanlama

- `active_lock`: `none` veya etkin görev kimliği. Yeni modül işi/discovery, değer `none` değilse başlamaz.
- `last_module_completion_at`: son modül işinin tamamlanma zamanı (ISO 8601, yerel Europe/Istanbul offset'iyle). Release açıkça istenmişse ilgili release doğrulaması ve push tamamlanmadan bu değer son completion sayılmaz.
- `last_module_task_id`: yukarıdaki completion zamanına ait görev.
- `baseline_discovery_completed_at`: ilk tam katalog taramasının tamamlanma zamanı. Boşsa sonraki uygun discovery tam baseline taramasıdır.
- `last_discovery_completion_at`: en son discovery run'ının bitiş zamanı. Sonraki run'lar yeni adayları arar; özet/planlarda bulunan adayı yinelemez.

Kilit sahibi işi başlatmadan önce günlüğe kaydeder, `active_lock` alanını görev kimliğine ayarlar ve her aşama değişiminde `updated_at` değerini yeniler. Başarıda `status: completed`, `lock: released`, `completed_at` kaydedilir ve global kilit `none` yapılır. Engellenmiş/askıda ama devam etmesi beklenen işte kilit tutulur. Stale kilidi otomasyon kendiliğinden temizlemez; görev sahibi işi tamamlayıp veya açıkça iptal edip günlüğü günceller.

Otomasyon her gün Türkiye saatiyle 00:15'te başlar ve aynı gün en fazla iki yeni modül işini sırayla yürütür. İkinci iş 00:45'ten önce başlayamaz; ilk iş o saatte sürüyorsa tamamlanana kadar bekler. Aynı günlük çalışmanın iki işi arasında iki saat bekleme yoktur. Etkin kilit her durumda yeni işi engeller. `last_module_completion_at`, release talep edilmediyse uygulama, test/build ve kaynak staging tamamlandığı zamandır; release talep edilmişse release doğrulaması ve push tamamlandığında güncellenir.

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

- `active_lock`: `none`
- `last_module_completion_at`: `2026-10-21T21:42:24+03:00`
- `last_module_task_id`: `TASK-20261021-1912-store-implementation`
- `baseline_discovery_completed_at`: `2026-10-21T18:50:05+03:00`
- `last_discovery_completion_at`: `2026-10-22T08:28:53+03:00`
- `last_discovery_task_id`: `TASK-20261022-0827-oneoff-discovery`

## Kayıtlar

### TASK-20261022-0832-branches-plan
- origin: user-started
- task_type: planning
- module: Branches (Şubeler/Bayiler)
- status: completed
- lock: released
- started_at: 2026-10-22T08:31:22+03:00
- updated_at: 2026-10-22T08:34:54+03:00
- completed_at: 2026-10-22T08:34:54+03:00
- relevant_tests: n/a — planlama
- build_results: n/a
- source_staging: n/a
- release_requested: no




- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- rules_skills_reviewed: .ai/rules/index.md, Proje.md, moduller.md, mimari-kod-yapisi.md, admin-arayuz.md, yetkilendirme.md, Konum rule+skill, related module/workflow rules
- plan: Modules/Plans/Branches/Branches - Yeni.md
- notes: Kullanıcı Kariyer/Etkinlik önerileri yerine Şubeler/Bayiler seçti ve Türkiye Konum'a bağlı çalışma istedi. Konum rule/skill okundu; modül ortak depoda var, yayın kataloğunda yok. Birleşik Şubeler/Bayiler planı hazırlandı; tek kayıt modelinde Şube/Bayi kategorisi, Konum bağımlılığı ve Konum katalog önkoşulu işlendi. Kullanıcı kapsam kararı bekleniyor; kodlama/görsel/test/build/staging yapılmadı.


### TASK-20261022-0827-oneoff-discovery
- origin: user-started
- task_type: discovery
- module: candidate-scan
- status: completed
- lock: released
- started_at: 2026-10-22T08:27:52+03:00
- updated_at: 2026-10-22T08:28:53+03:00
- completed_at: 2026-10-22T08:28:53+03:00
- relevant_tests: n/a — keşif
- build_results: n/a
- source_staging: n/a
- release_requested: no
- catalog_snapshot: 8537e58d3ce0e2ae03cb5e6db7922a3ba046064e835500bad0ddeeaf5e005c29
- modules_reviewed: Team, Blog, Services; baseline scope summaries for others; Forms boundary
- rules_skills_reviewed: Team rule+skill; Blog rule+skill; Services rule+skill; Forms scope unknown from current documentation
- notes: Kullanıcı isteğiyle bu oturum için tek seferlik discovery. İki yeni öneri: Kariyer/İş İlanları ve Etkinlikler; orta-düşük/düşük-orta güven, kullanıcı talebi varsayım. Forms başvuru kapsamı rule/skill belgelerinden çözülemedi; plan öncesi netleştirilmeli. KVKK girdisinde yeni kanıt yok. Kullanıcı değerlendirmesine dek planlama/uygulama bekletildi.


### TASK-20261021-2238-two-suggestions
- origin: user-started
- task_type: discovery
- module: candidate-scan
- status: completed
- lock: released
- started_at: 2026-10-21T22:38:38+03:00
- updated_at: 2026-10-21T22:41:50+03:00
- completed_at: 2026-10-21T22:41:50+03:00
- relevant_tests: n/a — keşif
- build_results: n/a
- source_staging: n/a
- release_requested: no
- notes: Güncellenen keşif istemi gpt-6-luna low ile çalıştı. Randevu ve Şubeler/Bayiler orta güvenli öneriler; talep kanıtı değil CMS amacı ve modül kapsamlarından çıkarım. KVKK girdisi kapsam doğrulaması bekliyor. Kullanıcı randevunun yalnız nasıl planlanacağını istedi; gpt-6.1-sol low plan görüşü tamamlandı ve kullanıcıya sunuldu. Tek takvim/kapasite 1, admin onayı ve admin iptali öneri; uygulama kararı değil. Kodlama, görsel, test/build/staging başlatılmadı. Gerçek scheduler run-now aracı yok; iş akışı bu oturumda yürütülüyor.

### TASK-20261021-2226-cms-gap-review
- origin: user-started
- task_type: discovery
- module: candidate-scan
- status: completed
- lock: released
- started_at: 2026-10-21T22:26:11+03:00
- updated_at: 2026-10-21T22:26:11+03:00
- completed_at: 2026-10-21T22:26:11+03:00
- relevant_tests: n/a — kapsam değerlendirmesi
- build_results: n/a
- source_staging: n/a
- release_requested: no
- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- notes: Kilit başlangıçta none idi. İmza değişikliği olmamasına rağmen CMS kapsam boşlukları baseline 16 modül rol haritası üzerinden yeniden değerlendirildi. Kullanıcı girdisi "KBKK ayarları" olarak kaydedilmiş; olası "KVKK ayarları" ihtiyacı incelendi. Settings tekil/sabit yapılandırılabilir alanları, Pages gizlilik metinlerini kapsayabilir; operasyonel rıza/çerez tercihleri/veri sahibi başvurusu/saklama iş akışları mevcut kapsam belgelerinden doğrulanmadı. Yeni bağımsız modül doğrulanmadı; Privacy/KVKK girdisi kapsam ve kullanım kanıtı bekliyor. Kod incelemesi yapılmadı; dış servis/API yok.
- catalog_snapshot: 8537e58d3ce0e2ae03cb5e6db7922a3ba046064e835500bad0ddeeaf5e005c29
- modules_reviewed: baseline role/scope assessments for all 16 modules; focused Settings and Users boundary review
- rules_skills_reviewed: .ai/rules/settings.md; D:\localhost\ekAgentModules\Users\.ai/rules/tema-entegrasyonu.md; D:\localhost\ekAgentModules\Users/skills/users-theme-integration/SKILL.md; README candidate input and workflow
### TASK-20261021-2218-incremental-discovery
- origin: user-started
- task_type: discovery
- module: candidate-scan
- status: completed
- lock: released
- started_at: 2026-10-21T22:18:47+03:00
- updated_at: 2026-10-21T22:18:47+03:00
- completed_at: 2026-10-21T22:18:47+03:00
- relevant_tests: n/a — keşif kaydı
- build_results: n/a
- source_staging: n/a
- release_requested: no
- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- notes: Incremental karşılaştırmada katalog SHA-256 baseline ile aynı; 16 katalog metadata kaydı ve kayıtlı rules/skills imzaları eşleşti. Yeni/değişmiş modül olmadığından derin inceleme gerekmeyen yeni modül adayı doğrulanmadı. Store tamamlandığı için yeniden adaylaştırılmadı. Günlük rapora aday yok sonucu eklendi.
- catalog_snapshot: 8537e58d3ce0e2ae03cb5e6db7922a3ba046064e835500bad0ddeeaf5e005c29
- modules_reviewed: none — 16 unchanged
- rules_skills_reviewed: imza kontrolü — 16 modülün baseline kayıtlı rules/skills yolları; değişiklik yok. İlgili kural anahtarları .ai/rules altında tarandı.

### TASK-20261021-1912-store-implementation
- origin: user-started
- task_type: implementation
- module: Store
- status: completed
- lock: released
- started_at: 2026-10-21T19:12:19+03:00
- updated_at: 2026-10-21T21:42:24+03:00
- completed_at: 2026-10-21T21:42:24+03:00
- relevant_tests: passed — Store 21 test; Store + ModuleInstallationQueueTest + ModuleManagementTest toplam 60 test; SettingsRegressionTest + ProductsCatalogTest + Blog 66 test. Son checkout anahtarı değişikliği hedef testle ayrıca geçti.
- build_results: passed — Pint, Blade view:cache, npm run build; admin/public tarayıcı kontrolü, 390px varyant ekranı taşmasız, console error yok. Build'de mevcut fontaine/login-wallpaper/chunk uyarıları devam ediyor.
- source_staging: completed — D:\localhost\ekAgentModules\Store — 2026-10-21T21:42:24+03:00; tüm kaynak dosyaları SHA-256 ile eşleştirildi.
- release_requested: no
- release_package: not_requested
- source_push: not_requested
- github_release: not_requested
- catalog_update: not_requested
- catalog_push: not_requested
- remote_verification: not_requested
- notes: Yeniden planlanan yerel Store kapsamı uygulandı. Products isteğe bağlı tek seferlik kapak/galeri/içerik aktarımı; bağımsız ürün, slug, medya kullanım referansları, beş sekme, ilk kayıt/edit varyantları, SKU/kombinasyon ve stok, teslimat/SEO, Store Settings, queue etkinleştirme, vergi/para, sepet, misafir/hesap checkout, idempotent sipariş, snapshot, iptal/rezervasyon süresi, manuel ödeme/takip ve erişim kontrolleri tamamlandı. Skill/rule/StoreFrontendService belgeleri güncel. Ortak ModuleManager opt-in ensure_on_enable, Settings modül yokluğu/regex doğrulaması ve SEO metadata koruması da uygulamada güncellendi. Lifecycle testinde dış süreçler fake; gerçek seeder komut sınırından çalışır. Tarayıcıda tam misafir ödeme veya gerçek ZIP kurulumu yapılmadı; checkout feature testinde doğrulandı. Scheduler rezervasyon süresi işi için gereklidir. Gerçek ödeme/kargo ve release kapsamı onay bekliyor. Plan: Modules/Plans/Store/Store - Tamamlandı.md.
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