// ignore_for_file: always_specify_types

import "package:mgi_investor/helper/formats.dart";

class InvestorMetrics {
  double totalInvested = 0.0;
  double totalPayoutReceived = 0.0;
  double totalPurchasesMiu = 0.0;
  int activeProjectsCount = 0;
  String? nextPayoutDate;
  String estRoiRate = "0%";

  InvestorMetrics parse(Map<String, dynamic> json) {
    totalInvested = Formats.tryParseNumber(
      json["total_invested"] ?? json["totalInvested"],
    ).toDouble();
    totalPayoutReceived = Formats.tryParseNumber(
      json["total_payout_received"] ?? json["totalPayoutReceived"],
    ).toDouble();
    totalPurchasesMiu = Formats.tryParseNumber(
      json["total_purchases_miu"] ?? json["totalPurchasesMiu"],
    ).toDouble();
    activeProjectsCount = Formats.tryParseNumber(
      json["active_projects_count"] ?? json["activeProjectsCount"],
    ).toInt();
    nextPayoutDate = json["next_payout_date"] ?? json["nextPayoutDate"];
    estRoiRate = json["est_roi_rate"] ?? json["estRoiRate"] ?? "0%";

    return this;
  }
}

class InvestorPortfolioItem {
  int id = 0;
  String? projectId;
  String? projectTitle;
  String? projectCategory;
  String? projectImage;
  String? contractNumber;
  double amount = 0.0;
  String? returnRate;
  String? tenor;
  String? startDate;
  String? endDate;
  String? nextPayoutDate;
  double payoutReceived = 0.0;
  String? allocatedUnits;
  String? status;

  InvestorPortfolioItem parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    projectId = json["project_id"];
    projectTitle = json["project_title"];
    projectCategory = json["project_category"];
    projectImage = json["project_image"];
    contractNumber = json["contract_number"];
    amount = Formats.tryParseNumber(json["amount"]).toDouble();
    returnRate = json["return_rate"];
    tenor = json["tenor"];
    startDate = json["start_date"];
    endDate = json["end_date"];
    nextPayoutDate = json["next_payout_date"];
    payoutReceived = Formats.tryParseNumber(json["payout_received"]).toDouble();
    allocatedUnits = json["allocated_units"];
    status = json["status"];

    return this;
  }
}

class InvestorDashboardResponse {
  InvestorMetrics metrics = InvestorMetrics();
  List<InvestorPortfolioItem> portfolios = [];

  InvestorDashboardResponse parse(Map<String, dynamic> json) {
    if (json["metrics"] != null && json["metrics"] is Map<String, dynamic>) {
      metrics = InvestorMetrics().parse(
        json["metrics"] as Map<String, dynamic>,
      );
    } else if (json["summary"] != null && json["summary"] is Map<String, dynamic>) {
      metrics = InvestorMetrics().parse(
        json["summary"] as Map<String, dynamic>,
      );
    }

    if (json["portfolios"] != null && json["portfolios"] is List) {
      portfolios = List<InvestorPortfolioItem>.from(
        (json["portfolios"] as List).map(
          (x) => InvestorPortfolioItem().parse(x as Map<String, dynamic>),
        ),
      );
    }

    return this;
  }
}
