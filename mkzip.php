<?php
function add2zip($zip, $dir, $cwd, $type, $level)
{
	$basedir = trim(substr($dir, strlen($cwd)), '/');

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
				default:
					break;
			}

			switch(pathinfo($entry, PATHINFO_EXTENSION))
			{
				case 'doc':
				case 'docx':
				case 'md':
				case 'odt':
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

			$zip->addFile(trim($basedir . '/' . $entry, '/'));
		}
		closedir($handle);
	}
}

if (count($argv) > 1)
{
	chdir($argv[1]);
}

$cwd = getcwd();
if (strpos($cwd, 'htdocs') !== false) die('Not a Joomla extension project.');

$zipfile = dirname($cwd) . '/' . basename($cwd) . '.zip';
if (is_file($zipfile)) unlink($zipfile);

$parts = explode('_', basename($cwd, 2));
if ($parts < 2) die('Invalid extension folder');

switch($parts[0])
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
			add2zip($zip, $cwd, $cwd, $parts[0], 0);

			$zip->close();
		}
		break;
	default:
		die($parts[0] . ' : This extension type is not supported');
}

if (file_exists($zipfile))
{
	echo "\nCreated : ${zipfile}\n";
	echo "\nFile size : " . filesize($zipfile) . "\n";
}
else
{
	echo "\nFailed : " . $zipfile . "\n";
}

