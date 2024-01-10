<?php
function add2zip($zip, $dir, $cwd, $type, $level)
{
	$basedir = [];
	$nodes = explode('/', trim(str_replace('\\', '/', $dir), '/'));
	krsort($nodes);
	foreach($nodes as $node)
	{
		if(count($basedir) >= $level) break;
		$basedir[] = $node;
	}
	krsort($basedir);
	$basedir = implode('/', $basedir);

	if ($handle = opendir($dir))
	{
		while (false !== ($entry = readdir($handle)))
		{
			switch($entry)
			{
				case '.':
				case '..':
				case '.git':
				case '.idea':
				case '_':
					continue 2;
				case 'extensions.txt';
					if ($level) break;
					if ($type != 'pkg') break;

					$extensions = file_get_contents($entry);
					if (empty($extensions)) continue 2;

					foreach(explode("\n", $extensions) as $extension)
					{
						$extension = trim($extension);
						if (empty($extension)) continue;
						if ($extension[0] == '#') continue;

						$srcdir = dirname($cwd, 2) . '/' . dirname($extension, 2);
						chdir($srcdir);

						add2zip($zip,
							dirname($dir, 2) . '/' . $extension,
							$basedir . '/extensions/' . basename($extension),
							$type, 2);
					}

					chdir($cwd);

					continue 2;
				default:
					break;
			}

			// In case .doc is open for editting
			if (str_starts_with($entry, '.~lock')) continue;

			switch(pathinfo($entry, PATHINFO_EXTENSION))
			{
				case 'doc':
				case 'docx':
				case 'md':
				case 'odt':
					continue 2;
				case 'zip':
					if ($level) break;
					if ($type != 'pkg') break;
					continue 2;
			}

			switch($type . '.' . $level)
			{
				case 'pkg.0':
					if ($entry[0] === '_')
					{
						// Ignore top-level files / folders with _ prefix
						continue 2;
					}
					break;
			}

			if (empty($dir))
			{
				$fullpath = $entry;
			}
			else
			{
				$fullpath = $dir . '/' . $entry;
			}

			if (is_dir($fullpath))
			{
				add2zip($zip, $fullpath, $cwd, $type, $level+1);
				continue;
			}

			$x = getcwd();
			$zip->addFile(trim($basedir . '/' . $entry, '/'));
		}
		closedir($handle);
	}
}

function process($type, $zipLevel=1)
{
	$cwd = getcwd();

	$zipfile = dirname($cwd, $zipLevel) . '/' . basename($cwd) . '.zip';
	if (is_file($zipfile)) unlink($zipfile);

	switch($type)
	{
		case 'com':
		case 'lib':
		case 'mod':
		case 'pkg':
		case 'plg':
		case 'tpl':
			$zip = new ZipArchive();
			if ($zip->open($zipfile, ZipArchive::CREATE) !== false)
			{
				add2zip($zip, $cwd, $cwd, $type, 0);

				$zip->close();
			}
			break;
		default:
			die($type . ' : This extension type is not supported');
	}

	if (file_exists($zipfile))
	{
		echo "\nCreated : ${zipfile}\n";
		echo "\nFile size : " . filesize($zipfile) . "\n";
		return true;
	}
	else
	{
		echo "\nFailed : " . $zipfile . "\n";
		return false;
	}
}

if (count($argv) > 1)
{
	chdir($argv[1]);
}

$cwd = getcwd();
if (strpos($cwd, 'htdocs') !== false) die('Not a Joomla extension project.');

$parts = explode('_', basename($cwd, 2));
if ($parts < 2) die('Invalid extension folder');
$type = $parts[0];

if (!process($type))
{
	exit(1);
}

if ($type == 'pkg' &&
	is_dir('extensions'))
{
	chdir('extensions');

	if ($handle = opendir('.'))
	{
		while (false !== ($entry = readdir($handle)))
		{
			if (!is_dir($entry)) continue;

			$parts = explode('_', $entry);
			if (count($parts) < 2) continue;
			if (empty($parts[0])) continue;

			chdir($entry);
			process($parts[0], 2);
			chdir('..');
		}
	}
}

