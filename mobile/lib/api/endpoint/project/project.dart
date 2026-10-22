// ignore_for_file: always_specify_types

import "package:mgi_investor/helper/formats.dart";

class FundingItem {
  int no = 0;
  String? item;
  int quantity = 0;
  double unitPrice = 0.0;
  double total = 0.0;

  FundingItem parse(Map<String, dynamic> json) {
    no = Formats.tryParseNumber(json["no"]).toInt();
    item = json["item"];
    quantity = Formats.tryParseNumber(json["quantity"]).toInt();
    unitPrice = Formats.tryParseNumber(json["unit_price"]).toDouble();
    total = Formats.tryParseNumber(json["total"]).toDouble();
    return this;
  }
}

class FundingTargetDetail {
  String? type;
  String? title;
  double grandTotal = 0.0;
  bool isGated = false;
  List<FundingItem> rows = [];

  FundingTargetDetail parse(Map<String, dynamic> json) {
    type = json["type"];
    title = json["title"];
    grandTotal = Formats.tryParseNumber(json["grand_total"]).toDouble();
    isGated = Formats.tryParseBool(json["is_gated"]);
    rows = json["rows"] != null
        ? List<FundingItem>.from(
            (json["rows"] as List).map(
              (x) => FundingItem().parse(x as Map<String, dynamic>),
            ),
          )
        : [];
    return this;
  }
}

class ProjectSimulation {
  int tenorBulan = 12;
  double estimasiReturnPersen = 28.62;
  double modalKerjaBulananPersen = 2.38;
  double minimumInvestasi = 500000000.0;
  double maximumInvestasi = 10000000000.0;
  double defaultInvestasi = 1000000000.0;
  String? notes;
  bool isGated = false;

  ProjectSimulation parse(Map<String, dynamic> json) {
    tenorBulan = Formats.tryParseNumber(json["tenor_bulan"]).toInt();
    estimasiReturnPersen = Formats.tryParseNumber(
      json["estimasi_return_persen"],
    ).toDouble();
    modalKerjaBulananPersen = Formats.tryParseNumber(
      json["modal_kerja_bulanan_persen"],
    ).toDouble();
    minimumInvestasi = Formats.tryParseNumber(
      json["minimum_investasi"],
    ).toDouble();
    maximumInvestasi = Formats.tryParseNumber(
      json["maximum_investasi"],
    ).toDouble();
    defaultInvestasi = Formats.tryParseNumber(
      json["default_investasi"],
    ).toDouble();
    notes = json["notes"];
    isGated = Formats.tryParseBool(json["is_gated"]);
    return this;
  }
}

class PackageOption {
  String? title;
  double nominal = 0.0;
  String? nominalDisplay;
  int unitQty = 0;
  String? operatorPerk;
  String? label;
  String? badge;

  PackageOption parse(Map<String, dynamic> json) {
    title = json["title"] ?? json["label"];
    nominal = Formats.tryParseNumber(json["nominal"]).toDouble();
    nominalDisplay = json["nominal_display"];
    unitQty = Formats.tryParseNumber(json["unit_qty"]).toInt();
    operatorPerk = json["operator_perk"] ?? json["badge"];
    label = json["label"];
    badge = json["badge"];
    return this;
  }
}

class ProjectDetail {
  String? tagline;
  bool isGated = false;
  String? viewerWatermark;
  FundingTargetDetail? fundingTarget;
  ProjectSimulation? simulation;

  ProjectDetail parse(Map<String, dynamic> json) {
    tagline = json["tagline"];
    isGated = Formats.tryParseBool(json["is_gated"]);
    viewerWatermark = json["viewer_watermark"];

    if (json["funding_target"] != null) {
      fundingTarget = FundingTargetDetail().parse(
        json["funding_target"] as Map<String, dynamic>,
      );
    }

    if (json["simulation"] != null) {
      simulation = ProjectSimulation().parse(
        json["simulation"] as Map<String, dynamic>,
      );
    }

    return this;
  }
}

class ProjectInfo {
  String? lokasi;
  String? target;
  String? targetRange;
  String? investorSlot;
  String? operatorPerk;
  String? model;
  String? tenor;
  String? returnRate;
  String? risk;
  String? minInvestment;
  String? payout;
  String? remainingDays;
  String? assetBacked;
  String? stockAvailable;
  String? capitalizationValue;

