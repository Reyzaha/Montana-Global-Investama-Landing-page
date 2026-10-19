// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/shared.dart";
import "package:smooth_corner/smooth_corner.dart";

class AuthGuard {
  static bool get isAuthenticated => Shared.ACCOUNT != null;

  static void promptLogin(
    BuildContext context, {
    String? title,
    String? message,
    VoidCallback? onContinueGuest,
  }) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (BuildContext ctx) {
        return Container(
          padding: EdgeInsets.all(Dimensions.size20),
          decoration: ShapeDecoration(
            color: AppColors.surfaceContainerLowest(),
            shape: SmoothRectangleBorder(
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(Dimensions.size20),
                topRight: Radius.circular(Dimensions.size20),
              ),
              smoothness: 1,
            ),
          ),
          child: SafeArea(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: Dimensions.size40,
                    height: Dimensions.size4,
                    decoration: BoxDecoration(
                      color: AppColors.outline(),
                      borderRadius: BorderRadius.circular(Dimensions.size2),
                    ),
                  ),
                ),
                SizedBox(height: Dimensions.size20),
                Row(
                  children: [
                    Container(
                      padding: EdgeInsets.all(Dimensions.size10),
                      decoration: ShapeDecoration(
                        shape: SmoothRectangleBorder(
                          borderRadius:
                              BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                        color: const Color(0xFF0F2C59).withValues(alpha: 0.1),
                      ),
                      child: Icon(
                        Icons.lock_outline,
                        color: const Color(0xFF0F2C59),
                        size: Dimensions.size25,
                      ),
                    ),
                    SizedBox(width: Dimensions.size15),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            title ?? "access_restricted".tr(),
                            style: TextStyle(
                              fontSize: Dimensions.text16,
                              fontWeight: FontWeight.bold,
                              color: AppColors.onSurface(),
                            ),
                          ),
                          SizedBox(height: Dimensions.size2),
                          Text(
                            "mgi_company_fullname".tr(),
                            style: TextStyle(
                              fontSize: Dimensions.text11,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFFC5A059),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                SizedBox(height: Dimensions.size15),
                Text(
                  message ?? "login_to_view_details_desc".tr(),
                  style: TextStyle(
                    fontSize: Dimensions.text12,
                    color: AppColors.onSurfaceVariant(),
                    height: 1.5,
                  ),
                ),
                SizedBox(height: Dimensions.size20),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF0F2C59),
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(
                        vertical: Dimensions.size15,
                      ),
                      shape: SmoothRectangleBorder(
                        borderRadius:
                            BorderRadius.circular(Dimensions.size10),
                        smoothness: 1,
                      ),
                    ),
                    icon: Icon(Icons.login, size: Dimensions.size15),
                    label: Text(
                      "sign_in_or_register".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      context.push("/sign-in");
                    },
                  ),
                ),
                if (onContinueGuest != null) ...[
                  SizedBox(height: Dimensions.size10),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.onSurfaceVariant(),
                        side: BorderSide(color: AppColors.outline()),
                        padding: EdgeInsets.symmetric(
                          vertical: Dimensions.size10,
                        ),
                        shape: SmoothRectangleBorder(
                          borderRadius:
                              BorderRadius.circular(Dimensions.size10),
                          smoothness: 1,
                        ),
                      ),
                      onPressed: () {
                        Navigator.of(ctx).pop();
                        onContinueGuest();
                      },
                      child: Text(
                        "learn_more".tr(),
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                        ),
                      ),
                    ),
                  ),
                ],
                SizedBox(height: Dimensions.size10),
              ],
            ),
          ),
        );
      },
    );
  }
}
