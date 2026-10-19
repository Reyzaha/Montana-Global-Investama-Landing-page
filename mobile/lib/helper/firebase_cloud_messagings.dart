import "dart:ui";

import "package:base/base.dart";
import "package:firebase_messaging/firebase_messaging.dart";
import "package:flutter/foundation.dart";
import "package:intl/date_symbol_data_local.dart";
import "package:jiffy/jiffy.dart";
import "package:mgi_investor/helper/notifications.dart";

bool isInBackground = false;
bool isMounted = false;

@pragma("vm:entry-point")
Future<void> firebaseCloudMessagingBackgroundHandler(RemoteMessage remoteMessage) async {
  isInBackground = true;
  await FirebaseCloudMessagings.receive(remoteMessage);
}

class FirebaseCloudMessagings {
  static Future<void> initialize() async {
    FirebaseMessaging.onBackgroundMessage(
      firebaseCloudMessagingBackgroundHandler,
    );

    FirebaseMessaging.onMessage.listen((RemoteMessage remoteMessage) async {
      await receive(remoteMessage);
    });

    await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );

    try {
      // Subskripsi topik publik untuk tamu / calon investor (sesuai Opsi A)
      await FirebaseMessaging.instance.subscribeToTopic("public_projects");
      await FirebaseMessaging.instance.subscribeToTopic("mgi_news");
    } catch (e) {
      if (kDebugMode) {
        print("FCM topic subscription error: $e");
      }
    }

    try {
      String? token = await FirebaseMessaging.instance.getToken();
      if (kDebugMode) {
        print("Firebase Cloud Messaging token: $token");
      }
    } catch (e) {
      if (kDebugMode) {
        print("FCM token retrieval error: $e");
      }
    }
  }

  static Future<void> initializePlugins() async {
    if (!isMounted) {
      DartPluginRegistrant.ensureInitialized();

      await BasePreferences.getInstance().init();
      await Notifications.getInstance().initialize();

      initializeDateFormatting();

      await Jiffy.setLocale("id");

      isMounted = true;
    }
  }

  static Future<void> receive(RemoteMessage remoteMessage) async {
    if (kDebugMode) {
      print("[MGI Investor] isInBackground -> $isInBackground, isMounted -> $isMounted");
    }

    if (isInBackground) {
      await initializePlugins();
    }

    if (remoteMessage.data.containsKey("notification.title")) {
      await Notifications.getInstance().showNotification(
        title: remoteMessage.data["notification.title"]?.toString(),
        body: remoteMessage.data["notification.body"]?.toString(),
        payload: remoteMessage.data["notification.payload"]?.toString(),
      );
    } else if (remoteMessage.notification != null) {
      await Notifications.getInstance().showNotification(
        title: remoteMessage.notification?.title,
        body: remoteMessage.notification?.body,
      );
    }
  }
}
