// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";

class AuthorizationInterceptor extends InterceptorsWrapper {
  static String? _sessionCookie;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    if (Preferences.contain(PreferenceKey.SESSION_ID)) {
      final String? token = Preferences.getString(PreferenceKey.SESSION_ID);
      if (token != null && token.isNotEmpty) {
        options.headers["Authorization"] = "Bearer $token";
        options.headers["X-Auth-Token"] = token;
        options.headers["X-Session-ID"] = token;
      }
    }

    if (_sessionCookie != null && _sessionCookie!.isNotEmpty) {
      options.headers["Cookie"] = _sessionCookie!;
    }

    return handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    final dynamic setCookie = response.headers["set-cookie"];
    if (setCookie != null) {
      List<String> cookies = [];
      if (setCookie is List) {
        cookies = setCookie.map((e) => e.toString()).toList();
      } else if (setCookie is String) {
        cookies = [setCookie];
      }

      for (final String c in cookies) {
        if (c.contains("PHPSESSID=")) {
          final RegExp reg = RegExp(r"PHPSESSID=([^;]+)");
          final Match? match = reg.firstMatch(c);
          if (match != null) {
            _sessionCookie = "PHPSESSID=${match.group(1)}";
          }
        }
      }
    }

    if (response.statusCode != null) {
      if (response.statusCode! >= 300) {
        if (response.data is String) {
          BaseOverlays.error(message: response.data);
        } else if (response.data is Map<String, dynamic>) {
          BaseOverlays.error(message: response.data["errorMessage"] ?? response.data["message"] ?? "");
        }

        handler.resolve(Response(requestOptions: response.requestOptions, statusCode: 218));

        return;
      }
    }

    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    return handler.next(err);
  }
}
