// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/notification/notification.dart";
import "package:mgi_investor/module/rear/notification/notification_event.dart";
import "package:mgi_investor/module/rear/notification/notification_state.dart";

class NotificationBloc extends Bloc<NotificationEvent, NotificationState> {
  NotificationBloc() : super(NotificationInitial()) {
    on<NotificationLoad>((event, emit) async {
      try {
        emit(NotificationLoadLoading());

        final Response response = await ApiManager.notifications(
          limit: event.limit,
        );

        if (response.statusCode == 200) {
          final Map<String, dynamic> data =
              response.data is Map<String, dynamic>
                  ? response.data as Map<String, dynamic>
                  : {};

          final InvestorNotificationResponse parsed =
              InvestorNotificationResponse().parse(data);

          emit(
            NotificationLoadSuccess(
              items: parsed.notifications,
              unreadCount: parsed.unreadCount,
            ),
          );
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(NotificationLoadFinished());
      }
    });

    on<NotificationMarkRead>((event, emit) async {
      try {
        emit(NotificationMarkReadLoading());

        final Response response = await ApiManager.markNotificationRead(
          id: event.id,
        );

        if (response.statusCode == 200) {
          emit(NotificationMarkReadSuccess());
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(NotificationMarkReadFinished());
      }
    });

    on<NotificationMarkAllRead>((event, emit) async {
      try {
        emit(NotificationMarkReadLoading());

        final Response response = await ApiManager.markAllNotificationsRead();

        if (response.statusCode == 200) {
          emit(NotificationMarkReadSuccess());
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(NotificationMarkReadFinished());
      }
    });
  }
}
