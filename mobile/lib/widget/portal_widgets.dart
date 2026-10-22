// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:smooth_corner/smooth_corner.dart";

const Color portalNavy = Color(0xFF0F2C59);
const Color portalGold = Color(0xFFC5A059);
const Color portalGoldSoft = Color(0xFFF5EEDC);

ShapeDecoration portalCardDecoration({
  Color? color,
  double? radius,
}) {
  return ShapeDecoration(
    shape: SmoothRectangleBorder(
      borderRadius: BorderRadius.circular(radius ?? Dimensions.size20),
      smoothness: 1,
      side: BorderSide(color: AppColors.outline()),
    ),
    color: color ?? AppColors.surfaceContainerLowest(),
  );
}

class PortalSectionHeaderSmall extends StatelessWidget {
  final String title;

  const PortalSectionHeaderSmall({
    required this.title,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        top: Dimensions.size25,
        bottom: Dimensions.size10,
      ),
      child: Text(
        title.toUpperCase(),
        style: TextStyle(
          fontSize: Dimensions.text10,
          fontWeight: FontWeight.bold,
          color: AppColors.onSurfaceVariant(),
          letterSpacing: 1.2,
        ),
      ),
    );
  }
}

class PortalSectionHeader extends StatelessWidget {
  final String eyebrow;
  final String title;
  final Widget? trailing;

  const PortalSectionHeader({
    required this.eyebrow,
    required this.title,
    this.trailing,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        top: Dimensions.size25,
        bottom: Dimensions.size10,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  eyebrow.toUpperCase(),
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: portalGold,
                    letterSpacing: 1.2,
                  ),
                ),
                SizedBox(height: Dimensions.size4),
                Text(
                  title,
                  style: TextStyle(
                    fontSize: Dimensions.text20,
                    fontWeight: FontWeight.w800,
                    color: portalNavy,
                  ),
                ),
              ],
            ),
          ),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}

class PortfolioHeroCard extends StatelessWidget {
  final String name;
  final String initial;
  final String totalCommitted;
  final String dividendsReceived;
  final int activeCount;
  final String? accountType;
  final String? investorCode;
  final String? roiTrend;

