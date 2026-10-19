// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/module/front/consultation/consultation_event.dart";
import "package:mgi_investor/module/front/consultation/consultation_state.dart";

class ConsultationBloc extends Bloc<ConsultationEvent, ConsultationState> {
  ConsultationBloc() : super(ConsultationInitial()) {
    on<ConsultationSubmit>((event, emit) async {
      try {
        emit(ConsultationSubmitLoading());

        final Response response = await ApiManager.submitContactInquiry(
          request: event.request,
        );

        if (response.statusCode == 200 || response.statusCode == 201) {
          String msg = "inquiry_sent_success".tr();
          if (response.data is Map && response.data["message"] != null) {
            msg = response.data["message"].toString();
          }
          emit(ConsultationSubmitSuccess(message: msg));
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(ConsultationSubmitFinished());
      }
    });
  }
}
