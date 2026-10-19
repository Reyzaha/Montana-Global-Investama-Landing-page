import "package:mgi_investor/model/account.dart";

abstract class SignInState {}

class SignInInitial extends SignInState {}

class SignInSubmitLoading extends SignInState {}

class SignInSubmitSuccess extends SignInState {
  final Account account;
  final String token;

  SignInSubmitSuccess({
    required this.account,
    required this.token,
  });
}

class SignInSubmitFinished extends SignInState {}