  ProjectInfo parse(Map<String, dynamic> json) {
    lokasi = json["lokasi"];
    target = json["target"];
    targetRange = json["target_range"];
    investorSlot = json["investor_slot"];
    operatorPerk = json["operator_perk"];
    model = json["model"];
    tenor = json["tenor"];
    returnRate = json["return"];
    risk = json["risk"];
    minInvestment = json["min_investment"];
    payout = json["payout"];
    remainingDays = json["remaining_days"]?.toString();
    assetBacked = json["asset_backed"];
    stockAvailable = json["stock_available"];
    capitalizationValue = json["capitalization_value"];
    return this;
  }
}

class Project {
  String? id;
  int? cityId;
  String? city;
  String? citySlug;
  String? cityIcon;
  String? cityIconImg;
  String? title;
  String? category;
  String? image;
  String? model;
  String? packageOptionsMode;
  List<PackageOption> packageOptions = [];
  String? operatorFacility;
  String? fundingRange;
  double fundingCollected = 0.0;
  double fundingTarget = 0.0;
  String? status;
  bool featured = false;
  ProjectInfo? info;
  ProjectDetail? detail;

  Project parse(Map<String, dynamic> json) {
    id = json["id"];
    cityId = json["city_id"] != null
        ? Formats.tryParseNumber(json["city_id"]).toInt()
        : null;
    city = json["city"];
    citySlug = json["city_slug"];
    cityIcon = json["city_icon"];
    cityIconImg = json["city_icon_img"];
    title = json["title"];
    category = json["category"];
    image = json["image"];
    model = json["model"];
    final Map? detailMap = json["detail"] is Map ? json["detail"] as Map : null;
    final Map? rabExecMap =
        (detailMap != null && detailMap["rab_executive"] is Map)
            ? detailMap["rab_executive"] as Map
            : null;

    packageOptionsMode =
        json["package_options_mode"] ?? rabExecMap?["package_options_mode"];
    operatorFacility =
        json["operator_facility"] ?? rabExecMap?["operator_facility"];

    final dynamic rawOptions = json["package_options"] ??
        json["tiers"] ??
        rabExecMap?["package_options"] ??
        rabExecMap?["tiers"];

    packageOptions = rawOptions != null && rawOptions is List
        ? List<PackageOption>.from(
            rawOptions.map(
              (x) => PackageOption().parse(x as Map<String, dynamic>),
            ),
          )
        : [];

    fundingRange = json["funding_range"];

    if (json["funding"] != null && json["funding"] is Map) {
      fundingCollected = Formats.tryParseNumber(
        json["funding"]["collected"],
      ).toDouble();
      fundingTarget = Formats.tryParseNumber(
        json["funding"]["target"],
      ).toDouble();
    } else {
      fundingCollected = Formats.tryParseNumber(
        json["funding_collected"],
      ).toDouble();
      fundingTarget = Formats.tryParseNumber(
        json["funding_target"],
      ).toDouble();
    }

    status = json["status"];
    featured = Formats.tryParseBool(json["featured"]);

    if (json["info"] != null) {
      info = ProjectInfo().parse(json["info"] as Map<String, dynamic>);
    }

    if (json["detail"] != null) {
      detail = ProjectDetail().parse(json["detail"] as Map<String, dynamic>);
    }

    return this;
  }
}

class ProjectListResponse {
  String? currency;
  bool isAuthenticated = false;
  bool requireAuthForDetails = true;
  String? disclaimerText;
  List<Project> projects = [];

  ProjectListResponse parse(Map<String, dynamic> json) {
    final Map<String, dynamic> target = json["data"] is Map<String, dynamic>
        ? json["data"] as Map<String, dynamic>
        : json;

    currency = target["currency"];
    isAuthenticated = Formats.tryParseBool(target["is_authenticated"]);
    requireAuthForDetails = Formats.tryParseBool(
      target["require_auth_for_details"],
    );
    disclaimerText = target["disclaimer_text"];
    projects = target["projects"] != null
        ? List<Project>.from(
            (target["projects"] as List).map(
              (x) => Project().parse(x as Map<String, dynamic>),
            ),
          )
        : [];
    return this;
  }
}
