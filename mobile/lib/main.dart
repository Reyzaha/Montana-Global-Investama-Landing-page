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

  AppColors.lightColorScheme = ColorScheme.fromSeed(
    seedColor: const Color(0xFF0F2C59),
    primary: const Color(0xFF0F2C59),
    secondary: const Color(0xFFC5A059),
    surface: const Color(0xFFF8FAFC),
    brightness: Brightness.light,
  );

  AppColors.darkColorScheme = ColorScheme.fromSeed(
    seedColor: const Color(0xFF0F2C59),
    primary: const Color(0xFF1E3A8A),
    secondary: const Color(0xFFD4AF37),
    brightness: Brightness.dark,
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
                  themeMode: state.themeMode,
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
