import "package:mgi_investor/api/endpoint/auth/auth_request.dart";

abstract class RegisterEvent {}

class RegisterIndividualSubmit extends RegisterEvent {
  final RegisterIndividualRequest request;

  RegisterIndividualSubmit({
    required this.request,
  });
}

class RegisterInstitutionSubmit extends RegisterEvent {
  final RegisterInstitutionRequest request;

  RegisterInstitutionSubmit({
    required this.request,
  });
}
