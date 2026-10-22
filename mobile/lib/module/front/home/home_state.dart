import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/company_profile/company_profile.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";

abstract class HomeState {}

class HomeInitial extends HomeState {}

class HomeLoadLoading extends HomeState {}

class HomeLoadSuccess extends HomeState {
  final CompanyProfile? companyProfile;
  final List<Project> featuredProjects;
  final List<City> cities;

  final String? disclaimerText;

  HomeLoadSuccess({
    required this.companyProfile,
    required this.featuredProjects,
    required this.cities,
    this.disclaimerText,
  });
}

class HomeLoadFinished extends HomeState {}
