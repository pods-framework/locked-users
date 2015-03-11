module.exports = function (grunt) {

	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-git' );
	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-svn-checkout' );
	grunt.loadNpmTasks( 'grunt-push-svn' );
	grunt.loadNpmTasks( 'grunt-remove' );

	copy_files = [
		'**',
		'!node_modules/**',
		'!release/**',
		'!.git/**',
		'!.sass-cache/**',
		'!Gruntfile.js',
		'!package.json',
		'!.gitignore',
		'!.gitmodules'
	];

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		clean: {
			post_build: [
				'.build',
				'release/<%= pkg.name %>',
				'release/build/<%= pkg.version %>',
				'release/build/'
			]
		},
		copy: {
			main: {
				src:  copy_files,
				dest: 'release/build/<%= pkg.version %>/'
			},
			svn_trunk: {
				options : {
					mode :true
				},
				src: copy_files,
				dest: 'release/<%= pkg.name %>/trunk/'
			},
			svn_tag: {
				options : {
					mode :true
				},
				src: copy_files,
				dest: 'release/<%= pkg.name %>/tags/<%= pkg.version %>/'
			}
		},
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/build/<%= pkg.version %>/',
				src: [ '**/*' ],
				dest: '<%= pkg.name %>-<%= pkg.version %>/'
			}
		},
		gittag: {
			addtag: {
				options: {
					tag: '<%= pkg.version %>',
					message: 'Version <%= pkg.version %>'
				}
			}
		},
		gitcommit: {
			commit: {
				options: {
					message: 'Version <%= pkg.version %>',
					noVerify: true,
					noStatus: false,
					allowEmpty: true
				},
				files: {
					src: [ 'locked-users.php', 'readme.txt', 'README.md', 'package.json' ]
				}
			}
		},
		gitpush: {
			push: {
				options: {
					tags: true,
					remote: 'origin',
					branch: 'master'
				}
			}
		},
		replace: {
			version: {
				src: [ 'locked-users.php', 'readme.txt', 'README.md' ],
				overwrite: true,
				replacements: [{
					from: "<%= pkg.last_version %>",
					to: "<%= pkg.version %>"
				}]
			}
		},
		svn_checkout: {
			make_local: {
				repos: [
					{
						path: [ 'release' ],
						repo: 'http://plugins.svn.wordpress.org/locked-users'
					}
				]
			}
		},
		push_svn: {
			options: {
				remove: true,

			},
			main: {
				src: 'release/<%= pkg.name %>',
				dest: 'http://plugins.svn.wordpress.org/locked-users',
				tmp: './.build'
			}
		}
	});

	grunt.registerTask( 'pre_vcs', [ 'replace:version', 'copy:main', 'compress' ] );
	grunt.registerTask( 'do_svn', [ 'svn_checkout', 'copy:svn_trunk', 'copy:svn_tag', 'push_svn' ] );
	grunt.registerTask( 'do_git', [ 'gitcommit', 'gittag', 'gitpush' ] );

	grunt.registerTask( 'release', [ 'pre_vcs', 'do_svn', 'do_git', 'clean:post_build' ] );
	grunt.registerTask( 'just_build', [ 'pre_vcs',  'clean:post_build' ] );

};
