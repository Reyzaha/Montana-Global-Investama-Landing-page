// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/helper/images.dart";
import "package:mgi_investor/module/front/home/home_bloc.dart";
import "package:mgi_investor/module/front/home/home_event.dart";
import "package:mgi_investor/module/front/home/home_state.dart";
import "package:mgi_investor/widget/empty_state.dart";
import "package:mgi_investor/widget/error_state.dart";
import "package:mgi_investor/widget/project_card.dart";
import "package:mgi_investor/widget/skeleton_loader.dart";
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
  bool isError = false;
  CompanyProfile? companyProfile;
  List<Project>? projects;
  String? disclaimerText;

  int heroSlideIndex = 0;
  int pillarIndex = 0;
  final PageController heroController = PageController();
  final PageController pillarController = PageController(viewportFraction: 0.92);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    heroController.dispose();
    pillarController.dispose();
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void refresh() {
    setState(() {
      isError = false;
    });
    context.read<HomeBloc>().add(HomeLoad());
  }

  BaseAppBar appBar() {
    final String currentLang = context.locale.languageCode;
    final bool isId = currentLang == "id";

    return BaseAppBar(
      context: context,
      name: "MGI",
      leading: Padding(
        padding: EdgeInsets.all(Dimensions.size10),
        child: Image.asset(
          Images.brandLogo(),
          errorBuilder: (_, __, ___) => const Icon(
            Icons.token,
            color: Color(0xFFC5A059),
          ),
        ),
      ),
      trailings: [
        Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                    side: BorderSide(
                      color: AppColors.outline().withValues(alpha: 0.6),
                      width: 1.0,
                    ),
                  ),
                  color: AppColors.surfaceContainerLowest(),
                ),
                padding: const EdgeInsets.all(2),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildLangButton(
                      label: "IDN",
                      active: isId,
                      onTap: () => Generals.changeLanguage(context, "id"),
                    ),
                    _buildLangButton(
                      label: "EN",
                      active: !isId,
                      onTap: () => Generals.changeLanguage(context, "en"),
                    ),
                  ],
                ),
              ),
              SizedBox(width: Dimensions.size10),
              InkWell(
                onTap: () {
                  context.push("/dashboard");
                },
                customBorder: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size20),
                  smoothness: 1,
                ),
                child: Ink(
                  padding: EdgeInsets.symmetric(
                    horizontal: Dimensions.size10,
                    vertical: Dimensions.size5,
                  ),
                  decoration: ShapeDecoration(
                    shape: SmoothRectangleBorder(
                      borderRadius: BorderRadius.circular(Dimensions.size20),
                      smoothness: 1,
                    ),
                    color: const Color(0xFF0F2C59),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.person_outline,
                        color: Colors.white,
                        size: 15,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        "portal_button".tr(),
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              SizedBox(width: Dimensions.size10),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildLangButton({
    required String label,
    required bool active,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size15),
        smoothness: 1,
      ),
      child: Container(
        padding: EdgeInsets.symmetric(
          horizontal: Dimensions.size10,
          vertical: Dimensions.size4,
        ),
        decoration: ShapeDecoration(
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size15),
            smoothness: 1,
          ),
          color: active ? const Color(0xFF0F2C59) : Colors.transparent,
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: Dimensions.text10,
            fontWeight: FontWeight.bold,
            color: active ? Colors.white : AppColors.onSurfaceVariant(),
            letterSpacing: 0.5,
          ),
        ),
      ),
    );
  }

  List<Map<String, String>> _getHeroSlides(bool isId) {
    final List<Map<String, String>> slides = [];

    if (companyProfile != null) {
      final String groupEyebrow =
          (companyProfile?.groupName ?? companyProfile?.shortName ?? "MGI")
              .toUpperCase();
      final String mainTagline = isId
          ? (companyProfile?.taglineId ?? "")
          : (companyProfile?.taglineEn ?? "");
      final String subtitle = companyProfile?.aboutGroup?.subtitle ??
          companyProfile?.companyName ??
          "";
      final String desc = companyProfile?.aboutGroup?.positioning ??
          companyProfile?.aboutGroup?.description ??
          "";

      slides.add({
        "eyebrow": groupEyebrow,
        "line1": mainTagline.isNotEmpty
            ? mainTagline
            : (companyProfile?.companyName ?? "PT Montana Global Investama"),
        "line2": subtitle,
        "desc": desc,
      });

      for (final entity in companyProfile!.entities) {
        if ((entity.code ?? "").toUpperCase() == "MGI") {
          continue;
        }
        slides.add({
          "eyebrow": (entity.badge ?? entity.code ?? "").toUpperCase(),
          "line1": entity.name ?? entity.code ?? "",
          "line2": entity.slogan ?? "",
          "desc": entity.description ?? entity.focus ?? "",
        });
      }

      for (final section in companyProfile!.sections) {
        if (section.id == "nilai-utama") {
          final String letters = section.items
              .map((i) => i.name ?? "")
              .take(3)
              .join(" • ");
          slides.add({
            "eyebrow": (section.title ?? "TATA KELOLA").toUpperCase(),
            "line1": section.subtitle ?? "Prinsip TARIF",
            "line2": letters,
            "desc": section.items.isNotEmpty
                ? (section.items.first.desc ?? "")
                : (section.content ?? ""),
          });
          break;
        }
      }
    }

    if (slides.isEmpty) {
      slides.add({
        "eyebrow": "PT MONTANA GLOBAL INVESTAMA",
        "line1": "real_assets".tr(),
        "line2": "sustainable_value".tr(),
        "desc": "strategic_partnership_desc".tr(),
      });
    }

    return slides;
  }

  Widget _buildHero() {
    final String currentLang = context.locale.languageCode;
    final bool isId = currentLang == "id";
    final List<Map<String, String>> slides = _getHeroSlides(isId);
    final int safeSlideIndex =
        slides.isEmpty ? 0 : heroSlideIndex.clamp(0, slides.length - 1);

    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size25),
          smoothness: 1,
        ),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFF0F2C59),
            Color(0xFF0A1F42),
          ],
        ),
        shadows: [
          BoxShadow(
            color: const Color(0xFF0F2C59).withValues(alpha: 0.25),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -60,
            top: -50,
            child: Container(
              width: 260,
              height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.05),
                  width: 1.5,
                ),
              ),
            ),
          ),
          Positioned(
            right: -20,
            top: -10,
            child: Container(
              width: 180,
              height: 180,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.08),
                  width: 1.5,
                ),
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.all(Dimensions.size20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                SizedBox(
                  height: 185,
                  child: PageView.builder(
                    controller: heroController,
                    itemCount: slides.length,
                    onPageChanged: (int index) {
                      setState(() {
                        heroSlideIndex = index;
                      });
                    },
                    itemBuilder: (BuildContext context, int index) {
                      final slide = slides[index];
                      return Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            slide["eyebrow"]!,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFFC5A059),
                              letterSpacing: 1.2,
                            ),
                          ),
                          SizedBox(height: Dimensions.size10),
                          Text(
                            slide["line1"]!,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: Dimensions.text20,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              height: 1.15,
                            ),
                          ),
                          if ((slide["line2"] ?? "").isNotEmpty) ...[
                            SizedBox(height: Dimensions.size4),
                            Text(
                              slide["line2"]!,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontSize: Dimensions.text14,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFFC5A059),
                                height: 1.15,
                              ),
                            ),
                          ],
                          SizedBox(height: Dimensions.size10),
                          Text(
                            slide["desc"]!,
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: Dimensions.text11,
                              color: Colors.white70,
                              height: 1.4,
                            ),
                          ),
                        ],
                      );
                    },
                  ),
                ),
                SizedBox(height: Dimensions.size15),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    InkWell(
                      onTap: () => context.push("/projects"),
                      customBorder: SmoothRectangleBorder(
                        borderRadius: BorderRadius.circular(Dimensions.size20),
                        smoothness: 1,
                      ),
                      child: Ink(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.size20,
                          vertical: Dimensions.size10,
                        ),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius:
                                BorderRadius.circular(Dimensions.size20),
                            smoothness: 1,
                          ),
                          color: const Color(0xFFC5A059),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              "explore_projects".tr(),
                              style: TextStyle(
                                fontSize: Dimensions.text12,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF0F2C59),
                              ),
                            ),
                            SizedBox(width: Dimensions.size5),
                            const Icon(
                              Icons.chevron_right,
                              size: 16,
                              color: Color(0xFF0F2C59),
                            ),
                          ],
                        ),
                      ),
                    ),
                    Text(
                      "0${safeSlideIndex + 1} / 0${slides.length}",
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        fontWeight: FontWeight.bold,
                        color: Colors.white54,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStats() {
    final List<CompanyStat> stats = companyProfile?.stats ?? [];
    if (stats.isEmpty) {
      return const SizedBox.shrink();
    }

    final List<IconData> statIcons = [
      Icons.precision_manufacturing_outlined,
      Icons.trending_up,
      Icons.apartment_outlined,
      Icons.hub_outlined,
      Icons.account_balance_wallet_outlined,
      Icons.check_circle_outline,
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size25),
        Text(
          "measured_performance".tr().toUpperCase(),
          style: TextStyle(
            fontSize: Dimensions.text10,
            fontWeight: FontWeight.bold,
            color: const Color(0xFFC5A059),
            letterSpacing: 1.2,
          ),
        ),
        SizedBox(height: Dimensions.size4),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              "mgi_in_numbers".tr(),
              style: TextStyle(
                fontSize: Dimensions.text20,
                fontWeight: FontWeight.w900,
                color: const Color(0xFF0F2C59),
              ),
            ),
            if (companyProfile?.foundedYear != null)
              Text(
                "Est. ${companyProfile!.foundedYear}",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: AppColors.onSurfaceVariant(),
                  fontWeight: FontWeight.w500,
                ),
              ),
          ],
        ),
        SizedBox(height: Dimensions.size15),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: Dimensions.size10,
            mainAxisSpacing: Dimensions.size10,
            childAspectRatio: 1.25,
          ),
          itemCount: stats.length,
          itemBuilder: (BuildContext context, int index) {
            final stat = stats[index];
            final icon = statIcons[index % statIcons.length];

            return Container(
              padding: EdgeInsets.all(Dimensions.size15),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size20),
                  smoothness: 1,
                  side: BorderSide(color: AppColors.outline()),
                ),
                color: AppColors.surfaceContainerLowest(),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Icon(
                        icon,
                        size: Dimensions.size20,
                        color: const Color(0xFFC5A059),
                      ),
                    ],
                  ),
                  Text(
                    stat.value ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text20,
                      fontWeight: FontWeight.w900,
                      color: const Color(0xFF0F2C59),
                      height: 1.0,
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        stat.label ?? "-",
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          fontWeight: FontWeight.bold,
                          color: AppColors.onSurface(),
                          height: 1.2,
                        ),
                      ),
                      if ((stat.desc ?? "").isNotEmpty)
                        Text(
                          stat.desc!,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            color: AppColors.onSurfaceVariant(),
                            height: 1.2,
                          ),
                        ),
                    ],
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
    final List<EcosystemEntity> pillars = (companyProfile?.entities ?? [])
        .where((e) => (e.code ?? "").toUpperCase() != "MGI")
        .toList();

    if (pillars.isEmpty) {
      return const SizedBox.shrink();
    }

    final int safePillarIndex = pillarIndex.clamp(0, pillars.length - 1);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size25),
        Text(
          "ecosystem_integrated".tr().toUpperCase(),
          style: TextStyle(
            fontSize: Dimensions.text10,
            fontWeight: FontWeight.bold,
            color: const Color(0xFFC5A059),
            letterSpacing: 1.2,
          ),
        ),
        SizedBox(height: Dimensions.size4),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "three_growth_pillars".tr(),
              style: TextStyle(
                fontSize: Dimensions.text20,
                fontWeight: FontWeight.w900,
                color: const Color(0xFF0F2C59),
              ),
            ),
            Text(
              "${safePillarIndex + 1} / ${pillars.length}",
              style: TextStyle(
                fontSize: Dimensions.text11,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size15),
        SizedBox(
          height: Dimensions.size100 + Dimensions.size50,
          child: PageView.builder(
            controller: pillarController,
            padEnds: false,
            itemCount: pillars.length,
            onPageChanged: (int index) {
              setState(() {
                pillarIndex = index;
              });
            },
            itemBuilder: (BuildContext context, int index) {
              final entity = pillars[index];
              final Color badgeColor = (entity.code ?? "").toUpperCase() == "MIU"
                  ? const Color(0xFF0F2C59)
                  : ((entity.code ?? "").toUpperCase() == "MSI"
                      ? const Color(0xFF1E3A8A)
                      : const Color(0xFFC5A059));

              return Padding(
                padding: EdgeInsets.only(right: Dimensions.size10),
                child: InkWell(
                  onTap: () => context.push("/company-profile"),
                  customBorder: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                  ),
                  child: Ink(
                    padding: EdgeInsets.all(Dimensions.size15),
                    decoration: ShapeDecoration(
                      shape: SmoothRectangleBorder(
                        borderRadius: BorderRadius.circular(Dimensions.size20),
                        smoothness: 1,
                        side: BorderSide(color: AppColors.outline()),
                      ),
                      color: AppColors.surfaceContainerLowest(),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: EdgeInsets.symmetric(
                                horizontal: Dimensions.size10,
                                vertical: Dimensions.size4,
                              ),
                              decoration: ShapeDecoration(
                                shape: SmoothRectangleBorder(
                                  borderRadius:
                                      BorderRadius.circular(Dimensions.size5),
                                  smoothness: 1,
                                ),
                                color: badgeColor,
                              ),
                              child: Text(
                                entity.badge ?? entity.code ?? "Unit",
                                style: TextStyle(
                                  fontSize: Dimensions.text10,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                            if ((entity.location ?? "").isNotEmpty)
                              Text(
                                "Kebumen",
                                style: TextStyle(
                                  fontSize: Dimensions.text10,
                                  fontWeight: FontWeight.w600,
                                  color: const Color(0xFFC5A059),
                                ),
                              )
                            else if ((entity.slogan ?? "").isNotEmpty)
                              Flexible(
                                child: Text(
                                  entity.slogan!,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: Dimensions.text10,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFFC5A059),
                                  ),
                                ),
                              ),
                          ],
                        ),
                        Text(
                          entity.name ?? entity.code ?? "",
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: Dimensions.text14,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F2C59),
                          ),
                        ),
                        Text(
                          entity.description ?? entity.focus ?? "",
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            color: AppColors.onSurfaceVariant(),
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildProjectsPreview() {
    final List<Project> list = projects ?? [];

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
              child: Text(
                "view_all_projects".tr(),
                style: const TextStyle(color: Color(0xFFC5A059)),
              ),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        if (list.isEmpty)
          EmptyState(
            title: "empty_projects_title".tr(),
            message: "empty_projects_html_desc".tr(),
            actionLabel: "reload".tr(),
            onAction: refresh,
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: list.length > 3 ? 3 : list.length,
            separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
            itemBuilder: (BuildContext context, int index) {
              return ProjectCard(project: list[index]);
            },
          ),
      ],
    );
  }

  Widget _buildTarifTeaser() {
    ProfileSection? tarifSection;
    if (companyProfile != null) {
      for (final s in companyProfile!.sections) {
        if (s.id == "nilai-utama") {
          tarifSection = s;
          break;
        }
      }
    }

    final String title = tarifSection?.title ?? "governance_tarif".tr();
    final String principles =
        tarifSection != null && tarifSection.items.isNotEmpty
            ? tarifSection.items.map((i) => i.name ?? "").join(" • ")
            : "Transparency • Accountability • Responsibility • Independency • Fairness";
    final String desc = tarifSection?.subtitle ??
        tarifSection?.content ??
        (companyProfile?.aboutGroup?.positioning ?? "");

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size20),
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
              Icon(
                Icons.verified_user_outlined,
                color: const Color(0xFF0F2C59),
                size: Dimensions.size20,
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                title,
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F2C59),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            principles,
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: FontWeight.w600,
              color: const Color(0xFFC5A059),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          if (desc.isNotEmpty)
            Text(
              desc,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: AppColors.onSurfaceVariant(),
                height: 1.4,
              ),
            ),
          SizedBox(height: Dimensions.size15),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFF0F2C59)),
                foregroundColor: const Color(0xFF0F2C59),
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                ),
              ),
              onPressed: () => context.push("/tarif-governance"),
              child: Text("learn_more".tr()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDisclaimer() {
    final String text = (disclaimerText != null && disclaimerText!.isNotEmpty)
        ? disclaimerText!
        : "disclaimer_investment_risk".tr();

    return Container(
      margin: EdgeInsets.symmetric(vertical: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size15),
          smoothness: 1,
          side: BorderSide(
            color: Colors.amber.shade700.withValues(alpha: 0.2),
          ),
        ),
        color: Colors.amber.withValues(alpha: 0.04),
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
              text,
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

  Widget _buildLoadingSkeleton() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        SkeletonLoader.card(height: Dimensions.size100 * 2),
        SizedBox(height: Dimensions.size20),
        SkeletonLoader.statsGrid(count: 4),
        SizedBox(height: Dimensions.size25),
        SkeletonLoader.line(
          width: Dimensions.size100,
          height: Dimensions.size20,
        ),
        SizedBox(height: Dimensions.size10),
        SkeletonLoader.projectCard(),
        SizedBox(height: Dimensions.size10),
        SkeletonLoader.projectCard(),
      ],
    );
  }

  Widget body() {
    if (loading && projects == null && companyProfile == null) {
      return _buildLoadingSkeleton();
    }

    if (isError && projects == null && companyProfile == null) {
      return ErrorState(
        title: "no_data_title".tr(),
        message: "connection_error_desc".tr(),
        onRetry: refresh,
      );
    }

    return RefreshIndicator(
      onRefresh: () async => refresh(),
      child: ListView(
        padding: EdgeInsets.all(Dimensions.size15),
        children: [
          _buildHero(),
          _buildStats(),
          _buildPillars(),
          _buildProjectsPreview(),
          _buildTarifTeaser(),
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
            disclaimerText = state.disclaimerText;
            isError = false;
          });
        } else if (state is HomeLoadFinished) {
          setState(() {
            loading = false;
            if (projects == null && companyProfile == null) {
              isError = true;
            }
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
            if (loading && projects == null && companyProfile == null) {
              return BaseBodyStatus.loading;
            }
            if (isError && projects == null && companyProfile == null) {
              return BaseBodyStatus.fail;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
