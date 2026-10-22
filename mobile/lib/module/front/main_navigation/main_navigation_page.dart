// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:mgi_investor/helper/auth_guard.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_page.dart";
import "package:mgi_investor/module/front/home/home_page.dart";
import "package:mgi_investor/module/front/project_list/project_list_page.dart";
import "package:mgi_investor/module/rear/portal/portal_dashboard_page.dart";

class MainNavigationPage extends StatefulWidget {
  final int initialIndex;

  const MainNavigationPage({
    this.initialIndex = 0,
    super.key,
  });

  @override
  MainNavigationPageState createState() => MainNavigationPageState();
}

class MainNavigationPageState extends State<MainNavigationPage>
    with WidgetsBindingObserver {
  late int currentIndex;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    currentIndex = widget.initialIndex.clamp(0, 3);
    if (currentIndex == 3 && !AuthGuard.isAuthenticated) {
      currentIndex = 0;
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      body: IndexedStack(
        index: currentIndex,
        children: [
          const HomePage(),
          const ProjectListPage(),
          const CompanyProfilePage(),
          if (AuthGuard.isAuthenticated)
            const PortalDashboardPage()
          else
            const SizedBox.shrink(),
        ],
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Container(
          decoration: BoxDecoration(
            color: AppColors.surfaceContainerLowest(),
            border: Border(
              top: BorderSide(
                color: AppColors.outline().withValues(alpha: 0.5),
                width: 1.0,
              ),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 10,
                offset: const Offset(0, -3),
              ),
            ],
          ),
          child: NavigationBarTheme(
            data: NavigationBarThemeData(
              indicatorColor: const Color(0xFFC5A059).withValues(alpha: 0.16),
              surfaceTintColor: Colors.transparent,
              backgroundColor: AppColors.surfaceContainerLowest(),
              labelTextStyle: WidgetStateProperty.resolveWith((states) {
                if (states.contains(WidgetState.selected)) {
                  return TextStyle(
                    fontSize: Dimensions.text11,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0F2C59),
                  );
                }
                return TextStyle(
                  fontSize: Dimensions.text11,
                  fontWeight: FontWeight.w500,
                  color: AppColors.onSurfaceVariant(),
                );
              }),
              iconTheme: WidgetStateProperty.resolveWith((states) {
                if (states.contains(WidgetState.selected)) {
                  return const IconThemeData(
                    color: Color(0xFF0F2C59),
                    size: 24,
                  );
                }
                return IconThemeData(
                  color: AppColors.onSurfaceVariant(),
                  size: 24,
                );
              }),
            ),
            child: NavigationBar(
              selectedIndex: currentIndex,
              elevation: 0,
              height: Dimensions.size50 + Dimensions.size15,
              onDestinationSelected: (int index) {
                if (index == 3 && !AuthGuard.isAuthenticated) {
                  AuthGuard.promptLogin(
                    context,
                    title: "investor_dashboard".tr(),
                    message: "investor_dashboard_login_desc".tr(),
                  );
                  return;
                }
                setState(() {
                  currentIndex = index;
                });
              },
              destinations: [
                NavigationDestination(
                  icon: const Icon(Icons.home_outlined),
                  selectedIcon: const Icon(Icons.home),
                  label: "home".tr(),
                ),
                NavigationDestination(
                  icon: const Icon(Icons.business_center_outlined),
                  selectedIcon: const Icon(Icons.business_center),
                  label: "projects".tr(),
                ),
                NavigationDestination(
                  icon: const Icon(Icons.domain_outlined),
                  selectedIcon: const Icon(Icons.domain),
                  label: "company_profile".tr(),
                ),
                NavigationDestination(
                  icon: const Icon(Icons.dashboard_outlined),
                  selectedIcon: const Icon(Icons.dashboard),
                  label: "investor_dashboard".tr(),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
