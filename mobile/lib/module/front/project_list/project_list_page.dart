// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:mgi_investor/module/front/project_list/project_list_bloc.dart";
import "package:mgi_investor/module/front/project_list/project_list_event.dart";
import "package:mgi_investor/module/front/project_list/project_list_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProjectListPage extends StatefulWidget {
  const ProjectListPage({
    super.key,
  });

  @override
  ProjectListPageState createState() => ProjectListPageState();
}

class ProjectListPageState extends State<ProjectListPage>
    with WidgetsBindingObserver {
  bool loading = false;
  List<Project>? projects;
  List<City> cities = [];
  String selectedStatus = "all";

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
    context.read<ProjectListBloc>().add(
      ProjectListLoad(city: "all"),
    );
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "project_catalog".tr(),
    );
  }

  bool _matchesStatusFilter(Project project, String filter) {
    if (filter == "all") {
      return true;
    }

    final String s = (project.status ?? "").toLowerCase().trim();

    if (filter == "open") {
      return s.contains("open") || s.contains("buka");
    }

    if (filter == "secured" || filter == "funded") {
      return s.contains("funded") ||
          s.contains("fund") ||
          s.contains("didanai") ||
          s.contains("secured") ||
          s.contains("terkunci") ||
          s.contains("closed") ||
          s.contains("tutup");
    }

    if (filter == "comingsoon" || filter == "coming") {
      return s.contains("soon") ||
          s.contains("coming") ||
          s.contains("segera");
    }

    return true;
  }

  Color _statusColor(String? status) {
    final String s = (status ?? "").toLowerCase();
    if (s.contains("fully") ||
        s.contains("fund") ||
        s.contains("secured") ||
        s.contains("terkunci")) {
      return const Color(0xFFC5A059);
    }
    if (s.contains("coming") || s.contains("soon") || s.contains("segera")) {
      return Colors.blueGrey;
    }
    return Colors.green.shade700;
  }

  String _statusLabel(String? status) {
    final String s = (status ?? "").toLowerCase();
    if (s.contains("fully") ||
        s.contains("fund") ||
        s.contains("secured") ||
        s.contains("terkunci")) {
      return "MITRA TERKUNCI (SECURED)";
    }
    if (s.contains("coming") || s.contains("soon") || s.contains("segera")) {
      return "SEGERA HADIR";
    }
    return (status ?? "Open").toUpperCase();
  }

  Widget _buildStatusFilters() {
    final List<Map<String, String>> filters = [
      {"id": "all", "label": "filter_all".tr()},
      {"id": "open", "label": "filter_open".tr()},
      {"id": "secured", "label": "filter_secured".tr()},
      {"id": "comingsoon", "label": "filter_coming_soon".tr()},
    ];

    return SizedBox(
      height: Dimensions.size40,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: EdgeInsets.symmetric(horizontal: Dimensions.size15),
        children: filters.map((f) {
          return _buildFilterChip(
            label: f["label"]!,
            slug: f["id"]!,
          );
        }).toList(),
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required String slug,
  }) {
    final bool isSelected = selectedStatus.toLowerCase() == slug.toLowerCase();

    return Padding(
      padding: EdgeInsets.only(right: Dimensions.size10),
      child: InkWell(
        onTap: () {
          setState(() {
            selectedStatus = slug;
          });
        },
        customBorder: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size20),
          smoothness: 1,
        ),
        child: Ink(
          padding: EdgeInsets.symmetric(
            horizontal: Dimensions.size15,
            vertical: Dimensions.size5,
          ),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size20),
              smoothness: 1,
              side: BorderSide(
                color: isSelected
                    ? const Color(0xFFC5A059)
                    : AppColors.outline(),
              ),
            ),
            color: isSelected
                ? const Color(0xFF0F2C59)
                : AppColors.surfaceContainerLowest(),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? Colors.white : AppColors.onSurface(),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildProjectCard(Project project) {
    final double percent = project.fundingTarget > 0
        ? (project.fundingCollected / project.fundingTarget).clamp(0.0, 1.0)
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
                      borderRadius: BorderRadius.circular(Dimensions.size5),
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
                      borderRadius: BorderRadius.circular(Dimensions.size20),
                      smoothness: 1,
                    ),
                    color: _statusColor(project.status),
                  ),
                  child: Text(
                    _statusLabel(project.status),
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
                fontSize: Dimensions.text16,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
            if (project.info?.target != null) ...[
              SizedBox(height: Dimensions.size4),
              Text(
                project.info!.target!,
                style: TextStyle(
                  fontSize: Dimensions.text12,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFFC5A059),
                ),
              ),
            ],
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
            SizedBox(height: Dimensions.size10),
            Container(
              padding: EdgeInsets.all(Dimensions.size10),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                ),
                color: AppColors.surface(),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildMetricCol(
                    label: "tenor".tr(),
                    value: project.info?.tenor ?? "12 Bulan",
                  ),
                  _buildMetricCol(
                    label: "estimated_roi".tr(),
                    value: project.info?.returnRate ?? "28.62%/thn",
                    highlight: true,
                  ),
                  _buildMetricCol(
                    label: "min_investment".tr(),
                    value: project.info?.minInvestment ?? "Rp 500 Juta",
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetricCol({
    required String label,
    required String value,
    bool highlight = false,
  }) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: Dimensions.text9,
            color: AppColors.onSurfaceVariant(),
          ),
        ),
        SizedBox(height: Dimensions.size2),
        Text(
          value,
          style: TextStyle(
            fontSize: Dimensions.text11,
            fontWeight: FontWeight.bold,
            color: highlight ? const Color(0xFFC5A059) : AppColors.onSurface(),
          ),
        ),
      ],
    );
  }

  Widget body() {
    final List<Project> all = projects ?? [];
    final List<Project> list =
        all.where((p) => _matchesStatusFilter(p, selectedStatus)).toList();

    return RefreshIndicator(
      onRefresh: () async => refresh(),
      child: Column(
        children: [
          SizedBox(height: Dimensions.size10),
          _buildStatusFilters(),
          SizedBox(height: Dimensions.size10),
          Expanded(
            child: list.isEmpty
                ? Center(
                    child: Text(
                      "no_data".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text13,
                        color: AppColors.onSurfaceVariant(),
                      ),
                    ),
                  )
                : ListView.separated(
                    padding: EdgeInsets.all(Dimensions.size15),
                    itemCount: list.length,
                    separatorBuilder: (_, __) =>
                        SizedBox(height: Dimensions.size10),
                    itemBuilder: (BuildContext context, int index) {
                      return _buildProjectCard(list[index]);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<ProjectListBloc, ProjectListState>(
      listener: (BuildContext context, ProjectListState state) {
        if (state is ProjectListLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is ProjectListLoadSuccess) {
          setState(() {
            projects = state.projects;
            cities = state.cities;
          });
        } else if (state is ProjectListLoadFinished) {
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
            if (projects == null) {
              return BaseBodyStatus.fail;
            }
            if (projects!.isEmpty) {
              return BaseBodyStatus.empty;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
