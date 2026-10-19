import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/model/account.dart";
import "package:mgi_investor/shared.dart";
import "package:smooth_corner/smooth_corner.dart";

class PortalDashboardPage extends StatefulWidget {
  const PortalDashboardPage({
    super.key,
  });

  @override
  PortalDashboardPageState createState() => PortalDashboardPageState();
}

class PortalDashboardPageState extends State<PortalDashboardPage>
    with WidgetsBindingObserver {
  bool loading = false;
  List<Project> activeProjects = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    loadProjects();
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

  Future<void> loadProjects() async {
    try {
      setState(() {
        loading = true;
      });

      final response = await ApiManager.projects();
      if (response.statusCode == 200 && response.data is List) {
        activeProjects = (response.data as List)
            .map((e) => Project().parse(e as Map<String, dynamic>))
            .toList();
      }
    } catch (e) {
      if (kDebugMode) {
        print("Portal load projects: $e");
      }
    } finally {
      if (mounted) {
        setState(() {
          loading = false;
        });
      }
    }
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "investor_portal".tr(),
    );
  }

  Widget _buildInvestorCard() {
    final Account? user = Shared.ACCOUNT;

    return Container(
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(color: Color(0xFFC5A059)),
        ),
        color: const Color(0xFF0F2C59),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: Dimensions.size25,
                backgroundColor: const Color(0xFFC5A059),
                child: Text(
                  (user?.displayName().isNotEmpty ?? false)
                      ? user!.displayName().substring(0, 1).toUpperCase()
                      : "M",
                  style: TextStyle(
                    fontSize: Dimensions.text20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
              SizedBox(width: Dimensions.size15),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user?.displayName() ?? "Investor Terdaftar",
                      style: TextStyle(
                        fontSize: Dimensions.text16,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    SizedBox(height: Dimensions.size4),
                    Text(
                      user?.email ?? "-",
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => context.push("/portal/profile"),
                icon: const Icon(
                  Icons.settings_outlined,
                  color: Color(0xFFC5A059),
                ),
                tooltip: "Profil Investor",
              ),
            ],
          ),
          SizedBox(height: Dimensions.size15),
          Row(
            children: [
              Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size10,
                  vertical: Dimensions.size4,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                  ),
                  color: Colors.green.shade700,
                ),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle, size: 14, color: Colors.white),
                    SizedBox(width: Dimensions.size5),
                    Text(
                      "account_verified".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text10,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(width: Dimensions.size10),
              Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size10,
                  vertical: Dimensions.size4,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                  ),
                  color: Colors.white24,
                ),
                child: Text(
                  user?.type == AccountType.institution
                      ? "investor_institution".tr()
                      : "investor_individual".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPortfolioStats() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          "portfolio_overview".tr(),
          style: TextStyle(
            fontSize: Dimensions.text16,
            fontWeight: FontWeight.bold,
            color: AppColors.onSurface(),
          ),
        ),
        SizedBox(height: Dimensions.size10),
        Row(
          children: [
            Expanded(
              child: _buildStatTile(
                title: "Total Komitmen",
                value: "Rp 1.500.000.000",
                subtitle: "Kemitraan 3 Unit",
                color: const Color(0xFF0F2C59),
              ),
            ),
            SizedBox(width: Dimensions.size10),
            Expanded(
              child: _buildStatTile(
                title: "Dividen / Bulan",
                value: "Rp 35.775.000",
                subtitle: "Yield 28.62%/thn",
                color: const Color(0xFFC5A059),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatTile({
    required String title,
    required String value,
    required String subtitle,
    required Color color,
  }) {
    return Container(
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
          Text(
            title,
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            value,
            style: TextStyle(
              fontSize: Dimensions.text14,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          SizedBox(height: Dimensions.size2),
          Text(
            subtitle,
            style: TextStyle(
              fontSize: Dimensions.text10,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDividendSchedule() {
    final List<Map<String, String>> schedules = [
      {
        "period": "1 Nov 2026",
        "amount": "Rp 35.775.000",
        "status": "Menunggu Siklus",
        "unit": "Excavator PC200 (MSI-BALIKPAPAN-01)",
      },
      {
        "period": "1 Des 2026",
        "amount": "Rp 35.775.000",
        "status": "Terjadwal",
        "unit": "Excavator PC200 (MSI-BALIKPAPAN-01)",
      },
      {
        "period": "1 Jan 2027",
        "amount": "Rp 35.775.000",
        "status": "Terjadwal",
        "unit": "Excavator PC200 (MSI-BALIKPAPAN-01)",
      },
    ];

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size20),
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
              Icon(
                Icons.calendar_month,
                size: Dimensions.size20,
                color: const Color(0xFF0F2C59),
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "dividend_schedule".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.onSurface(),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size15),
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: schedules.length,
            separatorBuilder: (_, __) => Divider(
              color: AppColors.outline(),
              height: Dimensions.size15,
            ),
            itemBuilder: (context, index) {
              final item = schedules[index];
              return Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item["period"]!,
                          style: TextStyle(
                            fontSize: Dimensions.text12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.onSurface(),
                          ),
                        ),
                        SizedBox(height: Dimensions.size2),
                        Text(
                          item["unit"]!,
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            color: AppColors.onSurfaceVariant(),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        item["amount"]!,
                        style: TextStyle(
                          fontSize: Dimensions.text13,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFFC5A059),
                        ),
                      ),
                      SizedBox(height: Dimensions.size2),
                      Text(
                        item["status"]!,
                        style: TextStyle(
                          fontSize: Dimensions.text10,
                          fontWeight: FontWeight.w600,
                          color: Colors.green.shade700,
                        ),
                      ),
                    ],
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildProtectedDocuments() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "Dokumen & Proposal Terlindungi",
                style: TextStyle(
                  fontSize: Dimensions.text16,
                  fontWeight: FontWeight.bold,
                  color: AppColors.onSurface(),
                ),
              ),
              TextButton(
                onPressed: () => context.push("/projects"),
                child: Text(
                  "Lihat Semua",
                  style: TextStyle(
                    fontSize: Dimensions.text12,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFFC5A059),
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          if (activeProjects.isEmpty)
            Center(
              child: Padding(
                padding: EdgeInsets.all(Dimensions.size20),
                child: Text(
                  "Memuat proposal proyek...",
                  style: TextStyle(
                    fontSize: Dimensions.text12,
                    color: AppColors.onSurfaceVariant(),
                  ),
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: activeProjects.take(2).length,
              separatorBuilder: (_, __) =>
                  SizedBox(height: Dimensions.size10),
              itemBuilder: (context, index) {
                final project = activeProjects[index];
                return Container(
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
                    children: [
                      Container(
                        padding: EdgeInsets.all(Dimensions.size10),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius: BorderRadius.circular(
                              Dimensions.size10,
                            ),
                            smoothness: 1,
                          ),
                          color: const Color(0xFF0F2C59),
                        ),
                        child: const Icon(
                          Icons.picture_as_pdf,
                          color: Color(0xFFC5A059),
                        ),
                      ),
                      SizedBox(width: Dimensions.size10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              project.title ?? "-",
                              style: TextStyle(
                                fontSize: Dimensions.text13,
                                fontWeight: FontWeight.bold,
                                color: AppColors.onSurface(),
                              ),
                            ),
                            SizedBox(height: Dimensions.size2),
                            Text(
                              "Target: Rp ${Formats.spell(project.fundingTarget)} • ${project.city ?? 'Indonesia'}",
                              style: TextStyle(
                                fontSize: Dimensions.text10,
                                color: AppColors.onSurfaceVariant(),
                              ),
                            ),
                          ],
                        ),
                      ),
                      SizedBox(width: Dimensions.size10),
                      FilledButton(
                        style: FilledButton.styleFrom(
                          padding: EdgeInsets.symmetric(
                            horizontal: Dimensions.size10,
                            vertical: Dimensions.size5,
                          ),
                          backgroundColor: const Color(0xFFC5A059),
                        ),
                        onPressed: () => context.push(
                          "/portal/projects/${project.id}",
                        ),
                        child: Text(
                          "Buka",
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  Widget body() {
    return RefreshIndicator(
      onRefresh: loadProjects,
      child: ListView(
        padding: EdgeInsets.all(Dimensions.size15),
        children: [
          _buildInvestorCard(),
          SizedBox(height: Dimensions.size20),
          _buildPortfolioStats(),
          _buildDividendSchedule(),
          _buildProtectedDocuments(),
          SizedBox(height: Dimensions.size30),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Background(
      child: BaseScaffold(
        context: context,
        backgroundColor: Colors.transparent,
        appBar: appBar(),
        contentBuilder: body,
      ),
    );
  }
}
