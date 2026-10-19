abstract class NewsEvent {}

class NewsLoad extends NewsEvent {
  final String? category;

  NewsLoad({
    this.category,
  });
}
