import "package:mgi_investor/api/endpoint/article/article.dart";

abstract class NewsState {}

class NewsInitial extends NewsState {}

class NewsLoadLoading extends NewsState {}

class NewsLoadSuccess extends NewsState {
  final List<Article> articles;
  final String? selectedCategory;

  NewsLoadSuccess({
    required this.articles,
    this.selectedCategory,
  });
}

class NewsLoadFinished extends NewsState {}
