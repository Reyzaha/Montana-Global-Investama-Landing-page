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
import "package:mgi_investor/helper/images.dart";
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
    final String name = user?.displayName().trim() ?? "";
    if (name.isEmpty) {
      return "M";
    }
    final List<String> parts =
        name.split(RegExp(r"\s+")).where((s) => s.isNotEmpty).toList();
    if (parts.length >= 2) {
      return "${parts[0][0]}${parts[1][0]}".toUpperCase();
    }
    return name.substring(0, name.length >= 2 ? 2 : 1).toUpperCase();
  }

  BaseAppBar appBar() {
    final int unreadCount = dashboardData?.unreadNotificationsCount ?? 0;

    return BaseAppBar(
      context: context,
      leading: Padding(
        padding: EdgeInsets.all(Dimensions.size10),
        child: Image.asset(
          Images.brandLogo(),
          errorBuilder: (_, __, ___) => const Icon(
            Icons.token,
            color: portalGold,
          ),
        ),
      ),
      name: "MGI",
      trailings: [
        Stack(
          alignment: Alignment.center,
          children: [
            InkWell(
              onTap: () async {
                await context.push("/portal/notifications");
                loadData();
              },
              customBorder: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size10),
                smoothness: 1,
              ),
              child: Container(
                width: Dimensions.size35,
                height: Dimensions.size35,
                alignment: Alignment.center,
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size10),
                    smoothness: 1,
                    side: BorderSide(color: AppColors.outline()),
                  ),
                  color: AppColors.surfaceContainerLowest(),
                ),
                child: Icon(
                  Icons.assignment_outlined,
                  size: Dimensions.size20,
                  color: portalNavy,
                ),
              ),
            ),
            if (unreadCount > 0)
              Positioned(
                top: Dimensions.size4,
                right: Dimensions.size4,
                child: Container(
                  width: Dimensions.size5 + Dimensions.size3,
                  height: Dimensions.size5 + Dimensions.size3,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: portalGold,
                  ),
                ),
              ),
          ],
        ),
        SizedBox(width: Dimensions.size10),
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
              border: Border.all(color: portalGold, width: 1.5),
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
        SizedBox(width: Dimensions.size15),
      ],
    );
  }

  Widget _buildHero() {
    final Account? user = Shared.ACCOUNT;
    final InvestorMetrics metrics =
        dashboardData?.metrics ?? InvestorMetrics();
    final List<InvestorPortfolioItem> portfolios =
        dashboardData?.portfolios ?? [];
    final String? investorCode = portfolios.isNotEmpty
        ? portfolios.first.contractNumber
        : (user?.id != null
            ? "MGI-INV-2026-${user!.id.toString().padLeft(4, '0')}"
            : "MGI-INV-2026-0088");
    final String roiTrend =
        metrics.estRoiRate.isNotEmpty && metrics.estRoiRate != "0%"
            ? "+${metrics.estRoiRate} ${"running_value".tr()}"
            : "+8,4% ${"running_value".tr()}";

    return PortfolioHeroCard(
      name: user?.displayName() ?? "Investor MGI",
      initial: initialOf(user),
      accountType: user?.type == AccountType.institution
          ? "INSTITUTIONAL INVESTOR"
          : "PRIVATE INVESTOR",
      investorCode: investorCode,
      roiTrend: roiTrend,
      totalCommitted: "Rp ${Formats.spell(metrics.totalInvested)}",
      dividendsReceived: "Rp ${Formats.spell(metrics.totalPayoutReceived)}",
      activeCount: metrics.activeProjectsCount > 0
          ? metrics.activeProjectsCount
          : portfolios.length,
    );
  }

  Widget _buildDividendCalendar() {
    final List<InvestorPortfolioItem> portfolios =
        dashboardData?.portfolios ?? [];
    final InvestorBankAccount? bank = dashboardData?.bankAccount;
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
    } else {
      labels.addAll(["Mar", "Jun", "Sep", "Des"]);
    }

    String bankSnippet = "";
    if (bank != null &&
        bank.accountNumber != null &&
        bank.accountNumber!.isNotEmpty) {
      final String num = bank.accountNumber!.replaceAll(RegExp(r"\s+"), "");
      final String last4 =
          num.length > 4 ? num.substring(num.length - 4) : num;
      bankSnippet = " • Rek. ${bank.bankName ?? 'BCA'} •• $last4";
    }

    final String statusText = "${"scheduled".tr()}$bankSnippet";

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
                "Excavator Fleet – Kaltim",
            amount:
                "Rp ${Formats.spell(next.payoutReceived > 0 ? next.payoutReceived : next.amount * 0.12)}",
            status: statusText,
            onTap: next.projectId != null
                ? () => context.push("/portal/projects/${next.projectId}")
                : null,
          ),
          if (labels.isNotEmpty) TimelineDots(labels: labels),
        ],
      ],
    );
  }

  Widget _buildDocuments() {
    final List<InvestorDocumentItem> docs = dashboardData?.documents ?? [];

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
        if (loading)
          Container(
            width: double.infinity,
            padding: EdgeInsets.all(Dimensions.size20),
            decoration: portalCardDecoration(),
            alignment: Alignment.center,
            child: const CircularProgressIndicator(
              color: portalGold,
            ),
          )
        else if (docs.isEmpty)
          Container(
            width: double.infinity,
            padding: EdgeInsets.all(Dimensions.size15),
            decoration: portalCardDecoration(),
            child: Text(
              "not_associated_with_project_yet".tr(),
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
            itemCount: docs.length,
            separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
            itemBuilder: (BuildContext context, int index) {
              final InvestorDocumentItem doc = docs[index];
              final bool isFirst = index == 0;
              final String metaDate = doc.publishedAt != null &&
                      doc.publishedAt!.isNotEmpty
                  ? (DateTime.tryParse(doc.publishedAt!) != null
                      ? "${"updated".tr()} ${DateFormat("d MMM", context.locale.languageCode).format(DateTime.parse(doc.publishedAt!))}"
                      : doc.publishedAt!)
                  : "watermarked".tr();

              return DocumentTile(
                title: doc.docTitle ?? "Dokumen Investasi",
                meta:
                    "${doc.docType ?? 'PDF'} • ${doc.fileSize ?? '2.4 MB'} • $metaDate",
                actionIcon: isFirst
                    ? Icons.visibility_outlined
                    : Icons.file_download_outlined,
                onTap: () {
                  context.push("/portal/proposal-watermark");
                },
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
              final DateTime? uDate =
                  DateTime.tryParse(update.updateDate ?? "");
              final String dateFormatted = uDate != null
                  ? DateFormat("d MMM yyyy", "id").format(uDate)
                  : (update.updateDate ?? "");

              return Padding(
                padding: EdgeInsets.only(right: Dimensions.size10),
                child: FieldUpdateCard(
                  imageUrl: resolveImage(update.imageUrl),
                  dateLabel: dateFormatted,
                  location:
                      update.projectTitle ?? "Sangatta, Kalimantan Timur",
                  title: update.title ?? "Pembaruan Operasional",
                  description: update.description ?? "",
                  progress: update.progressPercentage,
                  onTap: update.projectId != null
                      ? () =>
                          context.push("/portal/projects/${update.projectId}")
                      : null,
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
