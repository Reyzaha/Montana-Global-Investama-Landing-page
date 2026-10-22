abstract class NotificationEvent {}

class NotificationLoad extends NotificationEvent {
  final int limit;

  NotificationLoad({
    this.limit = 30,
  });
}

class NotificationMarkRead extends NotificationEvent {
  final int id;

  NotificationMarkRead({
    required this.id,
  });
}

class NotificationMarkAllRead extends NotificationEvent {}
