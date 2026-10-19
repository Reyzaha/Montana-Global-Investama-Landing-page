# AGENTS.md — Aturan Coding Flutter (Gaya Standar Tim)

Aturan ini berlaku untuk **semua aplikasi Flutter** yang saya bangun dengan gaya yang sama (BLoC + Dio + go_router + easy_localization + package internal `base`).
Placeholder: `<app>` = nama package aplikasi (field `name` di `pubspec.yaml`), `<Feature>` = nama fitur PascalCase, `<feature>` = snake_case, `<Action>` = nama aksi PascalCase (`Load`, `Submit`, `Delete`, ...).

> **Helper wajib** (Bagian 10A) harus ada di setiap proyek.
> Bagian yang spesifik per aplikasi ditandai **[ISI PER PROYEK]**. Jika belum terisi, agent harus membaca kode yang ada atau bertanya, jangan menebak.

---

## 0. Cara Kerja Agent

1. **Pelajari dulu, tulis kemudian.** Sebelum menulis kode, baca modul terdekat yang sudah ada (halaman list, form, detail) dan tiru strukturnya. Jika proyek masih kosong, ikuti template di dokumen ini apa adanya.
2. Ubah seminimal mungkin. Jangan refactor, rename, atau memformat ulang kode di luar permintaan.
3. **Pakai helper wajib (Bagian 10A) sebelum menulis utilitas baru.** Jangan membuat ulang fungsi yang sudah ada di helper.
4. **Jangan menambah package** di `pubspec.yaml` tanpa persetujuan. Pakai package yang sudah ada, terutama package internal `base` (`package:base/base.dart`).
5. Jangan menyentuh file generated atau sensitif: `firebase_options.dart`, `GeneratedPluginRegistrant.*`, `*.g.dart`, `.svn/`, `.git/`, `android/`, `ios/`, `*.jks`, `google-services.json`, `GoogleService-Info.plist`, `pubspec.lock` (kecuali diminta).
6. Selesai menulis, jalankan `flutter analyze` dan pastikan tidak ada warning baru dari kode Anda.
7. Balas pengguna dalam Bahasa Indonesia. Identifier kode dalam Bahasa Inggris. Komentar kode minimal; hanya untuk logika yang tidak jelas.
8. Jika ada pola yang tidak konsisten di kode lama, **ikuti pola dominan di dokumen ini**, jangan menyalin ketidakkonsistenan.

---

## 1. Stack Standar

| Kebutuhan | Pilihan |
|---|---|
| State management | `flutter_bloc` (`Bloc`, bukan `Cubit`) |
| HTTP | `dio` (lewat satu kelas `ApiManager`) |
| Navigasi | `go_router` |
| Lokalisasi | `easy_localization` (default `en` + `id`) |
| Tanggal | `jiffy` (bukan `DateTime` untuk nilai yang tampil/dikirim ke API) |
| UI kit & util | `package:base/base.dart` (package internal) |
| Lokal storage | `Preferences` (Bagian 10A) |
| Overlay loading | `loader_overlay` |
| Bentuk kartu | `smooth_corner` (`SmoothRectangleBorder`) |
| Animasi/empty state | `lottie` |
| Font | Manrope, diatur di `ThemeData` |
| Material | Material 3 (`useMaterial3: true`) |

- `get` (GetX) **hanya** dipakai untuk `Get.key` (navigator key) dan `Get.context`. Jangan dipakai untuk state atau navigasi.
- `provider` hanya untuk `ChangeNotifier` kecil (mis. `Timers`).
- Tanpa `Equatable` pada event/state kecuali sudah dipakai di proyek tersebut.
- Dependensi dasar mengikuti proyek referensi; versi dikunci eksak (tanpa `^`) bila proyek referensi melakukannya.

Pada proyek berbasis `base`, komponen UI dari `base` yang harus dipakai (untuk proyek tanpa `base`, padanannya adalah helper di Bagian 10A) sebelum membuat sendiri: `BaseScaffold`, `BaseAppBar`, `BaseBodyStatus`, `BaseWidgets` (`text`, `spinner`, `month`, `labelValue`, `shimmer`, `noData`, `loadingFail`), `BaseOverlays` (`error`, `success`), `BaseDialogs` (`confirmation`, `month`), `BaseSheets` (`dateRange`, `imagePreview`, `spinner`), `Dimensions`, `AppColors`, `BasePreferences`, `BaseScrollBehavior`, `BaseHttpOverrides`, `BaseEncodingInterceptor`.

---

## 2. Struktur Folder

```
lib/
  api/
    api_manager.dart                # semua pemanggilan API (static)
    endpoint/<endpoint>/            # model request/response per endpoint
    interceptor/
      authorization_interceptor.dart
  constant/                         # enum & konstanta (api_url, preference_key, status, ...)
  helper/                           # formats, generals, preferences, timers, notifications, ...
  model/                            # model global (mis. account)
  module/
    front/<feature>/                # sebelum login (sign_in, verify_pin, ...)
    rear/<feature>/                 # setelah login
      <feature>_bloc.dart
      <feature>_event.dart
      <feature>_state.dart
      <feature>_page.dart
  router_config.dart
  shared.dart                       # state global statis
  main.dart
assets/
  font/  image/  lottie/  i18n/{en,id}.json
```

- Satu fitur = satu folder dengan **4 file**: `bloc`, `event`, `state`, `page` (halaman tanpa logika jaringan boleh hanya `page`).
- Nama file `snake_case`; prefix file = nama folder fitur.
- Halaman yang butuh login → `module/rear/`; yang tidak → `module/front/`.
- Halaman pencarian dipisah sebagai fitur sendiri: `<feature>_search` (mis. `order_list` + `order_search`).
- Pasangan umum: `<feature>_list`, `<feature>_detail`, `<feature>_search`, `create_<feature>`.

---

## 3. Gaya Dart

Aturan di bagian ini harus selaras dengan `analysis_options.yaml` (lihat Bagian 11).

