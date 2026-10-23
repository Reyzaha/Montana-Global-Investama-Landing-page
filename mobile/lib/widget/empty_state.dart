import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";

class EmptyState extends StatelessWidget {
  final String? title;
  final String? message;
  final IconData? icon;
  final VoidCallback? onAction;
  final String? actionLabel;
  final EdgeInsetsGeometry? padding;

  const EmptyState({
    this.title,
    this.message,
    this.icon,
    this.onAction,
    this.actionLabel,
    this.padding,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: padding ?? EdgeInsets.all(Dimensions.size25),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: EdgeInsets.all(Dimensions.size15),
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.primary().withValues(alpha: 0.12),
              ),
              child: Icon(
                icon ?? Icons.folder_open_outlined,
                size: Dimensions.size35,
                color: AppColors.primary(),
              ),
            ),
            SizedBox(height: Dimensions.size15),
            Text(
              title ?? "no_data_title".tr(),
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: Dimensions.text14,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
            SizedBox(height: Dimensions.size5),
            Text(
              message ?? "no_data_desc".tr(),
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: Dimensions.text11,
                color: AppColors.onSurfaceVariant(),
                height: 1.4,
              ),
            ),
            if (onAction != null) ...[
              SizedBox(height: Dimensions.size20),
              ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 48),
                child: OutlinedButton(
                  onPressed: onAction,
                  child: Text(actionLabel ?? "try_again".tr()),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
