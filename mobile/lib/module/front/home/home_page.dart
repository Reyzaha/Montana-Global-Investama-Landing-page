// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/helper/images.dart";
import "package:mgi_investor/module/front/home/home_bloc.dart";
import "package:mgi_investor/module/front/home/home_event.dart";
import "package:mgi_investor/module/front/home/home_state.dart";
import "package:mgi_investor/shared.dart";
import "package:smooth_corner/smooth_corner.dart";

class HomePage extends StatefulWidget {
  const HomePage({
    super.key,
  });

  @override
  HomePageState createState() => HomePageState();
}

class HomePageState extends State<HomePage> with WidgetsBindingObserver {
  bool loading = false;
  CompanyProfile? companyProfile;
  List<Project>? projects;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
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

  void refresh() {
    context.read<HomeBloc>().add(HomeLoad());
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "mgi_app_title".tr(),
      trailings: [
        IconButton(
          icon: Icon(
            Icons.language,
            size: Dimensions.size20,
            color: AppColors.onSurface(),
          ),
          onPressed: () async {
            final String nextLang = context.locale.languageCode == "id"
                ? "en"
                : "id";
            await Generals.changeLanguage(context, nextLang);
          },
        ),
        IconButton(
          icon: Icon(
            Shared.ACCOUNT != null
                ? Icons.account_circle
                : Icons.login,
            size: Dimensions.size20,
            color: AppColors.primary(),
          ),
          onPressed: () {
            if (Shared.ACCOUNT != null) {
              context.push("/portal");
            } else {
              context.push("/sign-in");
            }
          },
        ),
      ],
    );
  }

  Widget _buildHero() {
    return Container(
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size15),
          smoothness: 1,
          side: BorderSide(
            color: const Color(0xFFC5A059).withValues(alpha: 0.3),
          ),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Image.asset(
                Images.logo(),
                height: Dimensions.size35,
              ),
              const Spacer(),
              Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size10,
                  vertical: Dimensions.size5,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                    side: const BorderSide(color: Color(0xFFC5A059)),
                  ),
                  color: const Color(0xFFC5A059).withValues(alpha: 0.1),
                ),
                child: Text(
                  "real_sector_assets".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFFC5A059),
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size15),
          Text(
            "mgi_tagline".tr(),
            style: TextStyle(
              fontSize: Dimensions.text18,
              fontWeight: FontWeight.w700,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            companyProfile?.aboutGroup?.positioning ??
                "Manajer investasi per project terpercaya bagi pemodal sektor riil alat berat.",
            style: TextStyle(
              fontSize: Dimensions.text12,
              color: AppColors.onSurfaceVariant(),
              height: 1.5,
            ),
          ),
          SizedBox(height: Dimensions.size20),
          Row(
            children: [
              Expanded(
                child: FilledButton.icon(
                  onPressed: () => context.push("/projects"),
                  icon: Icon(Icons.explore, size: Dimensions.size15),
                  label: Text("explore_projects".tr()),
                ),
              ),
              SizedBox(width: Dimensions.size10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => context.push("/roi-calculator"),
                  icon: Icon(Icons.calculate, size: Dimensions.size15),
                  label: Text("roi_calculator".tr()),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStats() {
    final List<CompanyStat> stats = companyProfile?.stats ?? [];
    if (stats.isEmpty) {
      return const SizedBox();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size20),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: Dimensions.size10,
            mainAxisSpacing: Dimensions.size10,
            childAspectRatio: 1.6,
          ),
          itemCount: stats.length,
          itemBuilder: (BuildContext context, int index) {
            final stat = stats[index];
            return Container(
              padding: EdgeInsets.all(Dimensions.size10),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                  side: BorderSide(color: AppColors.outline()),
                ),
                color: AppColors.surfaceContainerLowest(),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    stat.value ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text16,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF0F2C59),
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    stat.label ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      fontWeight: FontWeight.w600,
                      color: AppColors.onSurface(),
                    ),
                  ),
                  Text(
                    stat.desc ?? "-",
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildPillars() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size25),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "ecosystem_pillars".tr(),
              style: TextStyle(
                fontSize: Dimensions.text16,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
            TextButton(
              onPressed: () => context.push("/company-profile"),
              child: Text("view_all_projects".tr()),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        _buildPillarCard(
          badge: "MIU",
          title: "miu_title".tr(),
          subtitle: "Solusi Alat Berat Terintegrasi — Ketersediaan Armada, Workshop Sentral Kebumen & Penempatan Lapangan",
          accentColor: const Color(0xFF0F2C59),
        ),
        SizedBox(height: Dimensions.size10),
        _buildPillarCard(
          badge: "MSI",
          title: "msi_title".tr(),
          subtitle: "Mitra Impor & Logistik Terpadu — Izin Kepabeanan Pelabuhan & Jalur Distribusi Proyek Industri",
          accentColor: const Color(0xFF1E3A8A),
        ),
        SizedBox(height: Dimensions.size10),
        _buildPillarCard(
          badge: "Mypurcase",
          title: "mypurcase_title".tr(),
          subtitle: "Platform Pengadaan Digital B2B — Transparansi Rantai Pasok Pengadaan Alat Berat & Suku Cadang",
          accentColor: const Color(0xFFC5A059),
        ),
      ],
    );
  }

  Widget _buildPillarCard({
    required String badge,
    required String title,
    required String subtitle,
    required Color accentColor,
  }) {
    return InkWell(
      onTap: () => context.push("/company-profile"),
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
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: EdgeInsets.symmetric(
                horizontal: Dimensions.size10,
                vertical: Dimensions.size5,
              ),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size5),
                  smoothness: 1,
                ),
                color: accentColor,
              ),
              child: Text(
                badge,
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
            SizedBox(width: Dimensions.size10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: Dimensions.text13,
                      fontWeight: FontWeight.bold,
                      color: AppColors.onSurface(),
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: AppColors.onSurfaceVariant(),
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              Icons.chevron_right,
              size: Dimensions.size20,
              color: AppColors.onSurfaceVariant(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTarifTeaser() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(color: Color(0xFF0F2C59)),
        ),
        color: const Color(0xFF0F2C59),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.verified_user,
                color: const Color(0xFFC5A059),
                size: Dimensions.size20,
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "governance_tarif".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            "Transparency • Accountability • Responsibility • Independency • Fairness",
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: FontWeight.w600,
              color: const Color(0xFFC5A059),
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            "Prinsip tata kelola yang baik diterapkan secara konsisten dalam alokasi modal dan laporan kinerja berkala.",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: Colors.white70,
              height: 1.4,
            ),
          ),
          SizedBox(height: Dimensions.size15),
          OutlinedButton(
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Color(0xFFC5A059)),
              foregroundColor: Colors.white,
            ),
            onPressed: () => context.push("/tarif-governance"),
            child: Text("governance_tarif".tr()),
          ),
        ],
      ),
    );
  }

  Widget _buildProjectsPreview() {
    final List<Project> list = projects ?? [];
    if (list.isEmpty) {
      return const SizedBox();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size25),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "active_projects".tr(),
              style: TextStyle(
                fontSize: Dimensions.text16,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
            TextButton(
              onPressed: () => context.push("/projects"),
              child: Text("view_all_projects".tr()),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: list.length > 3 ? 3 : list.length,
          separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
          itemBuilder: (BuildContext context, int index) {
            final project = list[index];
            final double percent = project.fundingTarget > 0
                ? (project.fundingCollected / project.fundingTarget).clamp(
                    0.0,
                    1.0,
                  )
                : 0.0;

            return InkWell(
              onTap: () => context.push("/projects/${project.id}"),
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
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: Dimensions.size10,
                            vertical: Dimensions.size4,
                          ),
                          decoration: ShapeDecoration(
                            shape: SmoothRectangleBorder(
                              borderRadius: BorderRadius.circular(
                                Dimensions.size5,
                              ),
                              smoothness: 1,
                            ),
                            color: const Color(0xFF0F2C59),
                          ),
                          child: Text(
                            project.city ?? "Indonesia",
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                        SizedBox(width: Dimensions.size10),
                        Text(
                          project.category ?? "Alat Berat",
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            color: AppColors.onSurfaceVariant(),
                          ),
                        ),
                        const Spacer(),
                        Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: Dimensions.size10,
                            vertical: Dimensions.size4,
                          ),
                          decoration: ShapeDecoration(
                            shape: SmoothRectangleBorder(
                              borderRadius: BorderRadius.circular(
                                Dimensions.size20,
                              ),
                              smoothness: 1,
                            ),
                            color: project.status == "Open"
                                ? Colors.green.shade700
                                : Colors.grey.shade600,
                          ),
                          child: Text(
                            (project.status ?? "Open").toUpperCase(),
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: Dimensions.size10),
                    Text(
                      project.title ?? "-",
                      style: TextStyle(
                        fontSize: Dimensions.text14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.onSurface(),
                      ),
                    ),
                    SizedBox(height: Dimensions.size10),
                    LinearProgressIndicator(
                      value: percent,
                      backgroundColor: AppColors.outline(),
                      valueColor: const AlwaysStoppedAnimation<Color>(
                        Color(0xFFC5A059),
                      ),
                      minHeight: Dimensions.size5,
                    ),
                    SizedBox(height: Dimensions.size5),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          "${"collected_funding".tr()}: Rp ${Formats.spell(project.fundingCollected)}",
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            color: AppColors.onSurfaceVariant(),
                          ),
                        ),
                        Text(
                          "${(percent * 100).toStringAsFixed(1)}%",
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F2C59),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildDisclaimer() {
    return Container(
      margin: EdgeInsets.symmetric(vertical: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: BorderSide(
            color: Colors.amber.shade700.withValues(alpha: 0.3),
          ),
        ),
        color: Colors.amber.withValues(alpha: 0.05),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.info_outline,
            size: Dimensions.size20,
            color: Colors.amber.shade800,
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Text(
              "disclaimer_investment_risk".tr(),
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: AppColors.onSurfaceVariant(),
                height: 1.5,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget body() {
    return RefreshIndicator(
      onRefresh: () async => refresh(),
      child: ListView(
        padding: EdgeInsets.all(Dimensions.size15),
        children: [
          _buildHero(),
          _buildStats(),
          _buildPillars(),
          _buildTarifTeaser(),
          _buildProjectsPreview(),
          _buildDisclaimer(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<HomeBloc, HomeState>(
      listener: (BuildContext context, HomeState state) {
        if (state is HomeLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is HomeLoadSuccess) {
          setState(() {
            companyProfile = state.companyProfile;
            projects = state.featuredProjects;
          });
        } else if (state is HomeLoadFinished) {
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
          onRefresh: () async => refresh(),
          statusBuilder: () {
            if (loading && projects == null) {
              return BaseBodyStatus.loading;
            }
            if (projects == null && companyProfile == null) {
              return BaseBodyStatus.fail;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