- **Tanda kutip ganda** `"..."` selalu. Interpolasi sederhana tanpa kurung kurawal: `"$name"`; ekspresi: `"${a.b}"`.
- **Hanya `package:` import**, tanpa import relatif: `import "package:<app>/...";`
- Import diurutkan alfabetis; `dart:` di atas, lalu seluruh `package:` (termasuk `base`, `<app>`, `flutter`) tercampur alfabetis.
- **Trailing comma wajib** pada argumen/parameter multi-baris.
- Selalu tulis tipe return fungsi.
- Body `if`/`else`/`for` selalu pakai `{}` dan di baris baru. Hindari `if (x) return;` satu baris pada kode baru.
- `required` named parameter di depan; `super.key` paling akhir pada constructor widget.
- `child:` / `children:` paling akhir dalam constructor widget.
- Gunakan `SizedBox` untuk jarak (bukan `Container` kosong).
- Tidak memakai `final` pada parameter fungsi.
- Satu deklarasi per baris. Indentasi 2 spasi, format `dart format`.
- Tipe lokal eksplisit untuk objek penting: `Response response = await ...;`, `Dio dio = await getDio();`. `final` untuk hasil olahan: `final items = ...;`.
- Constructor widget dan model: named parameter, satu parameter per baris, trailing comma:

```dart
const ItemPage({
  required this.id,
  super.key,
});
```

- `// ignore_for_file:` hanya di baris paling atas file dan hanya untuk aturan yang benar-benar dilanggar file itu (mis. `use_build_context_synchronously`, `always_specify_types`, `depend_on_referenced_packages`). Jangan menambah ignore "berjaga-jaga".

---

## 4. BLoC

### 4.1 Event — `<feature>_event.dart`

```dart
abstract class <Feature>Event {}

class <Feature>Load extends <Feature>Event {
  final int id;

  <Feature>Load({
    required this.id,
  });
}
```

- Base: `abstract class <Feature>Event {}`.
- Nama event: `<Feature><Action>`.
- Field `final`; constructor named + `required` + trailing comma.

### 4.2 State — `<feature>_state.dart`

Setiap aksi memiliki **tiga state berpola tetap**, ditambah satu `Initial` per bloc:

```dart
abstract class <Feature>State {}

class <Feature>Initial extends <Feature>State {}

class <Feature>LoadLoading extends <Feature>State {}

class <Feature>LoadSuccess extends <Feature>State {
  final List<Item> items;

  <Feature>LoadSuccess({
    required this.items,
  });
}

class <Feature>LoadFinished extends <Feature>State {}
```

- Pola: `<Feature><Action>Loading` → `<Feature><Action>Success` → `<Feature><Action>Finished`.
- Hanya `...Success` yang membawa data.

### 4.3 Bloc — `<feature>_bloc.dart`

```dart
class <Feature>Bloc extends Bloc<<Feature>Event, <Feature>State> {
  <Feature>Bloc() : super(<Feature>Initial()) {
    on<<Feature>Load>((event, emit) async {
      try {
        emit(<Feature>LoadLoading());

        Response response = await ApiManager.<feature>(id: event.id);

        if (response.statusCode == 200) {
          emit(<Feature>LoadSuccess(items: ...));
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(<Feature>LoadFinished());
      }
    });
  }
}
```

Aturan:
- Satu `on<Event>` per aksi; semua didaftarkan di constructor.
- Urutan: `emit(...Loading)` → panggil `ApiManager` → cek `response.statusCode == 200` → `emit(...Success)`.
- **Selalu** `try / catch / finally`. `catch` → `if (kDebugMode) { print(e); }` lalu `BaseOverlays.error(message: "something_wrong_please_try_again".tr())`. `finally` → `emit(...Finished)`.
- Respons non-2xx ditangani interceptor (Bagian 5.4) yang menampilkan error dan mengembalikan status `218`; **bloc tidak menampilkan error ganda** untuk kasus itu.
- Daftar kosong: emit `...Success` dengan list `[]`.
- Parsing response dilakukan di bloc memakai model di `api/endpoint/`.
- Bloc baru **wajib didaftarkan** di `MultiBlocProvider` pada `main.dart`: `BlocProvider(create: (BuildContext context) => <Feature>Bloc())`.
- Import lazim di bloc: `base`, `api_manager`, model endpoint, event, state, `dio`, `easy_localization`, `flutter/foundation`, `flutter_bloc`.
- Aksi submit boleh meng-`emit(...Loading)` sebelum `try` bila tidak ada kebutuhan lain, tetapi `finally → Finished` tetap wajib.

---

## 5. API Layer

### 5.1 Enum path — `constant/api_url.dart`

```dart
// ignore_for_file: non_constant_identifier_names, constant_identifier_names

enum ApiUrl {
  SIGN_IN("sign-in"),
  ITEM("items");

  final String path;

  const ApiUrl(this.path);
}
```

- Nama enum `SCREAMING_SNAKE_CASE`, path `kebab-case` dan jamak untuk resource.
- Base URL dibaca dari konfigurasi (mis. preferensi `SERVER_URL` dengan fallback default). Jangan hardcode kredensial. Validasi URL kustom harus memakai fungsi yang namanya benar dan benar-benar dipanggil.

### 5.2 `ApiManager`

```dart
class ApiManager {
  static Future<Dio> getDio() async {
    Dio dio = Dio(
      BaseOptions(
        baseUrl: MAIN_BASE,
        connectTimeout: const Duration(seconds: 60),
        receiveTimeout: const Duration(seconds: 60),
        contentType: Headers.jsonContentType,
        receiveDataWhenStatusError: false,
        validateStatus: (status) => status != null,
        responseDecoder: (responseBytes, options, responseBody) { ... },
      ),
    );

    dio.interceptors.add(BaseEncodingInterceptor());
    dio.interceptors.add(AuthorizationInterceptor());
    dio.interceptors.add(LogInterceptor(...));

    return dio;
  }

  static Future<Response> itemDetail({
    required int id,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.get(
      "${ApiUrl.ITEM.path}/$id",
    );

    return response;
  }
}
```

- Semua pemanggilan HTTP **hanya** di `ApiManager` sebagai `static Future<Response>`. Bloc/page tidak memanggil `Dio` langsung.
- Nama method `camelCase`: `signIn`, `itemList`, `itemDetail`, `createItem`.
- `GET` → `queryParameters: {...}`; `POST`/`PUT` → `data: Formats.convert(request.toJson())`; upload → `FormData`.
- Path dengan id: `"${ApiUrl.ITEM.path}/$id"`.
- Parameter method: named + `required`; boleh positional tunggal untuk satu argumen sederhana.
- Struktur setiap method: `Dio dio = await getDio();` → `Response response = await dio.<verb>(...)` → `return response;`.

### 5.3 Model endpoint — `api/endpoint/<endpoint>/`

