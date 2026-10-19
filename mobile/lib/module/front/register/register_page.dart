// ignore_for_file: use_build_context_synchronously

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:loader_overlay/loader_overlay.dart";
import "package:mgi_investor/api/endpoint/auth/auth_request.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/module/front/register/register_bloc.dart";
import "package:mgi_investor/module/front/register/register_event.dart";
import "package:mgi_investor/module/front/register/register_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class RegisterPage extends StatefulWidget {
  const RegisterPage({
    super.key,
  });

  @override
  RegisterPageState createState() => RegisterPageState();
}

class RegisterPageState extends State<RegisterPage> with WidgetsBindingObserver {
  final GlobalKey<FormState> formState = GlobalKey<FormState>();

  String investorType = "perorangan";

  // Individual controllers
  final TextEditingController tecFullName = TextEditingController();
  final TextEditingController tecEmail = TextEditingController();
  final TextEditingController tecPhone = TextEditingController();
  final TextEditingController tecBusinessActivity = TextEditingController();
  final TextEditingController tecPassword = TextEditingController();
  final TextEditingController tecConfirmPassword = TextEditingController();

  // Institution controllers
  final TextEditingController tecBusinessName = TextEditingController();
  final TextEditingController tecPicName = TextEditingController();
  final TextEditingController tecPicPosition = TextEditingController(text: "Direktur Utama");
  final TextEditingController tecCompanyAddress = TextEditingController();
  final TextEditingController tecCompanyPhone = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    tecFullName.dispose();
    tecEmail.dispose();
    tecPhone.dispose();
    tecBusinessActivity.dispose();
    tecPassword.dispose();
    tecConfirmPassword.dispose();
    tecBusinessName.dispose();
    tecPicName.dispose();
    tecPicPosition.dispose();
    tecCompanyAddress.dispose();
    tecCompanyPhone.dispose();
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void submit() {
    if (formState.currentState?.validate() ?? false) {
      if (tecPassword.text != tecConfirmPassword.text) {
        BaseOverlays.error(message: "password_not_match".tr());
        return;
      }

      if (investorType == "perorangan") {
        context.read<RegisterBloc>().add(
              RegisterIndividualSubmit(
                request: RegisterIndividualRequest(
                  email: tecEmail.text.trim(),
                  password: tecPassword.text,
                  fullName: tecFullName.text.trim(),
                  phone: tecPhone.text.trim(),
                  businessActivity: tecBusinessActivity.text.trim(),
                ),
              ),
            );
      } else {
        context.read<RegisterBloc>().add(
              RegisterInstitutionSubmit(
                request: RegisterInstitutionRequest(
                  email: tecEmail.text.trim(),
                  password: tecPassword.text,
                  businessName: tecBusinessName.text.trim(),
                  picName: tecPicName.text.trim(),
                  picPosition: tecPicPosition.text.trim(),
                  companyAddress: tecCompanyAddress.text.trim(),
                  companyPhone: tecCompanyPhone.text.trim(),
                ),
              ),
            );
      }
    }
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "sign_up".tr(),
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
    final bool isSelected = investorType == value;

    return InkWell(
      onTap: () {
        setState(() {
          investorType = value;
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
          color: isSelected ? const Color(0xFF0F2C59) : Colors.transparent,
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

  Widget _buildIndividualFields() {
    return Column(
      children: [
        BaseWidgets.text(
          controller: tecFullName,
          label: "full_name".tr(),
          mandatory: true,
          readonly: false,
          validator: (String? value) {
            if (value == null || value.trim().isEmpty) {
              return "this_field_is_required".tr();
            }
            return null;
          },
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecPhone,
          label: "phone_number".tr(),
          mandatory: true,
          readonly: false,
          textInputType: TextInputType.phone,
          validator: (String? value) {
            if (value == null || value.trim().isEmpty) {
              return "this_field_is_required".tr();
            }
            return null;
          },
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecBusinessActivity,
          label: "business_field".tr(),
          mandatory: false,
          readonly: false,
        ),
      ],
    );
  }

  Widget _buildInstitutionFields() {
    return Column(
      children: [
        BaseWidgets.text(
          controller: tecBusinessName,
          label: "company_name".tr(),
          mandatory: true,
          readonly: false,
          validator: (String? value) {
            if (value == null || value.trim().isEmpty) {
              return "this_field_is_required".tr();
            }
            return null;
          },
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecPicName,
          label: "pic_name".tr(),
          mandatory: true,
          readonly: false,
          validator: (String? value) {
            if (value == null || value.trim().isEmpty) {
              return "this_field_is_required".tr();
            }
            return null;
          },
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecPicPosition,
          label: "pic_position".tr(),
          mandatory: true,
          readonly: false,
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecCompanyPhone,
          label: "Telepon Kantor / PIC",
          mandatory: true,
          readonly: false,
          textInputType: TextInputType.phone,
          validator: (String? value) {
            if (value == null || value.trim().isEmpty) {
              return "this_field_is_required".tr();
            }
            return null;
          },
        ),
        SizedBox(height: Dimensions.size15),
        BaseWidgets.text(
          controller: tecCompanyAddress,
          label: "company_address".tr(),
          mandatory: true,
          readonly: false,
          maxLines: 2,
        ),
      ],
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
            Text(
              "Daftar Akun Kemitraan Investor",
              style: TextStyle(
                fontSize: Dimensions.text16,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF0F2C59),
              ),
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              "Bergabung bersama pemodal strategis lain untuk mengalokasikan dana pada unit alat berat produktif.",
              style: TextStyle(
                fontSize: Dimensions.text12,
                color: AppColors.onSurfaceVariant(),
                height: 1.4,
              ),
            ),
            SizedBox(height: Dimensions.size20),
            _buildTypeToggle(),
            SizedBox(height: Dimensions.size20),
            if (investorType == "perorangan")
              _buildIndividualFields()
            else
              _buildInstitutionFields(),
            SizedBox(height: Dimensions.size15),
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
                if (value == null || value.length < 6) {
                  return "Minimal kata sandi 6 karakter";
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              controller: tecConfirmPassword,
              label: "confirm_password".tr(),
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
                child: Text("register_now".tr()),
              ),
            ),
            SizedBox(height: Dimensions.size25),
            Center(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    "already_have_account".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                  SizedBox(width: Dimensions.size5),
                  GestureDetector(
                    onTap: () => context.push("/sign-in"),
                    child: Text(
                      "sign_in".tr(),
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
    return BlocListener<RegisterBloc, RegisterState>(
      listener: (BuildContext context, RegisterState state) async {
        if (state is RegisterSubmitLoading) {
          context.loaderOverlay.show();
        } else if (state is RegisterSubmitFinished) {
          context.loaderOverlay.hide();
        } else if (state is RegisterSubmitSuccess) {
          await BaseOverlays.success(
            message: "Pendaftaran akun investor berhasil diverifikasi!",
          );
          context.go("/portal");
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
