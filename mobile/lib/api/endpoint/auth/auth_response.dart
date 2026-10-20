import "package:mgi_investor/model/account.dart";

class AuthResponse {
  Account? user;
  String? message;
  String? csrfToken;
  String? token;

  AuthResponse parse(Map<String, dynamic> json) {
    final Map<String, dynamic> target =
        json["data"] is Map<String, dynamic>
            ? json["data"] as Map<String, dynamic>
            : json;

    if (target["user"] != null && target["user"] is Map<String, dynamic>) {
      user = Account().parse(target["user"] as Map<String, dynamic>);
    }
    message = target["message"] ?? json["message"];
    token = target["token"] ?? json["token"];
    csrfToken = target["csrf_token"] ?? json["csrf_token"] ?? token;
    return this;
  }
}