File: `<endpoint>.dart`, `<endpoint>_request.dart`, `<endpoint>_response.dart`. Beberapa kelas yang saling terkait boleh dalam satu file.

**Request:**

```dart
class SignInRequest {
  final String identity;

  SignInRequest({
    required this.identity,
  });

  Map<String, dynamic> toJson() => {
    "identity": identity,
  };
}
```

**Response / model (pola default: `parse` yang mengembalikan `this`):**

```dart
class Item {
  int? id;
  String? name;
  Jiffy? date;
  int amount = 0;
  List<Tag> tags = [];

  Item parse(Map<String, dynamic> json) {
    id = json["id"];
    name = json["name"];
    date = Formats.tryParseJiffy(json["date"]);
    amount = Formats.tryParseNumber(json["amount"]).toInt();
    tags = json["tags"] != null ? List<Tag>.from(json["tags"].map((x) => Tag().parse(x))) : [];

    return this;
  }
}
```

- Gunakan pola `parse(...)`; **jangan** memperkenalkan `factory fromJson` pada file baru kecuali proyek tersebut memang memakainya secara dominan.
- Field mutable, nullable untuk data opsional; default `0` / `[]` untuk angka dan list yang selalu dipakai.
- Angka via `Formats.tryParseNumber`, tanggal via `Formats.tryParseJiffy`.

### 5.4 `AuthorizationInterceptor`

- `onRequest`: jika `Preferences.contain(PreferenceKey.SESSION_ID)`, set header `Authorization: Bearer <token>`.
- `onResponse`: untuk `statusCode >= 300` tampilkan `BaseOverlays.error(message: ...)` (dari `response.data` bila `String`, atau `response.data["errorMessage"]` bila `Map`), lalu `handler.resolve(Response(requestOptions: ..., statusCode: 218))` dan `return`.
- Failover server primer/sekunder (`ApiManager.PRIMARY`) hanya dipakai bila proyek memang punya dua server **[ISI PER PROYEK]**.

---

## 6. Page (UI)

### 6.1 Template halaman dengan BLoC

```dart
class <Feature>Page extends StatefulWidget {
  const <Feature>Page({
    super.key,
  });

  @override
  <Feature>PageState createState() => <Feature>PageState();
}

class <Feature>PageState extends State<<Feature>Page>
     WidgetsBindingObserver {
  bool loading = false;

  List<Item>? items;

  @override
  void initState() {
    super.initState();

    WidgetsBinding.instance.addObserver(this);

    refresh();
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<<Feature>Bloc, <Feature>State>(
      listener: (context, state) async {
        if (state is <Feature>LoadLoading) {
          setState(() {
            loading = true;
            items = null;
          });
        } else if (state is <Feature>LoadSuccess) {
          items = state.items;

          setState(() {});
        } else if (state is <Feature>LoadFinished) {
          setState(() {
            loading = false;
          });
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
          onRefresh: () async {
            refresh();
          },
          statusBuilder: () {
            if (loading) {
              return BaseBodyStatus.loading;
            } else {
              if (items != null && items!.isNotEmpty) {
                return BaseBodyStatus.loaded;
              } else {
                if (items == null) {
                  return BaseBodyStatus.fail;
                } else {
                  return BaseBodyStatus.empty;
                }
              }
            }
          },
        ),
      ),
    );
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();

    setState(() {});
  }

  @override
  void dispose() {
    super.dispose();

    WidgetsBinding.instance.removeObserver(this);
  }

  void refresh() {
    context.read<<Feature>Bloc>().add(<Feature>Load());
  }

  BaseAppBar appBar() { ... }

  Widget body() { ... }
}
```

### 6.2 Aturan halaman

- `StatefulWidget` + class State **publik** bernama `<Page>State` (bukan `_State`), dengan `with WidgetsBindingObserver`.
- Halaman **tidak membuat** `BlocProvider`; ambil dari provider global dengan `context.read<XBloc>().add(...)`.
- Data hasil load disimpan sebagai field nullable di State (`List<X>? items`). `null` = gagal, kosong = `empty`.
- `BlocListener` mengikuti siklus: `Loading` → `loading = true` + kosongkan data; `Success` → isi data + `setState`; `Finished` → `loading = false`.
- Muat data lewat `refresh()` yang dipanggil di `initState`, `onRefresh`, dan setelah kembali dari navigasi (`await context.push(...); refresh();`).
- Bungkus dengan `Background(child: BaseScaffold(... backgroundColor: Colors.transparent ...))`; `statusBuilder` selalu memetakan 4 status `BaseBodyStatus` (`loading`, `loaded`, `fail`, `empty`).
- Pecah UI menjadi method builder di State: `appBar()`, `body()`, `_buildXxx()` untuk potongan kecil. Hindari satu `build` raksasa.
- Halaman tanpa data jaringan (statis) tidak perlu `BlocListener`/`statusBuilder`, tetapi tetap memakai `Background` + `BaseScaffold` + `BaseAppBar`.

### 6.3 Aksi submit / mutasi

- `...Loading` → `context.loaderOverlay.show();`
- `...Finished` → `context.loaderOverlay.hide();`
- `...Success` → `await BaseOverlays.success(message: "data_has_been_successfully_saved".tr());` lalu `context.pop();`
- Konfirmasi aksi berisiko: `BaseDialogs.confirmation(title:, message:, positiveCallback:)`.

### 6.4 Form

- `GlobalKey<FormState> formState = GlobalKey<FormState>();`
- Controller dinamai `tecXxx` (`final TextEditingController tecName = TextEditingController();`) dan di-dispose.
- Input memakai `BaseWidgets.text`, `BaseWidgets.spinner`, `BaseWidgets.month` dengan parameter `mandatory`, `readonly`, `label`, dan `validator` bila perlu. Pesan validasi lewat `"...".tr()`.
- Jarak antar field: `SizedBox(height: Dimensions.size15)`.
- Pemilihan gambar: `image_picker`; kompres lewat helper `ImagesCompress`; izin lewat `permission_handler`.

### 6.5 Navigasi

- `context.push("/path")`, `context.pop()`, `context.go("/path")`. Setelah `push` ke halaman yang bisa mengubah data, panggil `refresh()` setelah `await`.
- Pada `build` yang memakai `context` setelah `await`, gunakan `// ignore_for_file: use_build_context_synchronously` seperlunya.

