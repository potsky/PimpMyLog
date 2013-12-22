module.exports = function(grunt) {
	// Load all NPM grunt tasks
	require('matchdep').filterAll('grunt-*').forEach( grunt.loadNpmTasks );

	var master_dir  = '/tmp';
	var master_name = 'PimpMyLog-master';
	var master      = master_dir + '/' + master_name;
	var beta_dir    = '/tmp';
	var beta_name   = 'PimpMyLog-beta';
	var beta        = beta_dir + '/' + beta_name;

	// Project configuration
	grunt.initConfig({

		// Clean target directories
		clean: {
			all  : [ '_build' , '_site' , '_tmp' ],
			dev  : [ '_site' ],
			prod : [ '_build' , '_tmp' ]
		},

		// Concat
		concat: {
			js: {
				src: [
					'js/vendor/modernizr-2.6.2-respond-1.1.0.min.js',
					'js/vendor/jquery-1.10.1.min.js',
					'js/vendor/jquery.cookie.js',
					'bower_components/bootstrap/js/affix.js',
					'bower_components/bootstrap/js/alert.js',
					'bower_components/bootstrap/js/button.js',
					'bower_components/bootstrap/js/carousel.js',
					'bower_components/bootstrap/js/collapse.js',
					'bower_components/bootstrap/js/dropdown.js',
					'bower_components/bootstrap/js/modal.js',
					'bower_components/bootstrap/js/scrollspy.js',
					'bower_components/bootstrap/js/tab.js',
					'bower_components/bootstrap/js/tooltip.js',
					'bower_components/bootstrap/js/popover.js',
					'bower_components/bootstrap/js/transition.js',
					'js/vendor/ua-parser.min.js',
					'js/vendor/Hook-js/mousewheel.js',
					'js/vendor/Hook-js/hook.min.js',
					'js/vendor/Numeral-js/min/numeral.min.js'
				],
				dest: '_tmp/pml.js',
				options: {
					separator: ';'
				}
			},
			css: {
				src: [
					'_tmp/main.css',
					'js/vendor/Hook-js/hook.css',
				],
				dest: '_tmp/pml.css'
			}
		},

		copy: {
			bsfoots: {
				files: [{
					expand: true,
					flatten: true,
					src: ['bower_components/bootstrap/dist/fonts/*'],
					dest: 'fonts/',
					filter: 'isFile'
				}]
			},
			dev: {
				files: [{
					expand: true,
					src: [
						'cfg/**',
						'fonts/**',
						'img/**',
						'inc/**',
						'js/**',
						'lang/**',
						'*.json',
						'*.txt',
						'*.php'
					],
					dest: '_site/'
				}]
			},
			devcss: {
				files: [{
					expand: true,
					flatten: true,
					filter: 'isFile',
					src: [ '_tmp/main.css' , 'css/config.inc.css' ],
					dest: '_site/css/'
				}]
			},
			devphp: {
				files: [{
					expand: true,
					src: [ 'version.json' , '*.php' , 'inc/*', 'cfg/*' ],
					dest: '_site/'
				}]
			},
			devjs: {
				files: [{
					expand: true,
					flatten: true,
					filter: 'isFile',
					src: [ 'js/**.js' ],
					dest: '_site/js/'
				}]
			},
			installmaster: {
				files: [{
					expand: true,
					cwd: '_build/',
					src: [ '**' ],
					dest: master
				}]
			},
			installmasterREADME: {
				files: [{
					expand: true,
					cwd: '_tools/',
					src: [ 'README.md' ],
					dest: master
				}]
			},
			installbeta: {
				files: [{
					expand: true,
					cwd: '_build/',
					src: [ '**' ],
					dest: beta
				}]
			},
			installbetaREADME: {
				files: [{
					expand: true,
					cwd: '_tools/',
					src: [ 'README.md' ],
					dest: beta
				}]
			},
			prod: {
				files: [{
					expand: true,
					src: [
						'cfg/**',
						'css/*.css',
						'fonts/**',
						'img/**',
						'inc/**',
						'lang/**',
						'version.json',
						'config.user.json',
						'version.txt',
						'*.php'
					],
					dest: '_build/'
				}]
			},
			prodindex: {
				src: '_build/index.php',
				dest: '_tmp/index.php'
			},
			prodcssimg: {
				expand: true,
				flatten: true,
				src: [
					'js/vendor/Hook-js/*.png',
					'js/vendor/Hook-js/*.gif'
				],
				dest: '_build/css/',
				filter: 'isFile'
			}
		},

		// Minify CSS
		cssmin: {
			minify: {
				files: {
					'_build/css/pml.min.css': [ '_tmp/pml.css' ]
				}
			}
		},

		// Minify HTML
		htmlmin: {
			prod: {
				options: {
					removeComments: true,
					collapseWhitespace: true
				},
				files: [{
					src: [ '<%= copy.prodindex.dest %>' ],
					dest: '_build/index.php',
				}]
			}
		},

		// LESS files to CSS
		less: {
			main: {
				files: {
					"_tmp/main.css" : ["css/main.less"]
				}
			}
		},

		// Make pkg available
		pkg: grunt.file.readJSON('package.json'),

		// Prod <> Dev
		preprocess : {
			dev : {
				src  : [
					'_site/*.php' ,
					'_site/inc/*.php'
				],
				options : {
					inline : true,
					context: {
						prod:'dev'
					}
				}
			},
			prod : {
				src  : [
					'_build/*.php' ,
					'_build/inc/*.php'
				],
				options : {
					inline : true,
					context: {
						prod:'prod'
					}
				}
			}
		},


		shell: {
			betagitclone: {
				command: 'cd "' + beta_dir + '" && git clone git@github.com:potsky/PimpMyLog.git -b beta "' + beta_name + '"',
				options: {
					stdout: true
				}
			},
			betagitremove: {
				command: 'cd "' + beta + '" && git rm -rf * ',
				options: {
					stdout: true
				}
			},
			betagitaddcommitpush : {
				command: 'a=$(git rev-parse --short HEAD); cd "' + beta + '"; rm -f config.user.json; git add -A . 2>&1; git commit -m "grunt install from branch dev commit $a" 2>&1; git pull origin beta 2>&1; git push origin beta 2>&1',
				options: {
					stdout: true
				}
			},
			mastergitclone: {
				command: 'cd "' + master_dir + '" && git clone git@github.com:potsky/PimpMyLog.git -b master "' + master_name + '"',
				options: {
					stdout: true
				}
			},
			mastergitremove: {
				command: 'cd "' + master + '" && git rm -rf * ',
				options: {
					stdout: true
				}
			},
			mastergitaddcommitpush : {
				command: 'a=$(git rev-parse --short HEAD); cd "' + master + '"; rm -f config.user.json; git add -A . 2>&1; git commit -m "grunt install from branch dev commit $a" 2>&1; git pull origin master 2>&1; git push origin master 2>&1',
				options: {
					stdout: true
				}
			},
			devaddcommitpush : {
				command: 'git add -A . 2>&1; git commit --author=\'Potsky <potsky@me.com>\' -m "prepare to publish on master or beta" 2>&1; git pull origin dev 2>&1; git push origin dev 2>&1',
				options: {
					stdout: true
				}
			}
		},

		// Minify JS
		uglify: {
			options: {
				// the banner is inserted at the top of the output
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			dist: {
				files: {
					'_build/js/pml.min.js': ['<%= concat.js.dest %>'],
					'_build/js/main.min.js': ['js/main.js'],
					'_build/js/test.min.js': ['js/test.js'],
					'_build/js/configure.min.js': ['js/configure.js'],
				}
			}
		},

		// Watch files for changes in dev
		watch: {
			css: {
				files: [ '_css/**/*.css' , '_tmp/**/*.css' ],
				tasks: [ 'copy:devcss' ]
			},
			cssless: {
				files: [ '_css/**/*.less' ],
				tasks: [ 'less' , 'copy:devcss' ]
			},
			html: {
				files: [ 'version.json' , '*.php' , 'inc/*', 'cfg/*' ],
				tasks: [ 'copy:devphp' , 'preprocess:dev' ]
			},
			js: {
				files: [ 'js/**/*.js' ],
				tasks: [ 'copy:devjs' ]
			}
		}
	});

	// Installation task which install the _build folder in beta , commit and push
	grunt.registerTask( 'installbeta' , function() {
		if ( grunt.file.exists( '_build/index.php' ) === false ) {
			grunt.verbose.or.error().error( 'File "_build/index.php" does not exist. Please build before installing with "grunt build"' );
			grunt.fail.warn('Unable to continue');
		}
		else if ( grunt.file.exists( beta + '/.git/config' ) === false ) {
			grunt.log.writeln('Cloning and installing in ' + beta );
			grunt.task.run(['shell:betagitclone']);
			grunt.task.run([
				'shell:devaddcommitpush',
				'shell:betagitremove',
				'copy:installbeta',
				'copy:installbetaREADME',
				'shell:betagitaddcommitpush'
			]);
		}
		else {
			grunt.log.writeln('Installing in ' + beta );
			grunt.task.run([
				'shell:devaddcommitpush',
				'shell:betagitremove',
				'copy:installbeta',
				'copy:installbetaREADME',
				'shell:betagitaddcommitpush'
			]);
		}
	});

	// Installation task which install the _build folder in master , commit and push
	grunt.registerTask( 'installmaster' , function() {
		if ( grunt.file.exists( '_build/index.php' ) === false ) {
			grunt.verbose.or.error().error( 'File "_build/index.php" does not exist. Please build before installing with "grunt build"' );
			grunt.fail.warn('Unable to continue');
		}
		else if ( grunt.file.exists( master + '/.git/config' ) === false ) {
			grunt.log.writeln('Cloning and installing in ' + master );
			grunt.task.run(['shell:mastergitclone']);
			grunt.task.run([
				'shell:devaddcommitpush',
				'shell:mastergitremove',
				'copy:installmaster',
				'copy:installmasterREADME',
				'shell:mastergitaddcommitpush'
			]);
		}
		else {
			grunt.log.writeln('Installing in ' + master );
			grunt.task.run([
				'shell:devaddcommitpush',
				'shell:mastergitremove',
				'copy:installmaster',
				'copy:installmasterREADME',
				'shell:mastergitaddcommitpush'
			]);
		}
	});

	// Development task, build and watch for file modification
	grunt.registerTask( 'dev' , function() {
		grunt.task.run([
			'clean:dev',
			'copy:bsfoots',
			'copy:dev',
			'less',
			'copy:devcss',
			'preprocess:dev'
		]);
		grunt.task.run('watch');
	});

	// Build task for production
	grunt.registerTask( 'prod' , function() {
		grunt.task.run([
			'clean:prod',
			'copy:bsfoots',
			'copy:prod',
			'copy:prodcssimg',
			'less',
			'concat:css',
			'cssmin',
			'concat:js',
			'uglify',
			'preprocess:prod',
			'copy:prodindex',
			'htmlmin'
		]);
	});

	grunt.registerTask('build', function() {
		grunt.task.run( 'prod' );
	});

	grunt.registerTask('install', function() {
		grunt.task.run( 'installbeta' );
	});

	grunt.registerTask('install-production', function() {
		grunt.task.run( 'installmaster' );
	});

	grunt.registerTask('default', [ 'dev' ] );

};
