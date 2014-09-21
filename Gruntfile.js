module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            options: {
                separator: ';'
            },
            vendor: {
                src: ['vendor/components/jquery/jquery.min.js', 'vendor/components/jqueryui/jquery-ui.min.js'],
                dest: 'js/dist/vendor.js'
            },
            dist: {
                src: 'js/src/**/*.js',
                dest: 'js/dist/<%= pkg.name %>.js'
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n',
                mangle: true
            },
            dist: {
                files: {
                    '<%= concat.dist.dest %>': ['<%= concat.dist.dest %>']
                }
            }
        },
        dust: {
            defaults: {
                files: {
                    "js/dist/templates.js": "templates/*.dust"
                }
            }
        },
        watch: {
            files: ['<%= concat.vendor.src %>', '<%= concat.dist.src %>', 'templates/*.dust'],
            tasks: ['concat', 'uglify', 'dust']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-dust');

    grunt.registerTask('default', ['concat', 'uglify', 'dust', 'watch']);

};