---

## 7. Gaya Visual

- **Tidak ada ukuran hardcode.** Gunakan `Dimensions.sizeN` (jarak, padding, radius, ikon) dan `Dimensions.textN` (font).
  - Skala yang dipakai: `size1–size5, size10, size15, size20, size25, size30, size40, size50, size80, size100` dan `text10, text12, text13, text14, text16, text18, text20`.
  - Default jarak antar elemen: `size15` (antar bagian/field), `size10` (antar item list), `size5` (ikon ↔ teks).
- **Tidak ada warna tema hardcode.** Gunakan `AppColors.xxx()` (fungsi, bukan properti): `onSurface()`, `outline()`, `surface()`, `surfaceContainerLowest()`, `primary()`, `secondary()`, `error()`, `success()`, `onSurfaceVariant()`, dst.
  - `Colors.xxx` hanya untuk warna status semantik (chip), `Colors.transparent`, atau `Colors.white` pada overlay gelap.
- **Kartu/list item:**

```dart
InkWell(
  onTap: () async { ... },
  customBorder: SmoothRectangleBorder(
    borderRadius: BorderRadius.circular(Dimensions.size10),
    smoothness: 1,
  ),
  child: Ink(
    padding: EdgeInsets.all(Dimensions.size15),
    decoration: ShapeDecoration(
      shape: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size10),
        smoothness: 1,
        side: BorderSide(color: AppColors.outline()),
      ),
      color: AppColors.surfaceContainerLowest(),
    ),
    child: ...,
  ),
)
```

  Gunakan `SmoothRectangleBorder` (radius `size10`, `smoothness: 1`), bukan `RoundedRectangleBorder`.
- **List:** `ListView.separated` dengan `padding: EdgeInsets.all(Dimensions.size15)` dan separator `SizedBox(height: Dimensions.size10)`.
- **Teks:** selalu `TextStyle(fontSize: Dimensions.textN, fontWeight: ...)`. Jangan set `fontFamily` manual; diatur di `ThemeData`.
- **Chip status:** `Container` dengan padding `size10 × size5`, radius `size20`, teks `text10` bold putih, huruf kapital.
- **Nilai dari API** ditampilkan dengan `Formats.spell(value)` (aman null; menangani angka/currency, bool, tanggal). Tampilan mata uang: `"Rp ${Formats.spell(amount)}"` (sesuaikan simbol per proyek).
- **Tanggal:** `jiffy.format(pattern: "d MMM 'yy")` atau helper di `Formats`.
- **Opasitas warna:** `withValues(alpha: x)`; hindari `withOpacity` (deprecated).
- Tema global di `main.dart` (button `FilledButton`/`OutlinedButton` dengan `SmoothRectangleBorder`, padding `size20`, font `text12` weight 500). Jangan menimpa tema per halaman.
- Latar halaman: widget `Background` (gambar `assets/image/background.png`, `BoxFit.cover`) di bawah `BaseScaffold` transparan.

---

## 8. Routing — `router_config.dart`

- Satu `final goRouter = GoRouter(initialLocation: "/", navigatorKey: Get.key, routes: [...])`.
- Route tanpa login ditaruh di luar `ShellRoute`; route setelah login di dalam `ShellRoute` (dengan `parentNavigatorKey: Get.key` dan `MaterialPage(child: child)`).
- Path `kebab-case`, **jamak** untuk resource: `/items`, `/items/:id`, `/items/search`.
- Setiap route halaman setelah login wajib punya redirect auth:

```dart
GoRoute(
  path: "/items",
  builder: (context, state) {
    return ItemListPage();
  },
  redirect: (context, state) {
    if (Shared.ACCOUNT == null) {
      return "/sign-in";
    }

    return null;
  },
  routes: [
    GoRoute(
      path: ":id",
      builder: (context, state) {
        int id = int.parse(state.pathParameters["id"] ?? "");

        return ItemDetailPage(id: id);
      },
    ),
  ],
),
```

- Query param tanggal: `Formats.tryParseJiffy(state.uri.queryParameters["date"])`. Objek kompleks via `state.extra`.
- Import halaman di `router_config.dart` diurutkan alfabetis.

---

## 9. Lokalisasi

- **Dilarang** teks UI hardcode. Selalu `"key".tr()` (`package:easy_localization/easy_localization.dart`).
- Key `snake_case` berupa frasa/kalimat deskriptif: `"data_has_been_successfully_saved"`, `"are_you_sure_want_to_sign_out"`, `"something_wrong_please_try_again"`.
- Key baru **wajib ditambahkan ke `assets/i18n/en.json` dan `assets/i18n/id.json`**. Cek dulu apakah key sudah ada.
- Label enum: `enumValue.name.tr()`.
- Inisialisasi di `main.dart`:

```dart
EasyLocalization(
  supportedLocales: const [Locale("en"), Locale("id")],
  path: "assets/i18n",
  useFallbackTranslations: true,
  fallbackLocale: const Locale("en"),
  saveLocale: true,
  startLocale: const Locale("en"),
  child: const App(),
)
```

- Key umum yang diharapkan ada di setiap proyek: `yes`, `no`, `close`, `save`, `search`, `loading`, `this_field_is_required`, `something_wrong_please_try_again`, `data_has_been_successfully_saved`, `no_data_hint`.

---

## 10. Konstanta, Penyimpanan, dan Helper

- **Enum** di `lib/constant/<nama>.dart`. Enum bernilai string memakai field `final String label;` / `final String path;` + `const` constructor. Nilai `SCREAMING_SNAKE_CASE`; enum status sederhana boleh lowercase bila sudah begitu di proyek. File dengan nama `SCREAMING_SNAKE_CASE` diberi header `// ignore_for_file: non_constant_identifier_names, constant_identifier_names`.
- **Preferensi lokal:** hanya lewat helper `Preferences` (Bagian 10A, varian sesuai ada/tidaknya `base`). Proyek berbasis `base`: `Preferences.getString/getInt/getBool/setString/...(PreferenceKey.XXX)` membungkus `BasePreferences`. Proyek tanpa `base`: `Preferences.getInstance().getString(SharedPreferenceKey.XXX)`. Jangan akses `SharedPreferences` langsung. Key baru ditambahkan di enum key proyek.
  - Key minimal: `SESSION_ID`, `THEME_MODE`, `USER_ID`, `SERVER_URL` (+ sesuai kebutuhan).
