import "dart:convert";

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/investor/investor_dashboard.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/model/account.dart";
import "package:mgi_investor/shared.dart";
import "package:mgi_investor/widget/portal_widgets.dart";
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
  int updateIndex = 0;
  List<Project> activeProjects = [];
  InvestorDashboardResponse? dashboardData;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    loadData();
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

  Future<void> loadData() async {
    try {
      setState(() {
        loading = true;
      });

      final Response dashResponse = await ApiManager.investorDashboard();
      if (dashResponse.statusCode == 200) {
        dynamic data = dashResponse.data;
        if (data is String) {
          try {
            data = jsonDecode(data);
          } catch (_) {}
        }
        if (data is Map<String, dynamic>) {
          dashboardData = InvestorDashboardResponse().parse(data);
        }
      }

      final Response projResponse = await ApiManager.projects();
      if (projResponse.statusCode == 200) {
        dynamic pData = projResponse.data;
        if (pData is String) {
          try {
            pData = jsonDecode(pData);
          } catch (_) {}
        }
        if (pData is List) {
          activeProjects = pData
              .map((e) => Project().parse(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (e) {
      if (kDebugMode) {
        print("Portal load data: $e");
      }
    } finally {
      if (mounted) {
        setState(() {
          loading = false;
        });
      }
    }
  }

  String? resolveImage(String? raw) {
    final String? value = raw?.trim();
    if (value == null || value.isEmpty) {
      return null;
    }
    if (value.startsWith("http://") || value.startsWith("https://")) {
      return value;
    }
    final String clean = value.startsWith("/") ? value.substring(1) : value;
    return "https://montanainvestama.com/$clean";
  }

  String initialOf(Account? user) {
    final String name = user?.displayName() ?? "";
    return name.isNotEmpty ? name.substring(0, 1).toUpperCase() : "M";
  }

  BaseAppBar appBar() {
    final int unreadCount = dashboardData?.unreadNotificationsCount ?? 0;

    return BaseAppBar(
      context: context,
      name: "investor_portal".tr(),
      trailings: [
        Stack(
          alignment: Alignment.center,
          children: [
            IconButton(
              onPressed: () async {
                await context.push("/portal/notifications");
                loadData();
              },
              icon: const Icon(
                Icons.notifications_outlined,
                color: portalGold,
              ),
              tooltip: "notifications".tr(),
            ),
            if (unreadCount > 0)
              Positioned(
                top: Dimensions.size5,
                right: Dimensions.size5,
                child: Container(
                  padding: EdgeInsets.symmetric(
                    horizontal: Dimensions.size5,
                    vertical: Dimensions.size2,
                  ),
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.redAccent,
                  ),
                  child: Text(
                    unreadCount > 9 ? "9+" : "$unreadCount",
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
          ],
        ),
        InkWell(
          onTap: () => context.push("/portal/profile"),
          customBorder: const CircleBorder(),
          child: Container(
            width: Dimensions.size35,
            height: Dimensions.size35,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: portalGoldSoft,
              border: Border.all(color: portalGold),
            ),
            child: Text(
              initialOf(Shared.ACCOUNT),
              style: TextStyle(
                fontSize: Dimensions.text12,
                fontWeight: FontWeight.bold,
                color: portalNavy,
              ),
            ),
          ),
        ),
        SizedBox(width: Dimensions.size10),
      ],
    );
  }

  Widget _buildHero() {
    final Account? user = Shared.ACCOUNT;
    final InvestorMetrics metrics =
        dashboardData?.metrics ?? InvestorMetrics();

    return PortfolioHeroCard(
      name: user?.displayName() ?? "Investor MGI",
      initial: initialOf(user),
      totalCommitted: "Rp ${Formats.spell(metrics.totalInvested)}",
      dividendsReceived: "Rp ${Formats.spell(metrics.totalPayoutReceived)}",
      activeCount: metrics.activeProjectsCount,
    );
  }

  Widget _buildDividendCalendar() {
    final List<InvestorPortfolioItem> portfolios =
        dashboardData?.portfolios ?? [];
    final InvestorPortfolioItem? next = portfolios.isNotEmpty
        ? portfolios.firstWhere(
            (InvestorPortfolioItem e) => e.nextPayoutDate != null,
            orElse: () => portfolios.first,
          )
        : null;
    final String rawDate = next?.nextPayoutDate ??
        dashboardData?.metrics.nextPayoutDate ??
        next?.endDate ??
        "-";
    final DateTime? date = DateTime.tryParse(rawDate);

    final List<String> labels = [];
    if (date != null) {
      for (int i = 0; i < 4; i++) {
        labels.add(
          DateFormat("MMM", context.locale.languageCode).format(
            DateTime(date.year, date.month + (i * 3)),
          ),
        );
      }
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        PortalSectionHeader(
          eyebrow: "next_distribution".tr(),
          title: "dividend_calendar".tr(),
          trailing: const Icon(
            Icons.event_note_outlined,
            color: portalGold,
          ),
        ),
        if (next == null)
          Container(
            width: double.infinity,
            padding: EdgeInsets.all(Dimensions.size15),
            decoration: portalCardDecoration(),
            child: Text(
              "no_dividend_schedule".tr(),
              style: TextStyle(
                fontSize: Dimensions.text12,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          )
        else ...[
          DividendDateCard(
            date: date,
            rawDate: rawDate,
            title: next.projectTitle ??
                next.allocatedUnits ??
                "Proyek Investasi MGI",
            amount: "Rp ${Formats.spell(next.payoutReceived)}",
            status: "${"scheduled".tr()} • ${next.status ?? "-"}",
          ),
          if (labels.isNotEmpty) TimelineDots(labels: labels),
        ],
      ],
    );
  }

  Widget _buildDocuments() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        PortalSectionHeader(
          eyebrow: "confidential_center".tr(),
          title: "investor_documents".tr(),
          trailing: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.lock_outline,
                size: Dimensions.size15,
                color: portalGold,
              ),
              SizedBox(width: Dimensions.size4),
              Text(
                "secure".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  fontWeight: FontWeight.bold,
                  color: portalGold,
                ),
              ),
            ],
          ),
        ),
        if (activeProjects.isEmpty)
          Padding(
            padding: EdgeInsets.all(Dimensions.size15),
            child: Text(
              "loading".tr(),
              style: TextStyle(
                fontSize: Dimensions.text12,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: activeProjects.take(2).length,
            separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
            itemBuilder: (BuildContext context, int index) {
              final Project project = activeProjects[index];

              return DocumentTile(
                title: project.title ?? "-",
                meta:
                    "PDF • ${project.city ?? "Indonesia"} • ${"watermarked".tr()}",
                onTap: () => context.push("/portal/projects/${project.id}"),
              );
            },
          ),
      ],
    );
  }

  Widget _buildBillingsSection() {
    final List<InvestorBillingItem> billings = dashboardData?.billings ?? [];
    if (billings.isEmpty) {
      return const SizedBox();
    }

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: portalCardDecoration(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.receipt_long_outlined,
                size: Dimensions.size20,
                color: portalGold,
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "billings_and_receipts".tr(),
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
            itemCount: billings.take(3).length,
            separatorBuilder: (_, __) => Divider(
              color: AppColors.outline(),
              height: Dimensions.size15,
            ),
            itemBuilder: (context, index) {
              final InvestorBillingItem item = billings[index];
              final bool isPositive = item.flowType?.toLowerCase() == "in";

              return Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.invoiceNumber ?? "INV-MGI",
                          style: TextStyle(
                            fontSize: Dimensions.text12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.onSurface(),
                          ),
                        ),
                        SizedBox(height: Dimensions.size2),
                        Text(
                          "${item.projectTitle ?? 'Montana Global Investama'} • ${item.transactionDate ?? '-'}",
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
                        "${isPositive ? '+' : ''}Rp ${Formats.spell(item.amount)}",
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.bold,
                          color: isPositive
                              ? Colors.green.shade700
                              : AppColors.onSurface(),
                        ),
                      ),
                      SizedBox(height: Dimensions.size2),
                      Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.size5,
                          vertical: Dimensions.size2,
                        ),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius:
                                BorderRadius.circular(Dimensions.size20),
                            smoothness: 1,
                          ),
                          color: Colors.green.shade50,
                        ),
                        child: Text(
                          item.status ?? "Verified",
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.w600,
                            color: Colors.green.shade800,
                          ),
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

  Widget _buildFieldUpdates() {
    final List<CampaignUpdateItem> updates =
        dashboardData?.campaignUpdates ?? [];
    if (updates.isEmpty) {
      return const SizedBox();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        PortalSectionHeader(
          eyebrow: "field_intelligence".tr(),
          title: "operational_updates".tr(),
          trailing: Text(
            "${updateIndex + 1} / ${updates.length}",
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
        ),
        SizedBox(
          height: Dimensions.size100 * 3 + Dimensions.size80,
          child: PageView.builder(
            padEnds: false,
            controller: PageController(viewportFraction: 0.92),
            itemCount: updates.length,
            onPageChanged: (int i) {
              setState(() {
                updateIndex = i;
              });
            },
            itemBuilder: (BuildContext context, int index) {
              final CampaignUpdateItem update = updates[index];

              return Padding(
                padding: EdgeInsets.only(right: Dimensions.size10),
                child: FieldUpdateCard(
                  imageUrl: resolveImage(update.imageUrl),
                  dateLabel: update.updateDate ?? "",
                  location: update.projectTitle ?? "-",
                  title: update.title ?? "Pembaruan Operasional",
                  description: update.description ?? "",
                  progress: update.progressPercentage,
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget body() {
    return RefreshIndicator(
      onRefresh: loadData,
      child: ListView(
        padding: EdgeInsets.all(Dimensions.size15),
        children: [
          _buildHero(),
          _buildDividendCalendar(),
          _buildDocuments(),
          _buildFieldUpdates(),
          _buildBillingsSection(),
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
