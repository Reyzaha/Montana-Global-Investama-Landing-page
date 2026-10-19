import "package:flutter/material.dart";

class Background extends StatelessWidget {
  final Widget child;

  const Background({
    required this.child,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      fit: StackFit.expand,
      children: [
        Image.asset(
          "assets/image/background.png",
          fit: BoxFit.cover,
        ),
        child,
      ],
    );
  }
}
