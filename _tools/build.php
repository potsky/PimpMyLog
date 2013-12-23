<?php
header("Content-Type: text/html");
?>
<html>
	<head>
		<style>
		body {
			background-color:#333;
			color: #f0f0f0;
			-webkit-hyphens: auto;
			-moz-hyphens: auto;
			hyphens: auto;
		}
		pre {
			display: block;
			width: auto;
			overflow: auto;
			-webkit-overflow-scrolling: touch;
			white-space: pre;
			word-wrap: normal;
		}
		pre code {
			font-family:Monaco;
			-webkit-hyphens: none!important;
			-moz-hyphens: none!important;
			hyphens: none!important;
			white-space: inherit;
		}
		.ok {
			color:#2f6;
		}
		.notice {
			color:#8af;
		}
		.error {
			color:#f24;
		}
		.black {
			color:#000;
		}
		.brown {
			color:#f60;
		}
		.blue {
			color:#08f;
		}
		.purple {
			color:#f0f;
		}
		.gray {
			color:#aaa;
		}
		</style>
	</head>
	<body>
		<pre><code><?php
		# sudoer
		# %_www   ALL=(potsky) NOPASSWD: /usr/local/bin/grunt
		echo str_replace(
			array(
				'[4m',
				'[24m',
				'[30m',
				'[31m',
				'[32m',
				'[33m',
				'[34m',
				'[35m',
				'[36m',
				'[37m',
				'[39m',
			),
			array(
				'<u>',
				'</u>',
				'<span class="black">',				
				'<span class="error">',
				'<span class="ok">',
				'<span class="brown">',				
				'<span class="blue">',				
				'<span class="purple">',				
				'<span class="notice">',
				'<span class="gray">',				
				'</span>',
			),
			shell_exec('cd ..; PATH=/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin:/opt/local/bin; export PATH; . ../../../.profile; sudo -u potsky /usr/local/bin/grunt build 2>&1')
		);
		?></code></pre>
	</body>
</html>


