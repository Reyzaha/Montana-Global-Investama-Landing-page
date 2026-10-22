// ignore_for_file: cascade_invocations, always_specify_types, avoid_print, non_constant_identifier_names, depend_on_referenced_packages

import "dart:convert";
import "dart:typed_data";

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:mgi_investor/api/endpoint/auth/auth_request.dart";
import "package:mgi_investor/api/endpoint/contact/contact_request.dart";
import "package:mgi_investor/api/interceptor/authorization_interceptor.dart";
import "package:mgi_investor/constant/api_url.dart";
import "package:mgi_investor/helper/formats.dart";

class ApiManager {
  static Future<Dio> getDio() async {
    Dio dio = Dio(
      BaseOptions(
        baseUrl: MAIN_BASE,
        connectTimeout: const Duration(seconds: 60),
        receiveTimeout: const Duration(seconds: 60),
        contentType: Headers.jsonContentType,
        receiveDataWhenStatusError: false,
        validateStatus: (status) => status != null,
        responseDecoder: (responseBytes, options, responseBody) {
          String value = utf8.decode(responseBytes, allowMalformed: true);

          if (responseBody.statusCode >= 300) {
            try {
              return jsonDecode(value);
            } catch (ex) {
              return value;
            }
          } else {
            return value;
          }
        },
      ),
    );

    dio.interceptors.add(BaseEncodingInterceptor());
    dio.interceptors.add(AuthorizationInterceptor());
    dio.interceptors.add(
      LogInterceptor(
        requestBody: true,
        responseBody: true,
        error: true,
        request: true,
        requestHeader: true,
        responseHeader: true,
      ),
    );

    return dio;
  }

  static Future<Uint8List> download({
    required String url,
  }) async {
    Response response = await Dio().get(
      url,
      options: Options(
        responseType: ResponseType.bytes,
      ),
    );

    return response.data;
  }

  static Future<Response> companyProfile() async {
    Dio dio = await getDio();

    Response response = await dio.get(ApiUrl.COMPANY_PROFILE.path);

    return response;
  }

  static Future<Response> projects({
    String? city,
  }) async {
    Dio dio = await getDio();

    Map<String, dynamic> query = {};
    if (city != null && city.isNotEmpty && city != "all") {
      query["city"] = city;
    }

    Response response = await dio.get(
      ApiUrl.PROJECTS.path,
      queryParameters: query,
    );

    return response;
  }

  static Future<Response> projectDetail({
    required String id,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.get(
      ApiUrl.PROJECTS.path,
      queryParameters: {"id": id},
    );

    return response;
  }

  static Future<Response> cities() async {
    Dio dio = await getDio();

    Response response = await dio.get(ApiUrl.CITIES.path);

    return response;
  }

  static Future<Response> articles({
    String? category,
    String? slug,
    int limit = 10,
  }) async {
    Dio dio = await getDio();

    Map<String, dynamic> query = {
      "limit": limit,
    };
    if (category != null && category.isNotEmpty && category != "all") {
      query["category"] = category;
    }
    if (slug != null && slug.isNotEmpty) {
      query["slug"] = slug;
    }

    Response response = await dio.get(
      ApiUrl.ARTICLES.path,
      queryParameters: query,
    );

    return response;
  }

  static Future<Response> signIn({
    required SignInRequest request,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.AUTH.path}?action=login",
      data: Formats.convert(request.toJson()),
    );

    return response;
  }

  static Future<Response> registerIndividual({
    required RegisterIndividualRequest request,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.AUTH.path}?action=register",
      data: Formats.convert(request.toJson()),
    );

    return response;
  }

  static Future<Response> registerInstitution({
    required RegisterInstitutionRequest request,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.AUTH.path}?action=register",
      data: Formats.convert(request.toJson()),
    );

    return response;
  }

  static Future<Response> googleLogin({
    required GoogleLoginRequest request,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.AUTH.path}?action=google_login",
      data: Formats.convert(request.toJson()),
    );

    return response;
  }

  static Future<Response> submitContactInquiry({
    required ContactInquiryRequest request,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      ApiUrl.CONTACT.path,
      data: Formats.convert(request.toJson()),
    );

    return response;
  }

  static Future<Response> account() async {
    Dio dio = await getDio();

    Response response = await dio.get(
      "${ApiUrl.AUTH.path}?action=me",
    );

    return response;
  }

  static Future<Response> signOut() async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.AUTH.path}?action=logout",
    );

    return response;
  }

  static Future<Response> investorDashboard() async {
    Dio dio = await getDio();

    Response response = await dio.get(
      "${ApiUrl.INVESTOR_DASHBOARD.path}?action=get",
    );

    return response;
  }

  static Future<Response> notifications({
    int limit = 30,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.get(
      ApiUrl.NOTIFICATIONS.path,
      queryParameters: {
        "limit": limit,
      },
    );

    return response;
  }

  static Future<Response> markNotificationRead({
    required int id,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.NOTIFICATIONS.path}?action=mark_read",
      data: Formats.convert({
        "id": id,
      }),
    );

    return response;
  }

  static Future<Response> markAllNotificationsRead() async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.NOTIFICATIONS.path}?action=mark_all_read",
    );

    return response;
  }

  static Future<Response> registerDeviceToken({
    required String token,
    String deviceType = "android",
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      ApiUrl.DEVICE_TOKEN.path,
      data: Formats.convert({
        "fcm_token": token,
        "device_type": deviceType,
      }),
    );

    return response;
  }

  static Future<Response> deleteDeviceToken({
    required String token,
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.DEVICE_TOKEN.path}?action=delete",
      data: Formats.convert({
        "fcm_token": token,
      }),
    );

    return response;
  }

  static Future<Response> saveBankAccount({
    required String bankName,
    required String accountNumber,
    required String accountHolder,
    String branch = "",
  }) async {
    Dio dio = await getDio();

    Response response = await dio.post(
      "${ApiUrl.INVESTOR_DASHBOARD.path}?action=save_bank",
      data: Formats.convert({
        "bank_name": bankName,
        "account_number": accountNumber,
        "account_holder": accountHolder,
        "branch": branch,
      }),
    );

    return response;
  }
}
