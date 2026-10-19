// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_event.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_state.dart";

class CompanyProfileBloc
    extends Bloc<CompanyProfileEvent, CompanyProfileState> {
  CompanyProfileBloc() : super(CompanyProfileInitial()) {
    on<CompanyProfileLoad>((event, emit) async {
      try {
        emit(CompanyProfileLoadLoading());

        final Response response = await ApiManager.companyProfile();

        if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
          final CompanyProfile profile = CompanyProfile().parse(
            response.data as Map<String, dynamic>,
          );
          emit(CompanyProfileLoadSuccess(profile: profile));
        } else {
          emit(CompanyProfileLoadSuccess(profile: null));
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(CompanyProfileLoadFinished());
      }
    });
  }
}