- **State global lintas halaman:** field static di `Shared` (`Shared.ACCOUNT`). Jangan menambah singleton lain tanpa alasan kuat.
- **Helper standar:** 14 helper di Bagian 10A wajib ada dan wajib dipakai. Tambahan non-wajib dari proyek referensi: `Timers`, `Background`, `ImagesCompress`, `FirebaseCloudMessagings`. **Gunakan ulang sebelum menulis fungsi baru.**
- **Bootstrap `main()`** urut: `WidgetsFlutterBinding.ensureInitialized()` → `HttpOverrides.global = BaseHttpOverrides()` → set `AppColors.lightColorScheme/darkColorScheme` → `usePathUrlStrategy()` / `initializeDateFormatting()` → `EasyLocalization.ensureInitialized()` → Firebase & notifikasi (bila dipakai) → init preferensi (`BasePreferences.getInstance().init()` pada proyek berbasis `base`, `Preferences.getInstance().init()` pada proyek tanpa `base`) → `Generals.self()` (bila ada) → `runApp(...)`.
- **Susunan widget root `App`:** `MultiBlocProvider` → `MultiProvider` → `GlobalLoaderOverlay` (overlay Lottie `assets/lottie/loading.json` + teks `loading`) → `DismissKeyboard` → `BlocBuilder<MainBloc, MainState>` → `MaterialApp.router`.
- **MainBloc** mengelola `ThemeMode` (event `MainThemeChanged`) dan menyimpannya via `Preferences.setInt(PreferenceKey.THEME_MODE, ...)`.

---

## 10A. Helper Wajib (`lib/helper/`)

Setiap proyek **wajib** memiliki 14 helper di bawah ini. Sebelum menulis utilitas apa pun (format tanggal/angka, dialog, bottom sheet, GPS, hash, navigasi, warna, ukuran, preferensi, notifikasi), **cek helper ini dulu dan pakai**. Dilarang membuat ulang fungsi yang sudah ada, dan dilarang memanggil `showDialog`, `showModalBottomSheet`, `showDatePicker`, `showTimePicker`, `Geolocator.*`, atau `SharedPreferences` langsung dari page/bloc.

Sumber salinan: folder helper referensi **[ISI PER PROYEK: path/repo helper referensi]**. Saat menyalin ke proyek baru, ganti `package:dea/...` menjadi `package:<app>/...`.

### 10A.1 Aturan sumber dan duplikasi

1. **Satu sumber per kelas per proyek.** Jangan membuat salinan lokal dengan nama kelas yang sama dengan yang sudah diekspor `base` (error import ambigu), dan jangan mencampur dua keluarga helper dalam satu proyek.
2. **Proyek memakai `base`:** `Dimensions`, `AppColors`, dan extension (`dateFormat()`, `currency()`, dst.) diambil dari `base` (terkonfirmasi dari pemakaian di proyek referensi pertama). Padanan helper ↔ `base`:

   | Helper di dokumen ini | Padanan di `base` |
   |---|---|
   | `Dialogs` | `BaseDialogs` |
   | `BottomSheets` | `BaseSheets` |
   | `Overlays` | `BaseOverlays` |
   | `Preferences.getInstance()` | `BasePreferences` (dibungkus wrapper statis `Preferences`, enum `PreferenceKey`) |

   Pada proyek berbasis `base`, pakai padanan `Base*`. Helper selain tabel di atas (`Navigators`, `Locations`, `Images`, `Exceptions`, `Formats`, `Generals`, `Notifications`, `Realms`) tetap wajib ada di `lib/helper/`.
3. **Proyek tanpa `base`:** seluruh 14 helper wajib ada di `lib/helper/`, memakai `Preferences.getInstance()` dan enum `SharedPreferenceKey` di `lib/constant.dart`.
4. Spesifikasi di bawah adalah **kontrak minimal**. Proyek boleh menambah fungsi (superset), tidak boleh mengubah nama atau signature yang sudah ada.

### 10A.2 Pendamping yang harus ada (tidak termasuk 14 file)

Helper ini mengimpor file lain. Jika belum ada di proyek, buat dulu:

- `lib/constant.dart` → enum `SharedPreferenceKey` (minimal `SESSION_ID`, `USER_NAME`) — proyek berbasis `base` memakai `PreferenceKey` di `lib/constant/preference_key.dart`.
- `lib/overlay/overlays.dart` → `Overlays.error({required String message})` dan `Overlays.success(...)` (dipakai `Dialogs` dan `Generals`); pada proyek berbasis `base` pakai `BaseOverlays`.
- `lib/api/api_manager.dart` → method `download({required String url})` yang mengembalikan `Uint8List` (dipakai `Notifications`).
- Halaman `SignInPage` (dipakai `Generals.signOut` dan `checkSession`).

### 10A.3 Daftar helper

