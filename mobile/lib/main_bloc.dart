// ignore_for_file: always_specify_types

import "dart:async";

import "package:flutter/material.dart";
import "package:flutter_bloc/flutter_bloc.dart";
import "package:mgi_investor/constant/preference_key.dart";
import "package:mgi_investor/helper/preferences.dart";
import "package:mgi_investor/main_event.dart";
import "package:mgi_investor/main_state.dart";

class MainBloc extends Bloc<MainEvent, MainState> {
  MainBloc() : super(MainState(themeMode: ThemeMode.light)) {
    Preferences.setInt(PreferenceKey.THEME_MODE, ThemeMode.light.index);
    on<MainThemeChanged>((event, emit) async {
      await Preferences.setInt(PreferenceKey.THEME_MODE, ThemeMode.light.index);
      await doIt(emit);
    });
  }

  FutureOr<void> doIt(Emitter<MainState> emit) {
    emit(MainState(themeMode: ThemeMode.light));
  }
}
