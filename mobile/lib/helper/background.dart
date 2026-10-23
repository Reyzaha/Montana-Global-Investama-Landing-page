import "package:base/base.dart";
import "package:flutter/material.dart";

class Background extends StatelessWidget {
  final Widget child;

  const Background({
    required this.child,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final bool isDark =
        Theme.of(context).brightness == Brightness.dark || AppColors.darkMode();

    return Container(
      color: isDark ? const Color(0xFF0B132B) : const Color(0xFFF8FAFC),
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (!isDark)
            Image.asset(
              "assets/image/background-white.png",
              fit: BoxFit.cover,
            )
          else
            // Gradient elegan bertema Deep Navy & Slate untuk Dark Mode MGI
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Color(0xFF070B14),
                    Color(0xFF0F1C36),
                    Color(0xFF0B132B),
                  ],
                ),
              ),
            ),
          child,
        ],
      ),
    );
  }
}
