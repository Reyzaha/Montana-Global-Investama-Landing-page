allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}
subprojects {
    project.evaluationDependsOn(":app")

    pluginManager.withPlugin("com.android.library") {
        project.dependencies.add("implementation", "androidx.concurrent:concurrent-futures:1.2.0")
    }

    if (project.name != "app") {
        afterEvaluate {
            project.extensions.findByName("android")?.let { android ->
                val clazz = android.javaClass
                for (method in clazz.methods) {
                    if (method.name in listOf("compileSdkVersion", "setCompileSdkVersion", "setCompileSdk")) {
                        val params = method.parameterTypes
                        if (params.size == 1) {
                            if (params[0] == Int::class.javaPrimitiveType || params[0] == java.lang.Integer::class.java) {
                                try {
                                    method.invoke(android, 36)
                                    break
                                } catch (_: Exception) {}
                            } else if (params[0] == String::class.java) {
                                try {
                                    method.invoke(android, "android-36")
                                    break
                                } catch (_: Exception) {}
                            }
                        }
                    }
                }
            }
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
