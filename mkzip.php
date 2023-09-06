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

