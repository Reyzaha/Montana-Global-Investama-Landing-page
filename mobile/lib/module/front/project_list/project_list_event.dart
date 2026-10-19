abstract class ProjectListEvent {}

class ProjectListLoad extends ProjectListEvent {
  final String? city;

  ProjectListLoad({
    this.city,
  });
}
