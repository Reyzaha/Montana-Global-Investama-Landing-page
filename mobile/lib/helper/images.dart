// ignore_for_file: non_constant_identifier_names, depend_on_referenced_packages

import "package:base/base.dart";
import "package:flutter/foundation.dart";
import "package:flutter/material.dart";
import "package:image/image.dart" as ui;
import "package:image_watermark/image_watermark.dart";

class Images {
  static Brightness brightness() => AppColors.brightness();

  static bool darkMode() => AppColors.darkMode();

  static String mgi_investor() {
    if (AppColors.darkMode()) {
      return "assets/image/mgi_investor_white.png";
    } else {
      return "assets/image/mgi_investor_black.png";
    }
  }

  static String mgi_investorInverse() {
    if (AppColors.darkMode()) {
      return "assets/image/mgi_investor_black.png";
    } else {
      return "assets/image/mgi_investor_white.png";
    }
  }

  static String mgiInvestor() => mgi_investor();

  static String mgiInvestorInverse() => mgi_investorInverse();

  static String logo() => mgi_investor();

  static String logoInverse() => mgi_investorInverse();

  static String brandLogo() => "assets/image/mgi_investor_logo.png";

  static String appIcon() => "assets/image/app_icon.png";

  static String background() => "assets/image/background.png";

  static Future<Uint8List> textWatermark({
    required Uint8List source,
    required String text,
    int? fontSize,
    int? width,
    int? height,
  }) async {
    ui.BitmapFont font = ui.arial24;
    final int size = fontSize ?? Dimensions.text14.toInt();
    if (size >= 48) {
      font = ui.arial48;
    } else if (size <= 14) {
      font = ui.arial14;
    }

    return ImageWatermark.addTextWatermark(
      imgBytes: source,
      watermarkText: text,
      font: font,
      color: Colors.black.withValues(alpha: 0.5),
      dstX: width != null ? (width * 0.1).toInt() : 20,
      dstY: height != null ? (height * 0.9).toInt() : 20,
    );
  }
}
