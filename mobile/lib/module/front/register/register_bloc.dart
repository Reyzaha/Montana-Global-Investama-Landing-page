import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/auth/auth_response.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";
import "package:mgi_investor/module/front/register/register_event.dart";
import "package:mgi_investor/module/front/register/register_state.dart";
import "package:mgi_investor/shared.dart";

class RegisterBloc extends Bloc<RegisterEvent, RegisterState> {
  RegisterBloc() : super(RegisterInitial()) {
    on<RegisterIndividualSubmit>((event, emit) async {
      try {
        emit(RegisterSubmitLoading());

        final Response response = await ApiManager.registerIndividual(
          request: event.request,
        );

        if (response.statusCode == 200) {
          final AuthResponse authResponse = AuthResponse().parse(
            response.data is Map<String, dynamic> ? response.data : {},
          );

          if (authResponse.user != null) {
            Shared.ACCOUNT = authResponse.user;

            final String token = authResponse.csrfToken ?? "session_token_${authResponse.user?.id}";
            await Preferences.setString(PreferenceKey.SESSION_ID, token);
            await Preferences.setString(
              PreferenceKey.USER_ID,
              authResponse.user?.id?.toString() ?? "",
            );

            emit(
              RegisterSubmitSuccess(
                account: authResponse.user!,
                message: authResponse.message ?? "data_has_been_successfully_saved".tr(),
              ),
            );
          } else {
            BaseOverlays.error(message: "something_wrong_please_try_again".tr());
          }
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(RegisterSubmitFinished());
      }
    });

    on<RegisterInstitutionSubmit>((event, emit) async {
      try {
        emit(RegisterSubmitLoading());

        final Response response = await ApiManager.registerInstitution(
          request: event.request,
        );

        if (response.statusCode == 200) {
          final AuthResponse authResponse = AuthResponse().parse(
            response.data is Map<String, dynamic> ? response.data : {},
          );

          if (authResponse.user != null) {
            Shared.ACCOUNT = authResponse.user;

            final String token = authResponse.csrfToken ?? "session_token_${authResponse.user?.id}";
            await Preferences.setString(PreferenceKey.SESSION_ID, token);
            await Preferences.setString(
              PreferenceKey.USER_ID,
              authResponse.user?.id?.toString() ?? "",
            );

            emit(
              RegisterSubmitSuccess(
                account: authResponse.user!,
                message: authResponse.message ?? "data_has_been_successfully_saved".tr(),
              ),
            );
          } else {
            BaseOverlays.error(message: "something_wrong_please_try_again".tr());
          }
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(RegisterSubmitFinished());
      }
    });
  }
}
