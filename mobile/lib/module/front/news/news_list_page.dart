// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/endpoint/article/article.dart";
import "package:mgi_investor/helper/background.dart";
import "package:mgi_investor/module/front/news/news_bloc.dart";
import "package:mgi_investor/module/front/news/news_event.dart";
import "package:mgi_investor/module/front/news/news_state.dart";
import "package:mgi_investor/widget/empty_state.dart";
import "package:mgi_investor/widget/error_state.dart";
import "package:mgi_investor/widget/skeleton_loader.dart";
import "package:smooth_corner/smooth_corner.dart";

class NewsListPage extends StatefulWidget {
  const NewsListPage({
    super.key,
  });

  @override
  NewsListPageState createState() => NewsListPageState();
}

class NewsListPageState extends State<NewsListPage>
    with WidgetsBindingObserver {
  bool loading = false;
  bool isError = false;
  List<Article>? articles;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangePlatformBrightness() {
    super.didChangePlatformBrightness();
    setState(() {});
  }

  void refresh() {
    setState(() {
      isError = false;
    });
    context.read<NewsBloc>().add(NewsLoad());
  }

  BaseAppBar appBar() {
    return BaseAppBar(
      context: context,
      name: "news_insights".tr(),
    );
  }

  String? _resolveImageUrl(String? raw) {
    if (raw == null || raw.trim().isEmpty) {
      return null;
    }
    final String clean = raw.trim();
    if (clean.startsWith("http://") || clean.startsWith("https://")) {
      return clean;
    }
    final String normalized = clean.startsWith("/") ? clean.substring(1) : clean;
    return "https://montanainvestama.com/$normalized";
  }

  void _showArticleDetail(Article article) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (BuildContext ctx) {
        final String? imageUrl = _resolveImageUrl(article.coverImage);
        final String dateStr = article.publishedAt != null
            ? article.publishedAt!.format(pattern: "d MMMM yyyy")
            : "-";

        return DraggableScrollableSheet(
          initialChildSize: 0.85,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          builder: (_, ScrollController scrollController) {
            return Container(
              decoration: ShapeDecoration(
                color: AppColors.surfaceContainerLowest(),
                shape: SmoothRectangleBorder(
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(Dimensions.size20),
                    topRight: Radius.circular(Dimensions.size20),
                  ),
                  smoothness: 1,
                ),
              ),
              child: ListView(
                controller: scrollController,
                padding: EdgeInsets.all(Dimensions.size20),
                children: [
                  Center(
                    child: Container(
                      width: Dimensions.size40,
                      height: Dimensions.size4,
                      decoration: BoxDecoration(
                        color: AppColors.outline(),
                        borderRadius: BorderRadius.circular(Dimensions.size2),
                      ),
                    ),
                  ),
                  SizedBox(height: Dimensions.size15),
                  if (imageUrl != null) ...[
                    ClipRRect(
                      borderRadius: BorderRadius.circular(Dimensions.size10),
                      child: Image.network(
                        imageUrl,
                        height: Dimensions.size100 * 1.8,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const SizedBox(),
                      ),
                    ),
                    SizedBox(height: Dimensions.size15),
                  ],
                  Row(
                    children: [
                      Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.size10,
                          vertical: Dimensions.size4,
                        ),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius:
                                BorderRadius.circular(Dimensions.size5),
                            smoothness: 1,
                          ),
                          color: const Color(0xFF0F2C59),
                        ),
                        child: Text(
                          article.category ?? "Wawasan",
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                      SizedBox(width: Dimensions.size10),
                      Text(
                        dateStr,
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          color: AppColors.onSurfaceVariant(),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: Dimensions.size10),
                  Text(
                    article.title ?? "-",
                    style: TextStyle(
                      fontSize: Dimensions.text18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.onSurface(),
                      height: 1.3,
                    ),
                  ),
                  SizedBox(height: Dimensions.size10),
                  Row(
                    children: [
                      Icon(
                        Icons.person_outline,
                        size: Dimensions.size15,
                        color: const Color(0xFFC5A059),
                      ),
                      SizedBox(width: Dimensions.size5),
                      Text(
                        article.authorName ?? "Tim MGI",
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFFC5A059),
                        ),
                      ),
                      const Spacer(),
                      Icon(
                        Icons.visibility_outlined,
                        size: Dimensions.size15,
                        color: AppColors.onSurfaceVariant(),
                      ),
                      SizedBox(width: Dimensions.size4),
                      Text(
                        "${article.viewsCount} pembaca",
                        style: TextStyle(
                          fontSize: Dimensions.text11,
                          color: AppColors.onSurfaceVariant(),
                        ),
                      ),
                    ],
                  ),
                  Divider(height: Dimensions.size25, color: AppColors.outline()),
                  if (article.excerpt != null &&
                      article.excerpt!.isNotEmpty) ...[
                    Container(
                      padding: EdgeInsets.all(Dimensions.size10),
                      decoration: ShapeDecoration(
                        shape: SmoothRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            Dimensions.size5,
                          ),
                          smoothness: 1,
                          side: BorderSide(
                            color: const Color(0xFFC5A059).withValues(
                              alpha: 0.3,
                            ),
                          ),
                        ),
                        color: const Color(0xFFC5A059).withValues(
                          alpha: 0.08,
                        ),
                      ),
                      child: Text(
                        article.excerpt!,
                        style: TextStyle(
                          fontSize: Dimensions.text12,
                          fontStyle: FontStyle.italic,
                          color: AppColors.onSurface(),
                          height: 1.5,
                        ),
                      ),
                    ),
                    SizedBox(height: Dimensions.size15),
                  ],
                  Text(
                    (article.content ?? article.excerpt ?? "")
                        .replaceAll(RegExp(r"<[^>]*>"), ""),
                    style: TextStyle(
                      fontSize: Dimensions.text13,
                      color: AppColors.onSurface(),
                      height: 1.6,
                    ),
                  ),
                  SizedBox(height: Dimensions.size30),
                  FilledButton(
                    onPressed: () => Navigator.of(ctx).pop(),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF0F2C59),
                    ),
                    child: Text("close".tr()),
                  ),
                  SizedBox(height: Dimensions.size20),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildArticleCard(Article article) {
    final String? imageUrl = _resolveImageUrl(article.coverImage);
    final String dateStr = article.publishedAt != null
        ? article.publishedAt!.format(pattern: "d MMM yyyy")
        : "-";

    return InkWell(
      onTap: () => _showArticleDetail(article),
      customBorder: SmoothRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.size10),
        smoothness: 1,
      ),
      child: Ink(
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
            if (imageUrl != null)
              ClipRRect(
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(Dimensions.size10),
                  topRight: Radius.circular(Dimensions.size10),
                ),
                child: Image.network(
                  imageUrl,
                  height: Dimensions.size100 * 1.4,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const SizedBox(),
                ),
              ),
            Padding(
              padding: EdgeInsets.all(Dimensions.size15),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: Dimensions.size10,
                          vertical: Dimensions.size4,
                        ),
                        decoration: ShapeDecoration(
                          shape: SmoothRectangleBorder(
                            borderRadius:
                                BorderRadius.circular(Dimensions.size5),
                            smoothness: 1,
                          ),
                          color:
                              const Color(0xFF0F2C59).withValues(alpha: 0.1),
                        ),
                        child: Text(
                          article.category ?? "Wawasan",
                          style: TextStyle(
                            fontSize: Dimensions.text10,
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F2C59),
                          ),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        dateStr,
                        style: TextStyle(
                          fontSize: Dimensions.text10,
                          color: AppColors.onSurfaceVariant(),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: Dimensions.size10),
                  Text(
                    article.title ?? "-",
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: Dimensions.text14,
                      fontWeight: FontWeight.bold,
                      color: AppColors.onSurface(),
                      height: 1.3,
                    ),
                  ),
                  if (article.excerpt != null &&
                      article.excerpt!.isNotEmpty) ...[
                    SizedBox(height: Dimensions.size5),
                    Text(
                      article.excerpt!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: Dimensions.text11,
                        color: AppColors.onSurfaceVariant(),
                        height: 1.4,
                      ),
                    ),
                  ],
                  SizedBox(height: Dimensions.size10),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.edit_note,
                            size: Dimensions.size15,
                            color: const Color(0xFFC5A059),
                          ),
                          SizedBox(width: Dimensions.size4),
                          Text(
                            article.authorName ?? "Tim MGI",
                            style: TextStyle(
                              fontSize: Dimensions.text10,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFFC5A059),
                            ),
                          ),
                        ],
                      ),
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            "read_more".tr(),
                            style: TextStyle(
                              fontSize: Dimensions.text11,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF0F2C59),
                            ),
                          ),
                          SizedBox(width: Dimensions.size2),
                          Icon(
                            Icons.arrow_forward_ios,
                            size: Dimensions.size10,
                            color: const Color(0xFF0F2C59),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget body() {
    if (loading && articles == null) {
      return Padding(
        padding: EdgeInsets.all(Dimensions.size15),
        child: Column(
          children: [
            SizedBox(height: Dimensions.size40),
            Expanded(child: SkeletonLoader.projectCard()),
          ],
        ),
      );
    }

    if (isError && articles == null) {
      return ErrorState(
        title: "failed_to_load_data".tr(),
        message: "connection_error_desc".tr(),
        onRetry: refresh,
      );
    }

    final List<Article> list = articles ?? [];

    return RefreshIndicator(
      onRefresh: () async => refresh(),
      child: list.isEmpty
          ? EmptyState(
              title: "no_data_title".tr(),
              message: "no_data_desc".tr(),
              icon: Icons.article_outlined,
            )
          : ListView.separated(
              padding: EdgeInsets.all(Dimensions.size15),
              itemCount: list.length,
              separatorBuilder: (_, __) =>
                  SizedBox(height: Dimensions.size15),
              itemBuilder: (BuildContext context, int index) {
                return _buildArticleCard(list[index]);
              },
            ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<NewsBloc, NewsState>(
      listener: (BuildContext context, NewsState state) {
        if (state is NewsLoadLoading) {
          setState(() {
            loading = true;
          });
        } else if (state is NewsLoadSuccess) {
          setState(() {
            articles = state.articles;
            isError = false;
          });
        } else if (state is NewsLoadFinished) {
          setState(() {
            loading = false;
          });
        }
      },
      child: Background(
        child: BaseScaffold(
          context: context,
          backgroundColor: Colors.transparent,
          appBar: appBar(),
          contentBuilder: body,
          onRefresh: () async => refresh(),
          statusBuilder: () {
            if (loading && articles == null) {
              return BaseBodyStatus.loading;
            }
            if (articles == null) {
              return BaseBodyStatus.fail;
            }
            if (articles!.isEmpty) {
              return BaseBodyStatus.empty;
            }
            return BaseBodyStatus.loaded;
          },
        ),
      ),
    );
  }
}
