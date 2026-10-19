abstract class ProjectDetailEvent {}

class ProjectDetailLoad extends ProjectDetailEvent {
  final String id;

  ProjectDetailLoad({
    required this.id,
  });
}
