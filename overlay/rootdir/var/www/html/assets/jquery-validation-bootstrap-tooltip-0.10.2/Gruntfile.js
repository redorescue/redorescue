module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    bump: {
       options: {
         files: ['package.json', 'bower.json'],
         updateConfigs: ['pkg'],
         commitFiles: ['-a'],
         pushTo: 'origin',
       },
     },
    uglify: {
      my_target: {
        options: {
          preserveComments: 'some',
        },
        files: {
          'jquery-validate.bootstrap-tooltip.min.js': ['jquery-validate.bootstrap-tooltip.js'],
        },
      },
    },
  });

  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-bump');

  //TODO: automate this by replacing old version in js comment.  String replace
  //grunt.registerTask('default', ['bump-only:minor', 'uglify', ???, 'bump-commit']);

};
