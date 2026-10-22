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

    final Color color;
    final Color bgColor;
    final Color borderColor;
    final IconData icon;
    final String label;

    if (s.contains("fully") ||
        s.contains("fund") ||
        s.contains("secured") ||
        s.contains("terkunci") ||
        s.contains("closed") ||
        s.contains("tutup")) {
      color = const Color(0xFF475569);
      bgColor = const Color(0xFFF1F5F9);
      borderColor = const Color(0xFFCBD5E1);
      icon = Icons.lock;
      label = "slot_locked".tr();
    } else if (s.contains("soon") ||
        s.contains("coming") ||
        s.contains("segera")) {
      color = const Color(0xFFD97706);
      bgColor = const Color(0xFFFEF3C7);
      borderColor = const Color(0xFFFDE68A);
      icon = Icons.access_time;
      label = "slot_opening_soon".tr();
    } else {
      color = const Color(0xFF047857);
      bgColor = const Color(0xFFECFDF5);
      borderColor = const Color(0xFFA7F3D0);
      icon = Icons.person_add_alt_1_outlined;
      label = "slot_available".tr();
    }

    return Container(
      padding: padding ??
          EdgeInsets.symmetric(
            horizontal: Dimensions.size10,
            vertical: Dimensions.size5,
          ),
      decoration: ShapeDecoration(
        shape: SmoothRectangleBorder(
          borderRadius: BorderRadius.circular(Dimensions.size20),
          smoothness: 1,
          side: BorderSide(color: borderColor),
        ),
        color: bgColor,
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: Dimensions.size10,
            color: color,
          ),
          SizedBox(width: Dimensions.size4),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: fontSize ?? Dimensions.text10,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}
