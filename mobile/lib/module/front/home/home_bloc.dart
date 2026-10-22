// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/module/front/home/home_event.dart";
import "package:mgi_investor/module/front/home/home_state.dart";

class HomeBloc extends Bloc<HomeEvent, HomeState> {
  HomeBloc() : super(HomeInitial()) {
    on<HomeLoad>((event, emit) async {
      try {
        emit(HomeLoadLoading());

        final Response profileRes = await ApiManager.companyProfile();
        CompanyProfile? profile;
        if (profileRes.statusCode == 200 &&
            profileRes.data is Map<String, dynamic>) {
          profile = CompanyProfile().parse(
            profileRes.data as Map<String, dynamic>,
          );
        }

        final Response projectRes = await ApiManager.projects();
        List<Project> projects = [];
        String? disclaimerText;
        if (projectRes.statusCode == 200 &&
            projectRes.data is Map<String, dynamic>) {
          final ProjectListResponse listRes = ProjectListResponse().parse(
            projectRes.data as Map<String, dynamic>,
          );
          projects = listRes.projects;
          disclaimerText = listRes.disclaimerText;
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
          HomeLoadSuccess(
            companyProfile: profile,
            featuredProjects: projects,
            cities: cities,
            disclaimerText: disclaimerText,
          ),
        );
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(HomeLoadFinished());
      }
    });
  }
}
