class SignInRequest {
  final String email;
  final String password;
  final String type;

  SignInRequest({
    required this.email,
    required this.password,
    this.type = "perorangan",
  });

  Map<String, dynamic> toJson() => {
    "email": email,
    "password": password,
    "type": type,
  };
}

class RegisterIndividualRequest {
  final String email;
  final String password;
  final String fullName;
  final String citizenship;
  final String phone;
  final String businessActivity;
  final int privacyPolicyAccepted;
  final String privacyPolicyVersion;

  RegisterIndividualRequest({
    required this.email,
    required this.password,
    required this.fullName,
    this.citizenship = "Indonesia (WNI)",
    this.phone = "",
    this.businessActivity = "",
    this.privacyPolicyAccepted = 1,
    this.privacyPolicyVersion = "1.0",
  });

  Map<String, dynamic> toJson() => {
    "type": "perorangan",
    "account_type": "perorangan",
    "email": email,
    "password": password,
    "full_name": fullName,
    "citizenship": citizenship,
    "phone": phone,
    "business_activity": businessActivity,
    "privacy_policy_accepted": privacyPolicyAccepted,
    "privacy_policy_version": privacyPolicyVersion,
  };
}

class RegisterInstitutionRequest {
  final String email;
  final String password;
  final String businessName;
  final String picName;
  final String legalEntity;
  final String companyAddress;
  final String picPosition;
  final String companyPhone;
  final String annualTurnover;
  final String businessActivity;
  final int privacyPolicyAccepted;
  final String privacyPolicyVersion;

  RegisterInstitutionRequest({
    required this.email,
    required this.password,
    required this.businessName,
    required this.picName,
    this.legalEntity = "Perseroan Terbatas (PT)",
    this.companyAddress = "",
    this.picPosition = "Direktur Utama",
    this.companyPhone = "",
    this.annualTurnover = "Rp10 Miliar – Rp50 Miliar",
    this.businessActivity = "Investasi Sektor Riil",
    this.privacyPolicyAccepted = 1,
    this.privacyPolicyVersion = "1.0",
  });

  Map<String, dynamic> toJson() => {
    "type": "perusahaan",
    "account_type": "perusahaan",
    "email": email,
    "password": password,
    "business_name": businessName,
    "legal_entity": legalEntity,
    "company_address": companyAddress,
    "pic_name": picName,
    "pic_position": picPosition,
    "company_phone": companyPhone,
    "annual_turnover": annualTurnover,
    "business_activity": businessActivity,
    "privacy_policy_accepted": privacyPolicyAccepted,
    "privacy_policy_version": privacyPolicyVersion,
  };
}

class GoogleLoginRequest {
  final String credential;
  final String type;

  GoogleLoginRequest({
    required this.credential,
    this.type = "perorangan",
  });

  Map<String, dynamic> toJson() => {
    "credential": credential,
    "type": type,
    "account_type": type,
  };
}
