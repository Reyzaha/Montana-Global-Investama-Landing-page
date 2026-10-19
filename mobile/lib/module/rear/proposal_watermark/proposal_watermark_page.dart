// ignore_for_file: use_build_context_synchronously

import "dart:math" as math;
import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:loader_overlay/loader_overlay.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/shared.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProposalWatermarkPage extends StatefulWidget {
  final String id;

  const ProposalWatermarkPage({
    required this.id,
    super.key,
  });

  @override
  ProposalWatermarkPageState createState() => ProposalWatermarkPageState();
}

class ProposalWatermarkPageState extends State<ProposalWatermarkPage>
    with WidgetsBindingObserver {
  bool loading = false;
  Project? project;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    loadDetail();
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

  Future<void> loadDetail() async {
    try {
      setState(() {
        loading = true;
      });

      final response = await ApiManager.projectDetail(id: widget.id);
      if (response.statusCode == 200) {
        if (response.data is Map<String, dynamic>) {
          project = Project().parse(response.data as Map<String, dynamic>);
        } else if (response.data is List && (response.data as List).isNotEmpty) {
          project = Project().parse((response.data as List).first as Map<String, dynamic>);
        }
      }
    } catch (e) {
      if (kDebugMode) {
        print("Load proposal error: $e");
      }
    } finally {
      if (mounted) {
        setState(() {
          loading = false;
        });
      }
    }
  }

  Future<void> downloadWatermarkedPdf() async {
    context.loaderOverlay.show();
    await Future.delayed(const Duration(milliseconds: 1500));
    context.loaderOverlay.hide();

    BaseOverlays.success(
      message: "pdf_downloaded_success".tr(),
    );
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "watermark_title".tr(),
    );
  }

  Widget _buildWatermarkOverlay() {
    final String investorName = Shared.ACCOUNT?.displayName().toUpperCase() ?? "INVESTOR MGI";
    final String dateStamp = DateFormat("dd-MM-yyyy HH:mm").format(DateTime.now());

    return IgnorePointer(
      child: Center(
        child: Transform.rotate(
          angle: -math.pi / 5,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                "DOKUMEN RAHASIA PEMODAL",
                style: TextStyle(
                  fontSize: Dimensions.text18,
                  fontWeight: FontWeight.w900,
                  color: Colors.red.withValues(alpha: 0.12),
                  letterSpacing: 3,
                ),
              ),
              SizedBox(height: Dimensions.size10),
              Text(
                "PT MONTANA GLOBAL INVESTAMA",
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F2C59).withValues(alpha: 0.14),
                  letterSpacing: 2,
                ),
              ),
              SizedBox(height: Dimensions.size5),
              Text(
                "DIAKSES OLEH: $investorName • $dateStamp WIB",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF0F2C59).withValues(alpha: 0.14),
                ),
              ),
              SizedBox(height: Dimensions.size5),
              Text(
                "DISTRIBUSI TANPA IZIN MERUPAKAN PELANGGARAN HUKUM",
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  color: Colors.red.withValues(alpha: 0.12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNoticeBanner() {
    return Container(
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(color: Color(0xFFC5A059)),
        ),
        color: const Color(0xFF0F2C59),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.security,
            size: Dimensions.size20,
            color: const Color(0xFFC5A059),
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Text(
              "watermark_security_desc".tr(),
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: Colors.white,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFinancialProjections() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size15),
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
            "Struktur Anggaran & Proyeksi Finansial",
            style: TextStyle(
              fontSize: Dimensions.text14,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          _buildRow("Target CAPEX Unit", "Rp ${Formats.spell(project?.fundingTarget ?? 0)}"),
          _buildRow("Estimasi Imbal Hasil", project?.info?.returnRate ?? "28.62%/tahun", isGold: true),
          _buildRow("Tenor Operasional", project?.info?.tenor ?? "12 Bulan"),
          _buildRow("Tiket Minimum Kemitraan", project?.info?.minInvestment ?? "Rp 500.000.000"),
          _buildRow("Proyeksi Periode BEP", "24 – 36 Bulan"),
          _buildRow("Mitra Pool Workshop", "MSI Karanganyar Kebumen"),
          _buildRow("Proteksi Unit", "Asuransi All-Risk + GPS Telemetri 24 Jam"),
        ],
      ),
    );
  }

  Widget _buildRow(String label, String value, {bool isGold = false}) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: Dimensions.size5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: FontWeight.bold,
              color: isGold ? const Color(0xFFC5A059) : AppColors.onSurface(),
            ),
          ),
        ],
      ),
    );
  }

  Widget body() {
    if (loading && project == null) {
      return Center(
        child: CircularProgressIndicator(
          color: const Color(0xFF0F2C59),
        ),
      );
    }

    return Stack(
      children: [
        ListView(
          padding: EdgeInsets.all(Dimensions.size15),
          children: [
            _buildNoticeBanner(),
            SizedBox(height: Dimensions.size15),
            Text(
              project?.title ?? "Proposal Investasi Kemitraan Alat Berat",
              style: TextStyle(
                fontSize: Dimensions.text18,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0F2C59),
              ),
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              "Wilayah Kerja: ${project?.city ?? 'Indonesia'} • Kategori: ${project?.category ?? 'Alat Berat'}",
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
            SizedBox(height: Dimensions.size15),
            Container(
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
                    "Ringkasan Eksekutif Proyek",
                    style: TextStyle(
                      fontSize: Dimensions.text14,
                      fontWeight: FontWeight.bold,
                      color: AppColors.onSurface(),
                    ),
                  ),
                  SizedBox(height: Dimensions.size10),
                  Text(
                    project?.detail?.tagline ??
                        "Pendanaan unit alat berat produktif dengan jaminan sewa operasional jangka panjang di sektor infrastruktur dan pertambangan, dioperasikan oleh operator bersertifikasi resmi dan diawasi dengan telemetri 24 jam.",
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      color: AppColors.onSurface(),
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            ),
            _buildFinancialProjections(),
            SizedBox(height: Dimensions.size20),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFFC5A059),
                foregroundColor: Colors.white,
              ),
              onPressed: downloadWatermarkedPdf,
              icon: Icon(Icons.picture_as_pdf, size: Dimensions.size15),
              label: Text("download_watermarked_pdf".tr()),
            ),
            SizedBox(height: Dimensions.size10),
            OutlinedButton.icon(
              onPressed: () => context.push("/consultation"),
              icon: Icon(Icons.support_agent, size: Dimensions.size15),
              label: Text("Konsultasi Alokasi Unit"),
            ),
            SizedBox(height: Dimensions.size30),
          ],
        ),
        _buildWatermarkOverlay(),
      ],
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
