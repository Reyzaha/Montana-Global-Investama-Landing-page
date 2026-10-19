// ignore_for_file: use_build_context_synchronously

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/main_bloc.dart";
import "package:mgi_investor/main_event.dart";
import "package:mgi_investor/main_state.dart";
import "package:mgi_investor/model/account.dart";
import "package:mgi_investor/shared.dart";
import "package:smooth_corner/smooth_corner.dart";

class ProfilePage extends StatefulWidget {
  const ProfilePage({
    super.key,
  });

  @override
  ProfilePageState createState() => ProfilePageState();
}

class ProfilePageState extends State<ProfilePage> with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
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
      name: "profile".tr(),
    );
  }

  Widget _buildAccountCard() {
    final Account? user = Shared.ACCOUNT;

    return Container(
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: BorderSide(color: AppColors.outline()),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Column(
        children: [
          CircleAvatar(
            radius: Dimensions.size35,
            backgroundColor: const Color(0xFF0F2C59),
            child: Text(
              (user?.displayName().isNotEmpty ?? false)
                  ? user!.displayName().substring(0, 1).toUpperCase()
                  : "M",
              style: TextStyle(
                fontSize: Dimensions.text28,
                fontWeight: FontWeight.bold,
                color: const Color(0xFFC5A059),
              ),
            ),
          ),
          SizedBox(height: Dimensions.size15),
          Text(
            user?.displayName() ?? "Investor MGI",
            style: TextStyle(
              fontSize: Dimensions.text16,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            user?.email ?? "-",
            style: TextStyle(
              fontSize: Dimensions.text12,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          Container(
            padding: EdgeInsets.symmetric(
              horizontal: Dimensions.size10,
              vertical: Dimensions.size5,
            ),
            decoration: ShapeDecoration(
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size20),
                smoothness: 1,
              ),
              color: const Color(0xFF0F2C59),
            ),
            child: Text(
              user?.type == AccountType.institution
                  ? "investor_institution".tr()
                  : "investor_individual".tr(),
              style: TextStyle(
                fontSize: Dimensions.text10,
                fontWeight: FontWeight.bold,
                color: Colors.white,
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
      margin: EdgeInsets.only(top: Dimensions.size15),
      padding: EdgeInsets.all(Dimensions.size15),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: BorderSide(color: AppColors.outline()),
        ),
        color: AppColors.surfaceContainerLowest(),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            "Informasi Kemitraan",
            style: TextStyle(
              fontSize: Dimensions.text14,
              fontWeight: FontWeight.bold,
              color: AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size10),
          _buildInfoRow(
            "phone_number".tr(),
            user?.phone ?? "-",
          ),
          _buildInfoRow(
            "Kewarganegaraan",
            user?.citizenship ?? "Indonesia (WNI)",
          ),
          if (user?.type == AccountType.institution) ...[
            _buildInfoRow(
              "company_name".tr(),
              user?.businessName ?? "-",
            ),
            _buildInfoRow(
              "pic_name".tr(),
              user?.picName ?? "-",
            ),
            _buildInfoRow(
              "pic_position".tr(),
              user?.picPosition ?? "-",
            ),
            _buildInfoRow(
              "company_address".tr(),
              user?.companyAddress ?? "-",
            ),
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

  Widget _buildPreferencesSection() {
    return BlocBuilder<MainBloc, MainState>(
      builder: (context, state) {
        final ThemeMode currentMode = state.themeMode;

        final String currentLang = context.locale.languageCode;

        return Container(
          margin: EdgeInsets.only(top: Dimensions.size15),
          padding: EdgeInsets.all(Dimensions.size15),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size10),
              smoothness: 1,
              side: BorderSide(color: AppColors.outline()),
            ),
            color: AppColors.surfaceContainerLowest(),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                "Pengaturan Aplikasi",
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.onSurface(),
                ),
              ),
              SizedBox(height: Dimensions.size15),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    "theme_settings".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      color: AppColors.onSurface(),
                    ),
                  ),
                  DropdownButton<ThemeMode>(
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
                ],
              ),
              Divider(color: AppColors.outline()),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    "language_settings".tr(),
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      color: AppColors.onSurface(),
                    ),
                  ),
                  DropdownButton<String>(
                    value: currentLang,
                    underline: const SizedBox(),
                    items: const [
                      DropdownMenuItem(
                        value: "id",
                        child: Text("Bahasa Indonesia"),
                      ),
                      DropdownMenuItem(
                        value: "en",
                        child: Text("English"),
                      ),
                    ],
                    onChanged: (String? lang) {
                      if (lang != null) {
                        Generals.changeLanguage(context, lang);
                      }
                    },
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSignOutButton() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size25),
      width: double.infinity,
      child: OutlinedButton.icon(
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.red.shade700,
          side: BorderSide(color: Colors.red.shade700),
          padding: EdgeInsets.symmetric(vertical: Dimensions.size15),
        ),
        onPressed: () {
          BaseDialogs.confirmation(
            title: "Keluar Akun",
            message: "sign_out_confirm".tr(),
            positiveCallback: () async {
              await Generals.signOut(context);
            },
          );
        },
        icon: const Icon(Icons.logout),
        label: Text("sign_out".tr()),
      ),
    );
  }

  Widget body() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        _buildAccountCard(),
        _buildAccountDetails(),
        _buildPreferencesSection(),
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
