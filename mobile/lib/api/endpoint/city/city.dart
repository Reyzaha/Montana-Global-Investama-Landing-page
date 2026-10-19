// ignore_for_file: always_specify_types

import "package:mgi_investor/helper/formats.dart";

class City {
  int id = 0;
  String? name;
  String? slug;
  String? icon;
  String? description;
  int sortOrder = 0;
  int projectCount = 0;
  int openProjectCount = 0;

  City parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    name = json["name"];
    slug = json["slug"];
    icon = json["icon"];
    description = json["description"];
    sortOrder = Formats.tryParseNumber(json["sort_order"]).toInt();
    projectCount = Formats.tryParseNumber(json["project_count"]).toInt();
    openProjectCount = Formats.tryParseNumber(
      json["open_project_count"],
    ).toInt();
    return this;
  }
}

class CityListResponse {
  List<City> cities = [];

  CityListResponse parse(Map<String, dynamic> json) {
    cities = json["cities"] != null
        ? List<City>.from(
            (json["cities"] as List).map(
              (x) => City().parse(x as Map<String, dynamic>),
            ),
          )
        : [];
    return this;
  }
}
