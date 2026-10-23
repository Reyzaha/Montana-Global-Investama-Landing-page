// ignore_for_file: depend_on_referenced_packages, avoid_print

import "dart:io";

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart" as el;
import "package:firebase_core/firebase_core.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:flutter_web_plugins/url_strategy.dart";
import "package:go_router/go_router.dart";
import "package:intl/date_symbol_data_local.dart";
import "package:loader_overlay/loader_overlay.dart";
import "package:lottie/lottie.dart";
import "package:mgi_investor/helper/firebase_cloud_messagings.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/helper/notifications.dart";
import "package:mgi_investor/helper/timers.dart";
import "package:mgi_investor/main_bloc.dart";
import "package:mgi_investor/main_state.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_bloc.dart";
import "package:mgi_investor/module/front/consultation/consultation_bloc.dart";
import "package:mgi_investor/module/front/home/home_bloc.dart";
import "package:mgi_investor/module/front/news/news_bloc.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_bloc.dart";
import "package:mgi_investor/module/front/project_list/project_list_bloc.dart";
import "package:mgi_investor/module/front/register/register_bloc.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_bloc.dart";
import "package:mgi_investor/module/rear/notification/notification_bloc.dart";
import "package:mgi_investor/router_config.dart";
import "package:provider/provider.dart";
import "package:smooth_corner/smooth_corner.dart";

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  HttpOverrides.global = BaseHttpOverrides();

  AppColors.lightColorScheme = const ColorScheme(
    brightness: Brightness.light,
    primary: Color(0xFF0F2C59),
    onPrimary: Color(0xFFFFFFFF),
    primaryContainer: Color(0xFF1D3589),
    onPrimaryContainer: Color(0xFFFFFFFF),
    secondary: Color(0xFFC5A059),
    onSecondary: Color(0xFFFFFFFF),
    secondaryContainer: Color(0xFFF6E7C9),
    onSecondaryContainer: Color(0xFF382902),
    tertiary: Color(0xFF274472),
    onTertiary: Color(0xFFFFFFFF),
    tertiaryContainer: Color(0xFFD6E4FF),
    onTertiaryContainer: Color(0xFF0B1D3A),
    error: Color(0xFFBA1A1A),
    errorContainer: Color(0xFFFFDAD6),
    onError: Color(0xFFFFFFFF),
    onErrorContainer: Color(0xFF410002),
    surface: Color(0xFFF8FAFC),
    onSurface: Color(0xFF0F172A),
    surfaceContainerHighest: Color(0xFFE2E8F0),
    onSurfaceVariant: Color(0xFF64748B),
    outline: Color(0xFFCBD5E1),
    onInverseSurface: Color(0xFFF1F5F9),
    inverseSurface: Color(0xFF1E293B),
    inversePrimary: Color(0xFF8AB4F8),
    shadow: Color(0xFF000000),
    surfaceTint: Color(0xFF0F2C59),
    outlineVariant: Color(0xFFE2E8F0),
    scrim: Color(0xFF000000),
  );

  AppColors.darkColorScheme = const ColorScheme(
    brightness: Brightness.dark,
    primary: Color(0xFF8AB4F8),
    onPrimary: Color(0xFF0A1B36),
    primaryContainer: Color(0xFF1E3A8A),
    onPrimaryContainer: Color(0xFFDBEAFE),
    secondary: Color(0xFFD4AF37),
    onSecondary: Color(0xFF2B2000),
    secondaryContainer: Color(0xFF4B3900),
    onSecondaryContainer: Color(0xFFFFE088),
    tertiary: Color(0xFF93C5FD),
    onTertiary: Color(0xFF082142),
    tertiaryContainer: Color(0xFF1A355A),
    onTertiaryContainer: Color(0xFFBFDBFE),
    error: Color(0xFFFFB4AB),
    errorContainer: Color(0xFF93000A),
    onError: Color(0xFF690005),
    onErrorContainer: Color(0xFFFFDAD6),
    surface: Color(0xFF0B132B),
    onSurface: Color(0xFFF8FAFC),
    surfaceContainerHighest: Color(0xFF1E293B),
    onSurfaceVariant: Color(0xFF94A3B8),
    outline: Color(0xFF334155),
    onInverseSurface: Color(0xFF0B132B),
    inverseSurface: Color(0xFFE2E8F0),
    inversePrimary: Color(0xFF0F2C59),
    shadow: Color(0xFF000000),
    surfaceTint: Color(0xFF8AB4F8),
    outlineVariant: Color(0xFF1E293B),
    scrim: Color(0xFF000000),
  );

  usePathUrlStrategy();
  initializeDateFormatting();
  GoRouter.optionURLReflectsImperativeAPIs = true;

  await el.EasyLocalization.ensureInitialized();

  try {
    await Firebase.initializeApp();
    await FirebaseCloudMessagings.initialize();
  } catch (e) {
    if (kDebugMode) {
      print("Firebase initialization notice: $e");
    }
  }

  await Notifications.getInstance().initialize();
  await BasePreferences.getInstance().init();
  await Generals.self();

  runApp(
    el.EasyLocalization(
      supportedLocales: const [Locale("en"), Locale("id")],
      path: "assets/i18n",
      useFallbackTranslations: true,
      fallbackLocale: const Locale("en"),
      saveLocale: true,
      startLocale: const Locale("en"),
      child: const App(),
    ),
  );
}

