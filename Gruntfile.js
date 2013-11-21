module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
      uglify: {
        options: {
          mangle: false
        },
        my_target: {
          files: {
            'wpsts/jquery.selected-text-sharer.min.js': ['wpsts/dev/jquery.selected-text-sharer.js']
          }
        }
      }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['uglify']);
}
