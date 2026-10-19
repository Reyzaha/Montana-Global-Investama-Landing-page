// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/article/article.dart";
import "package:mgi_investor/module/front/news/news_event.dart";
import "package:mgi_investor/module/front/news/news_state.dart";

class NewsBloc extends Bloc<NewsEvent, NewsState> {
  NewsBloc() : super(NewsInitial()) {
    on<NewsLoad>((event, emit) async {
      try {
        emit(NewsLoadLoading());

        final Response response = await ApiManager.articles(
          category: event.category,
        );

        if (response.statusCode == 200) {
          final ArticleListResponse listRes =
              ArticleListResponse().parse(response.data);
          emit(
            NewsLoadSuccess(
              articles: listRes.articles,
              selectedCategory: event.category,
            ),
          );
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }

        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(NewsLoadFinished());
      }
    });
  }
}
