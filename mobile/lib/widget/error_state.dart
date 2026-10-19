import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";

class ErrorState extends StatelessWidget {
  final String? title;
  final String? message;
  final VoidCallback? onRetry;
  final EdgeInsetsGeometry? padding;

  const ErrorState({
    this.title,
    this.message,
    this.onRetry,
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
                color: AppColors.error().withValues(alpha: 0.1),
              ),
              child: Icon(
                Icons.error_outline_rounded,
                size: Dimensions.size35,
                color: AppColors.error(),
              ),
            ),
            SizedBox(height: Dimensions.size15),
            Text(
              title ?? "something_wrong_please_try_again".tr(),
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: Dimensions.text14,
                fontWeight: FontWeight.bold,
                color: AppColors.onSurface(),
              ),
            ),
            if (message != null && message!.isNotEmpty) ...[
              SizedBox(height: Dimensions.size5),
              Text(
                message!,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: Dimensions.text11,
                  color: AppColors.onSurfaceVariant(),
                  height: 1.4,
                ),
              ),
            ],
            if (onRetry != null) ...[
              SizedBox(height: Dimensions.size20),
              ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 48),
                child: FilledButton.icon(
                  onPressed: onRetry,
                  icon: Icon(Icons.refresh, size: Dimensions.size20),
                  label: Text("try_again".tr()),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
