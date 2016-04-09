<?php

namespace Fewlines\Core\Helper;

class FileHelper
{
	/**
	 * @param string $tmpName
	 * @param string $uploadPath
	 * @param string $name
	 * @return boolean
	 */
	public static function moveUploadedFile($tmpName, $destination, $name) {
		return move_uploaded_file($tmpName, $destination . DR_SP . $name);
	}

	/**
	 * @param string $destination
	 * @param string $name
	 */
	public static function getUploadedFile($destination, $name) {
		return $destination . DR_SP . $name;
	}

	/**
	 * @param string $file
	 */
	public static function remove($file) {
		return unlink($file);
	}
}