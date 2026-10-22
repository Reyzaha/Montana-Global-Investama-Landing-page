// ignore_for_file: use_build_context_synchronously

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/investor/investor_dashboard.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/main_bloc.dart";
import "package:mgi_investor/main_event.dart";
import "package:mgi_investor/main_state.dart";
import "package:mgi_investor/model/account.dart";
import "package:mgi_investor/shared.dart";
import "package:mgi_investor/widget/portal_widgets.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProfilePage extends StatefulWidget {
  const ProfilePage({
    super.key,
  });

  @override
  ProfilePageState createState() => ProfilePageState();
}

class ProfilePageState extends State<ProfilePage> with WidgetsBindingObserver {
  InvestorBankAccount? bankAccount;
  bool biometricEnabled = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    loadBankInfo();
  }

  Future<void> loadBankInfo() async {
    try {
      final Response response = await ApiManager.investorDashboard();
      if (response.statusCode == 200) {
        final Map<String, dynamic> data =
            response.data is Map<String, dynamic>
                ? response.data as Map<String, dynamic>
                : {};
        final InvestorDashboardResponse parsed =
            InvestorDashboardResponse().parse(data);

        if (mounted) {
          setState(() {
            bankAccount = parsed.bankAccount;
          });
        }
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "investor_profile".tr(),
    );
  }

  Widget _buildHeader() {
    final Account? user = Shared.ACCOUNT;
    final String name = user?.displayName() ?? "Investor MGI";

    return Row(
      children: [
        Container(
          width: Dimensions.size80,
          height: Dimensions.size80,
          padding: EdgeInsets.all(Dimensions.size5),
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(color: portalGold),
          ),
          child: CircleAvatar(
            backgroundColor: portalNavy,
            child: Text(
              name.isNotEmpty ? name.substring(0, 1).toUpperCase() : "M",
              style: TextStyle(
                fontSize: Dimensions.text28,
                fontWeight: FontWeight.bold,
                color: portalGold,
              ),
            ),
          ),
        ),
        SizedBox(width: Dimensions.size15),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                (user?.type == AccountType.institution
                        ? "investor_institution"
                        : "private_investor")
                    .tr()
                    .toUpperCase(),
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: portalGold,
                  letterSpacing: 1.2,
                ),
              ),
              SizedBox(height: Dimensions.size4),
              Row(
                children: [
                  Flexible(
                    child: Text(
                      name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: Dimensions.text20,
                        fontWeight: FontWeight.w800,
                        color: portalNavy,
                      ),
                    ),
                  ),
                  SizedBox(width: Dimensions.size5),
                  Icon(
                    Icons.verified,
                    size: Dimensions.size20,
                    color: portalGold,
                  ),
                ],
              ),
              SizedBox(height: Dimensions.size4),
              Text(
                user?.email ?? "-",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: AppColors.onSurfaceVariant(),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildKycCard() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size20),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size20),
          smoothness: 1,
        ),
        color: Colors.green.shade50,
      ),
      child: Row(
        children: [
          Icon(
            Icons.shield_outlined,
            color: Colors.green.shade700,
            size: Dimensions.size25,
          ),
          SizedBox(width: Dimensions.size15),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "kyc_status".tr().toUpperCase(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: Colors.green.shade700,
                    letterSpacing: 1.0,
                  ),
                ),
                SizedBox(height: Dimensions.size4),
                Text(
                  "identity_verified".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text14,
                    fontWeight: FontWeight.w800,
                    color: portalNavy,
                  ),
                ),
              ],
            ),
          ),
          Icon(Icons.check, color: Colors.green.shade700),
        ],
      ),
    );
  }

  Widget _buildBankSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        PortalSectionHeaderSmall(title: "dividend_account".tr()),
        if (bankAccount != null)
          InkWell(
            onTap: showEditBankBottomSheet,
            customBorder: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size25),
              smoothness: 1,
            ),
            child: BankCard(
              bankName: bankAccount?.bankName ?? "-",
              accountNumber: bankAccount?.accountNumber ?? "",
              holder: bankAccount?.accountHolder ?? "-",
            ),
          ),
        SizedBox(height: Dimensions.size10),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            style: OutlinedButton.styleFrom(
              foregroundColor: portalGold,
              side: BorderSide(color: AppColors.outline()),
              padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size15),
                smoothness: 1,
              ),
            ),
            onPressed: showEditBankBottomSheet,
            icon: const Icon(Icons.add),
            label: Text("add_own_account".tr()),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: Dimensions.size5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: TextStyle(
                fontSize: Dimensions.text12,
                fontWeight: FontWeight.w600,
                color: AppColors.onSurface(),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAccountDetails() {
    final Account? user = Shared.ACCOUNT;

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: portalCardDecoration(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "partnership_info".tr(),
            style: TextStyle(
              fontSize: Dimensions.text14,
              fontWeight: FontWeight.w800,
              color: portalNavy,
            ),
          ),
          SizedBox(height: Dimensions.size10),
          _buildInfoRow("phone_number".tr(), user?.phone ?? "-"),
          _buildInfoRow(
            "Kewarganegaraan",
            user?.citizenship ?? "Indonesia (WNI)",
          ),
          if (user?.type == AccountType.institution) ...[
            _buildInfoRow("company_name".tr(), user?.businessName ?? "-"),
            _buildInfoRow("pic_name".tr(), user?.picName ?? "-"),
            _buildInfoRow("pic_position".tr(), user?.picPosition ?? "-"),
            _buildInfoRow("company_address".tr(), user?.companyAddress ?? "-"),
          ] else ...[
            _buildInfoRow(
              "business_field".tr(),
              user?.businessActivity ?? "Wiraswasta",
            ),
          ],
        ],
      ),
    );
  }

  void showEditBankBottomSheet() {
    final TextEditingController tecBank = TextEditingController(
      text: bankAccount?.bankName ?? "",
    );
    final TextEditingController tecNumber = TextEditingController(
      text: bankAccount?.accountNumber ?? "",
    );
    final TextEditingController tecHolder = TextEditingController(
      text: bankAccount?.accountHolder ??
          Shared.ACCOUNT?.displayName() ??
          "",
    );
    final TextEditingController tecBranch = TextEditingController(
      text: bankAccount?.branch ?? "",
    );

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface(),
      shape: SmoothRectangleBorder(
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(Dimensions.size20),
        ),
        smoothness: 1,
      ),
      builder: (bottomSheetContext) {
        return Padding(
          padding: EdgeInsets.only(
            left: Dimensions.size20,
            right: Dimensions.size20,
            top: Dimensions.size20,
            bottom: MediaQuery.of(bottomSheetContext).viewInsets.bottom +
                Dimensions.size20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      "save_bank_account".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text16,
                        fontWeight: FontWeight.bold,
                        color: AppColors.onSurface(),
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(bottomSheetContext),
                      icon: const Icon(Icons.close),
                    ),
                  ],
                ),
                SizedBox(height: Dimensions.size15),
                BaseWidgets.text(
                  controller: tecBank,
                  label: "bank_name".tr(),
                  mandatory: true,
                  readonly: false,
                ),
                SizedBox(height: Dimensions.size15),
                BaseWidgets.text(
                  controller: tecNumber,
                  label: "account_number".tr(),
                  mandatory: true,
                  readonly: false,
                  textInputType: TextInputType.number,
                ),
                SizedBox(height: Dimensions.size15),
                BaseWidgets.text(
                  controller: tecHolder,
                  label: "account_holder".tr(),
                  mandatory: true,
                  readonly: false,
                ),
                SizedBox(height: Dimensions.size15),
                BaseWidgets.text(
                  controller: tecBranch,
                  label: "branch".tr(),
                  mandatory: false,
                  readonly: false,
                ),
                SizedBox(height: Dimensions.size25),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFFC5A059),
                      padding: EdgeInsets.symmetric(
                        vertical: Dimensions.size15,
                      ),
                    ),
                    onPressed: () async {
                      if (tecBank.text.trim().isEmpty ||
                          tecNumber.text.trim().isEmpty ||
                          tecHolder.text.trim().isEmpty) {
                        BaseOverlays.error(
                          message: "this_field_is_required".tr(),
                        );
                        return;
                      }

                      try {
                        final Response res = await ApiManager.saveBankAccount(
                          bankName: tecBank.text.trim(),
                          accountNumber: tecNumber.text.trim(),
                          accountHolder: tecHolder.text.trim(),
                          branch: tecBranch.text.trim(),
                        );

                        if (res.statusCode == 200) {
                          Navigator.pop(bottomSheetContext);
                          BaseOverlays.success(
                            message: "bank_saved_success".tr(),
                          );
                          loadBankInfo();
                        }
                      } catch (_) {
                        BaseOverlays.error(
                          message: "something_wrong_please_try_again".tr(),
                        );
                      }
                    },
                    child: Text(
                      "save".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildPreferencesSection() {
    return BlocBuilder<MainBloc, MainState>(
      builder: (context, state) {
        final ThemeMode currentMode = state.themeMode;
        final String currentLang = context.locale.languageCode;
        final Widget divider = Divider(
          height: 1,
          color: AppColors.outline(),
        );

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            PortalSectionHeaderSmall(title: "security_preferences".tr()),
            Container(
              clipBehavior: Clip.antiAlias,
              decoration: portalCardDecoration(),
              child: Column(
                children: [
                  SettingsTile(
                    icon: Icons.lock_outline,
                    title: "account_security".tr(),
                    subtitle: "account_security_desc".tr(),
                  ),
                  divider,
                  SettingsTile(
                    icon: Icons.face_outlined,
                    title: "biometric".tr(),
                    subtitle: "biometric_desc".tr(),
                    trailing: Switch(
                      value: biometricEnabled,
                      activeTrackColor: portalGold,
                      onChanged: (bool value) {
                        setState(() {
                          biometricEnabled = value;
                        });
                      },
                    ),
                  ),
                  divider,
                  SettingsTile(
                    icon: Icons.language,
                    title: "language".tr(),
                    subtitle: "language_desc".tr(),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          currentLang == "id" ? "Indonesia" : "English",
                          style: TextStyle(
                            fontSize: Dimensions.text11,
                            fontWeight: FontWeight.bold,
                            color: portalGold,
                          ),
                        ),
                        Icon(
                          Icons.chevron_right,
                          color: portalNavy,
                          size: Dimensions.size20,
                        ),
                      ],
                    ),
                    onTap: () {
                      Generals.changeLanguage(
                        context,
                        currentLang == "id" ? "en" : "id",
                      );
                    },
                  ),
                  divider,
                  SettingsTile(
                    icon: Icons.brightness_6_outlined,
                    title: "theme_settings".tr(),
                    subtitle: "theme_settings".tr(),
                    trailing: DropdownButton<ThemeMode>(
                      value: currentMode,
                      underline: const SizedBox(),
                      items: [
                        DropdownMenuItem(
                          value: ThemeMode.system,
                          child: Text("system_mode".tr()),
                        ),
                        DropdownMenuItem(
                          value: ThemeMode.light,
                          child: Text("light_mode".tr()),
                        ),
                        DropdownMenuItem(
                          value: ThemeMode.dark,
                          child: Text("dark_mode".tr()),
                        ),
                      ],
                      onChanged: (ThemeMode? mode) {
                        if (mode != null) {
                          context.read<MainBloc>().add(
                                MainThemeChanged(value: mode.index),
                              );
                        }
                      },
                    ),
                  ),
                  divider,
                  SettingsTile(
                    icon: Icons.notifications_none,
                    title: "notifications".tr(),
                    subtitle: "notifications_desc".tr(),
                    onTap: () => context.push("/portal/notifications"),
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildSignOutButton() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      width: double.infinity,
      child: TextButton.icon(
        style: TextButton.styleFrom(
          foregroundColor: Colors.red.shade700,
          backgroundColor: Colors.red.shade50,
          padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size15),
            smoothness: 1,
          ),
        ),
        onPressed: () {
          BaseDialogs.confirmation(
            title: "sign_out".tr(),
            message: "sign_out_confirm".tr(),
            positiveCallback: () async {
              await Generals.signOut(context);
            },
          );
        },
        icon: const Icon(Icons.logout),
        label: Text(
          "sign_out_portal".tr(),
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget body() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        _buildHeader(),
        _buildKycCard(),
        _buildBankSection(),
        _buildPreferencesSection(),
        _buildAccountDetails(),
        _buildSignOutButton(),
        SizedBox(height: Dimensions.size30),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Background(
      child: BaseScaffold(
        context: context,
        backgroundColor: Colors.transparent,
        appBar: appBar(),
        contentBuilder: body,
      ),
    );
  }
}
