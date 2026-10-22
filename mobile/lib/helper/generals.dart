// ignore_for_file: use_build_context_synchronously, cascade_invocations, always_specify_types, depend_on_referenced_packages, avoid_print

import "dart:convert";
import "dart:io";

import "package:android_id/android_id.dart";
import "package:base/base.dart";
import "package:crypto/crypto.dart" as crypto;
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:firebase_messaging/firebase_messaging.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";
import "package:mgi_investor/model/account.dart";
import "package:mgi_investor/shared.dart";
import "package:package_info_plus/package_info_plus.dart";
import "package:url_launcher/url_launcher_string.dart";

class Generals {
  static Future<void> self() async {
    if (Preferences.contain(PreferenceKey.SESSION_ID)) {
      try {
        final Response response = await ApiManager.account();

        if (response.statusCode == 200) {
          Shared.ACCOUNT = Account().parse(
            response.data is Map<String, dynamic> ? response.data : {},
          );

          try {
            final String? fcmToken = await FirebaseMessaging.instance.getToken();
            if (fcmToken != null && fcmToken.isNotEmpty) {
              await ApiManager.registerDeviceToken(token: fcmToken);
            }
          } catch (_) {}
        }
      } catch (e) {
        if (kDebugMode) {
          print("Generals.self error: $e");
        }
      }
    }
  }

  static Future<void> signOut(BuildContext context) async {
    try {
      final String? fcmToken = await FirebaseMessaging.instance.getToken();
      if (fcmToken != null && fcmToken.isNotEmpty) {
        await ApiManager.deleteDeviceToken(token: fcmToken);
      }
      await ApiManager.signOut();
      await FirebaseMessaging.instance.deleteToken();
    } catch (e) {
      if (kDebugMode) {
        print("Sign out error: $e");
      }
    }

    if (Preferences.contain(PreferenceKey.SESSION_ID)) {
      await Preferences.remove(PreferenceKey.SESSION_ID);
    }
    if (Preferences.contain(PreferenceKey.USER_ID)) {
      await Preferences.remove(PreferenceKey.USER_ID);
    }

    Shared.ACCOUNT = null;

    context.go("/sign-in");
  }

  static Future<void> changeLanguage(
    BuildContext context,
    String locale,
  ) async {
    final Locale newLocale = Locale.fromSubtags(languageCode: locale);
    await context.setLocale(newLocale);
  }

  static Future<void> launchUrls(String url) async {
    try {
      final bool success = await launchUrlString(
        url,
        mode: LaunchMode.externalApplication,
      );

      if (!success) {
        throw Exception("Could not launch $url");
      }
    } catch (e) {
      BaseOverlays.error(message: e.toString());
    }
  }

  static String sha1(String? text) {
    if (text == null) {
      return "";
    }
    return crypto.sha1.convert(utf8.encode(text)).toString();
  }

  static String sha256(String? text) {
    if (text == null) {
      return "";
    }
    return crypto.sha256.convert(utf8.encode(text)).toString();
  }

  static String md5(String? text) {
    if (text == null) {
      return "";
    }
    return crypto.md5.convert(utf8.encode(text)).toString();
  }

  static Future<String> appVersion() async {
    final PackageInfo packageInfo = await PackageInfo.fromPlatform();
    return "${packageInfo.version}+${packageInfo.buildNumber}";
  }

  static Future<void> checkSession(BuildContext context) async {
    if (!Preferences.contain(PreferenceKey.SESSION_ID)) {
      context.go("/sign-in");
    }
  }

  static Future<String> deviceId() async {
    if (kReleaseMode) {
      if (Platform.isAndroid) {
        const AndroidId androidId = AndroidId();
        return await androidId.getId() ?? "0000000000000000";
      } else if (Platform.isIOS) {
        return "0000000000000000";
      }
    }
    return "dev-device-mgi-001";
  }
}