| # | File | Kelas / isi wajib | Aturan pakai |
|---|---|---|---|
| 1 | `dimensions.dart` | `Dimensions`: `screenHeight`, `screenWidth`, `statusBarHeight`; `text8…text50` (kelipatan 2); `size5…size100` (kelipatan 5) | Tidak ada angka ukuran/font hardcode. Perlu ukuran baru → tambah di `Dimensions`. Nilai statis diambil dari `Get.context!`, jadi hanya diakses setelah `MaterialApp` ter-mount (bukan di `main()` sebelum `runApp`). Layout proporsional: `Dimensions.screenHeight * x`. |
| 2 | `app_colors.dart` | `AppColors`: `brightness()`, `brightnessInverse()`, `darkMode()`; fungsi warna `primary/onPrimary/primaryContainer/onPrimaryContainer`, `secondary*`, `tertiary*`, `error/errorContainer/onError/onErrorContainer`, `background/onBackground`, `surface/onSurface`, `surfaceVariant/onSurfaceVariant`, `outline/outlineVariant`, `inverseSurface/onInverseSurface/inversePrimary`, `shadow`, `surfaceTint`, `scrim`; warna semantik `success/onSuccess/successContainer/onSuccessContainer`; konstanta `lightColorScheme` dan `darkColorScheme` | Warna selalu lewat `AppColors.x()`. Setiap fungsi berpola `if (darkMode()) { dark } else { light }`; warna baru ditulis dengan pola yang sama. Isi `lightColorScheme`/`darkColorScheme` diganti per aplikasi **[ISI PER PROYEK]**. Deteksi tema memakai `Navigators.navigatorState.currentContext`, jadi navigator key wajib terpasang di `MaterialApp`/`GoRouter`. |
| 3 | `navigators.dart` | `Navigators`: `navigatorState` (`GlobalKey<NavigatorState>`), `push<T>`, `pushReplacement`, `pushAndRemoveAll`, `pop`, `popAll`, `popUntil(context, name)` | Proyek tanpa go_router: navigasi imperatif hanya lewat `Navigators.*` (route diberi `settings.name = widget.toString()`). Proyek dengan go_router: navigasi pakai `context.push/go/pop`, tetapi `Navigators.navigatorState` (atau `Get.key` pada proyek berbasis `base`) tetap dipasang sebagai satu-satunya navigator key global. |
| 4 | `dialogs.dart` | `Dialogs.message({buildContext, title, message?, dismiss?, showButton, cancelable})`, `Dialogs.confirmation({context, title, message?, negative?, positive?, cancelable, negativeCallback?, positiveCallback?})`, `Dialogs.reason({context, title, message?, negative?, positive?, cancelable, negativeCallback?, required positiveCallback(String reason)})` | Semua dialog lewat helper ini. `reason` dipakai untuk input alasan (wajib terisi). Teks tombol dan pesan **wajib** dikirim lewat `.tr()`. |
| 5 | `bottom_sheets.dart` | `BottomSheets.spinner`, `checkable`, `dateRange`, `date`, `dateTime`, `time`, `standard`, `standardLow`; kelas `SpinnerItem` | Lihat 10A.4. Hanya bagian generik yang disalin. |
| 6 | `extensions.dart` | `TimeOfDay`: `compareTo`, `timeFormat()`; `Color`: `darken([percent])`, `lighten([percent])`; `List`: `unique([id, inplace])`; `Jiffy`: `dateFormat()` (`yyyy-MM-dd`), `dateTimeFormat()` (`yyyy-MM-dd HH:mm`), static `JiffyExtension.min` (1900-01-01) dan `max` (2099-12-31); `num`: `currency()` (`#,###.##`, locale `id`); `String`: `currencyString()`, `parseBool()`; `Map`: `append(map)` | Tanggal yang dikirim ke API **wajib** `dateFormat()` / `dateTimeFormat()`. Angka uang tampil via `currency()`. Batas picker tanggal pakai `JiffyExtension.min/max`. |
| 7 | `formats.dart` | `Formats.coalesce(value, {defaultString})` (default `"N/A"`), `tryParseJiffy(value, {pattern})`, `tryParseNumber(dynamic)`, `tryParseBool(dynamic)`, `date(jiffy)` (`d MMM 'yy`), `dateTime(jiffy)` (`d MMM 'yy HH:mm`), `dateDetail(jiffy)` (`dd-MM-yyyy`), `dateMonth(jiffy)` (`dd MMM ''yy`) | Parsing nilai API dan tampilan tanggal hanya lewat `Formats`. Fungsi tampil mengembalikan `"N/A"` untuk `null`. Proyek boleh menambah fungsi (mis. `spell`, `convert`, `parseTime`) tanpa mengubah yang ada. |
| 8 | `generals.dart` | `Generals.signOut()`, `launchUrls(url)`, `sha1/sha256/md5(String?)`, `appVersion()`, `checkSession(context)`, `deviceId()` | `signOut` menghapus preferensi dan data lokal lalu `pushAndRemoveAll` ke halaman sign-in; isinya spesifik proyek. `deviceId()` hanya mengembalikan ID asli pada mode release (Android: `AndroidId`; iOS: nol 16 digit); di luar release memakai ID dev tetap. `checkSession` memanggil `Preferences.reload()` dulu. Hash selalu lewat `Generals.sha*`/`md5`, bukan `crypto` langsung. |
| 9 | `images.dart` | `Images.brightness()`, `darkMode()`, `<app>()` / `<app>Inverse()` (path logo terang/gelap), `textWatermark({source, text, fontSize, width, height})` | Logo mengikuti tema: `assets/image/<app>_black.png` dan `<app>_white.png`. Watermark foto bukti hanya lewat `textWatermark`. Butuh package `image_watermark`. |
| 10 | `locations.dart` | `Locations.lastPosition()` → `LongLat?`, `distanceBetween(a, b, c, d)`, `lastPlacemarkPosition()` → `String?`; kelas `LongLat({required latitude, required longitude})` | Semua akses GPS lewat sini. `lastPosition` melempar `Exception` bila GPS mati, izin ditolak, atau lokasi palsu terdeteksi (`isMocked`); pemanggil menangkapnya dan menampilkan `e.toString()` lewat `Overlays.error`. |
| 11 | `exceptions.dart` | `GeneralException({required String message})` | Untuk error bisnis yang pesannya boleh tampil ke pengguna. Jangan membuat kelas exception baru. |
| 12 | `notifications.dart` | `Notifications.getInstance()` (singleton), `initialize(bool isolate)`, `initializeFirebase()`, `showNotification({title?, body?, imageUrl?, data?})`; fungsi top-level `firebaseCloudMessagingBackgroundHandler` dengan `@pragma("vm:entry-point")` | Notifikasi hanya ditampilkan bila `SESSION_ID` ada. Nama channel, id channel, dan ikon (`@drawable/notification_icon`) diganti per aplikasi **[ISI PER PROYEK]**. `initialize(true)` dipakai di isolate (tanpa permintaan izin). Gambar notifikasi diunduh lewat `ApiManager.download`. |
| 13 | `preferences.dart` | `Preferences.getInstance()`, `init()`, `getBool/getInt/getDouble/getString/getStrings(key, [defValue])`, `setBool/setInt/setDouble/setString/setStrings(key, value)`, `remove(key)`, `contain(key)`, `clear()`, `reload()` | `init()` dipanggil di `main()` sebelum `runApp`. `set*` mengabaikan nilai `null`. Key selalu enum, tidak pernah string literal. Proyek berbasis `base` memakai varian wrapper statis (lihat 10A.1). |
| 14 | `realms.dart` | `Realms.get()` (lazy singleton `Realm`), `Realms.clear()` | **Wajib bila proyek memakai penyimpanan lokal Realm.** Daftar skema diisi per proyek **[ISI PER PROYEK]**. `clear()` dipanggil dari `Generals.signOut`. Bila proyek tidak memakai Realm, file tidak dibuat dan pemanggilannya di `signOut` dihapus. |

