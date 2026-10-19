import "package:mgi_investor/api/endpoint/city/city.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";

abstract class ProjectListState {}

class ProjectListInitial extends ProjectListState {}

class ProjectListLoadLoading extends ProjectListState {}

class ProjectListLoadSuccess extends ProjectListState {
  final List<Project> projects;
  final List<City> cities;
  final String selectedCity;

  ProjectListLoadSuccess({
    required this.projects,
    required this.cities,
    required this.selectedCity,
  });
}

class ProjectListLoadFinished extends ProjectListState {}
