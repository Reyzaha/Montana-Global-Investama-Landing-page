// ignore_for_file: always_specify_types, use_build_context_synchronously

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:loader_overlay/loader_overlay.dart";
import "package:mgi_investor/api/endpoint/contact/contact_request.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/module/front/consultation/consultation_bloc.dart";
import "package:mgi_investor/module/front/consultation/consultation_event.dart";
import "package:mgi_investor/module/front/consultation/consultation_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class ConsultationPage extends StatefulWidget {
  const ConsultationPage({
    super.key,
  });

  @override
  ConsultationPageState createState() => ConsultationPageState();
}

class ConsultationPageState extends State<ConsultationPage>
    with WidgetsBindingObserver {
  final GlobalKey<FormState> formState = GlobalKey<FormState>();

  final TextEditingController tecName = TextEditingController();
  final TextEditingController tecEmail = TextEditingController();
  final TextEditingController tecPhone = TextEditingController();
  final TextEditingController tecSubject = TextEditingController(
    text: "Konsultasi Kemitraan Investasi Alat Berat",
  );
  final TextEditingController tecMessage = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    tecName.dispose();
    tecEmail.dispose();
    tecPhone.dispose();
    tecSubject.dispose();
    tecMessage.dispose();
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void submit() {
    if (formState.currentState?.validate() ?? false) {
      final ContactInquiryRequest request = ContactInquiryRequest(
        fullName: tecName.text.trim(),
        email: tecEmail.text.trim(),
        phone: tecPhone.text.trim(),
        subject: tecSubject.text.trim(),
        message: tecMessage.text.trim(),
      );

      context.read<ConsultationBloc>().add(
        ConsultationSubmit(request: request),
      );
    }
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "consultation".tr(),
    );
  }

  Widget body() {
    return SingleChildScrollView(
      padding: EdgeInsets.all(Dimensions.size15),
      child: Form(
        key: formState,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: EdgeInsets.all(Dimensions.size20),
              decoration: ShapeDecoration(
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.circular(Dimensions.size10),
                  smoothness: 1,
                  side: const BorderSide(color: Color(0xFFC5A059)),
                ),
                color: const Color(0xFF0F2C59),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.support_agent,
                        color: const Color(0xFFC5A059),
                        size: Dimensions.size20,
                      ),
                      SizedBox(width: Dimensions.size10),
                      Text(
                        "Konsultasi Investor Relations",
                        style: TextStyle(
                          fontSize: Dimensions.text14,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: Dimensions.size5),
                  Text(
                    "Hubungi relationship manager kami untuk konsultasi alokasi dana kemitraan alat berat, opsi sindikasi institusi, dan simulasi jadwal dividen.",
                    style: TextStyle(
                      fontSize: Dimensions.text11,
                      color: Colors.white70,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
            SizedBox(height: Dimensions.size20),
            BaseWidgets.text(
              mandatory: true,
              readonly: false,
              controller: tecName,
              label: "full_name".tr(),
              validator: (String? val) {
                if (val == null || val.trim().isEmpty) {
                  return "this_field_is_required".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              mandatory: true,
              readonly: false,
              controller: tecEmail,
              label: "email_address".tr(),
              textInputType: TextInputType.emailAddress,
              validator: (String? val) {
                if (val == null || val.trim().isEmpty) {
                  return "this_field_is_required".tr();
                }
                if (!val.contains("@") || !val.contains(".")) {
                  return "incorrect_email_format".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              mandatory: true,
              readonly: false,
              controller: tecPhone,
              label: "phone_number".tr(),
              textInputType: TextInputType.phone,
              validator: (String? val) {
                if (val == null || val.trim().isEmpty) {
                  return "this_field_is_required".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              mandatory: true,
              readonly: false,
              controller: tecSubject,
              label: "subject".tr(),
              validator: (String? val) {
                if (val == null || val.trim().isEmpty) {
                  return "this_field_is_required".tr();
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size15),
            BaseWidgets.text(
              mandatory: true,
              readonly: false,
              controller: tecMessage,
              label: "message".tr(),
              maxLines: 4,
              validator: (String? val) {
                if (val == null || val.trim().length < 10) {
                  return "minimum_character_is".tr(args: ["10"]);
                }
                return null;
              },
            ),
            SizedBox(height: Dimensions.size25),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: submit,
                icon: Icon(Icons.send, size: Dimensions.size15),
                label: Text("send_inquiry".tr()),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<ConsultationBloc, ConsultationState>(
      listener: (BuildContext context, ConsultationState state) async {
        if (state is ConsultationSubmitLoading) {
          context.loaderOverlay.show();
        } else if (state is ConsultationSubmitFinished) {
          context.loaderOverlay.hide();
        } else if (state is ConsultationSubmitSuccess) {
          await BaseOverlays.success(message: state.message);
          tecMessage.clear();
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
        ),
      ),
    );
  }
}