### 10A.4 Aturan khusus `bottom_sheets.dart`

File referensi berisi bagian generik dan bagian domain. **Hanya bagian generik yang disalin:**

- Disalin: `spinner({context, title, spinnerItems, onSelected(SpinnerItem), ispop = true})`, `checkable({context, title, spinnerItems, onSelected(List<SpinnerItem>)})`, `dateRange({context, from, until, onSelected(from, until), maxRangeInDays?})`, `date`/`dateTime({context, jiffy, min, max, onSelected})`, `time({context, timeOfDay, onSelected})`, `standard`/`standardLow({context, widget, backgroundColor?})`, dan kelas `SpinnerItem({required identity, required description, subdescription?, notif?, tag?, selected = false})`.
- **Tidak disalin** (spesifik domain aplikasi `dea`): `airwayBillUpdateBerkala`, `deliverySheetConfirm`, `VehicleCL*`, `RouteCL*`, `ItemCL*`, `ItemCACL`, `EditItem*`, serta kelas `TeamItem` dan `AddItems`.
- Bottom sheet khusus fitur **tidak** ditambahkan ke `BottomSheets`. Buat sebagai widget di folder modul fiturnya dan tampilkan lewat `BottomSheets.standard` / `standardLow`.
- Data pilihan dropdown selalu dibungkus `SpinnerItem` (`identity` = objek asli, `description` = teks tampil).
- Butuh package `syncfusion_flutter_datepicker` (untuk `dateRange`).

### 10A.5 Perbaikan wajib saat menyalin (jangan menyalin bug)

- `Navigators.pop`: ganti `Navigator.of(context).pop(context)` menjadi `Navigator.of(context).pop()`.
- `Images.textWatermark`: parameter `fontSize` harus dipakai (versi referensi mengabaikannya dan memakai `Dimensions.text20`).
- `Locations.lastPosition`: ganti `position!` dengan pengecekan `null` yang melempar `Exception` yang jelas.
- Teks default hardcode di `Dialogs` (`"Mengerti"`, `"Iya"`, `"Tidak"`, `"Tutup"`, `"Kirim"`, pesan alasan wajib diisi), `Locations`, dan `Generals.checkSession` diganti key i18n (`.tr()`).
- `Notifications`: nama channel `"Dino Express"` / `"Umum"` adalah milik aplikasi `dea`; ganti sesuai aplikasi.
- `Generals.signOut` mengandung `ActivityHistoryDao`, `Realms.clear()`, dan `SignInPage` milik `dea`; tulis ulang sesuai kebutuhan proyek, bukan disalin mentah.
- `print(...)` pada `Locations` / `Notifications` dibungkus `if (kDebugMode)`.

---

## 11. Konfigurasi Proyek Baru

### `analysis_options.yaml`

```yaml
analyzer:
  errors:
    avoid_function_literals_in_foreach_calls: ignore
include: package:flutter_lints/flutter.yaml

linter:
  rules:
    - always_use_package_imports
    - always_declare_return_types
    - always_put_control_body_on_new_line
    - always_put_required_named_parameters_first
    - annotate_overrides
    - avoid_bool_literals_in_conditional_expressions
    - avoid_escaping_inner_quotes
    - avoid_final_parameters
    - avoid_multiple_declarations_per_line
    - camel_case_types
    - cascade_invocations
    - curly_braces_in_flow_control_structures
    - directives_ordering
    - empty_catches
    - empty_constructor_bodies
    - file_names
    - implicit_call_tearoffs
    - leading_newlines_in_multiline_strings
    - no_leading_underscores_for_local_identifiers
    - require_trailing_commas
    - sized_box_for_whitespace
    - sized_box_shrink_expand
    - sort_child_properties_last
    - unnecessary_brace_in_string_interps
    - unnecessary_parenthesis
    - prefer_double_quotes
    - depend_on_referenced_packages
```

### `pubspec.yaml` (ringkas)

- `publish_to: none`; `environment.sdk: '>=3.3.1 <4.0.0'`.
- Dependensi inti: `flutter_bloc`, `provider`, `go_router`, `get`, `dio`, `easy_localization`, `jiffy`, `shared_preferences`, `loader_overlay`, `lottie`, `smooth_corner`, `shimmer`, `intl`, `url_launcher`, `package_info_plus`, dan package internal `base` (git, `ref` dikunci ke tag versi).
- Dev: `flutter_test`, `flutter_lints`, `icons_launcher`.
- Aset: `assets/`, `assets/font/`, `assets/image/`, `assets/lottie/`, `assets/i18n/`; font family `Manrope` (Light, Regular, Medium, SemiBold, Bold, ExtraBold, ExtraLight).
- Dependensi yang dibutuhkan helper wajib: `get`, `basic_utils`, `jiffy`, `intl`, `shared_preferences`, `syncfusion_flutter_datepicker`, `geolocator`, `geocoding`, `image_watermark`, `crypto`, `android_id`, `package_info_plus`, `url_launcher`, `firebase_messaging`, `flutter_local_notifications`, dan `realm` (hanya bila memakai Realm).
- Tambahkan package lain (firebase, geolocator, camera, image_picker, dst.) **hanya bila fiturnya dibutuhkan**.

### `.fvmrc`

```json
{ "flutter": "stable" }
```

---

## 12. Hal yang Dilarang

- `print(...)` tanpa `if (kDebugMode)`.
- Teks UI, ukuran, font size, atau warna tema yang di-hardcode.
- `BlocProvider` lokal di halaman, `Cubit`, atau state management lain (Riverpod, MobX, GetX controller, dst.).
- Logika jaringan di dalam `setState`/page; memanggil `Dio` di luar `ApiManager`.
- Import relatif; tanda kutip tunggal.
- Menambah/mengubah versi dependency tanpa persetujuan.
- Menaruh URL server produksi, token, kredensial, atau keystore di kode atau commit.
- Menyalin typo atau bug dari kode lama (mis. fungsi validasi yang dipanggil dengan nama salah).
- Membuat ulang fungsi yang sudah ada di helper wajib (format tanggal/angka, dialog, bottom sheet, GPS, hash, navigasi, preferensi).
- Memanggil `showDialog`, `showModalBottomSheet`, `showDatePicker`, `showTimePicker`, `Geolocator.*`, atau `SharedPreferences` langsung dari page/bloc.
- Mengirim tanggal ke API tanpa `dateFormat()` / `dateTimeFormat()`.
- Menaruh bottom sheet atau dialog khusus fitur di dalam `BottomSheets` / `Dialogs`.
- Membuat salinan lokal kelas yang sudah diekspor `base` (`Dimensions`, `AppColors`, dst.).
- Mengubah modul lain yang tidak diminta.

