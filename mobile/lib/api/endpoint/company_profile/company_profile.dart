// ignore_for_file: always_specify_types

class CompanyStat {
  String? label;
  String? value;
  String? desc;

  CompanyStat parse(Map<String, dynamic> json) {
    label = json["label"];
    value = json["value"];
    desc = json["desc"];
    return this;
  }
}

class AboutGroup {
  String? title;
  String? subtitle;
  String? description;
  String? positioning;

  AboutGroup parse(Map<String, dynamic> json) {
    title = json["title"];
    subtitle = json["subtitle"];
    description = json["description"];
    positioning = json["positioning"];
    return this;
  }
}

class EcosystemEntity {
  String? code;
  String? name;
  String? slogan;
  String? role;
  String? level;
  String? badge;
  String? color;
  String? description;
  String? focus;
  String? output;
  String? location;

  EcosystemEntity parse(Map<String, dynamic> json) {
    code = json["code"];
    name = json["name"];
    slogan = json["slogan"];
    role = json["role"];
    level = json["level"];
    badge = json["badge"];
    color = json["color"];
    description = json["description"];
    focus = json["focus"];
    output = json["output"];
    location = json["location"];
    return this;
  }
}

class ProfileSectionItem {
  String? code;
  String? name;
  String? desc;
  String? icon;

  ProfileSectionItem parse(Map<String, dynamic> json) {
    code = json["code"];
    name = json["name"];
    desc = json["desc"];
    icon = json["icon"];
    return this;
  }
}

class ProfileSection {
  String? type;
  String? id;
  String? title;
  String? subtitle;
  String? content;
  String? quote;
  String? vision;
  List<String> missions = [];
  List<ProfileSectionItem> items = [];

  ProfileSection parse(Map<String, dynamic> json) {
    type = json["type"];
    id = json["id"];
    title = json["title"];
    subtitle = json["subtitle"];
    content = json["content"];
    quote = json["quote"];
    vision = json["vision"];
    missions = json["missions"] != null
        ? List<String>.from(json["missions"])
        : [];
    items = json["items"] != null
        ? List<ProfileSectionItem>.from(
            (json["items"] as List).map(
              (x) => ProfileSectionItem().parse(x as Map<String, dynamic>),
            ),
          )
        : [];
    return this;
  }
}

class CompanyContact {
  String? corporateOffice;
  String? operationalOffice;
  String? email;
  String? workHours;
  String? googleMapsUrl;

  CompanyContact parse(Map<String, dynamic> json) {
    corporateOffice = json["corporate_office"];
    operationalOffice = json["operational_office"];
    email = json["email"];
    workHours = json["work_hours"];
    googleMapsUrl = json["google_maps_url"];
    return this;
  }
}

class CompanyProfile {
  String? companyName;
  String? shortName;
  String? groupName;
  String? taglineId;
  String? taglineEn;
  String? foundedYear;
  String? kantorOperasional;
  String? headquarters;
  List<CompanyStat> stats = [];
  AboutGroup? aboutGroup;
  List<EcosystemEntity> entities = [];
  List<ProfileSection> sections = [];
  CompanyContact? contact;

  CompanyProfile parse(Map<String, dynamic> json) {
    companyName = json["company_name"];
    shortName = json["short_name"];
    groupName = json["group_name"];
    taglineId = json["tagline_id"];
    taglineEn = json["tagline_en"];
    foundedYear = json["founded_year"];
    kantorOperasional = json["kantor_operasional"];
    headquarters = json["headquarters"];

    stats = json["stats"] != null
        ? List<CompanyStat>.from(
            (json["stats"] as List).map(
              (x) => CompanyStat().parse(x as Map<String, dynamic>),
            ),
          )
        : [];

    if (json["about_group"] != null) {
      aboutGroup = AboutGroup().parse(
        json["about_group"] as Map<String, dynamic>,
      );
    }

    entities = json["entities"] != null
        ? List<EcosystemEntity>.from(
            (json["entities"] as List).map(
              (x) => EcosystemEntity().parse(x as Map<String, dynamic>),
            ),
          )
        : [];

    sections = json["sections"] != null
        ? List<ProfileSection>.from(
            (json["sections"] as List).map(
              (x) => ProfileSection().parse(x as Map<String, dynamic>),
            ),
          )
        : [];

    if (json["contact"] != null) {
      contact = CompanyContact().parse(
        json["contact"] as Map<String, dynamic>,
      );
    }

    return this;
  }
}