  const PortfolioHeroCard({
    required this.name,
    required this.initial,
    required this.totalCommitted,
    required this.dividendsReceived,
    required this.activeCount,
    this.accountType,
    this.investorCode,
    this.roiTrend,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      clipBehavior: Clip.antiAlias,
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size25),
          smoothness: 1,
        ),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFF16397A),
            portalNavy,
            Color(0xFF0A1F42),
          ],
        ),
        shadows: [
          BoxShadow(
            color: portalNavy.withValues(alpha: 0.25),
            blurRadius: 24,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            right: -60,
            top: -60,
            child: IgnorePointer(
              child: Container(
                width: 240,
                height: 240,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.05),
                    width: 36,
                  ),
                ),
              ),
            ),
          ),
          Positioned(
            right: -20,
            top: -20,
            child: IgnorePointer(
              child: Container(
                width: 160,
                height: 160,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.04),
                    width: 22,
                  ),
                ),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          (accountType ?? "private_investor".tr()).toUpperCase(),
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.bold,
                            color: portalGold,
                            letterSpacing: 1.2,
                          ),
                        ),
                        SizedBox(height: Dimensions.size5),
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
                                  color: Colors.white,
                                ),
                              ),
                            ),
                            SizedBox(width: Dimensions.size5),
                            Icon(
                              Icons.check_circle,
                              size: Dimensions.size20,
                              color: portalGold,
                            ),
                          ],
                        ),
                        if (investorCode != null && investorCode!.isNotEmpty) ...[
                          SizedBox(height: Dimensions.size4),
                          Text(
                            investorCode!,
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.w500,
                              color: Colors.white54,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  Container(
                    width: Dimensions.size50,
                    height: Dimensions.size50,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withValues(alpha: 0.08),
                      border: Border.all(
                        color: portalGold,
                        width: 1.5,
                      ),
                    ),
                    child: Text(
                      initial,
                      style: TextStyle(
                        fontSize: Dimensions.text14,
                        fontWeight: FontWeight.bold,
                        color: portalGold,
                      ),
                    ),
                  ),
                ],
              ),
              SizedBox(height: Dimensions.size25),
              Text(
                "total_commitment".tr().toUpperCase(),
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: Colors.white60,
                  letterSpacing: 1.2,
                ),
              ),
              SizedBox(height: Dimensions.size5),
              FittedBox(
                fit: BoxFit.scaleDown,
                alignment: Alignment.centerLeft,
                child: Text(
                  totalCommitted,
                  style: TextStyle(
                    fontSize: Dimensions.text28,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                  ),
                ),
              ),
              if (roiTrend != null && roiTrend!.isNotEmpty) ...[
                SizedBox(height: Dimensions.size5),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.trending_up,
                      size: Dimensions.size15,
                      color: const Color(0xFF5EEAD4),
                    ),
                    SizedBox(width: Dimensions.size4),
                    Text(
                      roiTrend!,
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF5EEAD4),
                      ),
                    ),
                  ],
                ),
              ],
              SizedBox(height: Dimensions.size15),
              Divider(color: Colors.white.withValues(alpha: 0.15), height: 1),
              SizedBox(height: Dimensions.size15),
              Row(
                children: [
                  Expanded(
                    child: _stat(
                      label: "dividends_received".tr(),
                      value: dividendsReceived,
                    ),
                  ),
                  Container(
                    width: 1,
                    height: Dimensions.size30,
                    color: Colors.white.withValues(alpha: 0.15),
                  ),
                  SizedBox(width: Dimensions.size15),
                  Expanded(
                    child: _stat(
                      label: "active_projects_label".tr(),
                      value: "$activeCount ${"projects_unit".tr()}",
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _stat({
    required String label,
    required String value,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label.toUpperCase(),
          style: TextStyle(
            fontSize: Dimensions.text10,
            fontWeight: FontWeight.bold,
            color: Colors.white60,
            letterSpacing: 1.0,
          ),
        ),
        SizedBox(height: Dimensions.size4),
        Text(
          value,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontSize: Dimensions.text16,
            fontWeight: FontWeight.w800,
            color: Colors.white,
          ),
        ),
      ],
    );
  }
}

class DividendDateCard extends StatelessWidget {
  final DateTime? date;
  final String rawDate;
  final String title;
  final String amount;
  final String status;
  final VoidCallback? onTap;

  const DividendDateCard({
    required this.date,
    required this.rawDate,
    required this.title,
    required this.amount,
    required this.status,
    this.onTap,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final String day = date != null ? "${date!.day}".padLeft(2, "0") : "28";
    final String month =
        date != null ? DateFormat("MMM").format(date!).toUpperCase() : "JUN";
    final String year = date != null ? "${date!.year}" : "2025";

    return InkWell(
      onTap: onTap,
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.all(Dimensions.size15),
        decoration: portalCardDecoration(),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.symmetric(
                horizontal: Dimensions.size10,
                vertical: Dimensions.size10,
              ),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size15),
                  smoothness: 1,
                ),
                color: portalGoldSoft,
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    day,
                    style: TextStyle(
                      fontSize: Dimensions.text22,
                      fontWeight: FontWeight.w900,
                      color: portalNavy,
                    ),
                  ),
                  SizedBox(width: Dimensions.size5),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        month,
                        style: TextStyle(
                          fontSize: Dimensions.text9,
                          fontWeight: FontWeight.w800,
                          color: portalGold,
                          height: 1.1,
                        ),
                      ),
                      Text(
                        year,
                        style: TextStyle(
                          fontSize: Dimensions.text9,
                          fontWeight: FontWeight.w600,
                          color: portalGold,
                          height: 1.1,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            SizedBox(width: Dimensions.size15),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    "${"estimated_amount".tr()} $amount",
                    style: TextStyle(
                      fontSize: Dimensions.text14,
                      fontWeight: FontWeight.w800,
                      color: portalNavy,
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Row(
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Color(0xFF10B981),
                        ),
                      ),
                      SizedBox(width: Dimensions.size5),
                      Flexible(
                        child: Text(
                          status,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            color: AppColors.onSurfaceVariant(),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Icon(
              Icons.chevron_right,
              color: portalNavy,
              size: Dimensions.size25,
            ),
          ],
        ),
      ),
    );
  }
}

