import "dart:async";

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:go_router/go_router.dart";
import "package:lottie/lottie.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/helper/generals.dart";
import "package:mgi_investor/helper/images.dart";
import "package:smooth_corner/smooth_corner.dart";

class SplashPage extends StatefulWidget {
  const SplashPage({
    super.key,
  });

  @override
  SplashPageState createState() => SplashPageState();
}

class SplashPageState extends State<SplashPage>
    with WidgetsBindingObserver, SingleTickerProviderStateMixin {
  late final AnimationController animationController;
  late final Animation<double> fadeAnimation;
  late final Animation<double> scaleAnimation;

  Timer? navigationTimer;
  String version = "";

  @override
  void initState() {
    super.initState();

    WidgetsBinding.instance.addObserver(this);

    animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );

    fadeAnimation = CurvedAnimation(
      parent: animationController,
      curve: Curves.easeIn,
    );

    scaleAnimation = Tween<double>(begin: 0.85, end: 1.0).animate(
      CurvedAnimation(
        parent: animationController,
        curve: Curves.easeOutBack,
      ),
    );

    animationController.forward();

    loadVersion();
    startTransitionTimer();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  @override
  void dispose() {
    navigationTimer?.cancel();
    animationController.dispose();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  Future<void> loadVersion() async {
    try {
      final String ver = await Generals.appVersion();
      if (mounted) {
        setState(() {
          version = ver;
        });
      }
    } catch (_) {}
  }

  void startTransitionTimer() {
    navigationTimer = Timer(const Duration(milliseconds: 2800), () {
      proceedToNextScreen();
    });
  }

  void proceedToNextScreen() {
    if (!mounted) {
      return;
    }

    context.go("/");
  }

  @override
  Widget build(BuildContext context) {
    return Background(
      child: BaseScaffold(
        context: context,
        backgroundColor: Colors.transparent,
        contentBuilder: body,
      ),
    );
  }

  Widget body() {
    return SafeArea(
      child: InkWell(
        onTap: proceedToNextScreen,
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
        child: SizedBox.expand(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(),
              FadeTransition(
                opacity: fadeAnimation,
                child: ScaleTransition(
                  scale: scaleAnimation,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Stack(
                        alignment: Alignment.center,
                        children: [
                          SizedBox(
                            width: Dimensions.size100 * 2.6,
                            height: Dimensions.size100 * 2.6,
                            child: Lottie.asset(
                              "assets/lottie/splash.json",
                              frameRate: const FrameRate(60),
                              repeat: true,
                            ),
                          ),
                          Container(
                            padding: EdgeInsets.all(Dimensions.size15),
                            decoration: ShapeDecoration(
                              color: AppColors.surfaceContainerLowest(),
                              shape: SmoothRectangleBorder(
                                borderRadius: BorderRadius.circular(
                                  Dimensions.size25,
                                ),
                                smoothness: 1,
                                side: BorderSide(
                                  color: AppColors.outline().withValues(
                                    alpha: 0.25,
                                  ),
                                  width: 1.5,
                                ),
                              ),
                              shadows: [
                                BoxShadow(
                                  color: AppColors.primary().withValues(
                                    alpha: 0.12,
                                  ),
                                  blurRadius: Dimensions.size25,
                                  spreadRadius: Dimensions.size5,
                                  offset: const Offset(0, 8),
                                ),
                              ],
                            ),
                            child: Image.asset(
                              Images.brandLogo(),
                              width: Dimensions.size80,
                              height: Dimensions.size80,
                              fit: BoxFit.contain,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: Dimensions.size25),
                      Text(
                        "mgi_company_fullname".tr(),
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: Dimensions.text18,
                          fontWeight: FontWeight.bold,
                          color: AppColors.onSurface(),
                          letterSpacing: 0.8,
                        ),
                      ),
                      SizedBox(height: Dimensions.size5),
                      Text(
                        "mgi_tagline".tr(),
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: Dimensions.text13,
                          fontWeight: FontWeight.w500,
                          color: AppColors.onSurfaceVariant(),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: EdgeInsets.only(bottom: Dimensions.size25),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      width: Dimensions.size25,
                      height: Dimensions.size25,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppColors.secondary(),
                        ),
                      ),
                    ),
                    SizedBox(height: Dimensions.size10),
                    Text(
                      "mgi_loading_ecosystem".tr(),
                      style: TextStyle(
                        fontSize: Dimensions.text12,
                        color: AppColors.onSurfaceVariant(),
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                    if (version.isNotEmpty) ...[
                      SizedBox(height: Dimensions.size5),
                      Text(
                        "v$version",
                        style: TextStyle(
                          fontSize: Dimensions.text10,
                          color: AppColors.onSurfaceVariant().withValues(
                            alpha: 0.6,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
