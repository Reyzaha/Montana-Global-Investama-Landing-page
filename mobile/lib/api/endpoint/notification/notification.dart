// ignore_for_file: always_specify_types

import "package:mgi_investor/helper/formats.dart";

class InvestorNotification {
  int id = 0;
  int investorId = 0;
  String? projectId;
  String? projectTitle;
  String type = "system";
  String? title;
  String? message;
  String? linkUrl;
  bool isRead = false;
  String? createdAt;

  InvestorNotification parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    investorId = Formats.tryParseNumber(json["investor_id"]).toInt();
    projectId = json["project_id"]?.toString();
    projectTitle = json["project_title"]?.toString();
    type = json["type"]?.toString() ?? "system";
    title = json["title"]?.toString();
    message = json["message"]?.toString();
    linkUrl = json["link_url"]?.toString();
    isRead = json["is_read"] == 1 ||
        json["is_read"] == true ||
        json["is_read"] == "1";
    createdAt = json["created_at"]?.toString();

    return this;
  }
}

class InvestorNotificationResponse {
  int unreadCount = 0;
  int total = 0;
  List<InvestorNotification> notifications = [];

  InvestorNotificationResponse parse(Map<String, dynamic> json) {
    unreadCount = Formats.tryParseNumber(
      json["unread_count"] ?? json["unreadCount"],
    ).toInt();
    total = Formats.tryParseNumber(
      json["total"],
    ).toInt();

    if (json["notifications"] != null && json["notifications"] is List) {
      notifications = List<InvestorNotification>.from(
        (json["notifications"] as List).map(
          (x) => InvestorNotification().parse(x as Map<String, dynamic>),
        ),
      );
    }

    return this;
  }
}