---

## 13. Checklist Menambah Fitur Baru

0. Pastikan semua helper wajib (Bagian 10A) sudah ada di proyek.
1. Tambah path di `ApiUrl` (jika endpoint baru).
2. Buat model di `api/endpoint/<endpoint>/` dan method di `ApiManager`.
3. Buat `module/<front|rear>/<feature>/` berisi `event`, `state`, `bloc`, `page`.
4. Daftarkan bloc di `MultiBlocProvider` (`main.dart`).
5. Tambah route + redirect auth di `router_config.dart`.
6. Tambah key i18n di `en.json` **dan** `id.json`.
7. Jalankan `flutter analyze`; perbaiki semua warning dari kode yang Anda tulis.
8. Laporkan: daftar file yang dibuat/diubah dan hal yang perlu diuji manual.

---

## 14. Data Spesifik Proyek **[ISI PER PROYEK]**

Isi bagian ini di setiap aplikasi baru; agent wajib membacanya sebelum bekerja.

- Nama package (`<app>`): mgi_investor
- Nama tampilan aplikasi: Montana Global Investama
- Base URL dev / prod (sumber konfigurasi): Dev: http://localhost/Montana-Global-Investama-Landing-page/api (atau IP LAN Host http://192.168.x.x/...); Prod: https://montanainvestama.com/api; Sumber konfigurasi wajib dibaca via preferensi `PreferenceKey.SERVER_URL` dengan fallback otomatis ke alamat dev.
- Failover server primer/sekunder? (ya/tidak): Tidak (Single endpoint production/dev dengan mekanisme fallback internal ke cache statis lokal).
- Seed color tema: Hex #0F2C59 (Deep Royal Blue) dengan warna pendukung #C5A059 (Champagne Gold) dan #F8FAFC (Surface Container Lowest); Alasan: Palet identitas resmi korporat PT Montana Global Investama yang memancarkan ketegasan, kredibilitas, dan kemewahan manajer investasi sektor riil.
- Peran/tipe pengguna dan hak aksesnya: 3 Tipe Pengguna Halaman Front:
  1. Pengunjung Publik / Calon Investor (Unauthenticated/Guest): Akses profil MGI, 3 pilar ekosistem (MIU, MSI, Mypurcase), tata kelola TARIF, katalog proyek ringkas, filter kota (Jabodetabek, Surabaya, Makassar, Denpasar), dan form konsultasi/leads.
  2. Calon Investor Perorangan (Authenticated): Membuka gated content (CAPEX breakdown, dokumen legalitas), simulasi komitmen pendanaan, profil WNI/WNA, dan watermarked view.
  3. Calon Investor Perusahaan / Institusi (Authenticated): Akses dokumen proposal sindikasi, simulasi tiket investasi besar (Rp 5M – Rp 10M), profil badan hukum (PT/CV), PIC, dan omzet tahunan.
- Mata uang & format tanggal: Mata uang: Rupiah ("Rp ${Formats.spell(amount)}" / "Rp ${num.currency()}"), format desimal `#,###.##` locale `id`; Format tanggal API: "yyyy-MM-dd" dan "yyyy-MM-dd HH:mm"; Format tanggal UI: "d MMM 'yy", "d MMM 'yy HH:mm", "dd-MM-yyyy".
- Fitur Firebase yang dipakai (FCM, Analytics, ...): FCM (Push notifikasi rilis proyek kemitraan baru, kuota slot investor tersisa, dan berita dividen) serta Firebase Analytics (pelacakan eksplorasi katalog & kalkulator ROI).
- Path sumber salinan helper referensi: `C:\Users\Admin MGI\resident\resident` (proyek Flutter aktif standar tim berbasis `package:base` 2.0.6); rujukan pola umum dokumen: `dea`.
- Proyek memakai `base`? (ya/tidak): Ya (wajib menggunakan `package:base` git https://github.com/sisappmobile/base.git ref 2.0.6 sesuai stack standar Bagian 1 & 11).
- Memakai Realm? Daftar skema: Tidak (Data katalog & profil perusahaan di-cache via SharedPreferences dan state BLoC in-memory; tidak memerlukan database Realm).
- Nama channel notifikasi & ikon: Channel ID: "mgi_investor_updates", Channel Name: "Investor Updates", Description: "Pemberitahuan proyek baru & peluang investasi riil"; Ikon: `@drawable/notification_icon`.
- Nama aset logo (`<app>_black.png` / `<app>_white.png`): `mgi_investor_black.png` (mode terang) dan `mgi_investor_white.png` (mode gelap) di folder `assets/image/`, diturunkan dari aset resmi `mgi-official-logo.png`.
- Catatan khusus domain:
  1. Gated Content & Disclaimer Finansial: Data rinci RAB, spesifikasi teknis unit alat berat, dan simulator ROI terkunci (`is_gated: true`) bagi pengunjung non-login jika setting `require_auth_for_details` aktif; seluruh halaman wajib menampilkan *disclaimer* risiko investasi sektor riil.
  2. Watermarking Dinamis: Setiap tampilan proposal proyek pada investor terautentikasi diberi teks watermark: "DOKUMEN RAHASIA PT MONTANA GLOBAL INVESTAMA — DIAKSES OLEH: [NAMA] ([EMAIL])".
  3. Mode Paket Investasi (Package Options Mode): Proyek mendukung multi-tier (Mode 1 paket atau Mode 2 paket, misal Paket 1 Rp 5 Miliar & Paket 2 Rp 10 Miliar) dengan fasilitas 1–2 Operator Alat Berat Profesional.
  4. Dual-Registration Flow: Pendaftaran membedakan alur Perorangan (Nama, NIK/Paspor, Kewarganegaraan, No HP) dan Badan Usaha (Nama PT/CV, Entitas Hukum, PIC & Jabatan, Alamat, Kisaran Omzet Tahunan).
  5. Multi-Auth Provider: Mendukung otentikasi kata sandi (minimal 8 karakter) dan Google OAuth One-Tap (`api/auth.php?action=google_login`).
