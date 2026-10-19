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
import "package:smooth_corner/smooth_corner.dart";

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
    context.read<ProjectDetailBloc>().add(ProjectDetailLoad(id: widget.id));
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: project?.title ?? "project_details".tr(),
    );
  }

  Widget _buildOverviewCard() {
    final double percent = (project != null && project!.fundingTarget > 0)
        ? (project!.fundingCollected / project!.fundingTarget).clamp(0.0, 1.0)
        : 0.0;

    return Container(
      padding: EdgeInsets.all(Dimensions.size20),
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
                    borderRadius: BorderRadius.circular(Dimensions.size5),
                    smoothness: 1,
                  ),
                  color: const Color(0xFF0F2C59),
                ),
                child: Text(
                  project?.city ?? "Indonesia",
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                project?.category ?? "Alat Berat",
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
                    borderRadius: BorderRadius.circular(Dimensions.size20),
                    smoothness: 1,
                  ),
                  color: project?.status == "Open"
                      ? Colors.green.shade700
                      : Colors.grey.shade600,
                ),
                child: Text(
                  (project?.status ?? "Open").toUpperCase(),
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
            project?.title ?? "-",
            style: TextStyle(
              fontSize: Dimensions.text16,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            project?.info?.target ??
                "Target: Rp ${Formats.spell(project?.fundingTarget ?? 0)}",
            style: TextStyle(
              fontSize: Dimensions.text13,
              fontWeight: FontWeight.w600,
              color: const Color(0xFFC5A059),
            ),
          ),
          SizedBox(height: Dimensions.size15),
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
                "${"collected_funding".tr()}: Rp ${Formats.spell(project?.fundingCollected ?? 0)}",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: AppColors.onSurfaceVariant(),
                ),
              ),
              Text(
                "${(percent * 100).toStringAsFixed(1)}%",
                style: TextStyle(
                  fontSize: Dimensions.text12,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F2C59),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildKeyMetrics() {
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
            "Ringkasan Kemitraan",
            style: TextStyle(
              fontSize: Dimensions.text13,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          _buildDetailRow(
            label: "tenor".tr(),
            value: project?.info?.tenor ?? "12 Bulan",
          ),
          _buildDetailRow(
            label: "estimated_roi".tr(),
            value: project?.info?.returnRate ?? "28.62%/tahun",
            isHighlight: true,
          ),
          _buildDetailRow(
            label: "min_investment".tr(),
            value: project?.info?.minInvestment ?? "Rp 500.000.000",
          ),
          _buildDetailRow(
            label: "remaining_slot".tr(),
            value: project?.info?.investorSlot ?? "1 Slot Kemitraan Terbuka",
          ),
          if (project?.info?.operatorPerk != null &&
              project!.info!.operatorPerk!.isNotEmpty) ...[
            _buildDetailRow(
              label: "Fasilitas Operator",
              value: project!.info!.operatorPerk!,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildDetailRow({
    required String label,
    required String value,
    bool isHighlight = false,
  }) {
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
              color: isHighlight
                  ? const Color(0xFFC5A059)
                  : AppColors.onSurface(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGatedContentNotice() {
    final bool isAuthenticated = Shared.ACCOUNT != null;

    if (isAuthenticated) {
      return Container(
        margin: EdgeInsets.only(top: Dimensions.size15),
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
                Icon(
                  Icons.verified,
                  color: const Color(0xFFC5A059),
                  size: Dimensions.size20,
                ),
                SizedBox(width: Dimensions.size10),
                Text(
                  "Akses Dokumen Resmi Investor",
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              "Anda telah terautentikasi. Anda dapat mengunduh proposal lengkap proyek dengan stempel watermark digital resmi.",
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: Colors.white70,
                height: 1.4,
              ),
            ),
            SizedBox(height: Dimensions.size15),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFFC5A059),
                foregroundColor: Colors.white,
              ),
              onPressed: () => context.push("/portal/projects/${project?.id}"),
              icon: Icon(Icons.picture_as_pdf, size: Dimensions.size15),
              label: Text("download_proposal".tr()),
            ),
          ],
        ),
      );
    }

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size15),
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: BorderSide(
            color: const Color(0xFF0F2C59).withValues(alpha: 0.3),
          ),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.lock_outline,
                color: const Color(0xFF0F2C59),
                size: Dimensions.size20,
              ),
              SizedBox(width: Dimensions.size10),
              Expanded(
                child: Text(
                  "Dokumen Finansial Terlindungi",
                  style: TextStyle(
                    fontSize: Dimensions.text13,
                    fontWeight: FontWeight.bold,
                    color: AppColors.onSurface(),
                  ),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            "gated_content_notice".tr(),
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
              height: 1.4,
            ),
          ),
          SizedBox(height: Dimensions.size15),
          Row(
            children: [
              Expanded(
                child: FilledButton(
                  onPressed: () => context.push("/sign-in"),
                  child: Text("sign_in".tr()),
                ),
              ),
              SizedBox(width: Dimensions.size10),
              Expanded(
                child: OutlinedButton(
                  onPressed: () => context.push("/register"),
                  child: Text("sign_up".tr()),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget body() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        _buildOverviewCard(),
        _buildKeyMetrics(),
        _buildGatedContentNotice(),
        SizedBox(height: Dimensions.size20),
        FilledButton.icon(
          onPressed: () => context.push("/roi-calculator"),
          icon: Icon(Icons.calculate, size: Dimensions.size15),
          label: Text("simulate_investment".tr()),
        ),
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
          appBar: appBar(),
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