class TimelineDots extends StatelessWidget {
  final List<String> labels;

  const TimelineDots({
    required this.labels,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        top: Dimensions.size15,
        left: Dimensions.size10,
        right: Dimensions.size10,
      ),
      child: Column(
        children: [
          SizedBox(
            height: 12,
            child: Row(
              children: List.generate(labels.length * 2 - 1, (int i) {
                if (i.isOdd) {
                  return Expanded(
                    child: Container(
                      height: 1.5,
                      color: AppColors.outline(),
                    ),
                  );
                }
                final bool active = i == 0;
                return Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: active ? portalGold : AppColors.surface(),
                    border: Border.all(
                      color: active ? portalGold : AppColors.outline(),
                      width: 1.5,
                    ),
                  ),
                );
              }),
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: labels
                .map(
                  (String l) => Text(
                    l,
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                )
                .toList(),
          ),
        ],
      ),
    );
  }
}

class DocumentTile extends StatelessWidget {
  final String title;
  final String meta;
  final IconData actionIcon;
  final VoidCallback? onTap;

  const DocumentTile({
    required this.title,
    required this.meta,
    this.actionIcon = Icons.visibility_outlined,
    this.onTap,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        smoothness: 1,
      ),
      child: Ink(
        padding: EdgeInsets.all(Dimensions.size15),
        decoration: portalCardDecoration(),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.all(Dimensions.size10),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                ),
                color: portalGoldSoft,
              ),
              child: Icon(
                Icons.description_outlined,
                color: portalGold,
                size: Dimensions.size20,
              ),
            ),
            SizedBox(width: Dimensions.size15),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      fontWeight: FontWeight.w800,
                      color: portalNavy,
                    ),
                  ),
                  SizedBox(height: Dimensions.size4),
                  Text(
                    meta,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              actionIcon,
              color: portalGold,
              size: Dimensions.size20,
            ),
          ],
        ),
      ),
    );
  }
}

class FieldUpdateCard extends StatelessWidget {
  final String? imageUrl;
  final String dateLabel;
  final String location;
  final String title;
  final String description;
  final int progress;
  final VoidCallback? onTap;

