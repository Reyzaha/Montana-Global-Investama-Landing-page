abstract class SignInEvent {}

class SignInSubmit extends SignInEvent {
  final String email;
  final String password;
  final String type;

  SignInSubmit({
    required this.email,
    required this.password,
    this.type = "perorangan",
  });
}

class SignInGoogleSubmit extends SignInEvent {
  final String credential;
  final String type;

  SignInGoogleSubmit({
    required this.credential,
    this.type = "perorangan",
  });
}
