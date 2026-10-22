// ignore_for_file: non_constant_identifier_names, constant_identifier_names

import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";

String get MAIN_BASE {
  final String? customUrl = Preferences.getString(PreferenceKey.SERVER_URL);

  return _validateUrl(customUrl) ?? "https://montanainvestama.com/api/";
}

String? _validateUrl(String? url) {
  if (url == null || url.isEmpty) {
    return null;
  }

  try {
    final Uri uri = Uri.parse(url);
    if (uri.isAbsolute && uri.host.isNotEmpty) {
      return url.endsWith("/") ? url : "$url/";
    }
  } catch (_) {}

  return null;
}

enum ApiUrl {
  COMPANY_PROFILE("company-profile.php"),
  PROJECTS("projects.php"),
  AUTH("auth.php"),
  CONTACT("contact.php"),
  CITIES("cities.php"),
  EKOSISTEM("ekosistem.php"),
  ARTICLES("articles.php"),
  INVESTOR_DASHBOARD("investor/dashboard.php"),
  NOTIFICATIONS("investor/notifications.php"),
  DEVICE_TOKEN("investor/device_token.php");

  final String path;

  const ApiUrl(this.path);
}
