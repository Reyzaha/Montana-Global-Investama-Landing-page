// ignore_for_file: always_specify_types, always_declare_return_types

import "package:flutter_local_notifications/flutter_local_notifications.dart";
import "package:mgi_investor/helper/firebase_cloud_messagings.dart";

Future<void> notificationTapped(NotificationResponse details) async {}

class Notifications {
  static Notifications? _instance;

  Notifications._internal();

  static Notifications getInstance() {
    _instance ??= Notifications._internal();
    return _instance!;
  }

  late FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin;

  static const String channelId = "mgi_investor_updates";
  static const String channelName = "Investor Updates";
  static const String channelDescription = "Pemberitahuan proyek baru & peluang investasi riil";

  Future<void> initialize() async {
    flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin()
      ..initialize(
        const InitializationSettings(
          android: AndroidInitializationSettings("@mipmap/ic_launcher"),
          iOS: DarwinInitializationSettings(),
          macOS: DarwinInitializationSettings(),
          windows: WindowsInitializationSettings(
            appName: "Montana Global Investama",
            appUserModelId: "com.montana.investor.mgi_investor",
            guid: "64720999-7157-41ab-bc9c-9824d5218d99",
          ),
        ),
        onDidReceiveNotificationResponse: notificationTapped,
        onDidReceiveBackgroundNotificationResponse: notificationTapped,
      )
      ..resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.createNotificationChannel(
        const AndroidNotificationChannel(
          channelId,
          channelName,
          description: channelDescription,
          importance: Importance.max,
        ),
      );

    if (!isInBackground) {
      flutterLocalNotificationsPlugin
        ..resolvePlatformSpecificImplementation<IOSFlutterLocalNotificationsPlugin>()?.requestPermissions(
          alert: true,
          badge: true,
          sound: true,
        )
        ..resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()?.requestNotificationsPermission();
    }
  }

  Future<void> showNotification({
    String channelName = channelName,
    String channelDescription = channelDescription,
    String? title,
    String? body,
    String? payload,
  }) async {
    NotificationDetails platformChannelSpecifics = NotificationDetails(
      android: AndroidNotificationDetails(
        channelId,
        channelName,
        channelDescription: channelDescription,
        importance: Importance.max,
        playSound: true,
        priority: Priority.high,
        styleInformation: BigTextStyleInformation(
          body ?? "",
          htmlFormatTitle: true,
          htmlFormatSummaryText: true,
          htmlFormatContentTitle: true,
          htmlFormatBigText: true,
          htmlFormatContent: true,
        ),
      ),
      iOS: const DarwinNotificationDetails(),
    );

    await flutterLocalNotificationsPlugin.show(
      0,
      title,
      body,
      platformChannelSpecifics,
      payload: payload,
    );
  }
}
