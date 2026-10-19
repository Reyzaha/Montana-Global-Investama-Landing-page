// ignore_for_file: always_specify_types

import "package:jiffy/jiffy.dart";
import "package:mgi_investor/helper/formats.dart";

class Article {
  int? id;
  String? title;
  String? slug;
  String? category;
  String? excerpt;
  String? content;
  String? coverImage;
  String? authorName;
  int viewsCount = 0;
  Jiffy? publishedAt;

  Article parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    title = json["title"];
    slug = json["slug"];
    category = json["category"];
    excerpt = json["excerpt"];
    content = json["content"];
    coverImage = json["cover_image"];
    authorName = json["author_name"];
    viewsCount = Formats.tryParseNumber(json["views_count"]).toInt();
    publishedAt = Formats.tryParseJiffy(json["published_at"]);
    return this;
  }
}

class ArticleListResponse {
  List<Article> articles = [];

  ArticleListResponse parse(dynamic json) {
    if (json is List) {
      articles = List<Article>.from(
        json.map((x) => Article().parse(x as Map<String, dynamic>)),
      );
    } else if (json is Map<String, dynamic>) {
      final dynamic raw = json["data"] ?? json["articles"];
      if (raw is List) {
        articles = List<Article>.from(
          raw.map((x) => Article().parse(x as Map<String, dynamic>)),
        );
      }
    }
    return this;
  }
}
