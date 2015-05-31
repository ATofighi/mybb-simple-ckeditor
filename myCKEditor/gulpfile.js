var gulp = require("gulp");

var editorPath = {
	src: {
		bower: "./bower_components/ckeditor",
		mybb: "./mybb-ckeditor"
	},
	dest: "./ckeditor"
};

var editor = {
	bower: [
		"./bower_components/ckeditor/**/*.js",
		"./bower_components/ckeditor/**/*.css",
		"./bower_components/ckeditor/**/*.png"
	],
	mybb: [
		"./mybb-ckeditor/**/*.js",
		"./mybb-ckeditor/**/*.css",
		"./mybb-ckeditor/**/*.png"
	]
};

gulp.task("default", ["copyEditorFiles"]);

gulp.task("copyEditorFiles", function () {
	gulp.src(editor.bower, { base: editorPath.src.bower })
		.pipe(gulp.dest(editorPath.dest));
	return gulp.src(editor.mybb, { base: editorPath.src.mybb })
		.pipe(gulp.dest(editorPath.dest));
});