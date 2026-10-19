import "package:base/base.dart";
import "package:flutter/material.dart";
import "package:get/get.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_page.dart";
import "package:mgi_investor/module/front/consultation/consultation_page.dart";
import "package:mgi_investor/module/front/home/home_page.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_page.dart";
import "package:mgi_investor/module/front/project_list/project_list_page.dart";
import "package:mgi_investor/module/front/register/register_page.dart";
import "package:mgi_investor/module/front/roi_calculator/roi_calculator_page.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_page.dart";
import "package:mgi_investor/module/front/splash/splash_page.dart";
import "package:mgi_investor/module/front/tarif_governance/tarif_governance_page.dart";
import "package:mgi_investor/module/rear/portal/portal_dashboard_page.dart";
import "package:mgi_investor/module/rear/profile/profile_page.dart";
import "package:mgi_investor/module/rear/proposal_watermark/proposal_watermark_page.dart";
import "package:mgi_investor/shared.dart";

final GoRouter goRouter = GoRouter(
  initialLocation: "/splash",
  navigatorKey: Get.key,
  routes: [
    ...baseRoutes,
    GoRoute(
      path: "/splash",
      builder: (context, state) {
        return const SplashPage();
      },
    ),
    GoRoute(
      path: "/",
      builder: (context, state) {
        return const HomePage();
      },
    ),
    GoRoute(
      path: "/company-profile",
      builder: (context, state) {
        return const CompanyProfilePage();
      },
    ),
    GoRoute(
      path: "/tarif-governance",
      builder: (context, state) {
        return const TarifGovernancePage();
      },
    ),
    GoRoute(
      path: "/consultation",
      builder: (context, state) {
        return const ConsultationPage();
      },
    ),
    GoRoute(
      path: "/roi-calculator",
      builder: (context, state) {
        return const RoiCalculatorPage();
      },
    ),
    GoRoute(
      path: "/sign-in",
      builder: (context, state) {
        return const SignInPage();
      },
    ),
    GoRoute(
      path: "/register",
      builder: (context, state) {
        return const RegisterPage();
      },
    ),
    GoRoute(
      path: "/projects",
      builder: (context, state) {
        return const ProjectListPage();
      },
      routes: [
        GoRoute(
          path: ":id",
          builder: (context, state) {
            String id = state.pathParameters["id"] ?? "";
            return ProjectDetailPage(id: id);
          },
        ),
      ],
    ),
    ShellRoute(
      parentNavigatorKey: Get.key,
      pageBuilder: (context, state, child) {
        return MaterialPage(child: child);
      },
      routes: [
        GoRoute(
          path: "/portal",
          builder: (context, state) {
            return const PortalDashboardPage();
          },
          redirect: (context, state) {
            if (Shared.ACCOUNT == null) {
              return "/sign-in";
            }
            return null;
          },
          routes: [
            GoRoute(
              path: "profile",
              builder: (context, state) {
                return const ProfilePage();
              },
              redirect: (context, state) {
                if (Shared.ACCOUNT == null) {
                  return "/sign-in";
                }
                return null;
              },
            ),
            GoRoute(
              path: "projects/:id",
              builder: (context, state) {
                String id = state.pathParameters["id"] ?? "";
                return ProposalWatermarkPage(id: id);
              },
              redirect: (context, state) {
                if (Shared.ACCOUNT == null) {
                  return "/sign-in";
                }
                return null;
              },
            ),
          ],
        ),
      ],
    ),
  ],
);
