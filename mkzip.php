<?php
function add2zip($zip, $dir, $cwd)
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
			}

			switch(pathinfo($entry, PATHINFO_EXTENSION))
			{
				case 'doc':
				case 'md':
					continue 2;
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
				add2zip($zip, $fullpath, $cwd);
				continue;
			}

			$zip->addFile(trim($basedir . '/' . $entry, '/'));
		}
		closedir($handle);
	}
}

$cwd = getcwd();
if (strpos($cwd, 'htdocs') !== false) die('Not a Joomla extension project.');

$zipfile = dirname($cwd) . '/' . basename($cwd) . '.zip';
if (is_file($zipfile)) unlink($zipfile);

$zip = new ZipArchive();
if ($zip->open($zipfile, ZipArchive::CREATE) !== false)
{
	add2zip($zip, $cwd, $cwd);

	$zip->close();
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

