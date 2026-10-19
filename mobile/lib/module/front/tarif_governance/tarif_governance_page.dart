// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:mgi_investor/helper/background.dart";
import "package:smooth_corner/smooth_corner.dart";

class TarifGovernancePage extends StatefulWidget {
  const TarifGovernancePage({
    super.key,
  });

  @override
  TarifGovernancePageState createState() => TarifGovernancePageState();
}

class TarifGovernancePageState extends State<TarifGovernancePage>
    with WidgetsBindingObserver {
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
      name: "governance_tarif".tr(),
    );
  }

  Widget _buildTarifCard({
    required String letter,
    required String title,
    required String indonesianTitle,
    required String desc,
    required IconData icon,
  }) {
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
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: Dimensions.size40,
            height: Dimensions.size40,
            decoration: ShapeDecoration(
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size10),
                smoothness: 1,
              ),
              color: const Color(0xFF0F2C59),
            ),
            child: Center(
              child: Text(
                letter,
                style: TextStyle(
                  fontSize: Dimensions.text20,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFFC5A059),
                ),
              ),
            ),
          ),
          SizedBox(width: Dimensions.size10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: Dimensions.text14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.onSurface(),
                      ),
                    ),
                    SizedBox(width: Dimensions.size5),
                    Text(
                      "($indonesianTitle)",
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        color: const Color(0xFFC5A059),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                SizedBox(height: Dimensions.size5),
                Text(
                  desc,
                  style: TextStyle(
                    fontSize: Dimensions.text11,
                    color: AppColors.onSurfaceVariant(),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget body() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        Container(
          padding: EdgeInsets.all(Dimensions.size20),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size10),
              smoothness: 1,
              side: const BorderSide(color: Color(0xFFC5A059)),
            ),
            color: const Color(0xFF0F2C59),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                "Tata Kelola sebagai Fondasi Kepercayaan",
                style: TextStyle(
                  fontSize: Dimensions.text16,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              SizedBox(height: Dimensions.size10),
              Text(
                "PT Montana Global Investama menerapkan prinsip tata kelola yang baik (Good Corporate Governance), transparan, akuntabel, dan terukur melalui kerangka TARIF dalam setiap proses alokasi modal dan pengelolaan proyek.",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: Colors.white70,
                  height: 1.5,
                ),
              ),
            ],
          ),
        ),
        SizedBox(height: Dimensions.size15),
        _buildTarifCard(
          letter: "T",
          title: "Transparency",
          indonesianTitle: "transparency".tr(),
          desc: "Keterbukaan informasi material dan relevan kepada seluruh pemodal melalui visibilitas alokasi belanja modal (CAPEX) dan laporan operasional berkala.",
          icon: Icons.visibility,
        ),
        SizedBox(height: Dimensions.size10),
        _buildTarifCard(
          letter: "A",
          title: "Accountability",
          indonesianTitle: "accountability".tr(),
          desc: "Kejelasan fungsi, struktur kepemilikan armada, sistem kontrol berkala, dan pertanggungjawaban pengelolaan investasi sektor riil.",
          icon: Icons.assignment_turned_in,
        ),
        SizedBox(height: Dimensions.size10),
        _buildTarifCard(
          letter: "R",
          title: "Responsibility",
          indonesianTitle: "responsibility".tr(),
          desc: "Kepatuhan penuh terhadap hukum, regulasi perpajakan, sertifikasi keselamatan kerja alat berat, dan kepedulian sosial lingkungan proyek.",
          icon: Icons.shield,
        ),
        SizedBox(height: Dimensions.size10),
        _buildTarifCard(
          letter: "I",
          title: "Independency",
          indonesianTitle: "independency".tr(),
          desc: "Pengambilan keputusan investasi secara objektif, rasional, dan profesional bebas dari benturan kepentingan.",
          icon: Icons.balance,
        ),
        SizedBox(height: Dimensions.size10),
        _buildTarifCard(
          letter: "F",
          title: "Fairness",
          indonesianTitle: "fairness".tr(),
          desc: "Perlakuan setara, adil, dan transparan bagi setiap investor dalam pemenuhan hak dividen serta akses dokumen perjanjian kemitraan.",
          icon: Icons.handshake,
        ),
        SizedBox(height: Dimensions.size25),
        Container(
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
                "Komitmen Perlindungan Aset Pemodal",
                style: TextStyle(
                  fontSize: Dimensions.text13,
                  fontWeight: FontWeight.bold,
                  color: AppColors.onSurface(),
                ),
              ),
              SizedBox(height: Dimensions.size5),
              Text(
                "Seluruh armada unit alat berat dilengkapi sistem pelacakan GPS telemetri 24 jam, perlindungan asuransi komprehensif, dan diservis secara rutin di Pool & Workshop Sentral Karanganyar Kebumen.",
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: AppColors.onSurfaceVariant(),
                  height: 1.4,
                ),
              ),
            ],
          ),
        ),
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
