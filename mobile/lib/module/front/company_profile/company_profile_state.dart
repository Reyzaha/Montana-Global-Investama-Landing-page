import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";

abstract class CompanyProfileState {}

class CompanyProfileInitial extends CompanyProfileState {}

class CompanyProfileLoadLoading extends CompanyProfileState {}

class CompanyProfileLoadSuccess extends CompanyProfileState {
  final CompanyProfile? profile;

  CompanyProfileLoadSuccess({
    required this.profile,
  });
}

class CompanyProfileLoadFinished extends CompanyProfileState {}
