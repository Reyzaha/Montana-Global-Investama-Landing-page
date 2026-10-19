import "package:mgi_investor/model/account.dart";

class AuthResponse {
  Account? user;
  String? message;
  String? csrfToken;

  AuthResponse parse(Map<String, dynamic> json) {
    if (json["user"] != null && json["user"] is Map<String, dynamic>) {
      user = Account().parse(json["user"] as Map<String, dynamic>);
    }
    message = json["message"];
    csrfToken = json["csrf_token"];
    return this;
  }
}
