abstract class ConsultationState {}

class ConsultationInitial extends ConsultationState {}

class ConsultationSubmitLoading extends ConsultationState {}

class ConsultationSubmitSuccess extends ConsultationState {
  final String message;

  ConsultationSubmitSuccess({
    required this.message,
  });
}

class ConsultationSubmitFinished extends ConsultationState {}
