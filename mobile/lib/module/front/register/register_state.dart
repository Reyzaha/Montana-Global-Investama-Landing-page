import "package:mgi_investor/model/account.dart";

abstract class RegisterState {}

class RegisterInitial extends RegisterState {}

class RegisterSubmitLoading extends RegisterState {}

class RegisterSubmitSuccess extends RegisterState {
  final Account account;
  final String message;

  RegisterSubmitSuccess({
    required this.account,
    required this.message,
  });
}

class RegisterSubmitFinished extends RegisterState {}
