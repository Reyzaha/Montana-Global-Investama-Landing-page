// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/formats.dart";
import "package:smooth_corner/smooth_corner.dart";

class RoiCalculatorPage extends StatefulWidget {
  const RoiCalculatorPage({
    super.key,
  });

  @override
  RoiCalculatorPageState createState() => RoiCalculatorPageState();
}

class RoiCalculatorPageState extends State<RoiCalculatorPage>
    with WidgetsBindingObserver {
  double investmentAmount = 1000000000.0; // Rp 1 Miliar
  int tenorMonths = 12;
  double annualReturnRate = 28.62; // 28.62%
  double monthlyYieldRate = 2.38; // 2.38%

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
      name: "roi_calculator".tr(),
    );
  }

  Widget _buildPresetChips() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          "Pilihan Tiket / Paket Kemitraan:",
          style: TextStyle(
            fontSize: Dimensions.text12,
            fontWeight: FontWeight.bold,
            color: AppColors.onSurface(),
          ),
        ),
        SizedBox(height: Dimensions.size10),
        Wrap(
          spacing: Dimensions.size10,
          runSpacing: Dimensions.size10,
          children: [
            _buildPresetButton(
              label: "Rp 500 Juta",
              amount: 500000000.0,
            ),
            _buildPresetButton(
              label: "Rp 1 Miliar",
              amount: 1000000000.0,
            ),
            _buildPresetButton(
              label: "Paket 1 (Rp 5M)",
              amount: 5000000000.0,
            ),
            _buildPresetButton(
              label: "Paket 2 (Rp 10M)",
              amount: 10000000000.0,
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildPresetButton({
    required String label,
    required double amount,
  }) {
    final bool isSelected = investmentAmount == amount;

    return InkWell(
      onTap: () {
        setState(() {
          investmentAmount = amount;
        });
      },
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.symmetric(
          horizontal: Dimensions.size10,
          vertical: Dimensions.size5,
        ),
        decoration: ShapeDecoration(
          shape: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size20),
            smoothness: 1,
            side: BorderSide(
              color: isSelected ? const Color(0xFFC5A059) : AppColors.outline(),
            ),
          ),
          color: isSelected
              ? const Color(0xFF0F2C59)
              : AppColors.surfaceContainerLowest(),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: Dimensions.text11,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            color: isSelected ? Colors.white : AppColors.onSurface(),
          ),
        ),
      ),
    );
  }

  Widget _buildSliderSection() {
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
          Text(
            "investment_amount".tr(),
            style: TextStyle(
              fontSize: Dimensions.text12,
              color: AppColors.onSurfaceVariant(),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            "Rp ${Formats.spell(investmentAmount)}",
            style: TextStyle(
              fontSize: Dimensions.text20,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF0F2C59),
            ),
          ),
          SizedBox(height: Dimensions.size15),
          Slider(
            value: investmentAmount,
            min: 500000000.0,
            max: 10000000000.0,
            divisions: 19,
            activeColor: const Color(0xFFC5A059),
            inactiveColor: AppColors.outline(),
            onChanged: (double val) {
              setState(() {
                investmentAmount = val;
              });
            },
          ),
          SizedBox(height: Dimensions.size10),
          _buildPresetChips(),
        ],
      ),
    );
  }

  Widget _buildResultsCard() {
    final double monthlyDividend = investmentAmount * (monthlyYieldRate / 100.0);
    final double annualReturn = investmentAmount * (annualReturnRate / 100.0);
    final double totalEstimated = investmentAmount + (annualReturn * (tenorMonths / 12.0));

    return Container(
      margin: EdgeInsets.only(top: Dimensions.size15),
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
          Row(
            children: [
              Icon(
                Icons.trending_up,
                color: const Color(0xFFC5A059),
                size: Dimensions.size20,
              ),
              SizedBox(width: Dimensions.size10),
              Text(
                "Proyeksi Hasil Investasi",
                style: TextStyle(
                  fontSize: Dimensions.text14,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size15),
          _buildResultItem(
            label: "estimated_monthly_dividend".tr(),
            value: "Rp ${Formats.spell(monthlyDividend)} / bulan",
            desc: "Estimasi arus kas dividen per bulan ($monthlyYieldRate%)",
            isHighlight: true,
          ),
          Divider(color: Colors.white24, height: Dimensions.size20),
          _buildResultItem(
            label: "estimated_total_return".tr(),
            value: "Rp ${Formats.spell(annualReturn)} / tahun",
            desc: "Proyeksi imbal hasil tahunan ($annualReturnRate%)",
          ),
          Divider(color: Colors.white24, height: Dimensions.size20),
          _buildResultItem(
            label: "Total Proyeksi Modal + Hasil",
            value: "Rp ${Formats.spell(totalEstimated)}",
            desc: "Nilai akumulasi pada akhir tenor $tenorMonths bulan",
          ),
          Divider(color: Colors.white24, height: Dimensions.size20),
          _buildResultItem(
            label: "bep_period".tr(),
            value: "2 – 3 Tahun (24–36 Bulan)",
            desc: "Proyeksi titik impas balik modal berbasis perputaran unit",
          ),
        ],
      ),
    );
  }

  Widget _buildResultItem({
    required String label,
    required String value,
    required String desc,
    bool isHighlight = false,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: Dimensions.text11,
            color: Colors.white70,
          ),
        ),
        SizedBox(height: Dimensions.size3),
        Text(
          value,
          style: TextStyle(
            fontSize: isHighlight ? Dimensions.text16 : Dimensions.text14,
            fontWeight: FontWeight.bold,
            color: isHighlight ? const Color(0xFFC5A059) : Colors.white,
          ),
        ),
        SizedBox(height: Dimensions.size2),
        Text(
          desc,
          style: TextStyle(
            fontSize: Dimensions.text10,
            color: Colors.white60,
          ),
        ),
      ],
    );
  }

  Widget body() {
    return ListView(
      padding: EdgeInsets.all(Dimensions.size15),
      children: [
        _buildSliderSection(),
        _buildResultsCard(),
        SizedBox(height: Dimensions.size15),
        Container(
          padding: EdgeInsets.all(Dimensions.size10),
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.circular(Dimensions.size10),
              smoothness: 1,
              side: BorderSide(
                color: Colors.amber.shade700.withValues(alpha: 0.3),
              ),
            ),
            color: Colors.amber.withValues(alpha: 0.05),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(
                Icons.info_outline,
                size: Dimensions.size15,
                color: Colors.amber.shade800,
              ),
              SizedBox(width: Dimensions.size10),
              Expanded(
                child: Text(
                  "disclaimer_investment_risk".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    color: AppColors.onSurfaceVariant(),
                    height: 1.4,
                  ),
                ),
              ),
            ],
          ),
        ),
        SizedBox(height: Dimensions.size20),
        FilledButton.icon(
          onPressed: () => context.push("/consultation"),
          icon: Icon(Icons.send, size: Dimensions.size15),
          label: Text("send_inquiry".tr()),
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
