// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_bloc.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_event.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_state.dart";
import "package:mgi_investor/shared.dart";

class ProjectDetailPage extends StatefulWidget {
  final String id;

  const ProjectDetailPage({
    required this.id,
    super.key,
  });

  @override
  ProjectDetailPageState createState() => ProjectDetailPageState();
}

class ProjectDetailPageState extends State<ProjectDetailPage>
    with WidgetsBindingObserver {
  bool loading = false;
  Project? project;
  int _currentImageIndex = 0;
  final PageController _pageController = PageController();

  final Map<String, bool> _expandedSections = {
    "overview": true,
    "cash_flow": false,
    "risk": false,
    "collateral": false,
  };

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _pageController.dispose();
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void refresh() {
    context.read<ProjectDetailBloc>().add(ProjectDetailLoad(id: widget.id));
  }

  String _resolveImageUrl(String rawImg) {
    if (rawImg.startsWith("http://") || rawImg.startsWith("https://")) {
      return rawImg;
    }
    final String clean = rawImg.startsWith("/") ? rawImg.substring(1) : rawImg;
    return "https://montanainvestama.com/$clean";
  }

  List<String> _getProjectImages(Project? proj) {
    final List<String> list = [];
    if (proj?.image != null && proj!.image!.trim().isNotEmpty) {
      list.add(_resolveImageUrl(proj.image!));
    }
    return list.isEmpty ? [_resolveImageUrl("assets/img/1.png")] : list;
  }

  String _resolveUnitsDisplay(Project proj) {
    if (proj.operatorFacility != null &&
        proj.operatorFacility!.trim().isNotEmpty) {
      return proj.operatorFacility!.trim();
    }
    if (proj.packageOptions.isNotEmpty &&
        proj.packageOptions.first.unitQty > 0) {
      return "${proj.packageOptions.first.unitQty} Unit Alat Berat";
    }
    if (proj.info?.stockAvailable != null &&
        proj.info!.stockAvailable!.trim().isNotEmpty) {
      final RegExp qtyRegex =
          RegExp(r"(\d+\s*Unit\s*[A-Za-z]*)", caseSensitive: false);
      final RegExpMatch? match =
          qtyRegex.firstMatch(proj.info!.stockAvailable!);
      if (match != null) {
        return match.group(1)!;
      }
    }
    if (proj.info?.operatorPerk != null &&
        proj.info!.operatorPerk!.trim().isNotEmpty) {
      return proj.info!.operatorPerk!.trim();
    }
    return "8 Excavator";
  }

  String _resolveTargetDisplay(Project proj) {
    if (proj.fundingTarget >= 1000000000) {
      final double miliars = proj.fundingTarget / 1000000000;
      final String val = (miliars % 1 == 0)
          ? miliars.toStringAsFixed(0)
          : miliars.toStringAsFixed(1);
      return "Rp $val Miliar";
    }
    return "Rp ${Formats.spell(proj.fundingTarget)}";
  }

  String _cleanRoi(String? raw) {
    if (raw == null || raw.trim().isEmpty) {
      return "28,6% p.a.";
    }
    final String trimmed = raw.trim();
    if (!trimmed.toLowerCase().contains("p.a.")) {
      return "$trimmed p.a.";
    }
    return trimmed;
  }

  Widget _buildCarouselImage(String imageUrl) {
    return GestureDetector(
      onTap: () {
        BaseSheets.imagePreview(
          imageProvider: NetworkImage(imageUrl),
        );
      },
      child: Container(
        color: const Color(0xFF0F2C59),
        child: Image.network(
          imageUrl,
          fit: BoxFit.cover,
          errorBuilder: (
            BuildContext context,
            Object error,
            StackTrace? stackTrace,
          ) {
            return Container(
              color: const Color(0xFF0F2C59),
              child: Center(
                child: Icon(
                  Icons.precision_manufacturing_outlined,
                  size: Dimensions.size50,
                  color: const Color(0xFFC5A059),
                ),
              ),
            );
          },
          loadingBuilder: (
            BuildContext context,
            Widget child,
            ImageChunkEvent? loadingProgress,
          ) {
            if (loadingProgress == null) {
              return child;
            }
            return Container(
              color: const Color(0xFF0F2C59),
              child: Center(
                child: CircularProgressIndicator(
                  value: loadingProgress.expectedTotalBytes != null
                      ? loadingProgress.cumulativeBytesLoaded /
                          loadingProgress.expectedTotalBytes!
                      : null,
                  strokeWidth: 2,
                  color: const Color(0xFFC5A059),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildFloatingButton({
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(Dimensions.size10),
      child: Container(
        width: Dimensions.size40,
        height: Dimensions.size40,
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.45),
          borderRadius: BorderRadius.circular(Dimensions.size10),
        ),
        child: Icon(
          icon,
          color: Colors.white,
          size: Dimensions.size20,
        ),
      ),
    );
  }

  Widget _buildPageCounterPill({
    required int current,
    required int total,
  }) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: Dimensions.size10,
        vertical: Dimensions.size4,
      ),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.45),
        borderRadius: BorderRadius.circular(Dimensions.size15),
      ),
      child: Text(
        "$current/$total",
        style: TextStyle(
          color: Colors.white,
          fontSize: Dimensions.text11,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _buildHeroCarousel(Project proj) {
    final List<String> images = _getProjectImages(proj);
    final bool hasMultipleImages = images.length > 1;

    return SizedBox(
      height: 330,
      child: Stack(
        children: [
          if (hasMultipleImages)
            PageView.builder(
              controller: _pageController,
              onPageChanged: (int index) {
                setState(() {
                  _currentImageIndex = index;
                });
              },
              itemCount: images.length,
              itemBuilder: (BuildContext context, int index) {
                return _buildCarouselImage(images[index]);
              },
            )
          else
            Positioned.fill(
              child: _buildCarouselImage(images.first),
            ),
          Positioned.fill(
            child: IgnorePointer(
              child: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.black.withValues(alpha: 0.45),
                      Colors.transparent,
                      Colors.transparent,
                    ],
                    stops: const [0.0, 0.35, 1.0],
                  ),
                ),
              ),
            ),
          ),
          if ((proj.status ?? "").toLowerCase().contains("funded"))
            Center(
              child: IgnorePointer(
                child: Image.asset(
                  "assets/image/closed-stamp.png",
                  width: Dimensions.size100 * 2,
                  fit: BoxFit.contain,
                ),
              ),
            ),
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size15,
                  vertical: Dimensions.size10,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildFloatingButton(
                      icon: Icons.chevron_left,
                      onTap: () => context.pop(),
                    ),
                    if (hasMultipleImages)
                      _buildPageCounterPill(
                        current: _currentImageIndex + 1,
                        total: images.length,
                      )
                    else
                      const SizedBox(),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _buildFloatingButton(
                          icon: Icons.fullscreen,
                          onTap: () {
                            final int idx = hasMultipleImages
                                ? _currentImageIndex.clamp(0, images.length - 1)
                                : 0;
                            BaseSheets.imagePreview(
                              imageProvider: NetworkImage(images[idx]),
                            );
                          },
                        ),
                        SizedBox(width: Dimensions.size10),
                        _buildFloatingButton(
                          icon: Icons.business_center_outlined,
                          onTap: () => context.push("/portal/portfolio"),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
          if (hasMultipleImages)
            Positioned(
              bottom: 38,
              left: 0,
              right: 0,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  images.length,
                  (int i) => AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    width: _currentImageIndex == i ? 18 : 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color: _currentImageIndex == i
                          ? const Color(0xFFC5A059)
                          : Colors.white.withValues(alpha: 0.6),
                      borderRadius: BorderRadius.circular(3),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(Project proj) {
    final bool isOpen = (proj.status ?? "Open").toLowerCase() == "open";
    final String label =
        isOpen ? "active_funding".tr() : (proj.status ?? "Open").toUpperCase();

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: Dimensions.size10,
        vertical: Dimensions.size4,
      ),
      decoration: BoxDecoration(
        color: isOpen ? const Color(0xFFE8F5E9) : const Color(0xFFECEFF1),
        borderRadius: BorderRadius.circular(Dimensions.size20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: Dimensions.size5,
            height: Dimensions.size5,
            decoration: BoxDecoration(
              color: isOpen
                  ? const Color(0xFF2E7D32)
                  : const Color(0xFF546E7A),
              shape: BoxShape.circle,
            ),
          ),
          SizedBox(width: Dimensions.size5),
          Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text10,
              fontWeight: FontWeight.bold,
              color: isOpen
                  ? const Color(0xFF2E7D32)
                  : const Color(0xFF546E7A),
              letterSpacing: 0.4,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocationRow(Project proj) {
    final String loc = (proj.info?.lokasi != null &&
            proj.info!.lokasi!.trim().isNotEmpty)
        ? proj.info!.lokasi!.trim()
        : (proj.city != null && proj.city!.trim().isNotEmpty
            ? proj.city!.trim()
            : "Indonesia");

    return Row(
      children: [
        Icon(
          Icons.location_on_outlined,
          size: Dimensions.size15,
          color: const Color(0xFFC5A059),
        ),
        SizedBox(width: Dimensions.size4),
        Expanded(
          child: Text(
            loc,
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: FontWeight.w500,
              color: const Color(0xFF9E8B67),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTitle(Project proj) {
    return Text(
      proj.title ?? "-",
      style: TextStyle(
        fontSize: Dimensions.text20,
        fontWeight: FontWeight.w800,
        color: const Color(0xFF0F2C59),
        height: 1.25,
      ),
    );
  }

  Widget _buildSubtitle(Project proj) {
    final String subtitle = (proj.detail?.tagline != null &&
            proj.detail!.tagline!.trim().isNotEmpty)
        ? proj.detail!.tagline!.trim()
        : (proj.category ?? "Kontrak operasional pengadaan unit alat berat");

    return Text(
      subtitle,
      style: TextStyle(
        fontSize: Dimensions.text13,
        color: const Color(0xFF64748B),
        height: 1.4,
      ),
    );
  }

  Widget _buildFundingProgressCard(Project proj) {
    final double percent = (proj.fundingTarget > 0)
        ? (proj.fundingCollected / proj.fundingTarget).clamp(0.0, 1.0)
        : 0.0;

    final String slotInfo = (proj.info?.investorSlot != null &&
            proj.info!.investorSlot!.trim().isNotEmpty)
        ? proj.info!.investorSlot!.trim()
        : "exclusive_partner".tr();

    return Container(
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(Dimensions.size15),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    "collected_funds".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: const Color(0xFF64748B),
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    "Rp ${Formats.spell(proj.fundingCollected)}",
                    style: TextStyle(
                      fontSize: Dimensions.text16,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF0F2C59),
                    ),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    "target_funding".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: const Color(0xFF64748B),
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    _resolveTargetDisplay(proj),
                    style: TextStyle(
                      fontSize: Dimensions.text13,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF0F2C59),
                    ),
                  ),
                ],
              ),
            ],
          ),
          SizedBox(height: Dimensions.size15),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: percent,
              minHeight: 6,
              backgroundColor: const Color(0xFFF1F5F9),
              valueColor: const AlwaysStoppedAnimation<Color>(
                Color(0xFFC5A059),
              ),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                "${(percent * 100).toStringAsFixed(0)}% ${"fulfilled".tr()}",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFFC5A059),
                ),
              ),
              Text(
                slotInfo,
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: const Color(0xFF94A3B8),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMetricCard({
    required String label,
    required String value,
    required IconData icon,
  }) {
    return Container(
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(Dimensions.size15),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF94A3B8),
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              SizedBox(width: Dimensions.size4),
              Icon(
                icon,
                size: Dimensions.size15,
                color: const Color(0xFFC5A059),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: Dimensions.text14,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF0F2C59),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKeyMetricsGrid(Project proj) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                label: "tenor_label".tr(),
                value: proj.info?.tenor ?? "12 Bulan",
                icon: Icons.access_time_rounded,
              ),
            ),
            SizedBox(width: Dimensions.size10),
            Expanded(
              child: _buildMetricCard(
                label: "target_roi_label".tr(),
                value: _cleanRoi(proj.info?.returnRate),
                icon: Icons.trending_up,
              ),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                label: "heavy_equipment_unit_label".tr(),
                value: _resolveUnitsDisplay(proj),
                icon: Icons.precision_manufacturing_outlined,
              ),
            ),
            SizedBox(width: Dimensions.size10),
            Expanded(
              child: _buildMetricCard(
                label: "scheme_label".tr(),
                value: proj.model ??
                    proj.info?.payout ??
                    "profit_sharing".tr(),
                icon: Icons.business_center_outlined,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildAccordionItem({
    required String key,
    required String title,
    required Widget content,
  }) {
    final bool isExpanded = _expandedSections[key] ?? false;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        InkWell(
          onTap: () {
            setState(() {
              _expandedSections[key] = !isExpanded;
            });
          },
          child: Padding(
            padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: Dimensions.text14,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF0F2C59),
                  ),
                ),
                Icon(
                  isExpanded
                      ? Icons.keyboard_arrow_up
                      : Icons.keyboard_arrow_down,
                  color: const Color(0xFF0F2C59),
                  size: Dimensions.size20,
                ),
              ],
            ),
          ),
        ),
        if (isExpanded) ...[
          Padding(
            padding: EdgeInsets.only(bottom: Dimensions.size15),
            child: content,
          ),
        ],
        const Divider(color: Color(0xFFE2E8F0), height: 1, thickness: 1),
      ],
    );
  }

  Widget _buildAccordionSection(Project proj) {
    final String overviewText = (proj.detail?.whatWillProvideContent != null &&
            proj.detail!.whatWillProvideContent!.trim().isNotEmpty)
        ? proj.detail!.whatWillProvideContent!.trim()
        : ((proj.detail?.tagline != null &&
                proj.detail!.tagline!.trim().isNotEmpty)
            ? proj.detail!.tagline!.trim()
            : "Kontrak kerja 12 bulan bersama operator tambang berizin dengan estimasi utilisasi armada 82%.");

    final String cashFlowText = "Estimasi Return: ${proj.info?.returnRate ?? "28,6% – 42,9% p.a."}\n"
        "Siklus Pembagian: ${proj.info?.payout ?? "Tutup Buku Laporan Tahunan"}\n"
        "${proj.detail?.simulation?.notes ?? "Proyeksi imbal hasil berbasis kinerja riil penjualan unit armada alat berat."}";

    final String riskText = "Tingkat Risiko: ${proj.info?.risk ?? "Menengah - Terukur"}\n"
        "Jaminan Fisik: ${proj.info?.assetBacked ?? "Aset Fisik Unit Alat Berat Siap Operasi"}\n"
        "Mitigasi Operasional: Dilengkapi asuransi operasional komprehensif, telematika GPS real-time, dan manajemen workshop tersentralisasi.";

    final String collateralText = "Jaminan Kolateral: ${proj.info?.assetBacked ?? "Unit Alat Berat Siap Operasi"}\n"
        "Lokasi Unit: ${proj.info?.lokasi ?? proj.city ?? "Central Workshop PT Montana Indo Utama"}\n"
        "Legalitas Kepemilikan: Tercatat resmi atas nama kemitraan dengan dokumen faktur impor CBU Jepang dan pemeriksaan fisik terverifikasi.";

    return Column(
      children: [
        _buildAccordionItem(
          key: "overview",
          title: "project_overview".tr(),
          content: Text(
            overviewText,
            style: TextStyle(
              fontSize: Dimensions.text13,
              color: const Color(0xFF475569),
              height: 1.5,
            ),
          ),
        ),
        _buildAccordionItem(
          key: "cash_flow",
          title: "cash_flow_projection".tr(),
          content: Text(
            cashFlowText,
            style: TextStyle(
              fontSize: Dimensions.text13,
              color: const Color(0xFF475569),
              height: 1.5,
            ),
          ),
        ),
        _buildAccordionItem(
          key: "risk",
          title: "risk_mitigation".tr(),
          content: Text(
            riskText,
            style: TextStyle(
              fontSize: Dimensions.text13,
              color: const Color(0xFF475569),
              height: 1.5,
            ),
          ),
        ),
        _buildAccordionItem(
          key: "collateral",
          title: "fixed_asset_collateral".tr(),
          content: Text(
            collateralText,
            style: TextStyle(
              fontSize: Dimensions.text13,
              color: const Color(0xFF475569),
              height: 1.5,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDueDiligenceCard() {
    return Container(
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: BoxDecoration(
        color: const Color(0xFFFBF7EE),
        borderRadius: BorderRadius.circular(Dimensions.size15),
        border: Border.all(color: const Color(0xFFEFE4C8)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.verified_user_outlined,
            color: const Color(0xFFC5A059),
            size: Dimensions.size20,
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "due_diligence_verified".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF0F2C59),
                  ),
                ),
                SizedBox(height: Dimensions.size4),
                Text(
                  "due_diligence_desc".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text11,
                    color: const Color(0xFF64748B),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStickyBottomBar(Project proj) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: Dimensions.size15,
        vertical: Dimensions.size10,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Expanded(
              flex: 2,
              child: OutlinedButton(
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF0F2C59),
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size10),
                  ),
                ),
                onPressed: () {
                  if (Shared.ACCOUNT == null) {
                    context.push("/sign-in");
                  } else {
                    context.push("/portal/projects/${proj.id}");
                  }
                },
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.file_download_outlined,
                      size: 18,
                      color: Color(0xFF0F2C59),
                    ),
                    SizedBox(width: Dimensions.size5),
                    Text(
                      "proposal".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text13,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF0F2C59),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(width: Dimensions.size10),
            Expanded(
              flex: 3,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFC5A059),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size10),
                  ),
                ),
                onPressed: () {
                  context.push("/consultation");
                },
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      "consultation_ticket".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text13,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                    SizedBox(width: Dimensions.size5),
                    const Icon(
                      Icons.chevron_right,
                      size: 18,
                      color: Colors.white,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget body() {
    final Project? proj = project;
    if (proj == null) {
      return const SizedBox();
    }

    return Column(
      children: [
        Expanded(
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeroCarousel(proj),
                Transform.translate(
                  offset: const Offset(0, -28),
                  child: Container(
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(28),
                      ),
                    ),
                    padding: EdgeInsets.symmetric(
                      horizontal: Dimensions.size20,
                      vertical: Dimensions.size25,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildStatusBadge(proj),
                        SizedBox(height: Dimensions.size10),
                        _buildLocationRow(proj),
                        SizedBox(height: Dimensions.size10),
                        _buildTitle(proj),
                        SizedBox(height: Dimensions.size5),
                        _buildSubtitle(proj),
                        SizedBox(height: Dimensions.size20),
                        _buildFundingProgressCard(proj),
                        SizedBox(height: Dimensions.size15),
                        _buildKeyMetricsGrid(proj),
                        SizedBox(height: Dimensions.size25),
                        _buildAccordionSection(proj),
                        SizedBox(height: Dimensions.size20),
                        _buildDueDiligenceCard(),
                        SizedBox(height: Dimensions.size20),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        _buildStickyBottomBar(proj),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<ProjectDetailBloc, ProjectDetailState>(
      listener: (BuildContext context, ProjectDetailState state) {
        if (state is ProjectDetailLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is ProjectDetailLoadSuccess) {
          setState(() {
            project = state.project;
          });
        } else if (state is ProjectDetailLoadFinished) {
          setState(() {
            loading = false;
          });
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          contentBuilder: body,
          onRefresh: () async => refresh(),
          statusBuilder: () {
            if (loading && project == null) {
              return BaseBodyStatus.loading;
            }
            if (project == null) {
              return BaseBodyStatus.fail;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
