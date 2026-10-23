// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/images.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_bloc.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_event.dart";
import "package:mgi_investor/module/front/company_profile/company_profile_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class CompanyProfilePage extends StatefulWidget {
  const CompanyProfilePage({
    super.key,
  });

  @override
  CompanyProfilePageState createState() => CompanyProfilePageState();
}

class CompanyProfilePageState extends State<CompanyProfilePage>
    with WidgetsBindingObserver {
  bool loading = false;
  CompanyProfile? profile;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
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

  void refresh() {
    context.read<CompanyProfileBloc>().add(CompanyProfileLoad());
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "company_profile".tr(),
    );
  }

  Widget _buildGroupHeader() {
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
              profile?.companyName ?? "PT Montana Global Investama",
              style: TextStyle(
                fontSize: Dimensions.text16,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Center(
            child: Text(
              profile?.taglineId ?? "mgi_tagline".tr(),
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: Dimensions.text12,
                fontWeight: FontWeight.w600,
                color: const Color(0xFFC5A059),
              ),
            ),
          ),
          SizedBox(height: Dimensions.size15),
          Text(
            profile?.aboutGroup?.description ??
                "Montana Group adalah kelompok usaha yang dipimpin oleh PT Montana Global Investama (MGI) selaku grup dan manajer investasi project, yang menaungi tiga pilar usaha operasional teruji di lapangan.",
            style: TextStyle(
              fontSize: Dimensions.text12,
              color: AppColors.onSurface(),
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVisionMission() {
    return Container(
      margin: EdgeInsets.only(top: Dimensions.size15),
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size10),
          smoothness: 1,
          side: const BorderSide(color: Color(0xFF0F2C59)),
        ),
        color: const Color(0xFF0F2C59),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.lightbulb_outline,
                color: const Color(0xFFC5A059),
                size: Dimensions.size20,
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "vision_and_mission".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            "Visi Perusahaan:",
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: FontWeight.bold,
              color: const Color(0xFFC5A059),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            "Menjadi perusahaan pengelolaan dana investasi terpercaya di Indonesia yang menghubungkan modal strategis dengan potensi sektor riil bernilai tinggi secara berkesinambungan.",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: Colors.white,
              height: 1.4,
            ),
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            "Misi Utama:",
            style: TextStyle(
              fontSize: Dimensions.text12,
              fontWeight: FontWeight.bold,
              color: const Color(0xFFC5A059),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            "• Mengalokasikan modal pada aset fisik produktif dengan perlindungan legal dan asuransi.\n"
            "• Membangun kemitraan strategis eksklusif jangka menengah hingga panjang.\n"
            "• Menerapkan prinsip tata kelola yang baik melalui kerangka TARIF.\n"
            "• Mengoptimalkan sinergi ekosistem (MGI, MIU, MSI, Mypurcase) untuk efisiensi biaya terintegrasi.\n"
            "• Memberikan pelaporan berkala transparan demi akuntabilitas pemodal.",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: Colors.white70,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEntitiesList() {
    final List<EcosystemEntity> entities = profile?.entities ?? [];
    if (entities.isEmpty) {
      return const SizedBox();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(height: Dimensions.size25),
        Text(
          "ecosystem_pillars".tr(),
          style: TextStyle(
            fontSize: Dimensions.text16,
            fontWeight: FontWeight.bold,
            color: AppColors.onSurface(),
          ),
        ),
        SizedBox(height: Dimensions.size10),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: entities.length,
          separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
          itemBuilder: (BuildContext context, int index) {
            final e = entities[index];
            return Container(
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
                  Row(
                    children: [
                      Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.size10,
                          vertical: Dimensions.size4,
                        ),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius: BorderRadius.circular(
                              Dimensions.size5,
                            ),
                            smoothness: 1,
                          ),
                          color: const Color(0xFF0F2C59),
                        ),
                        child: Text(
                          e.code ?? "-",
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                      SizedBox(width: Dimensions.size10),
                      Expanded(
                        child: Text(
                          e.name ?? "-",
                          style: TextStyle(
                            fontSize: Dimensions.text13,
                            fontWeight: FontWeight.bold,
                            color: AppColors.onSurface(),
                          ),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: Dimensions.size5),
                  Text(
                    e.slogan ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFFC5A059),
                    ),
                  ),
                  SizedBox(height: Dimensions.size5),
                  Text(
                    e.description ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: AppColors.onSurfaceVariant(),
                      height: 1.4,
                    ),
                  ),
                  if (e.location != null && e.location!.isNotEmpty) ...[
                    SizedBox(height: Dimensions.size5),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          Icons.location_on,
                          size: Dimensions.size15,
                          color: AppColors.onSurfaceVariant(),
                        ),
                        SizedBox(width: Dimensions.size4),
                        Expanded(
                          child: Text(
                            e.location!,
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              color: AppColors.onSurfaceVariant(),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildContactInfo() {
    final contact = profile?.contact;
    if (contact == null) {
      return const SizedBox();
    }

    return Container(
      margin: EdgeInsets.symmetric(vertical: Dimensions.size25),
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
          Row(
            children: [
              Icon(
                Icons.business,
                size: Dimensions.size20,
                color: AppColors.primary(),
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "contact_us".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.onSurface(),
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          Text(
            "Kantor Korporat: ${contact.corporateOffice ?? '-'}",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
              height: 1.4,
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            "Pool & Workshop Sentral: ${contact.operationalOffice ?? '-'}",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
              height: 1.4,
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            "Email: ${contact.email ?? '-'}",
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: FontWeight.w600,
              color: AppColors.primary(),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            "Jam Operasional: ${contact.workHours ?? '-'}",
            style: TextStyle(
              fontSize: Dimensions.text11,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
        ],
      ),
    );
  }

  Widget body() {
    return RefreshIndicator(
      onRefresh: () async => refresh(),
      child: ListView(
        padding: EdgeInsets.all(Dimensions.size15),
        children: [
          _buildGroupHeader(),
          _buildVisionMission(),
          _buildEntitiesList(),
          _buildContactInfo(),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<CompanyProfileBloc, CompanyProfileState>(
      listener: (BuildContext context, CompanyProfileState state) {
        if (state is CompanyProfileLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is CompanyProfileLoadSuccess) {
          setState(() {
            profile = state.profile;
          });
        } else if (state is CompanyProfileLoadFinished) {
          setState(() {
            loading = false;
          });
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
          onRefresh: () async => refresh(),
          statusBuilder: () {
            if (loading && profile == null) {
              return BaseBodyStatus.loading;
            }
            if (profile == null) {
              return BaseBodyStatus.fail;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
