import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:firebase_messaging/firebase_messaging.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/auth/auth_request.dart";
import "package:mgi_investor/api/endpoint/auth/auth_response.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_event.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_state.dart";
import "package:mgi_investor/shared.dart";

class SignInBloc extends Bloc<SignInEvent, SignInState> {
  SignInBloc() : super(SignInInitial()) {
    on<SignInSubmit>((event, emit) async {
      try {
        emit(SignInSubmitLoading());

        final SignInRequest request = SignInRequest(
          email: event.email,
          password: event.password,
          type: event.type,
        );

        final Response response = await ApiManager.signIn(request: request);

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

            try {
              final String? fcmToken = await FirebaseMessaging.instance.getToken();
              if (fcmToken != null && fcmToken.isNotEmpty) {
                await ApiManager.registerDeviceToken(token: fcmToken);
              }
            } catch (_) {}

            emit(
              SignInSubmitSuccess(
                account: authResponse.user!,
                token: token,
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
        emit(SignInSubmitFinished());
      }
    });

    on<SignInGoogleSubmit>((event, emit) async {
      try {
        emit(SignInSubmitLoading());

        final GoogleLoginRequest request = GoogleLoginRequest(
          credential: event.credential,
          type: event.type,
        );

        final Response response = await ApiManager.googleLogin(request: request);

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

            try {
              final String? fcmToken = await FirebaseMessaging.instance.getToken();
              if (fcmToken != null && fcmToken.isNotEmpty) {
                await ApiManager.registerDeviceToken(token: fcmToken);
              }
            } catch (_) {}

            emit(
              SignInSubmitSuccess(
                account: authResponse.user!,
                token: token,
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
        emit(SignInSubmitFinished());
      }
    });
  }
}
