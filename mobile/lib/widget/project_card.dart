import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/auth_guard.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/shared.dart";
import "package:mgi_investor/widget/status_badge.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProjectCard extends StatelessWidget {
  final Project project;
  final VoidCallback? onTap;

  const ProjectCard({
    required this.project,
    this.onTap,
    super.key,
  });

  String? get _resolvedImageUrl {
    final String? rawImg = project.image?.trim();
    if (rawImg == null || rawImg.isEmpty) {
      return null;
    }
    if (rawImg.startsWith("http://") || rawImg.startsWith("https://")) {
      return rawImg;
    }
    final String clean = rawImg.startsWith("/") ? rawImg.substring(1) : rawImg;
    return "https://montanainvestama.com/$clean";
  }

  String get _resolvedCity {
    if (project.city != null && project.city!.trim().isNotEmpty) {
      return project.city!.trim();
    }
    final String? loc = project.info?.lokasi;
    if (loc != null && loc.trim().isNotEmpty) {
      return loc.split(",").first.trim();
    }
    return "Indonesia";
  }

  String get _resolvedNominalDisplay {
    if (project.packageOptionsMode == "2" &&
        project.packageOptions.length >= 2) {
      final PackageOption opt1 = project.packageOptions[0];
      final PackageOption opt2 = project.packageOptions[1];
      final String nom1 =
          opt1.nominalDisplay ?? "Rp ${Formats.spell(opt1.nominal)}";
      final String nom2 =
          opt2.nominalDisplay ?? "Rp ${Formats.spell(opt2.nominal)}";
      return "$nom1 & $nom2";
    }

    if (project.packageOptions.isNotEmpty &&
        project.packageOptions.first.nominalDisplay != null &&
        project.packageOptions.first.nominalDisplay!.isNotEmpty) {
      return project.packageOptions.first.nominalDisplay!;
    }

    if (project.packageOptions.isNotEmpty &&
        project.packageOptions.first.nominal > 0) {
      return "Rp ${Formats.spell(project.packageOptions.first.nominal)}";
    }

    if (project.fundingTarget > 0) {
      return "Rp ${Formats.spell(project.fundingTarget)}";
    }

    if (project.info?.target != null && project.info!.target!.isNotEmpty) {
      return project.info!.target!;
    }

    return "Rp 5.000.000.000";
  }

  String get _resolvedUnitCountText {
    final List<FundingItem>? rows = project.detail?.fundingTarget?.rows;
    if (rows != null && rows.isNotEmpty && rows.first.quantity > 0) {
      return "heavy_equipment_units".tr(args: ["${rows.first.quantity}"]);
    }

    if (project.packageOptions.isNotEmpty &&
        project.packageOptions.first.unitQty > 0) {
      return "heavy_equipment_units".tr(
        args: ["${project.packageOptions.first.unitQty}"],
      );
    }

    final String? stock = project.info?.stockAvailable;
    if (stock != null && stock.trim().isNotEmpty) {
      final RegExp qtyRegex = RegExp(r"(\d+)\s*Unit", caseSensitive: false);
      final RegExpMatch? match = qtyRegex.firstMatch(stock);
      if (match != null) {
        return "heavy_equipment_units".tr(args: [match.group(1)!]);
      }
      return stock;
    }

    return "batch_heavy_equipment_ready".tr();
  }

  String get _resolvedOperatorPerk {
    if (project.packageOptions.isNotEmpty) {
      final String? perk = project.packageOptions.first.operatorPerk;
      if (perk != null && perk.trim().isNotEmpty) {
        return perk.trim();
      }
    }
    if (project.operatorFacility != null &&
        project.operatorFacility!.trim().isNotEmpty) {
      return project.operatorFacility!.trim();
    }
    if (project.info?.operatorPerk != null &&
        project.info!.operatorPerk!.trim().isNotEmpty) {
      return project.info!.operatorPerk!.trim();
    }
    return "";
  }

  String get _cleanRoi {
    final String raw = (project.info?.returnRate != null &&
            project.info!.returnRate!.trim().isNotEmpty)
        ? project.info!.returnRate!.trim()
        : "30,9% – 46,3%";
    return raw
        .replaceAll(RegExp(r"\s*\(p\.a\.\)", caseSensitive: false), "")
        .trim();
  }

  String get _cleanTenor {
    final String raw =
        (project.info?.tenor != null && project.info!.tenor!.trim().isNotEmpty)
            ? project.info!.tenor!.trim()
            : "12 Bulan";
    return raw
        .replaceAll(RegExp(r"\s*\(Target[^\)]*\)", caseSensitive: false), "")
        .split("(")
        .first
        .trim();
  }

  String get _resolvedLokasi {
    final String? loc = project.info?.lokasi;
    if (loc != null && loc.trim().isNotEmpty) {
      return loc.trim();
    }
    return project.city ?? "Regional";
  }

  String get _resolvedPayout {
    final String raw = (project.info?.payout ?? "").toLowerCase();
    if (raw.contains("tutup buku") || raw.contains("kuartal") || raw.isEmpty) {
      return "annual_book_closing".tr();
    }
    return project.info!.payout!;
  }

  void _navigateToDetail(BuildContext context) {
    if (onTap != null) {
      onTap!();
      return;
    }
    if (Shared.ACCOUNT == null) {
      AuthGuard.promptLogin(
        context,
        onContinueGuest: () => context.push("/projects/${project.id}"),
      );
    } else {
      context.push("/projects/${project.id}");
    }
  }

  Widget _buildImagePlaceholder() {
    return Container(
      color: const Color(0xFF0F2C59).withValues(alpha: 0.08),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.precision_manufacturing_outlined,
              size: Dimensions.size40,
              color: const Color(0xFF0F2C59).withValues(alpha: 0.4),
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              project.category ?? "Alat Berat",
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImageHeader(BuildContext context) {
    final String? imageUrl = _resolvedImageUrl;
    final String city = _resolvedCity;

    return ClipRRect(
      borderRadius: BorderRadius.circular(Dimensions.size10),
      child: Container(
        height: Dimensions.size100 + Dimensions.size50,
        width: double.infinity,
        color: const Color(0xFF0F2C59).withValues(alpha: 0.06),
        child: Stack(
          fit: StackFit.expand,
          children: [
            if (imageUrl != null)
              Image.network(
                imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (
                  BuildContext context,
                  Object error,
                  StackTrace? stackTrace,
                ) {
                  return _buildImagePlaceholder();
                },
                loadingBuilder: (
                  BuildContext context,
                  Widget child,
                  ImageChunkEvent? loadingProgress,
                ) {
                  if (loadingProgress == null) {
                    return child;
                  }
                  return Center(
                    child: CircularProgressIndicator(
                      value: loadingProgress.expectedTotalBytes != null
                          ? loadingProgress.cumulativeBytesLoaded /
                              loadingProgress.expectedTotalBytes!
                          : null,
                      strokeWidth: 2,
                      color: const Color(0xFFC5A059),
                    ),
                  );
                },
              )
            else
              _buildImagePlaceholder(),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withValues(alpha: 0.45),
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.25),
                  ],
                ),
              ),
            ),
            Positioned(
              top: Dimensions.size10,
              left: Dimensions.size10,
              child: Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size10,
                  vertical: Dimensions.size4,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size5),
                    smoothness: 1,
                  ),
                  color: const Color(0xFFC5A059),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.location_on,
                      size: Dimensions.size10,
                      color: Colors.white,
                    ),
                    SizedBox(width: Dimensions.size3),
                    Text(
                      city,
                      style: TextStyle(
                        fontSize: Dimensions.text10,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Positioned(
              top: Dimensions.size10,
              right: Dimensions.size10,
              child: StatusBadge(status: project.status),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderBadges() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Container(
          padding: EdgeInsets.symmetric(
            horizontal: Dimensions.size10,
            vertical: Dimensions.size4,
          ),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size50),
              smoothness: 1,
              side: const BorderSide(
                color: Color(0x66C5A059),
                width: 1.0,
              ),
            ),
            color: const Color(0xFF0E1B48),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.military_tech,
                size: Dimensions.size15,
                color: const Color(0xFFC5A059),
              ),
              SizedBox(width: Dimensions.size4),
              Text(
                "exclusive_badge".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFFF8FAFC),
                  letterSpacing: 0.8,
                ),
              ),
            ],
          ),
        ),
        Container(
          padding: EdgeInsets.symmetric(
            horizontal: Dimensions.size10,
            vertical: Dimensions.size4,
          ),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size5),
              smoothness: 1,
              side: const BorderSide(
                color: Color(0xFFCBD5E1),
                width: 1.0,
              ),
            ),
            color: const Color(0xFFF8FAFC),
          ),
          child: Text(
            "${"project_code_prefix".tr()}: ${(project.id ?? "PROJ").toUpperCase()}",
            style: TextStyle(
              fontSize: Dimensions.text10,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF64748B),
              letterSpacing: 0.5,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTitle() {
    return Text(
      project.title ?? "-",
      maxLines: 2,
      overflow: TextOverflow.ellipsis,
      style: TextStyle(
        fontSize: Dimensions.text16,
        fontWeight: FontWeight.bold,
        color: const Color(0xFF0F172A),
        height: 1.3,
      ),
    );
  }

  Widget _buildOptionPills() {
    final bool isMode2 =
        project.packageOptionsMode == "2" && project.packageOptions.length >= 2;

    if (isMode2) {
      final PackageOption opt1 = project.packageOptions[0];
      final PackageOption opt2 = project.packageOptions[1];
      final String nom1 =
          opt1.nominalDisplay ?? "Rp ${Formats.spell(opt1.nominal)}";
      final String nom2 =
          opt2.nominalDisplay ?? "Rp ${Formats.spell(opt2.nominal)}";
      final String perk1 =
          (opt1.operatorPerk != null && opt1.operatorPerk!.trim().isNotEmpty)
              ? "1 Operator"
              : "Opsi 1";
      final String perk2 =
          (opt2.operatorPerk != null && opt2.operatorPerk!.trim().isNotEmpty)
              ? "2 Operator"
              : "Opsi 2";

      return Wrap(
        spacing: Dimensions.size10,
        runSpacing: Dimensions.size5,
        children: [
          Container(
            padding: EdgeInsets.symmetric(
              horizontal: Dimensions.size10,
              vertical: Dimensions.size4,
            ),
            decoration: ShapeDecoration(
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size5),
                smoothness: 1,
                side: const BorderSide(color: Color(0xFFBFDBFE)),
              ),
              color: const Color(0xFFEFF6FF),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.check_circle_outline,
                  size: Dimensions.size10,
                  color: const Color(0xFF1D3589),
                ),
                SizedBox(width: Dimensions.size4),
                Text(
                  "${"allocation_1_prefix".tr()}: $nom1 ($perk1)",
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF1D3589),
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: EdgeInsets.symmetric(
              horizontal: Dimensions.size10,
              vertical: Dimensions.size4,
            ),
            decoration: ShapeDecoration(
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size5),
                smoothness: 1,
                side: const BorderSide(color: Color(0xFFFEF08A)),
              ),
              color: const Color(0xFFFEFCE8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.check_circle_outline,
                  size: Dimensions.size10,
                  color: const Color(0xFF92400E),
                ),
                SizedBox(width: Dimensions.size4),
                Text(
                  "${"allocation_2_prefix".tr()}: $nom2 ($perk2)",
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF92400E),
                  ),
                ),
              ],
            ),
          ),
        ],
      );
    }

    final String nomDisp = _resolvedNominalDisplay;

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: Dimensions.size10,
        vertical: Dimensions.size5,
      ),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size5),
          smoothness: 1,
          side: const BorderSide(color: Color(0xFFBBF7D0)),
        ),
        color: const Color(0xFFF0FDF4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.check_circle,
            size: Dimensions.size15,
            color: const Color(0xFF166534),
          ),
          SizedBox(width: Dimensions.size5),
          Text(
            "${"budget_prefix".tr()}: $nomDisp",
            style: TextStyle(
              fontSize: Dimensions.text10,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF166534),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFundingBox() {
    final bool isMode2 =
        project.packageOptionsMode == "2" && project.packageOptions.length >= 2;
    final String nomDisp = _resolvedNominalDisplay;
    final String unitText = _resolvedUnitCountText;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(Dimensions.size10),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(
            color: Color(0xFFE2E8F0),
            width: 1.2,
          ),
        ),
        color: const Color(0xFFF8FAFC),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(
                child: Text(
                  "project_budget_requirement_label".tr(),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF64748B),
                    letterSpacing: 0.4,
                  ),
                ),
              ),
              SizedBox(width: Dimensions.size10),
              Container(
                padding: EdgeInsets.symmetric(
                  horizontal: Dimensions.size10,
                  vertical: Dimensions.size3,
                ),
                decoration: ShapeDecoration(
                  shape: SmoothRectangleBorder(
                    borderRadius: BorderRadius.circular(Dimensions.size50),
                    smoothness: 1,
                  ),
                  color: isMode2
                      ? const Color(0xFFC5A059)
                      : const Color(0xFF0E1B48),
                ),
                child: Text(
                  isMode2
                      ? "two_budget_options_badge".tr()
                      : "full_allocation_badge".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: isMode2 ? const Color(0xFF0E1B48) : Colors.white,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            nomDisp,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: Dimensions.text18,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF1D3589),
              letterSpacing: -0.3,
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Row(
            children: [
              Icon(
                Icons.local_shipping_outlined,
                size: Dimensions.size15,
                color: const Color(0xFF2563EB),
              ),
              SizedBox(width: Dimensions.size5),
              Expanded(
                child: Text(
                  unitText,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.w500,
                    color: const Color(0xFF64748B),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildOperatorCallout(String operatorPerk) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(Dimensions.size10),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(
            color: Color(0xFFE8D7B0),
            width: 1.0,
          ),
        ),
        color: const Color(0xFFFCF8EE),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.badge,
            size: Dimensions.size20,
            color: const Color(0xFFD97706),
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "operator_facility_bonus_title".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF78350F),
                  ),
                ),
                SizedBox(height: Dimensions.size2),
                Text(
                  operatorPerk,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    color: const Color(0xFF78350F),
                    height: 1.3,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKeyMetrics(String roi, String tenor) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(
        vertical: Dimensions.size10,
        horizontal: Dimensions.size4,
      ),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(
            color: Color(0xFFE2E8F0),
            width: 1.0,
          ),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              children: [
                Text(
                  roi,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF047857),
                  ),
                ),
                SizedBox(height: Dimensions.size3),
                Text(
                  "roi_metric".tr(),
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF64748B),
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 1.0,
            height: Dimensions.size30,
            color: const Color(0xFFE2E8F0),
          ),
          Expanded(
            child: Column(
              children: [
                Text(
                  tenor,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF0F172A),
                  ),
                ),
                SizedBox(height: Dimensions.size3),
                Text(
                  "tenor_metric".tr(),
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF64748B),
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          Container(
            width: 1.0,
            height: Dimensions.size30,
            color: const Color(0xFFE2E8F0),
          ),
          Expanded(
            child: Column(
              children: [
                Text(
                  "one_investor_metric".tr(),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1D3589),
                  ),
                ),
                SizedBox(height: Dimensions.size3),
                Text(
                  "exclusive_metric".tr(),
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFFC5A059),
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetaRows(String lokasi, String payout, String assetBadge) {
    return Column(
      children: [
        const Divider(
          color: Color(0xFFE2E8F0),
          height: 1.0,
          thickness: 1.0,
        ),
        SizedBox(height: Dimensions.size10),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "project_region_label".tr(),
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: const Color(0xFF64748B),
              ),
            ),
            Flexible(
              child: Text(
                lokasi,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.end,
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF0F172A),
                ),
              ),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "profit_distribution_label".tr(),
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: const Color(0xFF64748B),
              ),
            ),
            Flexible(
              child: Text(
                payout,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.end,
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF2563EB),
                ),
              ),
            ),
          ],
        ),
        SizedBox(height: Dimensions.size10),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              "physical_asset_collateral_label".tr(),
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: const Color(0xFF64748B),
              ),
            ),
            Container(
              padding: EdgeInsets.symmetric(
                horizontal: Dimensions.size10,
                vertical: Dimensions.size3,
              ),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size5),
                  smoothness: 1,
                  side: const BorderSide(
                    color: Color(0xFFA7F3D0),
                    width: 1.0,
                  ),
                ),
                color: const Color(0xFFECFDF5),
              ),
              child: Text(
                assetBadge,
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF047857),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildCtaButton(BuildContext context) {
    return InkWell(
      onTap: () => _navigateToDetail(context),
      customBorder: const StadiumBorder(),
      child: Container(
        width: double.infinity,
        padding: EdgeInsets.symmetric(vertical: Dimensions.size10),
        decoration: ShapeDecoration(
          shape: const StadiumBorder(
            side: BorderSide(
              color: Color(0xFFCBD5E1),
              width: 1.2,
            ),
          ),
          color: AppColors.surfaceContainerLowest(),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              "capital_budget_plan_cta".tr(),
              style: TextStyle(
                fontSize: Dimensions.text13,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0F172A),
              ),
            ),
            SizedBox(width: Dimensions.size5),
            Icon(
              Icons.arrow_forward,
              size: Dimensions.size15,
              color: const Color(0xFF0F172A),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final String cleanRoi = _cleanRoi;
    final String cleanTenor = _cleanTenor;
    final String operatorPerk = _resolvedOperatorPerk;
    final String lokasi = _resolvedLokasi;
    final String payout = _resolvedPayout;
    final String assetBadge = "productive_heavy_equipment_badge".tr();

    return InkWell(
      onTap: () => _navigateToDetail(context),
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size15),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.all(Dimensions.size15),
        decoration: ShapeDecoration(
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size15),
            smoothness: 1,
            side: const BorderSide(
              color: Color(0xFFE2E8F0),
              width: 1.2,
            ),
          ),
          color: AppColors.surfaceContainerLowest(),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildImageHeader(context),
            SizedBox(height: Dimensions.size15),
            _buildHeaderBadges(),
            SizedBox(height: Dimensions.size10),
            _buildTitle(),
            SizedBox(height: Dimensions.size10),
            _buildOptionPills(),
            SizedBox(height: Dimensions.size10),
            _buildFundingBox(),
            if (operatorPerk.isNotEmpty) ...[
              SizedBox(height: Dimensions.size10),
              _buildOperatorCallout(operatorPerk),
            ],
            SizedBox(height: Dimensions.size10),
            _buildKeyMetrics(cleanRoi, cleanTenor),
            SizedBox(height: Dimensions.size10),
            _buildMetaRows(lokasi, payout, assetBadge),
            SizedBox(height: Dimensions.size15),
            _buildCtaButton(context),
          ],
        ),
      ),
    );
  }
}
