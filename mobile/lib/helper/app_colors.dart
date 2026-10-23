// ignore_for_file: depend_on_referenced_packages

import "package:flutter/material.dart";
import "package:get/get.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";

class AppColors {
  static Brightness brightness() {
    if (Get.context != null) {
      return MediaQuery.of(Get.context!).platformBrightness;
    }

    return WidgetsBinding.instance.platformDispatcher.platformBrightness;
  }

  static Brightness brightnessInverse() {
    if (darkMode()) {
      return Brightness.light;
    } else {
      return Brightness.dark;
    }
  }

  static bool darkMode() {
    return brightness() == Brightness.dark;
  }

  static ThemeMode themeMode() {
    int? pref = Preferences.getInt(PreferenceKey.THEME_MODE);
    if (pref == ThemeMode.light.index) {
      return ThemeMode.light;
    } else if (pref == ThemeMode.dark.index) {
      return ThemeMode.dark;
    }
    return ThemeMode.system;
  }

  static Color primary() {
    if (darkMode()) {
      return darkColorScheme.primary;
    } else {
      return lightColorScheme.primary;
    }
  }

  static Color onPrimary() {
    if (darkMode()) {
      return darkColorScheme.onPrimary;
    } else {
      return lightColorScheme.onPrimary;
    }
  }

  static Color primaryContainer() {
    if (darkMode()) {
      return darkColorScheme.primaryContainer;
    } else {
      return lightColorScheme.primaryContainer;
    }
  }

  static Color onPrimaryContainer() {
    if (darkMode()) {
      return darkColorScheme.onPrimaryContainer;
    } else {
      return lightColorScheme.onPrimaryContainer;
    }
  }

  static Color secondary() {
    if (darkMode()) {
      return darkColorScheme.secondary;
    } else {
      return lightColorScheme.secondary;
    }
  }

  static Color onSecondary() {
    if (darkMode()) {
      return darkColorScheme.onSecondary;
    } else {
      return lightColorScheme.onSecondary;
    }
  }

  static Color secondaryContainer() {
    if (darkMode()) {
      return darkColorScheme.secondaryContainer;
    } else {
      return lightColorScheme.secondaryContainer;
    }
  }

  static Color onSecondaryContainer() {
    if (darkMode()) {
      return darkColorScheme.onSecondaryContainer;
    } else {
      return lightColorScheme.onSecondaryContainer;
    }
  }

  static Color tertiary() {
    if (darkMode()) {
      return darkColorScheme.tertiary;
    } else {
      return lightColorScheme.tertiary;
    }
  }

  static Color onTertiary() {
    if (darkMode()) {
      return darkColorScheme.onTertiary;
    } else {
      return lightColorScheme.onTertiary;
    }
  }

  static Color tertiaryContainer() {
    if (darkMode()) {
      return darkColorScheme.tertiaryContainer;
    } else {
      return lightColorScheme.tertiaryContainer;
    }
  }

  static Color onTertiaryContainer() {
    if (darkMode()) {
      return darkColorScheme.onTertiaryContainer;
    } else {
      return lightColorScheme.onTertiaryContainer;
    }
  }

  static Color error() {
    if (darkMode()) {
      return darkColorScheme.error;
    } else {
      return lightColorScheme.error;
    }
  }

  static Color errorContainer() {
    if (darkMode()) {
      return darkColorScheme.errorContainer;
    } else {
      return lightColorScheme.errorContainer;
    }
  }

  static Color onError() {
    if (darkMode()) {
      return darkColorScheme.onError;
    } else {
      return lightColorScheme.onError;
    }
  }

  static Color onErrorContainer() {
    if (darkMode()) {
      return darkColorScheme.onErrorContainer;
    } else {
      return lightColorScheme.onErrorContainer;
    }
  }

  static Color background() {
    if (darkMode()) {
      return darkColorScheme.surface;
    } else {
      return lightColorScheme.surface;
    }
  }

  static Color onBackground() {
    if (darkMode()) {
      return darkColorScheme.onSurface;
    } else {
      return lightColorScheme.onSurface;
    }
  }

  static Color surface() {
    if (darkMode()) {
      return darkColorScheme.surface;
    } else {
      return lightColorScheme.surface;
    }
  }

  static Color onSurface() {
    if (darkMode()) {
      return darkColorScheme.onSurface;
    } else {
      return lightColorScheme.onSurface;
    }
  }

  static Color surfaceContainerLowest() {
    if (darkMode()) {
      return const Color(0xFF070B19);
    } else {
      return Colors.white;
    }
  }

  static Color surfaceContainerHighest() {
    if (darkMode()) {
      return darkColorScheme.surfaceContainerHighest;
    } else {
      return lightColorScheme.surfaceContainerHighest;
    }
  }

  static Color surfaceVariant() {
    if (darkMode()) {
      return darkColorScheme.surfaceContainerHighest;
    } else {
      return lightColorScheme.surfaceContainerHighest;
    }
  }

  static Color onSurfaceVariant() {
    if (darkMode()) {
      return darkColorScheme.onSurfaceVariant;
    } else {
      return lightColorScheme.onSurfaceVariant;
    }
  }

  static Color outline() {
    if (darkMode()) {
      return darkColorScheme.outline;
    } else {
      return lightColorScheme.outline;
    }
  }

