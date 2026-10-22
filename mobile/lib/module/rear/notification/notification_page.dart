// ignore_for_file: always_specify_types, use_build_context_synchronously

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:go_router/go_router.dart";
import "package:mgi_investor/api/endpoint/notification/notification.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/module/rear/notification/notification_bloc.dart";
import "package:mgi_investor/module/rear/notification/notification_event.dart";
import "package:mgi_investor/module/rear/notification/notification_state.dart";
import "package:smooth_corner/smooth_corner.dart";

class NotificationPage extends StatefulWidget {
  const NotificationPage({
    super.key,
  });

  @override
  NotificationPageState createState() => NotificationPageState();
}

class NotificationPageState extends State<NotificationPage>
    with WidgetsBindingObserver {
  bool loading = false;
  List<InvestorNotification>? items;
  int unreadCount = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  void refresh() {
    context.read<NotificationBloc>().add(NotificationLoad());
  }

  void markAllRead() {
    context.read<NotificationBloc>().add(NotificationMarkAllRead());
  }

  void markSingleRead(InvestorNotification item) {
    if (!item.isRead) {
      context.read<NotificationBloc>().add(NotificationMarkRead(id: item.id));
      setState(() {
        item.isRead = true;
        if (unreadCount > 0) {
          unreadCount--;
        }
      });
    }

    if (item.projectId != null && item.projectId!.isNotEmpty) {
      context.push("/portal/projects/${item.projectId}");
    }
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "notifications".tr(),
      trailings: [
        if (unreadCount > 0)
          TextButton(
            onPressed: markAllRead,
            child: Text(
              "mark_all_read".tr(),
              style: TextStyle(
                fontSize: Dimensions.text11,
                fontWeight: FontWeight.bold,
                color: const Color(0xFFC5A059),
              ),
            ),
          ),
      ],
    );
  }

  Color _getTypeColor(String type) {
    switch (type.toLowerCase()) {
      case "dividen":
      case "financial":
        return Colors.green.shade700;
      case "project":
      case "portfolio":
        return const Color(0xFFC5A059);
      case "alert":
        return Colors.red.shade700;
      default:
        return const Color(0xFF0F2C59);
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type.toLowerCase()) {
      case "dividen":
      case "financial":
        return Icons.payments_outlined;
      case "project":
      case "portfolio":
        return Icons.business_outlined;
      case "alert":
        return Icons.warning_amber_rounded;
      default:
        return Icons.notifications_active_outlined;
    }
  }

  Widget body() {
    final List<InvestorNotification> list = items ?? [];

    return ListView.separated(
      padding: EdgeInsets.all(Dimensions.size15),
      itemCount: list.length,
      separatorBuilder: (_, __) => SizedBox(height: Dimensions.size10),
      itemBuilder: (context, index) {
        final InvestorNotification item = list[index];
        final Color typeColor = _getTypeColor(item.type);

        return InkWell(
          onTap: () => markSingleRead(item),
          customBorder: SmoothRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.size10),
            smoothness: 1,
          ),
          child: Ink(
            padding: EdgeInsets.all(Dimensions.size15),
            decoration: ShapeDecoration(
              shape: SmoothRectangleBorder(
                borderRadius: BorderRadius.circular(Dimensions.size10),
                smoothness: 1,
                side: BorderSide(
                  color: item.isRead
                      ? AppColors.outline()
                      : const Color(0xFFC5A059),
                  width: item.isRead ? 1 : 1.5,
                ),
              ),
              color: item.isRead
                  ? AppColors.surfaceContainerLowest()
                  : const Color(0xFF0F2C59).withValues(alpha: 0.05),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: EdgeInsets.all(Dimensions.size10),
                  decoration: ShapeDecoration(
                    shape: SmoothRectangleBorder(
                      borderRadius: BorderRadius.circular(Dimensions.size10),
                      smoothness: 1,
                    ),
                    color: typeColor.withValues(alpha: 0.15),
                  ),
                  child: Icon(
                    _getTypeIcon(item.type),
                    color: typeColor,
                    size: Dimensions.size20,
                  ),
                ),
                SizedBox(width: Dimensions.size15),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              item.title ?? "Notifikasi MGI",
                              style: TextStyle(
                                fontSize: Dimensions.text13,
                                fontWeight: item.isRead
                                    ? FontWeight.w600
                                    : FontWeight.bold,
                                color: AppColors.onSurface(),
                              ),
                            ),
                          ),
                          if (!item.isRead)
                            Container(
                              width: Dimensions.size10,
                              height: Dimensions.size10,
                              decoration: const BoxDecoration(
                                shape: BoxShape.circle,
                                color: Color(0xFFC5A059),
                              ),
                            ),
                        ],
                      ),
                      SizedBox(height: Dimensions.size4),
                      Text(
                        item.message ?? "",
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          color: AppColors.onSurfaceVariant(),
                          height: 1.4,
                        ),
                      ),
                      if (item.projectTitle != null &&
                          item.projectTitle!.isNotEmpty) ...[
                        SizedBox(height: Dimensions.size5),
                        Text(
                          "Proyek: ${item.projectTitle}",
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFFC5A059),
                          ),
                        ),
                      ],
                      SizedBox(height: Dimensions.size5),
                      Text(
                        item.createdAt ?? "",
                        style: TextStyle(
                          fontSize: Dimensions.text10,
                          color: AppColors.onSurfaceVariant()
                              .withValues(alpha: 0.7),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<NotificationBloc, NotificationState>(
      listener: (context, state) async {
        if (state is NotificationLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is NotificationLoadSuccess) {
          setState(() {
            items = state.items;
            unreadCount = state.unreadCount;
          });
        } else if (state is NotificationLoadFinished) {
          setState(() {
            loading = false;
          });
        } else if (state is NotificationMarkReadSuccess) {
          refresh();
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
          onRefresh: () async {
            refresh();
          },
          statusBuilder: () {
            if (loading && items == null) {
              return BaseBodyStatus.loading;
            } else {
              if (items != null && items!.isNotEmpty) {
                return BaseBodyStatus.loaded;
              } else {
                if (items == null) {
                  return BaseBodyStatus.fail;
                } else {
                  return BaseBodyStatus.empty;
                }
              }
            }
          },
        ),
      ),
    );
  }
}
