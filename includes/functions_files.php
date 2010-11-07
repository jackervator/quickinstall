<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
 * Useful class for directory and file actions
 */
class file_functions
{
	public static function delete_file($file)
	{
		return unlink($file);
	}

	public static function copy_file($src_file, $dst_file)
	{
		return copy($src_file, $dst_file);
	}

	public static function move_file($src_file, $dst_file)
	{
		self::copy_file($src_file, $dst_file);
		self::delete_file($src_file);
	}

	public static function copy_dir($src_dir, $dst_dir)
	{
		self::append_slash($src_dir);
		self::append_slash($dst_dir);

		if (!is_dir($dst_dir))
		{
			mkdir($dst_dir);
		}

		foreach (scandir($src_dir) as $file)
		{
			if (in_array($file, array('.', '..'), true))
			{
				continue;
			}

			$src_file = $src_dir . $file;
			$dst_file = $dst_dir . $file;

			if (is_file($src_file))
			{
				if (is_file($dst_file))
				{
					$ow = filemtime($src_file) - filemtime($dst_file);
				}
				else
				{
					$ow = 1;
				}

				if ($ow > 0)
				{
					if (copy($src_file, $dst_file))
					{
						touch($dst_file, filemtime($src_file));
					}
				}
			}
			else if (is_dir($src_file))
			{
				self::copy_dir($src_file, $dst_file);
			}
		}
	}

	public static function delete_dir($dir, $empty = false)
	{
		self::append_slash($dir);

		if (!file_exists($dir) || !is_dir($dir) || !is_readable($dir))
		{
			return false;
		}

		foreach (scandir($dir) as $file)
		{
			if (in_array($file, array('.', '..'), true))
			{
				continue;
			}

			if (is_dir($dir . $file))
			{
				self::delete_dir($dir . $file);
			}
			else
			{
				self::delete_file($dir . $file);
			}
		}

		if (!$empty)
		{
			@rmdir($dir);
		}
	}

	public static function delete_files($dir, $files_ary, $recursive = true)
	{
		self::append_slash($dir);

		foreach (scandir($dir) as $file)
		{
			if (in_array($file, array('.', '..'), true))
			{
				continue;
			}

			if (is_dir($dir . $file))
			{
				if ($recursive)
				{
					self::delete_files($dir . $file, $files_ary, true);
				}
			}

			if (in_array($file, $files_ary, true))
			{
				if (is_dir($dir . $file))
				{
					self::delete_dir($dir . $file);
				}
				else
				{
					self::delete_file($dir . $file);
				}
			}
		}
	}

	public static function append_slash(&$dir)
	{
		if ($dir[strlen($dir) - 1] != '/')
		{
			$dir .= '/';
		}
	}

	/**
	 * Recursive make all files and directories world writable.
	 */
	public static function make_writable($dir, $root = true)
	{
		global $phpEx;

		$file_arr = scandir($dir);
		$dir .= '/';

		foreach ($file_arr as $file)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			if ($root && $file == 'config.' . $phpEx)
			{
				chmod($dir . $file, 0644);
				continue;
			}

			$file = $dir . $file;

			if (is_file($file))
			{
				chmod($file, 0666);
			}
			else
			{
				chmod($file, 0777);
				self::make_writable($file, false);
			}
		}

	}
}

?>