  static Color onInverseSurface() {
    if (darkMode()) {
      return darkColorScheme.onInverseSurface;
    } else {
      return lightColorScheme.onInverseSurface;
    }
  }

  static Color inverseSurface() {
    if (darkMode()) {
      return darkColorScheme.inverseSurface;
    } else {
      return lightColorScheme.inverseSurface;
    }
  }

  static Color inversePrimary() {
    if (darkMode()) {
      return darkColorScheme.inversePrimary;
    } else {
      return lightColorScheme.inversePrimary;
    }
  }

  static Color shadow() {
    if (darkMode()) {
      return darkColorScheme.shadow;
    } else {
      return lightColorScheme.shadow;
    }
  }

  static Color surfaceTint() {
    if (darkMode()) {
      return darkColorScheme.surfaceTint;
    } else {
      return lightColorScheme.surfaceTint;
    }
  }

  static Color outlineVariant() {
    if (darkMode()) {
      return darkColorScheme.outlineVariant;
    } else {
      return lightColorScheme.outlineVariant;
    }
  }

  static Color scrim() {
    if (darkMode()) {
      return darkColorScheme.scrim;
    } else {
      return lightColorScheme.scrim;
    }
  }

  static Color success() {
    if (darkMode()) {
      return const Color(0xFF4ADE80);
    } else {
      return const Color(0xFF16A34A);
    }
  }

  static Color onSuccess() {
    if (darkMode()) {
      return const Color(0xFF052E16);
    } else {
      return Colors.white;
    }
  }

  static Color successContainer() {
    if (darkMode()) {
      return const Color(0xFF14532D);
    } else {
      return const Color(0xFFDCFCE7);
    }
  }

  static Color onSuccessContainer() {
    if (darkMode()) {
      return const Color(0xFFBBF7D0);
    } else {
      return const Color(0xFF14532D);
    }
  }

  // --- Palet Warna Montana Global Investama (MGI) ---
  // Primary: Deep Navy Blue (#0F2C59)
  // Secondary: Luxury Gold (#C5A059 / #D4AF37)
  // Surface: Clean Pearl Slate (#F8FAFC) / Deep Midnight Slate (#0B132B)
  static const ColorScheme lightColorScheme = ColorScheme(
    brightness: Brightness.light,
    primary: Color(0xFF0F2C59),
    onPrimary: Color(0xFFFFFFFF),
    primaryContainer: Color(0xFF1D3589),
    onPrimaryContainer: Color(0xFFFFFFFF),
    secondary: Color(0xFFC5A059),
    onSecondary: Color(0xFFFFFFFF),
    secondaryContainer: Color(0xFFF6E7C9),
    onSecondaryContainer: Color(0xFF382902),
    tertiary: Color(0xFF274472),
    onTertiary: Color(0xFFFFFFFF),
    tertiaryContainer: Color(0xFFD6E4FF),
    onTertiaryContainer: Color(0xFF0B1D3A),
    error: Color(0xFFBA1A1A),
    errorContainer: Color(0xFFFFDAD6),
    onError: Color(0xFFFFFFFF),
    onErrorContainer: Color(0xFF410002),
    surface: Color(0xFFF8FAFC),
    onSurface: Color(0xFF0F172A),
    surfaceContainerHighest: Color(0xFFE2E8F0),
    onSurfaceVariant: Color(0xFF64748B),
    outline: Color(0xFFCBD5E1),
    onInverseSurface: Color(0xFFF1F5F9),
    inverseSurface: Color(0xFF1E293B),
    inversePrimary: Color(0xFF8AB4F8),
    shadow: Color(0xFF000000),
    surfaceTint: Color(0xFF0F2C59),
    outlineVariant: Color(0xFFE2E8F0),
    scrim: Color(0xFF000000),
  );

  static const ColorScheme darkColorScheme = ColorScheme(
    brightness: Brightness.dark,
    primary: Color(0xFF8AB4F8),
    onPrimary: Color(0xFF0A1B36),
    primaryContainer: Color(0xFF1E3A8A),
    onPrimaryContainer: Color(0xFFDBEAFE),
    secondary: Color(0xFFD4AF37),
    onSecondary: Color(0xFF2B2000),
    secondaryContainer: Color(0xFF4B3900),
    onSecondaryContainer: Color(0xFFFFE088),
    tertiary: Color(0xFF93C5FD),
    onTertiary: Color(0xFF082142),
    tertiaryContainer: Color(0xFF1A355A),
    onTertiaryContainer: Color(0xFFBFDBFE),
    error: Color(0xFFFFB4AB),
    errorContainer: Color(0xFF93000A),
    onError: Color(0xFF690005),
    onErrorContainer: Color(0xFFFFDAD6),
    surface: Color(0xFF0B132B),
    onSurface: Color(0xFFF8FAFC),
    surfaceContainerHighest: Color(0xFF1E293B),
    onSurfaceVariant: Color(0xFF94A3B8),
    outline: Color(0xFF334155),
    onInverseSurface: Color(0xFF0B132B),
    inverseSurface: Color(0xFFE2E8F0),
    inversePrimary: Color(0xFF0F2C59),
    shadow: Color(0xFF000000),
    surfaceTint: Color(0xFF8AB4F8),
    outlineVariant: Color(0xFF1E293B),
    scrim: Color(0xFF000000),
  );
}
