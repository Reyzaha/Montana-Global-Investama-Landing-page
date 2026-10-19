import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:smooth_corner/smooth_corner.dart";

class StatusBadge extends StatelessWidget {
  final String? status;
  final double? fontSize;
  final EdgeInsetsGeometry? padding;

  const StatusBadge({
    required this.status,
    this.fontSize,
    this.padding,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final String s = (status ?? "").toLowerCase().trim();

    Color color;
    String label;

    if (s.contains("open") || s.contains("buka")) {
      color = const Color(0xFF059669);
      label = "status_open".tr();
    } else if (s.contains("fully") ||
        s.contains("fund") ||
        s.contains("secured") ||
        s.contains("terkunci") ||
        s.contains("closed") ||
        s.contains("tutup")) {
      color = const Color(0xFF2563EB);
      label = "status_secured".tr();
    } else if (s.contains("soon") ||
        s.contains("coming") ||
        s.contains("segera")) {
      color = const Color(0xFFD97706);
      label = "status_coming_soon".tr();
    } else {
      color = AppColors.onSurfaceVariant();
      label = (status ?? "-").toUpperCase();
    }

    return Container(
      padding: padding ??
          EdgeInsets.symmetric(
            horizontal: Dimensions.size10,
            vertical: Dimensions.size4,
          ),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size20),
          smoothness: 1,
        ),
        color: color.withValues(alpha: 0.12),
      ),
      child: Text(
        label,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
        style: TextStyle(
          fontSize: fontSize ?? Dimensions.text10,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
    );
  }
}