  const FieldUpdateCard({
    required this.imageUrl,
    required this.dateLabel,
    required this.location,
    required this.title,
    required this.description,
    required this.progress,
    this.onTap,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        smoothness: 1,
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(Dimensions.size20),
        child: Ink(
          decoration: portalCardDecoration(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SizedBox(
                height: Dimensions.size100 + Dimensions.size50,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (imageUrl != null && imageUrl!.isNotEmpty)
                      Image.network(
                        imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return _placeholder();
                        },
                      )
                    else
                      _placeholder(),
                    if (dateLabel.isNotEmpty)
                      Positioned(
                        left: Dimensions.size10,
                        top: Dimensions.size10,
                        child: Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: Dimensions.size10,
                            vertical: Dimensions.size4,
                          ),
                          decoration: ShapeDecoration(
                            shape: SmoothRectangleBorder(
                              borderRadius:
                                  BorderRadius.circular(Dimensions.size10),
                              smoothness: 1,
                            ),
                            color: Colors.black.withValues(alpha: 0.65),
                          ),
                          child: Text(
                            dateLabel.toUpperCase(),
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.all(Dimensions.size15),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.location_on_outlined,
                          size: Dimensions.size15,
                          color: portalGold,
                        ),
                        SizedBox(width: Dimensions.size4),
                        Expanded(
                          child: Text(
                            location,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: Dimensions.text11,
                              fontWeight: FontWeight.bold,
                              color: portalGold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: Dimensions.size10),
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: Dimensions.text14,
                        fontWeight: FontWeight.w800,
                        color: portalNavy,
                      ),
                    ),
                    SizedBox(height: Dimensions.size4),
                    Text(
                      description,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        color: AppColors.onSurfaceVariant(),
                        height: 1.4,
                      ),
                    ),
                    if (progress > 0) ...[
                      SizedBox(height: Dimensions.size10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            "project_progress".tr(),
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              color: AppColors.onSurfaceVariant(),
                            ),
                          ),
                          Text(
                            "$progress%",
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.bold,
                              color: portalNavy,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: Dimensions.size5),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(Dimensions.size10),
                        child: LinearProgressIndicator(
                          value: (progress / 100.0).clamp(0.0, 1.0),
                          minHeight: Dimensions.size5,
                          backgroundColor: portalGoldSoft,
                          valueColor: const AlwaysStoppedAnimation<Color>(
                            portalGold,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      color: portalNavy.withValues(alpha: 0.08),
      alignment: Alignment.center,
      child: Icon(
        Icons.construction,
        size: Dimensions.size40,
        color: portalGold,
      ),
    );
  }
}

class BankCard extends StatelessWidget {
  final String bankName;
  final String accountNumber;
  final String holder;

  const BankCard({
    required this.bankName,
    required this.accountNumber,
    required this.holder,
    super.key,
  });

  String get _masked {
    final String digits = accountNumber.replaceAll(RegExp(r"\s"), "");
    final String last = digits.length > 4
        ? digits.substring(digits.length - 4)
        : digits;
    return "•••• •••• $last";
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      clipBehavior: Clip.antiAlias,
      padding: EdgeInsets.all(Dimensions.size20),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size25),
          smoothness: 1,
        ),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFF16397A),
            portalNavy,
          ],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                bankName.toUpperCase(),
                style: TextStyle(
                  fontSize: Dimensions.text20,
                  fontWeight: FontWeight.w900,
                  fontStyle: FontStyle.italic,
                  color: Colors.white,
                ),
              ),
              Text(
                "primary_account".tr().toUpperCase(),
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  fontWeight: FontWeight.bold,
                  color: portalGold,
                  letterSpacing: 1.0,
                ),
              ),
            ],
          ),
          SizedBox(height: Dimensions.size25),
          Text(
            "account_number_label".tr().toUpperCase(),
            style: TextStyle(
              fontSize: Dimensions.text10,
              color: Colors.white54,
              letterSpacing: 1.0,
            ),
          ),
          SizedBox(height: Dimensions.size5),
          Text(
            _masked,
            style: TextStyle(
              fontSize: Dimensions.text20,
              fontWeight: FontWeight.w800,
              color: Colors.white,
              letterSpacing: 2.0,
            ),
          ),
          SizedBox(height: Dimensions.size25),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  holder.toUpperCase(),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: Dimensions.text10,
                    fontWeight: FontWeight.bold,
                    color: Colors.white70,
                  ),
                ),
              ),
              Icon(
                Icons.check,
                size: Dimensions.size15,
                color: const Color(0xFF5EEAD4),
              ),
              SizedBox(width: Dimensions.size4),
              Text(
                "verified".tr(),
                style: TextStyle(
                  fontSize: Dimensions.text10,
                  color: const Color(0xFF5EEAD4),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class SettingsTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  const SettingsTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    this.trailing,
    this.onTap,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: EdgeInsets.all(Dimensions.size15),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.all(Dimensions.size10),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                ),
                color: portalGoldSoft,
              ),
              child: Icon(icon, color: portalGold, size: Dimensions.size20),
            ),
            SizedBox(width: Dimensions.size15),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: Dimensions.text12,
                      fontWeight: FontWeight.w800,
                      color: portalNavy,
                    ),
                  ),
                  SizedBox(height: Dimensions.size2),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: Dimensions.text10,
                      color: AppColors.onSurfaceVariant(),
                    ),
                  ),
                ],
              ),
            ),
            trailing ??
                Icon(
                  Icons.chevron_right,
                  color: portalNavy,
                  size: Dimensions.size20,
                ),
          ],
        ),
      ),
    );
  }
}
