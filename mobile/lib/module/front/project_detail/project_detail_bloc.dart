// ignore_for_file: always_specify_types

import "package:base/base.dart";
import "package:dio/dio.dart";
import "package:easy_localization/easy_localization.dart";
import "package:flutter/foundation.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/api/api_manager.dart";
import "package:mgi_investor/api/endpoint/project/project.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_event.dart";
import "package:mgi_investor/module/front/project_detail/project_detail_state.dart";

class ProjectDetailBloc
    extends Bloc<ProjectDetailEvent, ProjectDetailState> {
  ProjectDetailBloc() : super(ProjectDetailInitial()) {
    on<ProjectDetailLoad>((event, emit) async {
      try {
        emit(ProjectDetailLoadLoading());

        final Response response = await ApiManager.projectDetail(id: event.id);

        if (response.statusCode == 200 && response.data is Map<String, dynamic>) {
          Project? single;
          final Map<String, dynamic> raw =
              response.data as Map<String, dynamic>;
          final Map<String, dynamic> data =
              raw["data"] is Map<String, dynamic>
                  ? raw["data"] as Map<String, dynamic>
                  : raw;

          if (data["project"] != null && data["project"] is Map<String, dynamic>) {
            single = Project().parse(data["project"] as Map<String, dynamic>);
          } else if (data["projects"] != null && data["projects"] is List) {
            final list = (data["projects"] as List)
                .map((x) => Project().parse(x as Map<String, dynamic>))
                .toList();
            single = list.firstWhere(
              (p) => p.id == event.id,
              orElse: () => list.first,
            );
          }

          emit(ProjectDetailLoadSuccess(project: single));
        } else {
          emit(ProjectDetailLoadSuccess(project: null));
        }
      } catch (e) {
        if (kDebugMode) {
          print(e);
        }
        BaseOverlays.error(message: "something_wrong_please_try_again".tr());
      } finally {
        emit(ProjectDetailLoadFinished());
      }
    });
  }
}
