import "package:base/base.dart";
import "package:flutter/material.dart";
import "package:smooth_corner/smooth_corner.dart";

class SkeletonLoader extends StatefulWidget {
  final double? width;
  final double? height;
  final BorderRadius? borderRadius;
  final EdgeInsetsGeometry? margin;

  const SkeletonLoader({
    this.width,
    this.height,
    this.borderRadius,
    this.margin,
    super.key,
  });

  static Widget line({
    double? width,
    double? height,
  }) {
    return SkeletonLoader(
      width: width,
      height: height ?? Dimensions.size15,
      borderRadius: BorderRadius.circular(Dimensions.size5),
    );
  }

  static Widget card({
    double? width,
    double? height,
  }) {
    return SkeletonLoader(
      width: width,
      height: height ?? Dimensions.size100,
      borderRadius: BorderRadius.circular(Dimensions.size10),
    );
  }

  static Widget statsGrid({
    int count = 4,
  }) {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: Dimensions.size10,
        mainAxisSpacing: Dimensions.size10,
        childAspectRatio: 1.6,
      ),
      itemCount: count,
      itemBuilder: (BuildContext context, int index) {
        return const SkeletonLoader(
          borderRadius: BorderRadius.all(Radius.circular(10)),
        );
      },
    );
  }

  static Widget projectCard() {
    return Container(
      padding: EdgeInsets.all(Dimensions.size15),
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
        children: [
          Row(
            children: [
              SkeletonLoader.line(
                width: Dimensions.size80,
                height: Dimensions.size20,
              ),
              const Spacer(),
              SkeletonLoader.line(
                width: Dimensions.size50,
                height: Dimensions.size20,
              ),
            ],
          ),
          SizedBox(height: Dimensions.size10),
          SkeletonLoader.line(
            width: double.infinity,
            height: Dimensions.size15,
          ),
          SizedBox(height: Dimensions.size15),
          SkeletonLoader.line(
            width: double.infinity,
            height: Dimensions.size5,
          ),
          SizedBox(height: Dimensions.size10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              SkeletonLoader.line(
                width: Dimensions.size100,
                height: Dimensions.size10,
              ),
              SkeletonLoader.line(
                width: Dimensions.size40,
                height: Dimensions.size10,
              ),
            ],
          ),
        ],
      ),
    );
  }

  @override
  State<SkeletonLoader> createState() => _SkeletonLoaderState();
}

class _SkeletonLoaderState extends State<SkeletonLoader>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat(reverse: true);

    _animation = Tween<double>(begin: 0.05, end: 0.16).animate(
      CurvedAnimation(
        parent: _controller,
        curve: Curves.easeInOut,
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final BorderRadius radius =
        widget.borderRadius ?? BorderRadius.circular(Dimensions.size10);

    return AnimatedBuilder(
      animation: _animation,
      builder: (BuildContext context, Widget? child) {
        return Container(
          width: widget.width,
          height: widget.height,
          margin: widget.margin,
          decoration: ShapeDecoration(
            shape: SmoothRectangleBorder(
              borderRadius: radius,
              smoothness: 1,
            ),
            color: AppColors.onSurface().withValues(
              alpha: _animation.value,
            ),
          ),
        );
      },
    );
  }
}
