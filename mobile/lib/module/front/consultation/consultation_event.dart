import "package:mgi_investor/api/endpoint/contact/contact_request.dart";

abstract class ConsultationEvent {}

class ConsultationSubmit extends ConsultationEvent {
  final ContactInquiryRequest request;

  ConsultationSubmit({
    required this.request,
  });
}
