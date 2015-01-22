<?php
/**
 * Upgrader that replaces occurrences of "reason_4.0" with "reason"
 * and changes "reason_4.0" directory to "reason"
 *
 * @package reason
 * @subpackage scripts
 */

/**
 * Include dependencies
 */
include_once('reason_header.php');
reason_include_once('classes/upgrade/upgrader_interface.php');

$GLOBALS['_reason_upgraders']['4.5_to_4.6']['update_reason_directory'] = 'ReasonUpgrader_46_UpdateReasonDirectory';

/**
 * Adds a custom deleter to the site type
 */
class ReasonUpgrader_46_UpdateReasonDirectory implements reasonUpgraderInterface
{
	protected $user_id;
	protected $site_type_entity;
	public function user_id( $user_id = NULL)
	{
		if(!empty($user_id))
			return $this->_user_id = $user_id;
		else
			return $this->_user_id;
	}
	/**
	 * Get the title of the upgrader
	 * @return string
	 */
	public function title()
	{
		return 'Update/Rename Reason Directory ';
	}
	/**
	 * Get a description of what this upgrade script will do
	 * @return string HTML description
	 */
	public function description()
	{
		return '<p>Upgrader that replaces occurrences of "reason_4.0" with "reason" and changes "reason_4.0" directory to "reason"</p>';
	}
	
	/**
	 * Upgrade already complete
	 * 
	 * @return Returns true if the reason_4.0 directory has already been replaced
	 */
	public function reasonDirectoryUpdateComplete()
	{
		// TODO: check and return false if the reason_4.0 directory exists
		if (file_exists($this->getReasonPackageDirectory()+'/reason_4.0'))
			return false;
		else
			return true;
	}

	/**
	 * Do a test run of the upgrader
	 * @return string HTML report
	 */
	public function test()
	{
		if($this->reasonDirectoryUpdateComplete())
			return '<p>The "reason_4.0" directory has already been changed to "reason"</p>';
		else
			return '<p>This update will replace all occurrences of reason_4.0 in files and rename the reason_4.0 directory to reason.</p>';
	}

	/**
	 * Get the reason_package directory
	 *
	 * @return reason_package directory
	 */
	protected function getReasonPackageDirectory()
	{
		return REASON_PACKAGE_HTTP_BASE_PATH;
	}

    /**
     * Run the upgrader
     * @return string HTML report
     */
	public function run()
	{
		if (reasonDirectoryUpdateComplete()) {
			return '<p>The "reason_4.0" directory has already been changed to "reason"</p>';
		}
		else 
		{
			$stringsearch = "reason_4.0";
			$stringreplace = "reason";
			$dir = getReasonPackageDirectory();
			SearchandReplace($dir,$stringsearch,$stringreplace);
			// TODO: execute code to rename the reason_4.0 directory to reason
		}
	}
	protected function SearchandReplace($dir, $stringsearch, $stringreplace)
	{
	echo "Starting search for $stringsearch within directory $dir";
		$listDir = array();
		if($handler = opendir($dir)) {
			while (($sub = readdir($handler)) !== FALSE) {
				if ($sub != "." && $sub != ".." && $sub != "Thumb.db") {
					if(is_file($dir."/".$sub)) {
						if(substr_count($sub,'.php'))
							{
							$getfilecontents = file_get_contents($dir."/".$sub);
							if(substr_count($getfilecontents,$stringsearch)>0)
							{
							$replacer = str_replace($stringsearch,$stringreplace,$getfilecontents);
							// Let's make sure the file exists and is writable first.
							  if (is_writable($dir."/".$sub)) {
								  if (!$handle = fopen($dir."/".$sub, 'w')) {
									   echo "Cannot open file (".$dir."/".$sub.")";
									   exit;
								  }
								  // Write $somecontent to our opened file.
								  if (fwrite($handle, $replacer) === FALSE) {
									  echo "Cannot write to file (".$dir."/".$sub.")";
									  exit;
								  }
								  echo "Success, removed searched content from:".$dir."/".$sub."";
								  fclose($handle);
							  } else {
								  echo "The file ".$dir."/".$sub." is not writable";
							  }
							}
							}
						$listDir[] = $sub;
					}elseif(is_dir($dir."/".$sub)){
						$listDir[$sub] = SearchandReplace($dir."/".$sub,$stringsearch,$stringreplace);
					}
				}
			}
			closedir($handler);
		}
		return $listDir;
	}
}
?>