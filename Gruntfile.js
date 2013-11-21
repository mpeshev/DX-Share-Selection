module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
      uglify: {
        options: {
          mangle: false
        },
        my_target: {
          files: {
            'dxss/jquery.selected-text-sharer.min.js': ['dxss/dev/jquery.selected-text-sharer.js'],
            'dxss/selected-text-sharer.min.js': ['dxss/dev/selected-text-sharer.js']
          }
        }
      }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['uglify']);
}
