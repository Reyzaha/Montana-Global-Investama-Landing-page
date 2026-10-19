// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/module/front/project_list/project_list_bloc.dart";
import "package:mgi_investor/module/front/project_list/project_list_event.dart";
import "package:mgi_investor/module/front/project_list/project_list_state.dart";
import "package:mgi_investor/widget/empty_state.dart";
import "package:mgi_investor/widget/project_card.dart";
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
                ? EmptyState(
                    title: "empty_projects_title".tr(),
                    message: "empty_projects_desc".tr(),
                    icon: Icons.business_center_outlined,
                  )
                : ListView.separated(
                    padding: EdgeInsets.all(Dimensions.size15),
                    itemCount: list.length,
                    separatorBuilder: (_, __) =>
                        SizedBox(height: Dimensions.size15),
                    itemBuilder: (BuildContext context, int index) {
                      return ProjectCard(project: list[index]);
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
