class ContactInquiryRequest {
  final String fullName;
  final String email;
  final String phone;
  final String message;
  final String subject;

  ContactInquiryRequest({
    required this.fullName,
    required this.email,
    required this.phone,
    required this.message,
    this.subject = "Pertanyaan Umum / Konsultasi Investasi",
  });

  Map<String, dynamic> toJson() => {
    "full_name": fullName,
    "email": email,
    "phone": phone,
    "message": message,
    "subject": subject,
  };
}

class ContactInquiryResponse {
  int? inquiryId;
  String? message;

  ContactInquiryResponse parse(Map<String, dynamic> json) {
    inquiryId = json["inquiry_id"];
    message = json["message"];
    return this;
  }
}
