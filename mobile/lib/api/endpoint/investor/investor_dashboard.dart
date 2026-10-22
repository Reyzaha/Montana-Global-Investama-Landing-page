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

class InvestorBillingItem {
  int id = 0;
  String? invoiceNumber;
  String? transactionDate;
  double amount = 0.0;
  String? category;
  String? flowType;
  String? status;
  String? notes;
  String? projectTitle;

  InvestorBillingItem parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    invoiceNumber = json["reference_no"] ?? json["invoice_number"] ?? json["invoiceNumber"];
    transactionDate = json["transaction_date"] ?? json["transactionDate"];
    amount = Formats.tryParseNumber(json["amount"]).toDouble();
    category = json["category"];
    flowType = json["flow_type"] ?? json["flowType"];
    status = json["status"] ?? "Paid";
    notes = json["notes"] ?? json["description"];
    projectTitle = json["project_title"] ?? json["projectTitle"];

    return this;
  }
}

class CampaignUpdateItem {
  int id = 0;
  String? projectId;
  String? projectTitle;
  String? title;
  String? description;
  String? updateDate;
  int progressPercentage = 0;
  String? imageUrl;

  CampaignUpdateItem parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    projectId = json["project_id"]?.toString();
    projectTitle = json["project_title"]?.toString();
    title = json["title"]?.toString();
    description = json["description"]?.toString();
    updateDate = json["update_date"]?.toString();
    progressPercentage = Formats.tryParseNumber(json["progress_percentage"]).toInt();
    imageUrl = json["image_url"]?.toString();

    return this;
  }
}

class InvestorBankAccount {
  int id = 0;
  String? bankName;
  String? accountNumber;
  String? accountHolder;
  String? branch;

  InvestorBankAccount parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    bankName = json["bank_name"]?.toString() ?? json["bankName"]?.toString();
    accountNumber = json["account_number"]?.toString() ?? json["accountNumber"]?.toString();
    accountHolder = json["account_holder"]?.toString() ?? json["accountHolder"]?.toString();
    branch = json["branch"]?.toString();

    return this;
  }
}

class InvestorDocumentItem {
  int id = 0;
  String? projectId;
  String? docNumber;
  String? docTitle;
  String? docType;
  String? filePath;
  String? fileSize;
  String? publishedAt;

  InvestorDocumentItem parse(Map<String, dynamic> json) {
    id = Formats.tryParseNumber(json["id"]).toInt();
    projectId = json["project_id"]?.toString();
    docNumber = json["doc_number"]?.toString();
    docTitle = json["doc_title"]?.toString();
    docType = json["doc_type"]?.toString();
    filePath = json["file_path"]?.toString();
    fileSize = json["file_size"]?.toString() ?? "2.4 MB";
    publishedAt =
        json["published_at"]?.toString() ?? json["created_at"]?.toString();

    return this;
  }
}

class InvestorDashboardResponse {
  InvestorMetrics metrics = InvestorMetrics();
  List<InvestorPortfolioItem> portfolios = [];
  List<InvestorBillingItem> billings = [];
  List<CampaignUpdateItem> campaignUpdates = [];
  List<InvestorDocumentItem> documents = [];
  InvestorBankAccount? bankAccount;
  int unreadNotificationsCount = 0;

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

    if (json["billings"] != null && json["billings"] is List) {
      billings = List<InvestorBillingItem>.from(
        (json["billings"] as List).map(
          (x) => InvestorBillingItem().parse(x as Map<String, dynamic>),
        ),
      );
    }

    if (json["campaign_updates"] != null && json["campaign_updates"] is List) {
      campaignUpdates = List<CampaignUpdateItem>.from(
        (json["campaign_updates"] as List).map(
          (x) => CampaignUpdateItem().parse(x as Map<String, dynamic>),
        ),
      );
    }

    if (json["documents"] != null && json["documents"] is List) {
      documents = List<InvestorDocumentItem>.from(
        (json["documents"] as List).map(
          (x) => InvestorDocumentItem().parse(x as Map<String, dynamic>),
        ),
      );
    }

    if (json["bank_account"] != null && json["bank_account"] is Map<String, dynamic>) {
      bankAccount = InvestorBankAccount().parse(
        json["bank_account"] as Map<String, dynamic>,
      );
    } else if (json["bankAccount"] != null && json["bankAccount"] is Map<String, dynamic>) {
      bankAccount = InvestorBankAccount().parse(
        json["bankAccount"] as Map<String, dynamic>,
      );
    }

    unreadNotificationsCount = Formats.tryParseNumber(
      json["unread_notifications_count"] ?? json["unreadNotificationsCount"],
    ).toInt();

    return this;
  }
}
