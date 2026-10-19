enum AccountType {
  individual,
  institution,
}

class Account {
  int? id;
  AccountType type = AccountType.individual;
  String? email;
  String? fullName;
  String? avatarUrl;
  String? phone;
  String? businessActivity;
  String? citizenship;
  String? status;
  String? authProvider;

  // Institution / Perusahaan fields
  String? businessName;
  String? legalEntity;
  String? companyAddress;
  String? picName;
  String? picPosition;
  String? annualTurnover;

  Account parse(Map<String, dynamic> json) {
    id = json["id"] is int ? json["id"] as int : int.tryParse(json["id"]?.toString() ?? "");
    String rawType = (json["type"] ?? json["account_type"] ?? json["accountType"] ?? "perorangan").toString().toLowerCase();
    type = (rawType == "perusahaan" || rawType == "institution")
        ? AccountType.institution
        : AccountType.individual;
    email = json["email"]?.toString();
    fullName = json["fullName"]?.toString() ?? json["full_name"]?.toString();
    avatarUrl = json["avatarUrl"]?.toString() ?? json["avatar_url"]?.toString();
    phone = json["phone"]?.toString() ?? json["company_phone"]?.toString();
    businessActivity = json["businessActivity"]?.toString() ?? json["business_activity"]?.toString();
    citizenship = json["citizenship"]?.toString();
    status = json["status"]?.toString();
    authProvider = json["authProvider"]?.toString() ?? json["auth_provider"]?.toString();

    businessName = json["businessName"]?.toString() ?? json["business_name"]?.toString();
    legalEntity = json["legalEntity"]?.toString() ?? json["legal_entity"]?.toString();
    companyAddress = json["companyAddress"]?.toString() ?? json["company_address"]?.toString();
    picName = json["picName"]?.toString() ?? json["pic_name"]?.toString();
    picPosition = json["picPosition"]?.toString() ?? json["pic_position"]?.toString();
    annualTurnover = json["annualTurnover"]?.toString() ?? json["annual_turnover"]?.toString();

    return this;
  }

  String displayName() {
    if (type == AccountType.institution && (businessName?.isNotEmpty ?? false)) {
      return businessName!;
    }
    if (fullName?.isNotEmpty ?? false) {
      return fullName!;
    }
    if (email?.isNotEmpty ?? false) {
      return email!.split("@")[0];
    }
    return "Investor MGI";
  }
}