class App extends StatefulWidget {
  const App({super.key});

  @override
  State<App> createState() => AppState();
}

class AppState extends State<App> {
  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(create: (BuildContext context) => MainBloc()),
        BlocProvider(create: (BuildContext context) => HomeBloc()),
        BlocProvider(create: (BuildContext context) => CompanyProfileBloc()),
        BlocProvider(create: (BuildContext context) => ProjectListBloc()),
        BlocProvider(create: (BuildContext context) => ProjectDetailBloc()),
        BlocProvider(create: (BuildContext context) => NewsBloc()),
        BlocProvider(create: (BuildContext context) => ConsultationBloc()),
        BlocProvider(create: (BuildContext context) => SignInBloc()),
        BlocProvider(create: (BuildContext context) => RegisterBloc()),
        BlocProvider(create: (BuildContext context) => NotificationBloc()),
      ],
      child: MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => Timers.getInstance()),
        ],
        child: GlobalLoaderOverlay(
          overlayWidgetBuilder: (_) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Lottie.asset(
                    "assets/lottie/loading.json",
                    frameRate: const FrameRate(60),
                    width: Dimensions.size100 * 2,
                    repeat: true,
                  ),
                  Text(
                    "${el.tr("loading")}...",
                    style: TextStyle(
                      fontSize: Dimensions.text20,
                      fontWeight: FontWeight.w500,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            );
          },
          overlayColor: Colors.black.withValues(alpha: 0.8),
          child: DismissKeyboard(
            child: BlocBuilder<MainBloc, MainState>(
              builder: (context, state) {
                return MaterialApp.router(
                  scrollBehavior: BaseScrollBehavior(),
                  title: "mgi_app_title".tr(),
                  routerConfig: goRouter,
                  localizationsDelegates: context.localizationDelegates,
                  supportedLocales: context.supportedLocales,
                  locale: context.locale,
                  debugShowCheckedModeBanner: false,
                  theme: ThemeData(
                    useMaterial3: true,
                    fontFamily: "Manrope",
                    colorScheme: AppColors.lightColorScheme,
                    filledButtonTheme: FilledButtonThemeData(
                      style: FilledButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    outlinedButtonTheme: OutlinedButtonThemeData(
                      style: OutlinedButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                        foregroundColor: AppColors.onSurface(),
                        iconColor: AppColors.onSurface(),
                      ),
                    ),
                    textButtonTheme: TextButtonThemeData(
                      style: TextButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    iconButtonTheme: IconButtonThemeData(
                      style: IconButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        padding: EdgeInsets.zero,
                        minimumSize: Size.square(
                          Dimensions.size40 + Dimensions.size10,
                        ),
                      ),
                    ),
                  ),
                  darkTheme: ThemeData(
                    useMaterial3: true,
                    fontFamily: "Manrope",
                    colorScheme: AppColors.darkColorScheme,
                    filledButtonTheme: FilledButtonThemeData(
                      style: FilledButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    outlinedButtonTheme: OutlinedButtonThemeData(
                      style: OutlinedButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                        foregroundColor: AppColors.onSurface(),
                        iconColor: AppColors.onSurface(),
                      ),
                    ),
                    textButtonTheme: TextButtonThemeData(
                      style: TextButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        padding: EdgeInsets.all(Dimensions.size20),
                        textStyle: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    iconButtonTheme: IconButtonThemeData(
                      style: IconButton.styleFrom(
                        visualDensity: VisualDensity.compact,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        padding: EdgeInsets.zero,
                        minimumSize: Size.square(
                          Dimensions.size40 + Dimensions.size10,
                        ),
                      ),
                    ),
                  ),
                  themeMode: ThemeMode.light,
                );
              },
            ),
          ),
        ),
      ),
    );
  }
}

class DismissKeyboard extends StatelessWidget {
  final Widget child;

  const DismissKeyboard({
    required this.child,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScopeNode currentFocus = FocusScope.of(context);

        if (!currentFocus.hasPrimaryFocus && currentFocus.focusedChild != null) {
          FocusManager.instance.primaryFocus?.unfocus();
        }
      },
      child: child,
    );
  }
}
