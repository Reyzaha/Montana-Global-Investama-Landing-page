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
  bool privacyPolicyAccepted = false;

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
      if (!privacyPolicyAccepted) {
        BaseOverlays.error(message: "please_accept_privacy_policy".tr());
        return;
      }

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
                  privacyPolicyAccepted: 1,
                  privacyPolicyVersion: "1.0",
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
                  privacyPolicyAccepted: 1,
                  privacyPolicyVersion: "1.0",
                ),
              ),
            );
      }
    }
  }

  void _showPrivacyPolicySheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (BuildContext ctx) {
        return Container(
          height: MediaQuery.of(ctx).size.height * 0.85,
          decoration: BoxDecoration(
            color: AppColors.surface(),
            borderRadius: BorderRadius.vertical(
              top: Radius.circular(Dimensions.size20),
            ),
          ),
          child: Column(
            children: [
              Container(
                padding: EdgeInsets.all(Dimensions.size15),
                decoration: BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: AppColors.outline()),
                  ),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        "privacy_policy_dialog_title".tr(),
                        style: TextStyle(
                          fontSize: Dimensions.text14,
                          fontWeight: FontWeight.bold,
                          color: AppColors.onSurface(),
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.of(ctx).pop(),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.all(Dimensions.size15),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "Kebijakan Privasi PT Montana Global Investama\nVersi 1.0 • Terakhir diperbarui: 22 Oktober 2026\n",
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary(),
                        ),
                      ),
                      Text(
                        "1. Pendahuluan\nPT Montana Global Investama ('Perusahaan', 'Kami') menghargai dan melindungi data pribadi Anda berdasarkan UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi ('UU PDP') dan UU ITE.\n\n"
                        "2. Identitas Pengendali Data\nPT Montana Global Investama\nRoseville Soho & Suite, Serpong, Banten 15321\nEmail: contact@montanainvestama.com | Telp: 0858-2050-0786\n\n"
                        "3. Data Pribadi yang Kami Kumpulkan\nData identitas (nama, NIK, foto KTP), data kontak (email, nomor telepon), data keuangan (nomor rekening), dan data teknis akses.\n\n"
                        "4. Tujuan Pemrosesan\nVerifikasi kepatuhan investor (KYC), administrasi kepemilikan aset, bagi hasil, dan kepatuhan hukum.\n\n"
                        "5. Keamanan & Retensi\nData disimpan secara aman terenkripsi dan tidak diperjualbelikan kepada pihak mana pun.\n\n"
                        "6. Hak Pemilik Data\nAnda berhak mengakses, memperbaiki, dan meminta penghapusan data Anda sesuai ketentuan perundang-undangan.",
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                          color: AppColors.onSurface(),
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Container(
                padding: EdgeInsets.all(Dimensions.size15),
                decoration: BoxDecoration(
                  border: Border(
                    top: BorderSide(color: AppColors.outline()),
                  ),
                ),
                child: SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: () {
                      setState(() {
                        privacyPolicyAccepted = true;
                      });
                      Navigator.of(ctx).pop();
                    },
                    child: Text("privacy_policy_agree".tr()),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
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
          color: isSelected ? AppColors.primary() : Colors.transparent,
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? AppColors.onPrimary() : AppColors.onSurfaceVariant(),
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
                color: AppColors.onSurface(),
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
            SizedBox(height: Dimensions.size15),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: Dimensions.size25,
                  height: Dimensions.size25,
                  child: Checkbox(
                    value: privacyPolicyAccepted,
                    activeColor: AppColors.primary(),
                    onChanged: (bool? val) {
                      setState(() {
                        privacyPolicyAccepted = val ?? false;
                      });
                    },
                  ),
                ),
                SizedBox(width: Dimensions.size10),
                Expanded(
                  child: GestureDetector(
                    onTap: _showPrivacyPolicySheet,
                    child: Text.rich(
                      TextSpan(
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                          color: AppColors.onSurface(),
                        ),
                        children: [
                          TextSpan(text: "i_have_read_and_agree".tr()),
                          const TextSpan(text: " "),
                          TextSpan(
                            text: "privacy_policy".tr(),
                            style: TextStyle(
                              color: AppColors.primary(),
                              fontWeight: FontWeight.bold,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                          const TextSpan(text: " PT Montana Global Investama."),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
            SizedBox(height: Dimensions.size25),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: privacyPolicyAccepted ? submit : null,
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
