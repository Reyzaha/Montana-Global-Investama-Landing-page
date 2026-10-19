import "package:mgi_investor/api/endpoint/project/project.dart";

abstract class ProjectDetailState {}

class ProjectDetailInitial extends ProjectDetailState {}

class ProjectDetailLoadLoading extends ProjectDetailState {}

class ProjectDetailLoadSuccess extends ProjectDetailState {
  final Project? project;

  ProjectDetailLoadSuccess({
    required this.project,
  });
}

class ProjectDetailLoadFinished extends ProjectDetailState {}
