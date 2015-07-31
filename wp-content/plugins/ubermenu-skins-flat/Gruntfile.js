module.exports = function(grunt) {


  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    less: {
      development: {
        options: {
          compress: false,
        },
        files: [
          {
            expand: true,
            cwd: 'skins/',
            src: ['*.less'],
            dest: 'skins/',
            ext: '.css'
            // target.css file: source.less file
            //"pro/assets/css/skins/blackwhite2.css": "pro/assets/css/skins/blackwhite2.less"
          }
        ]
      }
    }

  });

  grunt.loadNpmTasks('grunt-contrib-less');
  

  // Default task(s).
  grunt.registerTask('default', ['less']);

  //grunt.registerTask('css', ['less','cssmin']);

};