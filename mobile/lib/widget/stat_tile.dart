import "package:base/base.dart";
import "package:flutter/material.dart";
import "package:smooth_corner/smooth_corner.dart";

class StatTile extends StatelessWidget {
  final String value;
  final String label;
  final String? desc;
  final Color? valueColor;
  final EdgeInsetsGeometry? padding;

  const StatTile({
    required this.value,
    required this.label,
    this.desc,
    this.valueColor,
    this.padding,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding ?? EdgeInsets.all(Dimensions.size10),
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
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: Dimensions.text16,
              fontWeight: FontWeight.bold,
              color: valueColor ?? AppColors.onSurface(),
            ),
          ),
          SizedBox(height: Dimensions.size4),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: Dimensions.text11,
              fontWeight: FontWeight.w600,
              color: AppColors.onSurface(),
            ),
          ),
          if (desc != null && desc!.isNotEmpty) ...[
            SizedBox(height: Dimensions.size2),
            Text(
              desc!,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: Dimensions.text10,
                color: AppColors.onSurfaceVariant(),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
