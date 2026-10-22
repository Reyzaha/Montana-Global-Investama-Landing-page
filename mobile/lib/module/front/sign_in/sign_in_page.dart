// ignore_for_file: use_build_context_synchronously

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:google_sign_in/google_sign_in.dart";
import "package:loader_overlay/loader_overlay.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/images.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_bloc.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_event.dart";
import "package:mgi_investor/module/front/sign_in/sign_in_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class SignInPage extends StatefulWidget {
  const SignInPage({
    super.key,
  });

  @override
  SignInPageState createState() => SignInPageState();
}

class SignInPageState extends State<SignInPage> with WidgetsBindingObserver {
  final GlobalKey<FormState> formState = GlobalKey<FormState>();

  final TextEditingController tecEmail = TextEditingController();
  final TextEditingController tecPassword = TextEditingController();

  String accountType = "perorangan";

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    tecEmail.dispose();
    tecPassword.dispose();
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void submit() {
    if (formState.currentState?.validate() ?? false) {
      context.read<SignInBloc>().add(
            SignInSubmit(
              email: tecEmail.text.trim(),
              password: tecPassword.text,
              type: accountType,
            ),
          );
    }
  }

  Future<void> signInWithGoogle() async {
    try {
      final GoogleSignIn googleSignIn = GoogleSignIn(
        serverClientId:
            "674380740237-cuia5ftfji23ulm3htn1veb0eed3p7g9.apps.googleusercontent.com",
        scopes: ["email", "profile"],
      );
      final GoogleSignInAccount? account = await googleSignIn.signIn();
      if (account != null) {
        final GoogleSignInAuthentication auth = await account.authentication;
        final String? idToken = auth.idToken ?? auth.accessToken;
        if (idToken != null) {
          context.read<SignInBloc>().add(
                SignInGoogleSubmit(
                  credential: idToken,
                  type: accountType,
                ),
              );
        }
      }
    } catch (e) {
      if (kDebugMode) {
        print("Google Sign In Error: $e");
      }
      final String err = e.toString();
      if (err.contains("10:") || err.contains("ApiException: 10")) {
        BaseOverlays.error(
          message: "google_sign_in_config_error".tr(),
        );
      } else {
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      }
    }
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "sign_in".tr(),
    );
  }

  Widget _buildTypeToggle() {
    return Container(
      padding: EdgeInsets.all(Dimensions.size3),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size25),
          smoothness: 1,
          side: BorderSide(color: AppColors.outline()),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Row(
        children: [
          Expanded(
            child: _buildToggleTab(
              label: "investor_individual".tr(),
              value: "perorangan",
            ),
          ),
          Expanded(
            child: _buildToggleTab(
              label: "investor_institution".tr(),
              value: "perusahaan",
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildToggleTab({
    required String label,
    required String value,
  }) {
    final bool isSelected = accountType == value;

    return InkWell(
      onTap: () {
        setState(() {
          accountType = value;
        });
      },
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.symmetric(
          vertical: Dimensions.size10,
        ),
        decoration: ShapeDecoration(
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size20),
            smoothness: 1,
          ),
          color: isSelected
              ? const Color(0xFF0F2C59)
              : Colors.transparent,
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? Colors.white : AppColors.onSurfaceVariant(),
            ),
          ),
        ),
      ),
    );
  }

  Widget body() {
    return SingleChildScrollView(
      padding: EdgeInsets.all(Dimensions.size20),
      child: Form(
        key: formState,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Image.asset(
                Images.logo(),
                height: Dimensions.size40,
              ),
            ),
            SizedBox(height: Dimensions.size15),
            Center(
              child: Text(
                "PT Montana Global Investama",
                style: TextStyle(
                  fontSize: Dimensions.text16,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F2C59),
                ),
              ),
            ),
            SizedBox(height: Dimensions.size5),
            Center(
              child: Text(
                "sign_in_with_your_account".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text12,
                  color: AppColors.onSurfaceVariant(),
                ),
              ),
            ),
            SizedBox(height: Dimensions.size25),
            _buildTypeToggle(),
            SizedBox(height: Dimensions.size20),
            BaseWidgets.text(
              controller: tecEmail,
              label: "email".tr(),
              mandatory: true,
              readonly: false,
              textInputType: TextInputType.emailAddress,
              validator: (String? value) {
                if (value == null || value.trim().isEmpty) {
                  return "this_field_is_required".tr();
                }
                if (!value.contains("@")) {
                  return "email_format_is_invalid".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              controller: tecPassword,
              label: "password".tr(),
              mandatory: true,
              readonly: false,
              obscureText: true,
              validator: (String? value) {
                if (value == null || value.isEmpty) {
                  return "this_field_is_required".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size25),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: submit,
                child: Text("sign_in".tr()),
              ),
            ),
            SizedBox(height: Dimensions.size15),
            Row(
              children: [
                Expanded(child: Divider(color: AppColors.outline())),
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: Dimensions.size10),
                  child: Text(
                    "or_sign_in_with".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                ),
                Expanded(child: Divider(color: AppColors.outline())),
              ],
            ),
            SizedBox(height: Dimensions.size15),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: signInWithGoogle,
                icon: const Icon(Icons.g_mobiledata, size: 28),
                label: Text("sign_in_with_google".tr()),
              ),
            ),
            SizedBox(height: Dimensions.size30),
            Center(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    "dont_have_account".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                  SizedBox(width: Dimensions.size5),
                  GestureDetector(
                    onTap: () => context.push("/register"),
                    child: Text(
                      "register_now".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text12,
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFFC5A059),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<SignInBloc, SignInState>(
      listener: (BuildContext context, SignInState state) async {
        if (state is SignInSubmitLoading) {
          context.loaderOverlay.show();
        } else if (state is SignInSubmitFinished) {
          context.loaderOverlay.hide();
        } else if (state is SignInSubmitSuccess) {
          await BaseOverlays.success(
            message: "Selamat datang kembali, ${state.account.displayName()}",
          );
          context.go("/dashboard");
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
        ),
      ),
    );
  }
}
