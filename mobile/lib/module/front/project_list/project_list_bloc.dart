// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/module/front/project_list/project_list_event.dart";
import "package:mgi_investor/module/front/project_list/project_list_state.dart";

class ProjectListBloc extends Bloc<ProjectListEvent, ProjectListState> {
  ProjectListBloc() : super(ProjectListInitial()) {
    on<ProjectListLoad>((event, emit) async {
      try {
        emit(ProjectListLoadLoading());

        final String activeCity = event.city ?? "all";

        final Response projectRes = await ApiManager.projects(city: activeCity);
        List<Project> projects = [];
        if (projectRes.statusCode == 200 &&
            projectRes.data is Map<String, dynamic>) {
          final ProjectListResponse listRes = ProjectListResponse().parse(
            projectRes.data as Map<String, dynamic>,
          );
          projects = listRes.projects;
        }

        final Response cityRes = await ApiManager.cities();
        List<City> cities = [];
        if (cityRes.statusCode == 200 && cityRes.data is Map<String, dynamic>) {
          final CityListResponse listRes = CityListResponse().parse(
            cityRes.data as Map<String, dynamic>,
          );
          cities = listRes.cities;
        }

        emit(
          ProjectListLoadSuccess(
            projects: projects,
            cities: cities,
            selectedCity: activeCity,
          ),
        );
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(ProjectListLoadFinished());
      }
    });
  }
}
