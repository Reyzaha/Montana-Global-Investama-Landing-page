import "package:mgi_investor/api/endpoint/notification/notification.dart";

abstract class NotificationState {}

class NotificationInitial extends NotificationState {}

class NotificationLoadLoading extends NotificationState {}

class NotificationLoadSuccess extends NotificationState {
  final List<InvestorNotification> items;
  final int unreadCount;

  NotificationLoadSuccess({
    required this.items,
    required this.unreadCount,
  });
}

class NotificationLoadFinished extends NotificationState {}

class NotificationMarkReadLoading extends NotificationState {}

class NotificationMarkReadSuccess extends NotificationState {}

class NotificationMarkReadFinished extends NotificationState {}
